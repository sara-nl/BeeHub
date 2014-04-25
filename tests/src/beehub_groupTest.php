<?php
/**
 * Contains tests for the class BeeHub_Group
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
 * Tests for the class BeeHub_Group
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_GroupTest extends BeeHub_Tests_Db_Test_Case {

  public function testChange_membershipsAcceptInvitation() {
    $this->setCurrentUser( '/system/users/jane' );

    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $this->assertTrue( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertFalse( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );
    $this->assertSame( array( '/system/users/john' ), $foo->user_prop_group_member_set() );

    $foo->change_memberships( array( 'jane' ), true, true, false, true, true, false );
    $this->assertFalse( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertTrue( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );
    $this->assertSame( array( '/system/users/john', '/system/users/jane' ), $foo->user_prop_group_member_set() );
  }


  public function testChange_membershipsNewInvitation(){
    $this->setCurrentUser( '/system/users/johny' );

    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $this->assertFalse( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertFalse( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );

    $foo->change_memberships( array( 'johny' ), true, false, false, true, false, false );
    $this->assertTrue( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertFalse( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );
  }


  public function testChange_membershipsNewAdmin(){
    $this->setCurrentUser( '/system/users/jane' );

    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $this->assertTrue( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertFalse( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );

    $foo->change_memberships( array( 'jane' ), true, true, true, true, true, true );
    $this->assertFalse( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertTrue( $foo->is_member() );
    $this->assertTrue( $foo->is_admin() );
  }


  public function testChange_membershipsUnadmin(){
    $this->setCurrentUser( '/system/users/jane' );

    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $foo->change_memberships( array( 'jane' ), true, true, true, true, true, true );
    $this->assertFalse( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertTrue( $foo->is_member() );
    $this->assertTrue( $foo->is_admin() );

    $foo->change_memberships( array( 'jane' ), true, true, false, true, true, false );
    $this->assertFalse( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertTrue( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );
  }


  public function testChange_membershipsAcceptRequest(){
    $this->setCurrentUser( '/system/users/jane' );

    $bar = new \BeeHub_Group( '/system/groups/bar' );
    $this->assertFalse( $bar->is_invited() );
    $this->assertTrue( $bar->is_requested() );
    $this->assertFalse( $bar->is_member() );
    $this->assertFalse( $bar->is_admin() );
    $this->assertSame( array( '/system/users/john' ), $bar->user_prop_group_member_set() );

    $bar->change_memberships( array( 'jane' ), true, true, false, true, true, false );
    $this->assertFalse( $bar->is_invited() );
    $this->assertFalse( $bar->is_requested() );
    $this->assertTrue( $bar->is_member() );
    $this->assertFalse( $bar->is_admin() );
    $this->assertSame( array( '/system/users/john', '/system/users/jane' ), $bar->user_prop_group_member_set() );
  }


  public function testChange_membershipsNewRequest(){
    $this->setCurrentUser( '/system/users/johny' );

    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $this->assertFalse( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertFalse( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );

    $foo->change_memberships( array( 'johny' ), false, true, false, false, true, false );
    $this->assertFalse( $foo->is_invited() );
    $this->assertTrue( $foo->is_requested() );
    $this->assertFalse( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );
  }


  public function testMethod_GET() {
    $expected = array( 'members' => array(
        'john' => array(
            'user_name'    => 'john',
            'displayname'  => 'John Doe',
            'is_admin'     => true,
            'is_invited'   => true,
            'is_requested' => true
        ),
        'jane' => array(
            'user_name'    => 'jane',
            'displayname'  => 'Jane Doe',
            'is_admin'     => false,
            'is_invited'   => true,
            'is_requested' => false
        )
    ) );

    $foo = $this->getMock( '\BeeHub_Group', array( 'include_view', 'is_member' ), array( '/system/groups/foo' ) );
    $foo->expects( $this->any() )
        ->method( 'is_member' )
        ->will( $this->returnValue( true ) );
    $foo->expects( $this->once() )
        ->method( 'include_view' )
        ->with( $this->equalTo( null ), $this->equalTo( $expected ) );
    $this->setCurrentUser('/system/users/john');
    $foo->method_GET();
  }


  public function testMethod_POST_Leave() {
    $this->setCurrentUser( '/system/users/jane' );
    $_POST['leave'] = 1;
    $headers = array();

    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $this->assertTrue( $foo->is_invited() );
    $foo->method_POST( $headers );
    $this->assertFalse( $foo->is_member() );
  }


  public function testMethod_POST_LeaveLastAdmin() {
    $this->setCurrentUser( '/system/users/john' );
    $_POST['leave'] = 1;
    $headers = array();

    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_CONFLICT );
    $foo->method_POST( $headers );
  }


  public function testMethod_POST_AcceptInvite(){
    $this->setCurrentUser( '/system/users/jane' );
    $_POST['join'] = 1;
    $headers = array();

    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $this->assertTrue( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertFalse( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );

    $foo->method_POST( $headers );
    $this->assertFalse( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertTrue( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );
  }


  public function testMethod_POST_RequestMembership(){
    $this->setCurrentUser( '/system/users/johny' );
    $_POST['join'] = 1;
    $headers = array();

    $emailer = $this->getMock( '\BeeHub_Emailer', array( 'email' ) );
    $emailer->expects( $this->once() )
            ->method( 'email' );
    \BeeHub::setEmailer( $emailer );

    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $this->assertFalse( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertFalse( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );

    $foo->method_POST( $headers );
    $this->assertFalse( $foo->is_invited() );
    $this->assertTrue( $foo->is_requested() );
    $this->assertFalse( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );

    \BeeHub::setEmailer( new \BeeHub_Emailer() );
  }


  public function testMethod_POST_InviteMember(){
    $_POST['add_members'] = array('/system/users/johny');
    $headers = array();

    $emailer = $this->getMock( '\BeeHub_Emailer', array( 'email' ) );
    $emailer->expects( $this->once() )
            ->method( 'email' );
    \BeeHub::setEmailer( $emailer );

    $this->setCurrentUser( '/system/users/johny' );
    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $this->assertFalse( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertFalse( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );

    $this->setCurrentUser( '/system/users/john' );
    $foo->method_POST( $headers );

    $this->setCurrentUser( '/system/users/johny' );
    $this->assertTrue( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertFalse( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );

    \BeeHub::setEmailer( new \BeeHub_Emailer() );
  }


  public function testMethod_POST_AcceptRequest(){
    $_POST['add_members'] = array('/system/users/jane');
    $headers = array();

    $emailer = $this->getMock( '\BeeHub_Emailer', array( 'email' ) );
    $emailer->expects( $this->once() )
            ->method( 'email' );
    \BeeHub::setEmailer( $emailer );

    $this->setCurrentUser( '/system/users/jane' );
    $bar = new \BeeHub_Group( '/system/groups/bar' );
    $this->assertFalse( $bar->is_invited() );
    $this->assertTrue( $bar->is_requested() );
    $this->assertFalse( $bar->is_member() );
    $this->assertFalse( $bar->is_admin() );

    $this->setCurrentUser( '/system/users/john' );
    $bar->method_POST( $headers );

    $this->setCurrentUser( '/system/users/jane' );
    $this->assertFalse( $bar->is_invited() );
    $this->assertFalse( $bar->is_requested() );
    $this->assertTrue( $bar->is_member() );
    $this->assertFalse( $bar->is_admin() );

    \BeeHub::setEmailer( new \BeeHub_Emailer() );
  }


  public function testMethod_POST_AddAdmin(){
    $_POST['add_admins'] = array('/system/users/jane');
    $headers = array();

    $this->setCurrentUser( '/system/users/jane' );
    $bar = new \BeeHub_Group( '/system/groups/bar' );
    $this->assertFalse( $bar->is_invited() );
    $this->assertTrue( $bar->is_requested() );
    $this->assertFalse( $bar->is_member() );
    $this->assertFalse( $bar->is_admin() );

    $this->setCurrentUser( '/system/users/john' );
    $bar->method_POST( $headers );

    $this->setCurrentUser( '/system/users/jane' );
    $this->assertFalse( $bar->is_invited() );
    $this->assertFalse( $bar->is_requested() );
    $this->assertTrue( $bar->is_member() );
    $this->assertTrue( $bar->is_admin() );
  }


  public function testMethod_POST_DeleteAdmin(){
    $_POST['delete_admins'] = array('/system/users/jane');
    $headers = array();
    $bar = new \BeeHub_Group( '/system/groups/bar' );
    // No need to test the precondition because the next method is already tested separately
    $bar->change_memberships( array( 'jane' ), true, true, true, true, true, true );

    $this->setCurrentUser( '/system/users/john' );
    $bar->method_POST( $headers );

    $this->setCurrentUser( '/system/users/jane' );
    $this->assertFalse( $bar->is_invited() );
    $this->assertFalse( $bar->is_requested() );
    $this->assertTrue( $bar->is_member() );
    $this->assertFalse( $bar->is_admin() );
  }


  public function testMethod_POST_DeleteLastAdmin(){
    $_POST['delete_admins'] = array('/system/users/john');
    $headers = array();

    $this->setCurrentUser( '/system/users/john' );
    $bar = new \BeeHub_Group( '/system/groups/bar' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_CONFLICT );
    $bar->method_POST( $headers );
  }


  public function testMethod_POST_DeleteMember(){
    $_POST['delete_members'] = array('/system/users/jane');
    $headers = array();

    $emailer = $this->getMock( '\BeeHub_Emailer', array( 'email' ) );
    $emailer->expects( $this->once() )
            ->method( 'email' );
    \BeeHub::setEmailer( $emailer );

    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $foo->change_memberships( array( 'jane' ), true, true, false, true, true, false );
    $this->assertSame( array( '/system/users/john', '/system/users/jane' ), $foo->user_prop_group_member_set() );

    $this->setCurrentUser( '/system/users/john' );
    $foo->method_POST( $headers );
    $this->assertSame( array( '/system/users/john' ), $foo->user_prop_group_member_set() );

    $this->setCurrentUser( '/system/users/jane' );
    $this->assertFalse( $foo->is_invited() );
    $this->assertFalse( $foo->is_requested() );
    $this->assertFalse( $foo->is_member() );
    $this->assertFalse( $foo->is_admin() );

    \BeeHub::setEmailer( new \BeeHub_Emailer() );
  }


  public function testMethod_POST_DeleteMemberLastAdmin(){
    $_POST['delete_members'] = array('/system/users/john');
    $headers = array();

    $this->setCurrentUser( '/system/users/john' );
    $bar = new \BeeHub_Group( '/system/groups/bar' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_CONFLICT );
    $bar->method_POST( $headers );
  }


  public function testStoreProperties() {
    $displayname = 'Fu';
    $description = 'Same name, different spelling';

    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $foo->method_PROPPATCH( \DAV::PROP_DISPLAYNAME, $displayname );
    $foo->method_PROPPATCH( \BeeHub::PROP_DESCRIPTION, $description );
    $foo->storeProperties();

    // Now, if I create a new instance of BeeHub_Group for the same group, it should have the properties set
    $fooReloaded = new \BeeHub_Group( '/system/groups/foo' );
    $this->assertSame( $displayname, $fooReloaded->user_prop( \DAV::PROP_DISPLAYNAME ) );
    $this->assertSame( $description, $fooReloaded->user_prop( \BeeHub::PROP_DESCRIPTION ) );
  }


  public function testUser_prop_acl_internal() {
    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $acl = $foo->user_prop_acl_internal();
    $expected = array( new \DAVACL_Element_ace( '/system/users/john', false, array( \DAVACL::PRIV_WRITE ), false ) );
    $this->assertEquals( $expected, $acl );
  }


  public function testUser_prop_group_member_set() {
    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $this->assertSame( array( '/system/users/john' ), $foo->user_prop_group_member_set() );
  }


  public function testUser_propname() {
    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $this->assertSame( \BeeHub::$GROUP_PROPS, $foo->user_propname() );
  }


  public function testUser_set() {
    $expected = 'Some description';
    $foo = $this->getMock( 'BeeHub_Group', array( 'user_set_description' ), array( '/system/groups/foo' ) );
    $foo->expects( $this->once() )
        ->method( 'user_set_description' )
        ->with( $expected );
    $foo->user_set( \DAV::PROP_DISPLAYNAME, 'some displayname' );
    $foo->user_set( \BeeHub::PROP_DESCRIPTION, $expected );
  }

} // class BeeHub_GroupTest

// End of file