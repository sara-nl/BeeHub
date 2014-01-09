<?php
/**
 * Contains tests for the class BeeHub_Resource
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
 * Tests for the class BeeHub_Resource
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_ResourceTest extends \PHPUnit_Framework_TestCase {

  public function setUp() {
    parent::setUp();
    setUp();
  }


  public function testIsVisible() {
    $resourceVisible = $this->getMock( '\BeeHub_Resource', array( 'init_props', 'user_prop_acl_internal', 'assert' ), array( $_SERVER['REQUEST_URI'] ) );
    $resourceVisible->expects( $this->once() )
                    ->method( 'assert' )
                    ->with( $this->equalTo( \DAVACL::PRIV_READ ) );
    $this->assertTrue( $resourceVisible->isVisible(), 'If you have read privileges, the resource should be visible to you' );

    $resourceInvisible = $this->getMock( '\BeeHub_Resource', array( 'init_props', 'user_prop_acl_internal', 'assert' ), array( $_SERVER['REQUEST_URI'] ) );
    $resourceInvisible->expects( $this->once() )
                      ->method( 'assert' )
                      ->with( $this->equalTo( \DAVACL::PRIV_READ ) )
                      ->will( $this->throwException( new \DAV_Status( \DAV::HTTP_FORBIDDEN ) ) );
    $this->assertFalse( $resourceInvisible->isVisible(), 'If you don\'t have read privileges, the resource should be invisible to you' );
  }


  public function testProp_sponsor() {
    $obj = $this->getMock( '\BeeHub_Resource', array( 'init_props', 'user_prop_acl_internal', 'user_prop_sponsor' ), array( $_SERVER['REQUEST_URI'] ) );
    $obj->expects( $this->once() )
        ->method( 'user_prop_sponsor' )
        ->will( $this->returnValue( '/system/sponsors/sponsor_a' ) );
    $returned = $obj->prop_sponsor();

    $this->assertInstanceof( 'DAV_Element_href', $returned );
    $this->assertSame( array( '/system/sponsors/sponsor_a' ), $returned->URIs );
  }


  public function testProperty_priv_read() {
    $properties = array(
        \DAV::PROP_OWNER,
        \DAV::PROP_RESOURCETYPE,
        \DAV::PROP_DISPLAYNAME,
        \DAV::PROP_ACL,
        'http://namespace/ test_property'
    );

    $resourceReadable = $this->getMock( '\BeeHub_Resource', array( 'init_props', 'user_prop_acl_internal', 'assert' ), array( $_SERVER['REQUEST_URI'] ) );
    $resourceReadable->expects( $this->any() )
                     ->method( 'assert' );
    $expectedReadable = array(
        \DAV::PROP_OWNER => true,
        \DAV::PROP_RESOURCETYPE => true,
        \DAV::PROP_DISPLAYNAME => true,
        \DAV::PROP_ACL => true,
        'http://namespace/ test_property' => true
    );
    $this->assertSame( $expectedReadable, $resourceReadable->property_priv_read( $properties ), 'With read privileges, you are also allowed to read all properties' );

    $resourceUnreadable = $this->getMock( '\BeeHub_Resource', array( 'init_props', 'user_prop_acl_internal', 'assert' ), array( $_SERVER['REQUEST_URI'] ) );
    $resourceUnreadable->expects( $this->any() )
                       ->method( 'assert' );
    $expectedUnreadable = array(
        \DAV::PROP_OWNER => true,
        \DAV::PROP_RESOURCETYPE => true,
        \DAV::PROP_DISPLAYNAME => true,
        \DAV::PROP_ACL => true,
        'http://namespace/ test_property' => true
    );
    $this->assertSame( $expectedUnreadable, $resourceUnreadable->property_priv_read( $properties ), 'Without read privileges, you are only allowed to read displayname, resource type and owner' );
  }


  public function testSet_sponsor() {
    $resource = $this->getMock( '\BeeHub_Resource', array( 'init_props', 'user_prop_acl_internal', 'user_set_sponsor' ), array( $_SERVER['REQUEST_URI'] ) );
    $resource->expects( $this->once() )
             ->method( 'user_set_sponsor' )
             ->with( $this->equalTo( '/system/sponsors/sponsor_a' ) );

    $resource->set_sponsor( '<D:href>/system/sponsors/sponsor_a</D:href>' );
  }


  public function testUser_prop() {
    $resource = $this->getMock( '\BeeHub_Resource', array( 'init_props', 'user_prop_acl_internal' ), array( $_SERVER['REQUEST_URI'] ) );
    $resource->expects( $this->once() )
             ->method( 'init_props' );

    $resource->user_prop( \DAV::PROP_DISPLAYNAME );
  }


  public function testUser_prop_acl() {
    $_SERVER['REQUEST_URI'] = '/some/path';
    $resource = $this->getMock( '\BeeHub_Resource', array( 'init_props', 'user_prop_acl_internal', 'collection' ), array( $_SERVER['REQUEST_URI'] ) );
    $resource->expects( $this->once() )
             ->method( 'user_prop_acl_internal' )
             ->will( $this->returnValue( array( new \DAVACL_Element_ace( '/system/users/john', false, \DAVACL::PRIV_READ, false ) ) ) );
    $parent = $this->getMock( '\BeeHub_Resource', array( 'init_props', 'user_prop_acl_internal', 'user_prop_acl' ), array( dirname( $_SERVER['REQUEST_URI'] ) ) );
    $parent->expects( $this->once() )
           ->method( 'user_prop_acl' )
           ->will( $this->returnValue( array( new \DAVACL_Element_ace( \DAVACL::PRINCIPAL_AUTHENTICATED, true, \DAVACL::PRIV_WRITE, true ) ) ) );
    $resource->expects( $this->once() )
             ->method( 'collection' )
             ->will( $this->returnValue( $parent ) );

    $returnedAcl = $resource->user_prop_acl();
    $this->assertCount( 4, $returnedAcl );
    $this->assertSame( 'DAV: owner', $returnedAcl[0]->principal );
    $this->assertTrue( $returnedAcl[0]->protected );
    $this->assertSame( \DAVACL::PRINCIPAL_ALL, $returnedAcl[1]->principal );
    $this->assertTrue( in_array( \DAVACL::PRIV_UNBIND, $returnedAcl[1]->privileges ) );
    $this->assertTrue( $returnedAcl[1]->protected );
    $this->assertSame( '/system/users/john', $returnedAcl[2]->principal );
    $this->assertNull( $returnedAcl[2]->inherited );
    $this->assertFalse( $returnedAcl[2]->protected );
    $this->assertSame( \DAVACL::PRINCIPAL_AUTHENTICATED, $returnedAcl[3]->principal );
    $this->assertSame( '/some', $returnedAcl[3]->inherited );
  }


  public function testUser_prop_owner() {
    $resource = $this->getMock( '\BeeHub_Resource', array( 'init_props', 'user_prop_acl_internal', 'user_prop' ), array( $_SERVER['REQUEST_URI'] ) );
    $resource->expects( $this->once() )
             ->method( 'user_prop' )
             ->with( \DAV::PROP_OWNER )
             ->will( $this->returnValue( '/system/users/jane' ) );
    $this->assertSame( '/system/users/jane', $resource->user_prop_owner() );

    $ownerlessResource = $this->getMock( '\BeeHub_Resource', array( 'init_props', 'user_prop_acl_internal' ), array( $_SERVER['REQUEST_URI'] ) );
    $config = \BeeHub::config();
    $this->assertSame( $config['namespace']['wheel_path'], $ownerlessResource->user_prop_owner(), 'All resources have owners. If none is specified, then it defaults to wheel' );
  }


  public function testUser_set() {
    $resource = $this->getMock( '\BeeHub_Resource', array( 'init_props', 'user_prop_acl_internal' ), array( $_SERVER['REQUEST_URI'] ) );
    $resource->expects( $this->once() )
             ->method( 'init_props' );
    $resource->user_prop( \DAV::PROP_DISPLAYNAME, 'does not matter' );
  }


} // class BeeHub_ResourceTest

// End of file