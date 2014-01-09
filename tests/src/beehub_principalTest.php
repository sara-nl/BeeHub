<?php
/**
 * Contains tests for the class BeeHub_Principal
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
 * Tests for the class BeeHub_Principal
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_PrincipalTest extends \PHPUnit_Framework_TestCase {

  public function setUp() {
    parent::setUp();
    setUp();
    $_REQUEST['REQUEST_URI'] = '/system/users/jane';
  }


  public function testConstruct() {
    $principal = $this->getMock( '\BeeHub_Principal', array( 'init_props', 'is_admin', 'user_prop_acl_internal' ), array( $_REQUEST['REQUEST_URI'] ) );
    $this->assertSame( 'jane', $principal->name );
  }


  public function testProperty_priv_write() {
    $properties = array( \DAV::PROP_CREATIONDATE, \DAV::PROP_DISPLAYNAME, \DAV::PROP_ACL );

    $principalAdmin = $this->getMock( '\BeeHub_Principal', array( 'init_props', 'is_admin', 'user_prop_acl_internal' ), array( $_REQUEST['REQUEST_URI'] ) );
    $principalAdmin->expects( $this->once() )
                   ->method( 'is_admin' )
                   ->will( $this->returnValue( true ) );
    $this->assertFalse( in_array( false, $principalAdmin->property_priv_write( $properties ) ) );

    $principalNotAdmin = $this->getMock( '\BeeHub_Principal', array( 'init_props', 'is_admin', 'user_prop_acl_internal' ), array( $_REQUEST['REQUEST_URI'] ) );
    $principalNotAdmin->expects( $this->once() )
                      ->method( 'is_admin' )
                      ->will( $this->returnValue( false ) );
    $this->assertFalse( in_array( true, $principalNotAdmin->property_priv_write( $properties ) ) );
  }

} // Class BeeHub_PrincipalTest

// End of file