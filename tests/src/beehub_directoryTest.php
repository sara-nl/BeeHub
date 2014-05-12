<?php
/**
 * Contains tests for the class BeeHub_Directory
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
 * Tests for the class BeeHub_Directory
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_DirectoryTest extends BeeHub_Tests_Db_Test_Case {

  /**
   * @var  \BeeHub_Directory  The unit under test
   */
  private $obj;


  public function setUp() {
    parent::setUp();
    if ( ! setUpStorageBackend() ) {
      $this->markTestSkipped( 'No storage backend specified; all tests depending on the storage backend are skipped' );
      return;
    }

    $sponsorA = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsorA->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorA->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_a' );
    $jane->storeProperties();
    
    $directory = new \BeeHub_Directory( '/foo/directory/' );
    $this->setCurrentUser( '/system/users/john' );
    $directory->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), false ) ) );
    // I need to reload the directory to avoid cache polution
    $this->obj = new \BeeHub_Directory( '/foo/directory/' );
  }


  public function testCreate_memberWithoutWritePrivilege() {
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), true ) ) );

    $this->setCurrentUser( '/system/users/jane' );
    $reloadedDir = new \BeeHub_Directory( '/foo/directory/' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $reloadedDir->create_member( 'nextfile.txt' );
  }


  public function testCreate_memberWithoutSponsor() {
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/johny', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );

    $this->setCurrentUser( '/system/users/johny' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->create_member( 'nextfile.txt' );
  }


  public function testCreate_memberWithoutCollectionSponsor() {
    $sponsorA = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsorA->change_memberships( array( 'jane' ), \BeeHub_Sponsor::DELETE_MEMBER );
    $sponsorB = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_b' );
    $jane->storeProperties();

    $this->setCurrentUser( '/system/users/jane' );
    $this->obj->create_member( 'nextfile.txt' );
    $file = \DAV::$REGISTRY->resource( $this->obj->path . 'nextfile.txt' );

    $this->assertSame( '/system/sponsors/sponsor_b', $file->user_prop_sponsor() );
    $this->assertSame( '/system/users/jane', $file->user_prop_owner() );
    $this->assertNotNull( $file->user_prop( \DAV::PROP_GETETAG ) );
  }


  public function testCreate_member() {
    $sponsorB = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_b' );
    $jane->storeProperties();

    $this->setCurrentUser( '/system/users/jane' );
    $this->obj->create_member( 'nextfile.txt', true );
    $file = \DAV::$REGISTRY->resource( $this->obj->path . 'nextfile.txt' );

    $this->assertSame( '/system/sponsors/sponsor_a', $file->user_prop_sponsor() );
    $this->assertSame( '/system/users/jane', $file->user_prop_owner() );
    $this->assertNotNull( $file->user_prop( \DAV::PROP_GETETAG ) );

    // And now it already exists, we should not be able to create it again
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->create_member( 'nextfile.txt' );
  }


  public function testIterator() {
    $obj = new \BeeHub_Directory( '/foo/' );
    $children = array();
    foreach ( $obj as $child ) {
      $children[] = $child;
    }

    $expected = array( 'file.txt', 'file2.txt', 'directory/', 'directory2/', 'client_tests/' );
    $this->assertSame( $expected, $children );
  }


  public function testMethod_COPYToUnexistingCollection() {
    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_CONFLICT );
    $this->obj->method_COPY( '/unexisting_directory/directory/' );
  }


  public function testMethod_COPYToSystemCollection() {
    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_COPY( '/system/users/directory/' );
  }


  public function testMethod_COPYWithoutWritePrivilegeDestination() {
    $this->setCurrentUser( '/system/users/jane' );
    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_COPY( '/bar/directory/' );
  }


  public function testMethod_COPYWithoutSponsor() {
    $bar = new \BeeHub_Directory( '/bar/' );
    $bar->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/johny', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $this->setCurrentUser( '/system/users/johny' );

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_COPY( '/bar/directory/' );
  }


  public function testMethod_COPYWithoutCollectionSponsor() {
    $bar = new \BeeHub_Directory( '/bar/' );
    $bar->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $this->setCurrentUser( '/system/users/jane' );
    $this->obj->method_COPY( '/bar/directory/' );

    $newDirectory = \DAV::$REGISTRY->resource( '/bar/directory/' );
    $this->assertNull( $newDirectory->user_prop_getetag() );
    $this->assertSame( '/system/users/jane', $newDirectory->user_prop_owner() );
    $this->assertSame( \BeeHub::getAuth()->current_user()->user_prop_sponsor(), $newDirectory->user_prop_sponsor() );
    $this->assertSame( array(), $newDirectory->user_prop_acl_internal() );
    $this->assertSame( $this->obj->user_prop( 'test_namespace test_property' ), $newDirectory->user_prop( 'test_namespace test_property' ) );
  }


  public function testMethod_COPY() {
    $sponsorB = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $bar = new \BeeHub_Directory( '/bar/' );
    $bar->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $this->setCurrentUser( '/system/users/jane' );
    $this->obj->method_COPY( '/bar/directory/' );

    $newDirectory = \DAV::$REGISTRY->resource( '/bar/directory/' );
    $this->assertNull( $newDirectory->user_prop_getetag() );
    $this->assertSame( '/system/users/jane', $newDirectory->user_prop_owner() );
    $this->assertSame( $bar->user_prop_sponsor(), $newDirectory->user_prop_sponsor() );
    $this->assertSame( array(), $newDirectory->user_prop_acl_internal() );
    $this->assertSame( $this->obj->user_prop( 'test_namespace test_property' ), $newDirectory->user_prop( 'test_namespace test_property' ) );
  }


  public function testMethod_DELETEforNonemptyDir() {
    $this->obj->create_member( 'some_file.txt' );
    $obj = new \BeeHub_Directory( '/foo' );

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_CONFLICT );
    $obj->method_DELETE( 'directory' );
  }


  public function testMethod_DELETEwithoutWritePrivilege() {
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), true ) ) );
    $obj = new \BeeHub_Directory( '/foo' );
    $obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $this->setCurrentUser( '/system/users/jane' );

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $obj->method_DELETE( 'directory' );
  }


  public function testMethod_DELETEforDirectory() {
    $obj = new \BeeHub_Directory( '/foo' );
    $obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), true ) ) );
    $this->setCurrentUser( '/system/users/jane' );

    $this->assertInstanceOf( '\BeeHub_Directory', \DAV::$REGISTRY->resource( '/foo/directory/' ) );
    $obj->method_DELETE( 'directory' );
    $this->assertNull( \DAV::$REGISTRY->resource( '/foo/directory/' ) );
  }


  public function testMethod_DELETEforFile() {
    $obj = new \BeeHub_Directory( '/foo' );
    $obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), true ) ) );
    $file = new \BeeHub_File( '/foo/file.txt' );
    $file->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $this->setCurrentUser( '/system/users/jane' );

    $this->assertInstanceOf( '\BeeHub_File', \DAV::$REGISTRY->resource( '/foo/file.txt' ) );
    $obj->method_DELETE( 'file.txt' );
    $this->assertNull( \DAV::$REGISTRY->resource( '/foo/file.txt' ) );
  }


  public function testMethod_GETWithoutReadPrivileges() {
    $this->setCurrentUser( '/system/users/johny' );
    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_GET();
  }


  public function testMethod_HEAD() {
    $headers = $this->obj->method_HEAD();
    $this->assertSame( 'no-cache', $headers['Cache-Control'] );
  }


  public function testMethod_MKCOLWithoutWritePrivilege() {
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), true ) ) );

    $this->setCurrentUser( '/system/users/jane' );
    $dir = new \BeeHub_Directory( '/foo/directory/' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $dir->method_MKCOL( 'subdirectory' );
  }


  public function testMethod_MKCOLWithoutSponsor() {
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/johny', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );

    $this->setCurrentUser( '/system/users/johny' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_MKCOL( 'subdirectory' );
  }


  public function testMethod_MKCOLWithoutCollectionSponsor() {
    $sponsorA = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsorA->change_memberships( array( 'jane' ), \BeeHub_Sponsor::DELETE_MEMBER );
    $sponsorB = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_b' );
    $jane->storeProperties();

    $this->setCurrentUser( '/system/users/jane' );
    $this->obj->method_MKCOL( 'subdirectory' );
    $subdirectory = \DAV::$REGISTRY->resource( $this->obj->path . 'subdirectory' );

    $this->assertSame( '/system/sponsors/sponsor_b', $subdirectory->user_prop_sponsor() );
    $this->assertSame( '/system/users/jane', $subdirectory->user_prop_owner() );
    $this->assertNull( $subdirectory->user_prop( \DAV::PROP_GETETAG ) );
  }


  public function testMethod_MKCOL() {
    $sponsorB = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_b' );
    $jane->storeProperties();

    $this->setCurrentUser( '/system/users/jane' );
    $this->obj->method_MKCOL( 'subdirectory' );
    $subdirectory = \DAV::$REGISTRY->resource( $this->obj->path . 'subdirectory' );

    $this->assertSame( '/system/sponsors/sponsor_a', $subdirectory->user_prop_sponsor() );
    $this->assertSame( '/system/users/jane', $subdirectory->user_prop_owner() );
    $this->assertNull( $subdirectory->user_prop( \DAV::PROP_GETETAG ) );

    // And now it already exists, we should not be able to create it again
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_MKCOL( 'subdirectory' );
  }


  public function testMethod_MOVEwithoutWritePrivilegeSource() {
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), true ) ) );
    $bar = new \BeeHub_Directory( '/bar' );
    $bar->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $this->setCurrentUser( '/system/users/jane' );
    $foo = new \BeeHub_Directory( '/foo' );

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $foo->method_MOVE( 'directory', '/bar/directory' );
  }


  public function testMethod_MOVEwithoutWritePrivilegeDestination() {
    $bar = new \BeeHub_Directory( '/bar' );
    $bar->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), true ) ) );
    $foo = new \BeeHub_Directory( '/foo' );
    $this->setCurrentUser( '/system/users/jane' );

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $foo->method_MOVE( 'directory', '/bar/directory' );
  }


  public function testMethod_MOVErename() {
    $foo = new \BeeHub_Directory( '/foo' );
    $foo->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $this->setCurrentUser( '/system/users/jane' );
    \DAV::$REGISTRY->forget( '/foo' );
    $fooReloaded = new \BeeHub_Directory( '/foo' );

    $propExpected = $this->obj->user_prop( 'test_namespace test_property' );
    $ownerExpected = $this->obj->user_prop_owner();
    $sponsorExpected = $this->obj->user_prop_sponsor();
    $aclExpected = $this->obj->user_prop_acl();

    $fooReloaded->method_MOVE( 'directory', '/foo/renamed_directory' );
    $renamedResource = \DAV::$REGISTRY->resource( '/foo/renamed_directory' );
    $this->assertSame( $propExpected, $renamedResource->user_prop( 'test_namespace test_property' ) );
    $this->assertSame( $ownerExpected, $renamedResource->user_prop_owner() );
    $this->assertSame( $sponsorExpected, $renamedResource->user_prop_sponsor() );
    $this->assertEqualEffectiveAcl( $aclExpected, $renamedResource->user_prop_acl() );
  }


  public function testMethod_MOVEtoOtherParent() {
    $bar = new \BeeHub_Directory( '/bar' );
    $bar->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false ) ) );
    $foo = new \BeeHub_Directory( '/foo' );
    $foo->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), true ) ) );
    $this->setCurrentUser( '/system/users/jane' );

    $propExpected = $this->obj->user_prop( 'test_namespace test_property' );
    $ownerExpected = $this->obj->user_prop_owner();
    $sponsorExpected = $this->obj->user_prop_sponsor();
    $aclExpected = $this->obj->user_prop_acl();
    $foo->method_MOVE( 'directory', '/bar/directory' );
    $renamedResource = \DAV::$REGISTRY->resource( '/bar/directory' );
    $this->assertSame( $propExpected, $renamedResource->user_prop( 'test_namespace test_property' ) );
    $this->assertSame( $ownerExpected, $renamedResource->user_prop_owner() );
    $this->assertSame( $sponsorExpected, $renamedResource->user_prop_sponsor() );
    $this->assertEqualEffectiveAcl( $aclExpected, $renamedResource->user_prop_acl() );
  }


  /**
   * Asserts whether two acl's are effectively the same
   *
   * @param   array  $expected  An (ordered) array of \DAVACL_Element_ace's
   * @param   array  $actual    An (ordered) array of \DAVACL_Element_ace's
   * @return  void
   */
  private function assertEqualEffectiveAcl( $expected, $actual ) {
    foreach ( $actual as $key => $ace ) {
      if ( ! isset( $expected[ $key ] ) ) {
        $this->assertEqualAce( new \DAVACL_Element_ace( \DAVACL::PRINCIPAL_ALL, false, array( \DAVACL::PRIV_ALL ), true ), $ace, 'If there are more actual ACE\'s than expected, the one after the last expected should deny everything to everybody' );
        return;
      }
      $this->assertEqualAce( $expected[ $key ], $ace );
    }

    if ( isset( $expected[ $key + 1 ] ) ) {
      $this->assertEqualAce( new \DAVACL_Element_ace( \DAVACL::PRINCIPAL_ALL, false, array( \DAVACL::PRIV_ALL ), true ), $expected[ $key + 1 ], 'If there are more expected ACE\'s than in the actual ACL, the one after the last actual should deny everything to everybody' );
    }
  }


  /**
   * Asserts whether two ace's are effectively the same
   *
   * @param   \DAVACL_Element_ace  $expected  The expected ACE
   * @param   \DAVACL_Element_ace  $actual    The actual ACE
   * @return  void
   */
  private function assertEqualAce( $expected, $actual ) {
    $this->assertSame( $expected->principal,  $actual->principal );
    $this->assertSame( $expected->invert,     $actual->invert );
    $this->assertSame( $expected->privileges, $actual->privileges );
    $this->assertSame( $expected->deny,       $actual->deny );
  }

} // class BeeHub_DirectoryTest

// End of file
