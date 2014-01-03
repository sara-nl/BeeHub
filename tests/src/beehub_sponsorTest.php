<?php
/**
 * Contains tests for the class BeeHub_Sponsor
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
 * Tests for the class BeeHub_Sponsor
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_SponsorTest extends BeeHub_Tests_Db_Test_Case {

  public function testChange_membershipsNewAdmin(){
    $this->setCurrentUser( '/system/users/jane' );

    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $this->assertTrue( $sponsor->is_requested() );
    $this->assertFalse( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );

    $sponsor->change_memberships( array( 'jane' ), true, true, true, true );
    $this->assertFalse( $sponsor->is_requested() );
    $this->assertTrue( $sponsor->is_member() );
    $this->assertTrue( $sponsor->is_admin() );
  }


  public function testChange_membershipsUnadmin(){
    $this->setCurrentUser( '/system/users/john' );

    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $this->assertFalse( $sponsor->is_requested() );
    $this->assertTrue( $sponsor->is_member() );
    $this->assertTrue( $sponsor->is_admin() );

    $sponsor->change_memberships( array( 'john' ), true, false, true, false );
    $this->assertFalse( $sponsor->is_requested() );
    $this->assertTrue( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );
  }


  public function testChange_membershipsAcceptRequest(){
    $this->setCurrentUser( '/system/users/jane' );

    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $this->assertTrue( $sponsor->is_requested() );
    $this->assertFalse( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );

    $sponsor->change_memberships( array( 'jane' ), true, false, true, false );
    $this->assertFalse( $sponsor->is_requested() );
    $this->assertTrue( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );
  }


  public function testChange_membershipsNewRequest(){
    $this->setCurrentUser( '/system/users/johny' );

    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $this->assertFalse( $sponsor->is_requested() );
    $this->assertFalse( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );

    $sponsor->change_memberships( array( 'johny' ), false, false, false, false );
    $this->assertTrue( $sponsor->is_requested() );
    $this->assertFalse( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );
  }


  public function testMethod_GET() {
    $this->setCurrentUser( '/system/users/john' );
    $expected = array( 'members' => array(
        'john' => array(
            'user_name'    => 'john',
            'displayname'  => 'John Doe',
            'is_admin'     => true,
            'is_accepted'  => true
        ),
        'jane' => array(
            'user_name'    => 'jane',
            'displayname'  => 'Jane Doe',
            'is_admin'     => false,
            'is_accepted'  => false
        )
    ) );

    $foo = $this->getMock( '\BeeHub_Sponsor', array( 'include_view' ), array( '/system/sponsors/sponsor_b' ) );
    $foo->expects( $this->once() )
        ->method( 'include_view' )
        ->with( $this->equalTo( null ), $this->equalTo( $expected ) );
    $foo->method_GET();
  }


  public function testMethod_POST_Leave() {
    $this->setCurrentUser( '/system/users/jane' );
    $_POST['leave'] = 1;
    $headers = array();

    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $this->assertTrue( $sponsor->is_requested() );
    $sponsor->method_POST( $headers );
    $this->assertFalse( $sponsor->is_requested() );
  }


  public function testMethod_POST_LeaveLastAdmin() {
    $this->setCurrentUser( '/system/users/john' );
    $_POST['leave'] = 1;
    $headers = array();

    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_CONFLICT );
    $sponsor->method_POST( $headers );
  }


  public function testMethod_POST_RequestMembership(){
    $this->setCurrentUser( '/system/users/jane' );
    $_POST['join'] = 1;
    $headers = array();

    $emailer = $this->getMock( '\BeeHub_Emailer', array( 'email' ) );
    $emailer->expects( $this->once() )
            ->method( 'email' );
    \BeeHub::setEmailer( $emailer );

    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $this->assertFalse( $sponsor->is_requested() );
    $this->assertFalse( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );

    $sponsor->method_POST( $headers );
    $this->assertTrue( $sponsor->is_requested() );
    $this->assertFalse( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );

    \BeeHub::setEmailer( new \BeeHub_Emailer() );
  }


  public function testMethod_POST_AddMember(){
    $_POST['add_members'] = array('/system/users/johny');
    $headers = array();

    $emailer = $this->getMock( '\BeeHub_Emailer', array( 'email' ) );
    $emailer->expects( $this->once() )
            ->method( 'email' );
    \BeeHub::setEmailer( $emailer );

    $this->setCurrentUser( '/system/users/johny' );
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $this->assertFalse( $sponsor->is_requested() );
    $this->assertFalse( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );

    $this->setCurrentUser( '/system/users/john' );
    $sponsor->method_POST( $headers );

    $this->setCurrentUser( '/system/users/johny' );
    $this->assertFalse( $sponsor->is_requested() );
    $this->assertTrue( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );

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
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $this->assertTrue( $sponsor->is_requested() );
    $this->assertFalse( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );

    $this->setCurrentUser( '/system/users/john' );
    $sponsor->method_POST( $headers );

    $this->setCurrentUser( '/system/users/jane' );
    $this->assertFalse( $sponsor->is_requested() );
    $this->assertTrue( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );

    \BeeHub::setEmailer( new \BeeHub_Emailer() );
  }


  public function testMethod_POST_AddAdmin(){
    $_POST['add_admins'] = array('/system/users/jane');
    $headers = array();

    $this->setCurrentUser( '/system/users/jane' );
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $this->assertTrue( $sponsor->is_requested() );
    $this->assertFalse( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );

    $this->setCurrentUser( '/system/users/john' );
    $sponsor->method_POST( $headers );

    $this->setCurrentUser( '/system/users/jane' );
    $this->assertFalse( $sponsor->is_requested() );
    $this->assertTrue( $sponsor->is_member() );
    $this->assertTrue( $sponsor->is_admin() );
  }


  public function testMethod_POST_DeleteAdmin(){
    $_POST['delete_admins'] = array('/system/users/jane');
    $headers = array();
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    // No need to test the precondition because the next method is already tested separately
    $sponsor->change_memberships( array( 'jane' ), true, true, true, true );

    $this->setCurrentUser( '/system/users/john' );
    $sponsor->method_POST( $headers );

    $this->setCurrentUser( '/system/users/jane' );
    $this->assertFalse( $sponsor->is_requested() );
    $this->assertTrue( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );
  }


  public function testMethod_POST_DeleteLastAdmin(){
    $_POST['delete_admins'] = array('/system/users/john');
    $headers = array();

    $this->setCurrentUser( '/system/users/john' );
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_CONFLICT );
    $sponsor->method_POST( $headers );
  }


  public function testMethod_POST_DeleteMember(){
    $_POST['delete_members'] = array('/system/users/jane');
    $headers = array();

    $emailer = $this->getMock( '\BeeHub_Emailer', array( 'email' ) );
    $emailer->expects( $this->once() )
            ->method( 'email' );
    \BeeHub::setEmailer( $emailer );

    $this->setCurrentUser( '/system/users/jane' );
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $this->assertTrue( $sponsor->is_requested() );
    $this->assertFalse( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );

    $this->setCurrentUser( '/system/users/john' );
    $sponsor->method_POST( $headers );

    $this->setCurrentUser( '/system/users/jane' );
    $this->assertFalse( $sponsor->is_requested() );
    $this->assertFalse( $sponsor->is_member() );
    $this->assertFalse( $sponsor->is_admin() );

    \BeeHub::setEmailer( new \BeeHub_Emailer() );
  }


  public function testMethod_POST_DeleteMemberLastAdmin(){
    $_POST['delete_members'] = array('/system/users/john');
    $headers = array();

    $this->setCurrentUser( '/system/users/john' );
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_b' );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_CONFLICT );
    $sponsor->method_POST( $headers );
  }


  public function testStoreProperties() {
    $displayname = 'A sponsor';
    $description = 'Same name, different spelling';

    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsor->method_PROPPATCH( \DAV::PROP_DISPLAYNAME, $displayname );
    $sponsor->method_PROPPATCH( \BeeHub::PROP_DESCRIPTION, $description );
    $sponsor->storeProperties();

    // Now, if I create a new instance of BeeHub_Sponsor for the same sponsor, it should have the properties set
    $sponsorReloaded = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $this->assertSame( $displayname, $sponsorReloaded->user_prop( \DAV::PROP_DISPLAYNAME ) );
    $this->assertSame( $description, $sponsorReloaded->user_prop( \BeeHub::PROP_DESCRIPTION ) );
  }


  public function testUser_prop_acl_internal() {
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $acl = $sponsor->user_prop_acl_internal();
    $expected = array( new \DAVACL_Element_ace( '/system/users/john', false, array( \DAVACL::PRIV_WRITE ), false ) );
    $this->assertEquals( $expected, $acl );
  }


  public function testUser_prop_group_member_set() {
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $this->assertSame( array( '/system/users/john' ), $sponsor->user_prop_group_member_set() );
  }


  public function testUser_propname() {
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $this->assertSame( \BeeHub::$SPONSOR_PROPS, $sponsor->user_propname() );
  }


  public function testUser_set() {
    $expected = 'Some description';
    $sponsor = $this->getMock( '\BeeHub_Sponsor', array( 'user_set_description' ), array( '/system/sponsors/sponsor_a' ) );
    $sponsor->expects( $this->once() )
            ->method( 'user_set_description' )
            ->with( $expected );
    $sponsor->user_set( \DAV::PROP_DISPLAYNAME, 'some displayname' );
    $sponsor->user_set( \BeeHub::PROP_DESCRIPTION, $expected );
  }

} // class BeeHub_SponsorTest

// End of file