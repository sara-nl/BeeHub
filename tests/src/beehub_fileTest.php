<?php
/**
 * Contains tests for the class BeeHub_File
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
 * Tests for the class BeeHub_File
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_FileTest extends BeeHub_Tests_Db_Test_Case {

  /**
   * @var  \BeeHub_File  The unit under test
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
    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $foo->change_memberships( array( 'jane' ), \BeeHub_Group::USER_ACCEPT );
    $foo->change_memberships( array( 'jane' ), \BeeHub_Group::ADMIN_ACCEPT );
    $foo->change_memberships( array( 'jane' ), \BeeHub_Group::SET_ADMIN );
    $this->obj = new \BeeHub_File( '/foo/file.txt' );
    $_SERVER['REQUEST_URI'] = '/foo/file.txt';
  }


  public function testMethod_COPYwithoutReadPrivilege() {
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_READ ), true ) ) );
    $this->setCurrentUser( '/system/users/jane' );

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_COPY( '/bar/file.txt' );
  }


  public function testMethod_COPYwithoutWritePrivilegeDestinationCollection() {
    $this->setCurrentUser( '/system/users/john' );
    $bar = new \BeeHub_Directory( '/bar' );
    $bar->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), true ) ) );
    $this->setCurrentUser( '/system/users/jane' );

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_COPY( '/bar/file.txt' );
  }


  public function testMethod_COPYwithoutSponsor() {
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/johny', false, array( \DAVACL::PRIV_READ ), false ) ) );
    $bar = new \BeeHub_Directory( '/bar' );
    $bar->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/johny', false, array( \DAVACL::PRIV_WRITE ), false ) ) );
    $this->setCurrentUser( '/system/users/johny' );

    try {
      $this->obj->method_COPY( '/bar/file.txt' );
      $this->assertFalse( true, 'BeeHub_FILE::method_COPY() should throw a DAV_Status exception' );
    }catch( \DAV_Status $exception ) {
      $this->assertSame( \DAV::HTTP_FORBIDDEN, $exception->getCode() );
    }

    $this->assertNull( \DAV::$REGISTRY->resource( '/bar/file.txt' ) );
  }


  public function testMethod_COPYwithoutCollectionSponsor() {
    $this->setCurrentUser( '/system/users/john' );
    $bar = new \BeeHub_Directory( '/bar' );
    $bar->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), false ) ) );
    $this->setCurrentUser( '/system/users/jane' );

    $this->obj->method_COPY( '/bar/file.txt' );
    $copiedFile = \DAV::$REGISTRY->resource( '/bar/file.txt' );

    $this->assertSame( array(), $copiedFile->user_prop_acl_internal() );
    $this->assertNotSame( $this->obj->user_prop_getetag(), $copiedFile->user_prop_getetag() );
    $this->assertSame( '/system/users/jane', $copiedFile->user_prop_owner() );
    $this->assertSame( '/system/sponsors/sponsor_a', $copiedFile->user_prop_sponsor() );
  }


  public function testMethod_COPY() {
    $sponsorB = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::ADMIN_ACCEPT );
    $sponsorB->change_memberships( array( 'jane' ), \BeeHub_Sponsor::SET_ADMIN );
    $this->setCurrentUser( '/system/users/john' );
    $bar = new \BeeHub_Directory( '/bar' );
    $bar->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), false ) ) );
    $this->setCurrentUser( '/system/users/jane' );

    $this->obj->method_COPY( '/bar/file.txt' );
    $copiedFile = \DAV::$REGISTRY->resource( '/bar/file.txt' );

    $this->assertSame( array(), $copiedFile->user_prop_acl_internal() );
    $this->assertNotSame( $this->obj->user_prop_getetag(), $copiedFile->user_prop_getetag() );
    $this->assertSame( '/system/users/jane', $copiedFile->user_prop_owner() );
    $this->assertSame( '/system/sponsors/sponsor_b', $copiedFile->user_prop_sponsor() );
  }
  
  
  const STREAM_CONTENT = 'some content of the input stream';


  private static function createInputStream() {
    $stream = \fopen( "php://temp", 'rw' );
    \fwrite( $stream, self::STREAM_CONTENT );
    \rewind( $stream );
    return $stream;
  }


  public function testMethod_PUTwithoutWritePrivilege() {
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), true ) ) );
    $this->setCurrentUser( '/system/users/jane' );

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_PUT( self::createInputStream() );
  }


  public function testMethod_PUTwithTooShortBody() {
    unset( $_SERVER['HTTP_X_EXPECTED_ENTITY_LENGTH'] );
    $_SERVER['CONTENT_LENGTH'] = strlen( self::STREAM_CONTENT ) + 1;
    $this->setCurrentUser( '/system/users/jane' );
    \DAV::$CHUNK_SIZE = 7;

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->method_PUT( self::createInputStream() );
  }


  public function testMethod_PUTandGETwithoutLengthHeader() {
    unset( $_SERVER['HTTP_X_EXPECTED_ENTITY_LENGTH'], $_SERVER['CONTENT_LENGTH'] );
    $this->setCurrentUser( '/system/users/jane' );
    \DAV::$CHUNK_SIZE = 7;

    $oldEtag = $this->obj->user_prop_getetag();
    $this->obj->method_PUT( self::createInputStream() );
    $this->assertNotSame( $oldEtag, $this->obj->user_prop_getetag() );
    $this->assertSame( self::STREAM_CONTENT, \stream_get_contents( $this->obj->method_GET() ) );
    $this->assertSame( \strlen( self::STREAM_CONTENT ), $this->obj->user_prop_getcontentlength() );
    // I am not testing content-type as I feel I am too much testing PHP
    // internals to determine the content-type of a file. Especially because
    // below are seperate tests to see if I can set the file type.
  }


  public function testMethod_PUTandGET() {
    unset( $_SERVER['HTTP_X_EXPECTED_ENTITY_LENGTH'] );
    $_SERVER['CONTENT_LENGTH'] = strlen( self::STREAM_CONTENT );
    $this->setCurrentUser( '/system/users/jane' );
    \DAV::$CHUNK_SIZE = 7;

    $oldEtag = $this->obj->user_prop_getetag();
    $this->obj->method_PUT( self::createInputStream() );
    $this->assertNotSame( $oldEtag, $this->obj->user_prop_getetag() );
    $this->assertSame( self::STREAM_CONTENT, \stream_get_contents( $this->obj->method_GET() ) );
    $this->assertSame( \strlen( self::STREAM_CONTENT ), $this->obj->user_prop_getcontentlength() );
    // I am not testing content-type as I feel I am too much testing PHP
    // internals to determine the content-type of a file. Especially because
    // below are seperate tests to see if I can set the file type.
  }


  public function testMethod_PUT_rangeWithoutWritePrivilege() {
    $this->setCurrentUser( '/system/users/john' );
    $this->obj->user_set_acl( array( new \DAVACL_Element_ace( '/system/users/jane', false, array( \DAVACL::PRIV_WRITE ), true ) ) );
    $this->setCurrentUser( '/system/users/jane' );

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_PUT_range( self::createInputStream(), 26, \strlen( self::STREAM_CONTENT ), null );
  }


  public function testMethod_PUT_rangeWithoutLengthHeader() {
    unset( $_SERVER['HTTP_X_EXPECTED_ENTITY_LENGTH'], $_SERVER['CONTENT_LENGTH'] );
    $this->setCurrentUser( '/system/users/jane' );

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_LENGTH_REQUIRED );
    $this->obj->method_PUT_range( self::createInputStream(), 26, \strlen( self::STREAM_CONTENT ), null );
  }


  public function testMethod_PUT_rangeWithWrongLengthHeader() {
    unset( $_SERVER['HTTP_X_EXPECTED_ENTITY_LENGTH'] );
    $_SERVER['CONTENT_LENGTH'] = strlen( self::STREAM_CONTENT ) - 1;
    $this->setCurrentUser( '/system/users/jane' );

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->method_PUT_range( self::createInputStream(), 26, \strlen( self::STREAM_CONTENT ) + 25, null );
  }


  public function testMethod_PUT_rangeWithTooLongBody() {
    unset( $_SERVER['HTTP_X_EXPECTED_ENTITY_LENGTH'] );
    $_SERVER['CONTENT_LENGTH'] = strlen( self::STREAM_CONTENT ) + 1;
    $this->setCurrentUser( '/system/users/jane' );

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->method_PUT_range( self::createInputStream(), 26, \strlen( self::STREAM_CONTENT ) + 26, null );
  }


  public function testMethod_PUT_rangeAndGETappend() {
    unset( $_SERVER['HTTP_X_EXPECTED_ENTITY_LENGTH'] );
    $_SERVER['CONTENT_LENGTH'] = strlen( self::STREAM_CONTENT );
    $this->setCurrentUser( '/system/users/jane' );

    $oldEtag = $this->obj->user_prop_getetag();
    $oldContent = stream_get_contents( $this->obj->method_GET() );
    $this->obj->method_PUT_range( self::createInputStream(), 26, strlen( self::STREAM_CONTENT ) + 25, null );
    $this->assertNotSame( $oldEtag, $this->obj->user_prop_getetag() );
    $this->assertSame( $oldContent . self::STREAM_CONTENT, \stream_get_contents( $this->obj->method_GET() ) );
    $this->assertSame( \strlen( $oldContent ) + \strlen( self::STREAM_CONTENT ), $this->obj->user_prop_getcontentlength() );
  }


  public function testMethod_PUT_rangeAndGETinTheMiddle() {
    unset( $_SERVER['HTTP_X_EXPECTED_ENTITY_LENGTH'] );
    $_SERVER['CONTENT_LENGTH'] = 5;
    $this->setCurrentUser( '/system/users/jane' );
    $stream = \fopen( "php://temp", 'rw' );
    \fwrite( $stream, '12345' );
    \rewind( $stream );

    $oldEtag = $this->obj->user_prop_getetag();
    $oldContent = stream_get_contents( $this->obj->method_GET() );
    $expectedContent = substr( $oldContent, 0, 5 ) . '12345' . substr( $oldContent, 10 );

    $this->obj->method_PUT_range( $stream, 5, 9, \strlen( $expectedContent ) );
    $this->assertNotSame( $oldEtag, $this->obj->user_prop_getetag() );
    $this->assertSame( $expectedContent, \stream_get_contents( $this->obj->method_GET() ) );
    $this->assertSame( \strlen( $expectedContent ), $this->obj->user_prop_getcontentlength() );
  }


  public function testProp_executable() {
    $this->assertNull( $this->obj->user_prop_executable() );
    $this->obj->method_PROPPATCH( \DAV::PROP_EXECUTABLE2, 'T' );
    $this->assertTrue( $this->obj->user_prop_executable() );
    $this->obj->method_PROPPATCH( \DAV::PROP_EXECUTABLE2, '' );
    $this->assertFalse( $this->obj->user_prop_executable() );
    $this->obj->method_PROPPATCH( \DAV::PROP_EXECUTABLE2, null );
    $this->assertNull( $this->obj->user_prop_executable() );
  }


  public function testProp_getcontentlanguage() {
    $this->assertNull( $this->obj->user_prop_getcontentlanguage() );
    $this->obj->method_PROPPATCH( \DAV::PROP_GETCONTENTLANGUAGE, "NL-nl" );
    $this->assertSame( "NL-nl", $this->obj->user_prop_getcontentlanguage() );
  }


  public function testProp_getcontenttype() {
    $this->assertSame( 'text/plain; charset=UTF-8', $this->obj->user_prop_getcontenttype() );
    $this->obj->method_PROPPATCH( \DAV::PROP_GETCONTENTTYPE, 'application/octet-stream' );
    $this->assertSame( 'application/octet-stream', $this->obj->user_prop_getcontenttype() );
  }


  public function testUser_prop_getcontentlength() {
    $this->assertSame( 26, $this->obj->user_prop_getcontentlength() );
  }


  public function testUser_prop_getetag() {
    $this->assertSame( '"EA"', $this->obj->user_prop_getetag() );
  }

} // class BeeHub_FileTest

// End of file