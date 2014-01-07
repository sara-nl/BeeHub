<?php
/**
 * Contains tests for the class BeeHub_Principal_Collection
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
 * Tests for the class BeeHub_Principal_Collection
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_Principal_CollectionTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var  BeeHub_Principal_Collection  The unit under test
   */
  private $obj;


  public function setUp() {
    parent::setUp();
    setUp();
    $this->obj = $this->getMock( '\BeeHub_Principal_Collection', array( 'init_members', 'report_principal_property_search' ), array( '/system/users' ) );
    $this->obj->expects( $this->any() )
              ->method( 'init_members' );
    $this->obj->expects( $this->any() )
              ->method( 'report_principal_property_search' );
  }


  public function testCreate_member() {
    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->create_member( 'test' );
  }


  public function testMethod_DELETE() {
    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_DELETE( 'john' );
  }


// Because these methods assert privileges, they need to read the ACL of parent
// collections. Therefore xattributes are tested. Because not all test systems
// have a file system installed, I have commented these tests out. On a
// production system they should run however. Plus, when we moved to an noSQL
// implementation, it should also work (because the ACL is then stored in the
// database
// 
//  public function testMethod_HEAD() {
//    $headers = $this->obj->method_HEAD();
//    $this->assertSame( 'no-cache', $headers['Cache-Control'] );
//  }


  public function testMethod_MKCOL() {
    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_MKCOL( 'john' );
  }


  public function testMethod_MOVE() {
    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_MOVE( 'john', '/home/john' );
  }


  public function testReport_principal_search_property_set() {
    $this->assertSame( array( \DAV::PROP_DISPLAYNAME => 'Name'), $this->obj->report_principal_search_property_set() );
  }


  public function testUser_prop_acl_internal() {
    $expected = array( new \DAVACL_Element_ace( \DAVACL::PRINCIPAL_AUTHENTICATED, false, array( \DAVACL::PRIV_READ ), false, false ) );
    $this->assertEquals( $expected, $this->obj->user_prop_acl_internal() );
  }

} // class BeeHub_Principal_Collection

// End of file