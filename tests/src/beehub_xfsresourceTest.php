<?php
/**
 * Contains tests for the class BeeHub_XFSResource
 *
 * Copyright ©2007-2013 SURFsara b.v., Amsterdam, The Netherlands
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
 * Tests for the class BeeHub_XFSResource
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_XFSResourceTest extends BeeHub_Tests_Db_Test_Case {

  /**
   * @var  \BeeHub_XFSResource  The unit under test
   */
  private $obj;


  public function setUp() {
    parent::setUp();
    if ( ! setUpStorageBackend() ) {
      $this->markTestSkipped( 'No storage backend specified; all tests depending on the storage backend are skipped' );
      return;
    }

    $this->obj = new \BeeHub_XFSResource( '/foo/file.txt' );
  }


  public function testStoreProperties() {
    $value = 'Some random value';
    $property = 'test_namespace test_property';

    $this->assertNull( $this->obj->user_prop( $property ) );

    // Set some random property
    $this->obj->method_PROPPATCH( $property, $value );
    $this->obj->storeProperties();

    // Now, if I create a new instance of \BeeHub_XFSResource for the same file, it should have the property set
    $fileReloaded = new \BeeHub_XFSResource( '/foo/file.txt' );
    $this->assertSame( $value, $fileReloaded->user_prop( $property ) );

    // Delete the property
    $fileReloaded->method_PROPPATCH( $property );
    $fileReloaded->storeProperties();

    // Now, if I create yet another instance of \BeeHub_XFSResource for the same file, it should not have the property set
    $fileAgainReloaded = new \BeeHub_XFSResource( '/foo/file.txt' );
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
    $file = new \BeeHub_XFSResource( '/foo/file.txt' );
    $this->assertLessThan( 1, $file->user_prop_getlastmodified() - time() );
  }


  public function testUser_propname() {
    $this->assertSame( array(), $this->obj->user_propname() );

    $property = 'some_namespace some_property';
    $expected = array( $property => true );
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
    $sponsor->change_memberships( array( 'jane' ), true, true, true, true );

    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/jane</D:href>' );
    $this->assertSame( '/system/users/jane', $this->obj->user_prop( \DAV::PROP_OWNER ) );
  }


//  public function testBecomeOwnerNoResourceWritePriv() {
//    $this->setCurrentUser( '/system/users/jane' );
//    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
//    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/unexisting_user</D:href>' );
//  }
//
//
//  public function testBecomeOwnerNoCollectionWritePriv() {
//    $this->setCurrentUser( '/system/users/jane' );
//    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
//    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/unexisting_user</D:href>' );
//  }
//
//
//  public function testBecomeOwnerWrongSponsoredResource() {
//    $this->setCurrentUser( '/system/users/jane' );
//    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
//    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/unexisting_user</D:href>' );
//  }
//
//
//  public function testBecomeOwnerWithoutSponsor() {
//    $this->setCurrentUser( '/system/users/johny' );
//    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
//    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/unexisting_user</D:href>' );
//  }


//  public function testBecomeOwner() {
//    $this->setCurrentUser( '/system/users/jane' );
//    // Jane should have the same sponsor as the file
//    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
//    $sponsor->change_memberships( array( 'jane' ), true, true, true, true );
//
//    // The group 'foo' has write privileges on the collection and the file itself
//    $foo = new \BeeHub_Group( '/system/groups/foo' );
//    $foo->change_memberships( array( 'jane' ), true, true, true, true, true, true );
//
//    // So Jane should now be able to become owner of the file
//    $this->obj->method_PROPPATCH( \DAV::PROP_OWNER, '<D:href>/system/users/jane</D:href>' );
//  }


  protected function user_set_sponsor($sponsor) {
    $this->assert(DAVACL::PRIV_READ);

    // No (correct) sponsor given? Bad request!
    if ( ! ( $sponsor = BeeHub_Registry::inst()->resource($sponsor) ) ||
         ! $sponsor instanceof BeeHub_Sponsor ||
         ! $sponsor->isVisible() )
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST
      );

    // Only the resource owner (or an administrator) can change the sponsor
    if ( $this->user_prop_owner() !==
           $this->user_prop_current_user_principal() &&
         ! DAV::$ACLPROVIDER->wheel() )
      throw DAV::forbidden( 'Only the owner can change the sponsor of a resource.' );

    // And I can only change the sponsor into a sponsor that sponsors me
    if ( !in_array( $sponsor->path, BeeHub::getAuth()->current_user()->current_user_sponsors() ) )
      throw DAV::forbidden( "You're not sponsored by {$sponsor->path}" );
      
    return $this->user_set( BeeHub::PROP_SPONSOR, $sponsor->path);
  }


} // class BeeHub_XFSResourceTest

// End of file
