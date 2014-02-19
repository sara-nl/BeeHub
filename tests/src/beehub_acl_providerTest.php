<?php
/**
 * Contains tests for the class BeeHub_ACL_Provider
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
 * Tests for the class BeeHub_ACL_Provider
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_ACL_ProviderTest extends \PHPUnit_Framework_TestCase {

  public function setUp() {
    setUp();
  }


  public function testInst() {
    $this->assertInstanceOf( 'BeeHub_ACL_Provider', \BeeHub_ACL_Provider::inst(), 'BeeHub_ACL_Provider::inst() should return an instance of the class' );
  }


  public function testUser_prop_current_user_principal() {
    $authJohn = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'is_authenticated', 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJohn->expects( $this->any() )
             ->method( 'is_authenticated' )
             ->will( $this->returnValue( true ) );
    $authJohn->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( new \DAV_Resource( '/system/users/john' ) ) );
    \BeeHub::setAuth( $authJohn );

    $obj = new \BeeHub_ACL_Provider();
    $this->assertSame( '/system/users/john', $obj->user_prop_current_user_principal(), 'BeeHub_ACL_Provider::user_prop_current_user_principal() should return a string with the path to the current user' );

    $authNull = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'is_authenticated', 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authNull->expects( $this->any() )
             ->method( 'is_authenticated' )
             ->will( $this->returnValue( false ) );
    $authNull->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( null ) );
    \BeeHub::setAuth( $authNull );

    $this->assertSame( null, $obj->user_prop_current_user_principal(), 'BeeHub_ACL_Provider::user_prop_current_user_principal() should return null when no user is logged in' );
  }


  public function testWheel() {
    $config = \BeeHub::config();
    $authJohn = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'is_authenticated', 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJohn->expects( $this->any() )
             ->method( 'is_authenticated' )
             ->will( $this->returnValue( true ) );
    $authJohn->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( new \DAV_Resource( $config['namespace']['wheel_path'] ) ) );
    \BeeHub::setAuth( $authJohn );

    $obj = new \BeeHub_ACL_Provider();
    $this->assertTrue( $obj->wheel(), 'BeeHub_ACL_Provider::wheel() should return true, because we set the \'wheel_path\' to be logged in' );

    $authJane = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'is_authenticated', 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJane->expects( $this->any() )
             ->method( 'is_authenticated' )
             ->will( $this->returnValue( true ) );
    $authJane->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( new \BeeHub_User( '/system/users/jane' ) ) );
    \BeeHub::setAuth( $authJane );

    $this->assertFalse( $obj->wheel(), 'BeeHub_ACL_Provider::wheel() should return false, because Jane is not the administrator' );
  }


  public function testUser_prop_acl_restrictions() {
    $obj = new \BeeHub_ACL_Provider();
    $this->assertSame( array(), $obj->user_prop_acl_restrictions(), 'BeeHub_ACL_Provider::user_prop_acl_restrictions() determines which acl restrictions apply (which should be none)' );
  }


  public function testUser_prop_principal_collection_set() {
    $obj = new \BeeHub_ACL_Provider();
    $expected = array('/system/groups/', '/system/sponsors/', '/system/users/');
    sort( $expected );
    $returned = $obj->user_prop_principal_collection_set();
    sort( $returned );
    $this->assertSame( $expected, $returned, 'BeeHub_ACL_Provider::user_prop_principal_collection_set() should return the locations of all users, groups and sponsors' );
  }
  
} // Class BeeHub_ACL_ProviderTest

// End of file