<?php

/*·************************************************************************
 * Copyright ©2007-2013 SARA b.v., Amsterdam, The Netherlands
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
 **************************************************************************/

/**
 * Tests for the BeeHub_Resource class
 * @package     tests
 * @subpackage  models
 */

/**
 * Tests for the BeeHub_Resource class
 * @package     tests
 * @subpackage  models
 */
class BeeHub_ResourceTest extends PHPUnit_Framework_TestCase {


//  abstract protected function init_props();
//
//
//  abstract public function user_prop_acl_internal();

  
  public function testAssert() {
    $stub = $this->getMockForAbstractClass( 'BeeHub_Resource' );
    $stub->expects( $this->any() )
         ->method( 'user_prop_acl_internal' )
         ->will( $this->returnValue( array() ) );
    
    $this->assertCount( $stub->user_prop_acl(), 1, 'BeeHub_Resource::user_prop_acl() should return 1 protected ACE' );
  }


//  public function testUser_set() {
//  }
//
//
//  public function testIsVisible() {
//  }
//
//
//  public function testUser_prop() {
//  }
//
//
//  public function testCurrent_user_sponsors() {
//  }
//
//
//  public function testProperty_priv_read() {
//  }
//
//
//  public function testUser_prop_acl() {
//  }
//
//
//  public function testUser_prop_owner() {
//  }
//
//
//  public function testProp_sponsor() {
//  }
//
//
//  public function testSet_sponsor() {
//  }
//
//
//  public function testUser_prop_sponsor() {
//  }
//
//
//  public function testUser_prop_getcontenttype() {
//  }
//
//
//  public function testInclude_view() {
//  }


} // class BeeHub_ResourceTest

// End of file