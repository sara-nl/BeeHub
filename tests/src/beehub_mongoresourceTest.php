<?php
/**
 * Contains tests for the class BeeHub_MongoResource
 *
 * Copyright ©2007-2014 SURFsara b.v., Amsterdam, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at <http://www.apache.org/licenses/LICENSE-2.0>
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package     BeeHub
 * @subpackage  tests
 */

declare( encoding = 'UTF-8' );
namespace BeeHub\tests;

/**
 * Tests for the class BeeHub_MongoResource
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_MongoResourceTest extends BeeHub_Tests_Db_Test_Case {

  /**
   * @var  \BeeHub_MongoResource  The unit under test
   */
  private $obj;


  public function setUp() {
    parent::setUp();
    if ( ! setUpStorageBackend() ) {
      $this->markTestSkipped( 'No storage backend specified; all tests depending on the storage backend are skipped' );
      return;
    }

    $this->obj = new \BeeHub_MongoResource( '/foo/file.txt' );
  }


  public function testStoreProperties() {
    $value = 'Some random value';
    $property = 'test_namespace test_property';

    $this->assertSame( 'this is a random dead property', $this->obj->user_prop( $property ) );

    // Set some random property
    $this->obj->method_PROPPATCH( $property, $value );
    $this->obj->storeProperties();

    // Now, if I create a new instance of \BeeHub_MongoResource for the same file, it should have the property set
    $fileReloaded = new \BeeHub_MongoResource( '/foo/file.txt' );
    $this->assertSame( $value, $fileReloaded->user_prop( $property ) );

    // Delete the property
    $fileReloaded->method_PROPPATCH( $property );
    $fileReloaded->storeProperties();

    // Now, if I create yet another instance of \BeeHub_MongoResource for the same file, it should not have the property set
    $fileAgainReloaded = new \BeeHub_MongoResource( '/foo/file.txt' );
    $this->assertNull( $fileAgainReloaded->user_prop( $property ) );
  }


  public function testUser_prop_acl_internal() {
    $expected = array( new \DAVACL_Element_ace( '/system/groups/bar', false, array( \DAVACL::PRIV_READ ), false ) );
    $this->assertEquals( $expected, $this->obj->user_prop_acl_internal() );
  }


  public function testUser_prop_displayname() {
    $this->assertSame( 'file.txt', $this->obj->user_prop_displayname() );
  }


  public function testUser_prop_getetag() {
    $this->assertSame( '"EA"', $this->obj->user_prop_getetag() );
  }


  public function testUser_prop_getlastmodified() {
    // We touch the file (updating the 'last modified' timestamp) and reload
    // the resource. The difference between the getlastmodified property and
    // the current time should not be greater than 1 second. Else either the
    // property is not loaded correctly or it takes too long to load the
    // resource. This last case is actually not an error in
    // user_prop_getlastmodified, but a problem nevertheless
    touch( \BeeHub::localPath( '/foo/file.txt' ) );
    $file = new \BeeHub_MongoResource( '/foo/file.txt' );
    $this->assertLessThan( 1, $file->user_prop_getlastmodified() - time() );
  }


  public function testUser_propname() {
    $this->assertSame( array( 'test_namespace test_property' => true ), $this->obj->user_propname() );

    $property = 'some_namespace some_property';
    $expected = array( 'test_namespace test_property' => true, $property => true );
    $this->obj->method_PROPPATCH( $property, 'random value' );
    $this->assertSame( $expected, $this->obj->user_propname() );
  }


  public function testUser_set_acl() {
    $acl = array( new \DAVACL_Element_ace( '/system/users/johny', false, array( \DAVACL::PRIV_READ ), false ) );

    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set_acl( $acl );
    $this->assertEquals( $acl, $this->obj->user_prop_acl_internal() );

    $this->setCurrentUser( '/system/users/jane' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->user_set_acl( $acl );
  }


  public function testSetDisplayname() {
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_PROPPATCH( \DAV::PROP_DISPLAYNAME, 'Some new displayname' );
  }


  public function testChangeOwnerToUnexisting() {
    $this->setCurrentUser( '/system/users/john' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/unexisting_user</D:href>' );
  }


  public function testChangeOwnerUnauthenticated() {
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/jane</D:href>' );
  }


  public function testGiveAwayResourceWithoutMatchingSponsor() {
    $this->setCurrentUser( '/system/users/john' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/jane</D:href>' );
  }


  public function testGiveAwayResource() {
    $this->setCurrentUser( '/system/users/john' );
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsor->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsor->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );

    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/jane</D:href>' );
    $this->assertSame( '/system/users/jane', $this->obj->user_prop( \DAV::PROP_OWNER ) );
  }


  public function testBecomeOwnerNoResourceWritePriv() {
    // Make sure Jane has write privilege on the collection
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->collection()->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), true ) ) );
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsor->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsor->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_a' );
    $jane->storeProperties();

    $this->setCurrentUser( '/system/users/jane' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/jane</D:href>' );
  }


  public function testBecomeOwnerNoCollectionWritePriv() {
    // Make sure Jane has write privilege on the resource, but not on the collection
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->collection()->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), true ) ) );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsor->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsor->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_a' );
    $jane->storeProperties();

    $this->setCurrentUser( '/system/users/jane' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/jane</D:href>' );
  }


  public function testBecomeOwnerWrongSponsoredResource() {
    // Make sure Jane has write privilege on the resource, but not on the collection
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set( \BeeHub::PROP_SPONSOR, '/system/sponsors/sponsor_c' );
    $this->obj->collection()->user_set( \BeeHub::PROP_SPONSOR, '/system/sponsors/sponsor_b' );
    $this->obj->collection()->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $sponsorA = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsorA->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorA->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $sponsorB = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_a' );
    $jane->storeProperties();

    $this->setCurrentUser( '/system/users/jane' );
    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/jane</D:href>' );

    // Jane is sponsored by sponsor A and B, the resource by sponsor C, so this
    // should change; the collection is sponsored by resource B, so that's the
    // right choice (it takes precendence over the default sponsor of the user)
    $this->assertSame( '/system/sponsors/sponsor_b', $this->obj->user_prop_sponsor() );
    $this->assertSame( '/system/users/jane', $this->obj->user_prop_owner() );
  }


  public function testBecomeOwnerWrongSponsoredResourceAndCollection() {
    // Make sure Jane has write privilege on the resource, but not on the collection
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set( \BeeHub::PROP_SPONSOR, '/system/sponsors/sponsor_c' );
    $this->obj->collection()->user_set( \BeeHub::PROP_SPONSOR, '/system/sponsors/sponsor_c' );
    $this->obj->collection()->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $sponsorA = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsorA->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorA->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $sponsorB = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_a' );
    $jane->storeProperties();

    $this->setCurrentUser( '/system/users/jane' );
    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/jane</D:href>' );

    // Jane is sponsored by sponsor A and B, the resource by sponsor C, so this
    // should change; the collection is sponsored by resource C, so that's not
    // right, so it should take the default sponsor of the user
    $this->assertSame( '/system/sponsors/sponsor_a', $this->obj->user_prop_sponsor() );
    $this->assertSame( '/system/users/jane', $this->obj->user_prop_owner() );
  }


  public function testBecomeOwnerWithoutSponsor() {
    // Make sure Jane has write privilege on the resource, but not on the collection
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set( \BeeHub::PROP_SPONSOR, '/system/sponsors/sponsor_c' );
    $this->obj->collection()->user_set( \BeeHub::PROP_SPONSOR, '/system/sponsors/sponsor_c' );
    $this->obj->collection()->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );

    // Because Jane is not sponsored, she can not become the owner
    $this->setCurrentUser( '/system/users/jane' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/jane</D:href>' );
  }


  public function testBecomeOwner() {
    // Make sure Jane has write privilege on the resource, but not on the collection
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set( \BeeHub::PROP_SPONSOR, '/system/sponsors/sponsor_b' );
    $this->obj->collection()->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $sponsorA = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsorA->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorA->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $sponsorB = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_a' );
    $jane->storeProperties();

    $this->setCurrentUser( '/system/users/jane' );
    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/jane</D:href>' );

    // Both Jane and the object are sponsored by sponsor B, so no need to change it
    $this->assertSame( '/system/sponsors/sponsor_b', $this->obj->user_prop_sponsor() );
    $this->assertSame( '/system/users/jane', $this->obj->user_prop_owner() );
  }


  public function testChangeSponsorToUnexisting() {
    $this->setCurrentUser( '/system/users/john' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->method_PROPPATCH( \BeeHub::PROP_SPONSOR, '<D:href>/system/sponsors/sponsor_c</D:href>' );
  }


  public function testChangeSponsorOfUnownedResource() {
    $this->setCurrentUser( '/system/users/john' );
    $sponsorA = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsorA->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorA->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );

    $this->setCurrentUser( '/system/users/jane' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_PROPPATCH( \BeeHub::PROP_SPONSOR, '<D:href>/system/sponsors/sponsor_a</D:href>' );
  }


  public function testChangeSponsorToUnsponsoredSponsor() {
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsor->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsor->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $this->obj->user_set( \DAV::PROP_OWNER, '/system/users/jane' );

    $this->setCurrentUser( '/system/users/jane' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_PROPPATCH( \BeeHub::PROP_SPONSOR, '<D:href>/system/sponsors/sponsor_a</D:href>' );
  }


  public function testChangeSponsor() {
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsor->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsor->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $this->obj->user_set( \DAV::PROP_OWNER, '/system/users/jane' );

    $this->setCurrentUser( '/system/users/jane' );
    $this->obj->method_PROPPATCH( \BeeHub::PROP_SPONSOR, '<D:href>/system/sponsors/sponsor_b</D:href>' );

    $this->assertSame( '/system/sponsors/sponsor_b', $this->obj->user_prop_sponsor() );
  }

} // class BeeHub_MongoResourceTest

// End of file