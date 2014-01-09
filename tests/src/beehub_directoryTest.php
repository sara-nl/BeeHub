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
   * @var  \BeeHub_XFSResource  The unit under test
   */
  private $obj;


  public function setUp() {
    parent::setUp();
    if ( ! setUpStorageBackend() ) {
      $this->markTestSkipped( 'No storage backend specified; all tests depending on the storage backend are skipped' );
      return;
    }

    $sponsorA = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsorA->change_memberships( array( 'jane' ), true, true, true, true );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_a' );
    $jane->storeProperties();
    
    $this->obj = new \BeeHub_Directory( '/foo/directory/' );
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), false ) ) );
  }


  public function testCreate_memberWithoutWritePrivilege() {
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), true ) ) );

    $this->setCurrentUser( '/system/users/jane' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->create_member( 'nextfile.txt' );
  }


  public function testCreate_memberWithoutSponsor() {
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/johny', false, array( \DAVACL::PRIV_WRITE, \DAVACL::PRIV_WRITE ), false ) ) );

    $this->setCurrentUser( '/system/users/johny' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->create_member( 'nextfile.txt' );
  }


  public function testCreate_memberWithoutCollectionSponsor() {
    $sponsorA = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsorA->change_memberships( array( 'jane' ), false, false, false, false );
    $sponsorB = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsorB->change_memberships( array( 'jane' ), true, true, true, true );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_b' );
    $jane->storeProperties();

    $this->setCurrentUser( '/system/users/jane' );
    $this->obj->create_member( 'nextfile.txt' );
    $subdirectory = \DAV::$REGISTRY->resource( $this->obj->path . 'nextfile.txt' );

    $this->assertSame( '/system/sponsors/sponsor_b', $subdirectory->user_prop_sponsor() );
    $this->assertSame( '/system/users/jane', $subdirectory->user_prop_owner() );
    $this->assertNotNull( $subdirectory->user_prop( \DAV::PROP_GETETAG ) );
  }


  public function testCreate_member() {
    $sponsorB = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsorB->change_memberships( array( 'jane' ), true, true, true, true );
    $jane = new \BeeHub_User( '/system/users/jane' );
    $jane->user_set_sponsor( '/system/sponsors/sponsor_b' );
    $jane->storeProperties();

    $this->setCurrentUser( '/system/users/jane' );
    $this->obj->create_member( 'nextfile.txt' );
    $subdirectory = \DAV::$REGISTRY->resource( $this->obj->path . 'nextfile.txt' );

    $this->assertSame( '/system/sponsors/sponsor_a', $subdirectory->user_prop_sponsor() );
    $this->assertSame( '/system/users/jane', $subdirectory->user_prop_owner() );
    $this->assertNotNull( $subdirectory->user_prop( \DAV::PROP_GETETAG ) );

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

    $expected = array( 'file.txt', 'directory/' );
    $this->assertSame( $expected, $children );
  }


  private function internal_create_member( $name, $collection = false ) {
    $this->assert(DAVACL::PRIV_WRITE);
    $path = $this->path . $name;
    $localPath = BeeHub::localPath( $path );
    $cups = $this->current_user_principals();

    // Determine the sponsor
    $user = BeeHub::getAuth()->current_user();
    $user_sponsors = $user->prop(BeeHub::PROP_SPONSOR_MEMBERSHIP);
    if (count($user_sponsors) == 0) { // If the user doesn't have any sponsors, he/she can't create files and directories
      throw DAV::forbidden();
    }
    $sponsor = $this->prop(BeeHub::PROP_SPONSOR); // The default is the directory sponsor
    if (!in_array($sponsor, $user_sponsors)) { //But a user can only create files sponsored by his own sponsors
      $sponsor = $user->user_prop(BeeHub::PROP_SPONSOR);
    }

    // Create the subdirectory or file
    if (file_exists($localPath))
      throw DAV::forbidden();
    $result = $collection ? @mkdir($localPath) : touch($localPath);
    if ( !$result )
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);

    // And set the xattributes
    xattr_set( $localPath, rawurlencode( DAV::PROP_GETETAG), BeeHub_DB::ETag() );
    xattr_set( $localPath, rawurlencode( DAV::PROP_OWNER  ), $this->user_prop_current_user_principal() );
    xattr_set( $localPath, rawurlencode( BeeHub::PROP_SPONSOR ), $sponsor );
    return DAV::$REGISTRY->resource( $path );
  }


  public function method_COPY( $path ) {
    $parent = DAV::$REGISTRY->resource( dirname( $path ) );
    if (!$parent)
      throw new DAV_Status(DAV::HTTP_CONFLICT, 'Unable to COPY to unexisting collection');
    if (!$parent instanceof BeeHub_Directory)
      throw new DAV_Status(DAV::HTTP_FORBIDDEN);
    $parent->internal_create_member(basename($path), true);
    // TODO: Should we check here if the xattr to be copied is in the 'user.' realm?
    foreach(xattr_list($this->localPath) as $xattr)
      if ( !in_array( rawurldecode($xattr), array(
        DAV::PROP_GETETAG,
        DAV::PROP_OWNER,
        DAV::PROP_GROUP,
        BeeHub::PROP_SPONSOR,
        DAV::PROP_ACL,
        DAV::PROP_LOCKDISCOVERY
      ) ) )
        xattr_set( $localPath, $xattr, xattr_get( $this->localPath, $xattr ) );
  }


  public function method_DELETE( $name )
  {
    $path = $this->path . $name;
    $localpath = BeeHub::localPath( $path );
    $resource = DAV::$REGISTRY->resource( $path );
    $resource->assert(DAVACL::PRIV_WRITE);
    if (is_dir($localpath)) {
      if (!@rmdir($localpath))
        throw new DAV_Status(DAV::HTTP_CONFLICT, 'Unable to DELETE resource: ' . $name);
    }
    else {
      if (!@unlink($localpath))
        throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }
    DAV::$REGISTRY->forget( $path );
  }


  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET() {
    $this->assert(DAVACL::PRIV_READ);
    $this->include_view();
  }


  public function method_HEAD() {
    $retval = parent::method_HEAD();
    $retval['Cache-Control'] = 'no-cache';
    return $retval;
  }

  /**
   * @param string $name
   * @throws DAV_Status
   */
  public function method_MKCOL( $name ) {
    return $this->internal_create_member( $name, true );
  }


  public function method_MOVE( $member, $destination ) {
    // Get the ACL of the source (including inherited ACE's)
    $sourceAcl = DAV::$REGISTRY->resource( $this->path . $member )->user_prop_acl();

    // Determine if moving is allowed and if so, move the object
    DAV::$REGISTRY->resource( $this->path . $member )->assert( DAVACL::PRIV_WRITE );
    DAV::$REGISTRY->resource( dirname($destination) )->assert( DAVACL::PRIV_WRITE );
    $localDest = BeeHub::localPath($destination);
    rename(
      BeeHub::localPath( $this->path . $member ),
      $localDest
    );

    // We need to make sure that the effective ACL at the destination is the same as at the resource
    $destinationAcl = array();
    $inheritedAcl = array();
    $copyInherited = true;
    foreach ( $sourceAcl as $ace ) {
      if ( $ace->protected ) { // Protected ACE's don't require copying; at this moment all resources have the same protected resources
        continue;
      }
      if ( $ace->inherited ) { // Inherited ACE's don't always need to be copied, so let's store them seperately for now
        $ace->inherited= null;
        $inheritedAcl[] = $ace;
      }else{
        // If there is already a 'deny all to everybody' ACE in the ACL, then no need to copy any inherited ACL's
        if ( ( $ace->principal === DAVACL::PRINCIPAL_ALL ) &&
             ! $ace->invert &&
             in_array( DAVACL::PRIV_ALL, $ace->privileges ) &&
             $ace->deny
        ) {
          $copyInherited = false;
        }
        $destinationAcl[] = $ace;
      }
    }

    $destinationResource = DAV::$REGISTRY->resource( $destination );

    // If the inherited ACE's at the destination are the same as at the source, then no need to copy them (for example when moving within the same directory). The effective ACL will still be the same
    if ( $copyInherited ) {
      $oldDestinationAcl = $destinationResource->user_prop_acl();
      $destinationInheritedAcl = array();
      $copyInherited = false;
      foreach ( $oldDestinationAcl as $ace ) {
        if ( ! $ace->inherited ) {
          continue;
        }
        if ( ( count( $inheritedAcl) > 0 ) &&
             ( $ace->principal === $inheritedAcl[0]->principal ) &&
             ( $ace->invert === $inheritedAcl[0]->invert ) &&
             ( $ace->deny === $inheritedAcl[0]->deny ) &&
             ( $ace->privileges === $inheritedAcl[0]->privileges )
        ) {
          array_shift( $inheritedAcl );
        }else{
          $copyInherited = true;
          break;
        }
      }
    }

    // If needed; copy the inherited ACE's so we have the complete ACL of the source. And end it with a 'deny all to everybody' ACE so inherited ACE's at the destination don't change the effective ACL
    if ( $copyInherited ) {
      $destinationAcl = array_merge( $destinationAcl, $inheritedAcl );
      $destinationAcl[] = new DAVACL_Element_ace( DAVACL::PRINCIPAL_ALL, false, array( DAVACL::PRIV_ALL ), true, false, null );
    }

    // And store the ACL at the destination
    $destinationResource->user_set( DAV::PROP_ACL, ( $destinationAcl ? DAVACL_Element_ace::aces2json( $destinationAcl ) : null ) );
    $destinationResource->storeProperties();
  }

} // class BeeHub_DirectoryTest

// End of file