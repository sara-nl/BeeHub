<?php
/**
 * Contains tests for the class BeeHub_Group
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
 * Tests for the class BeeHub_Group
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_GroupTest extends BeeHub_Tests_Db_Test_Case {

  public function setUp() {
    parent::setUp();
    reset_SERVER();
    \DAV::$REGISTRY = \BeeHub_Registry::inst();
  }


  public function testChange_membershipsAcceptInvitation(){
    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $foo->change_memberships( array( 'jane' ), true, true, false, true, true, false );
    $realTable = $this->getConnection()->createQueryTable( 'beehub_group_members', 'SELECT * FROM beehub_group_members' );
    $expectedTable = $this->createXMLDataSet( $this->getDatasetPath() . 'beehub_groupTest' . \DIRECTORY_SEPARATOR . 'testChange_memberships_accept_invitation.xml' )
            ->getTable( 'beehub_group_members' );
    $this->assertTablesEqual( $expectedTable, $realTable );
  }


  public function testChange_membershipsNewInvitation(){
    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $foo->change_memberships( array( 'johny' ), true, false, false, true, false, false );
    $realTable = $this->getConnection()->createQueryTable( 'beehub_group_members', 'SELECT * FROM beehub_group_members' );
    $expectedTable = $this->createXMLDataSet( $this->getDatasetPath() . 'beehub_groupTest' . \DIRECTORY_SEPARATOR . 'testChange_memberships_new_invitation.xml' )
            ->getTable( 'beehub_group_members' );
    $this->assertTablesEqual( $expectedTable, $realTable );
  }


  public function testChange_membershipsNewAdmin(){
    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $foo->change_memberships( array( 'jane' ), true, true, true, true, true, true );
    $realTable = $this->getConnection()->createQueryTable( 'beehub_group_members', 'SELECT * FROM beehub_group_members' );
    $expectedTable = $this->createXMLDataSet( $this->getDatasetPath() . 'beehub_groupTest' . \DIRECTORY_SEPARATOR . 'testChange_memberships_new_admin.xml' )
            ->getTable( 'beehub_group_members' );
    $this->assertTablesEqual( $expectedTable, $realTable );
  }


  public function testChange_membershipsUnadmin(){
    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $foo->change_memberships( array( 'john' ), true, true, false, true, true, false );
    $realTable = $this->getConnection()->createQueryTable( 'beehub_group_members', 'SELECT * FROM beehub_group_members' );
    $expectedTable = $this->createXMLDataSet( $this->getDatasetPath() . 'beehub_groupTest' . \DIRECTORY_SEPARATOR . 'testChange_memberships_unadmin.xml' )
            ->getTable( 'beehub_group_members' );
    $this->assertTablesEqual( $expectedTable, $realTable );
  }


  public function testChange_membershipsAcceptRequest(){
    $foo = new \BeeHub_Group( '/system/groups/bar' );
    $foo->change_memberships( array( 'jane' ), true, true, false, true, true, false );
    $realTable = $this->getConnection()->createQueryTable( 'beehub_group_members', 'SELECT * FROM beehub_group_members' );
    $expectedTable = $this->createXMLDataSet( $this->getDatasetPath() . 'beehub_groupTest' . \DIRECTORY_SEPARATOR . 'testChange_memberships_accept_request.xml' )
            ->getTable( 'beehub_group_members' );
    $this->assertTablesEqual( $expectedTable, $realTable );
  }


  public function testChange_membershipsNewRequest(){
    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $foo->change_memberships( array( 'johny' ), false, true, false, false, true, false );
    $realTable = $this->getConnection()->createQueryTable( 'beehub_group_members', 'SELECT * FROM beehub_group_members' );
    $expectedTable = $this->createXMLDataSet( $this->getDatasetPath() . 'beehub_groupTest' . \DIRECTORY_SEPARATOR . 'testChange_memberships_new_request.xml' )
            ->getTable( 'beehub_group_members' );
    $this->assertTablesEqual( $expectedTable, $realTable );
  }
  public function testIs_admin() {
    $foo = new \BeeHub_Group( '/system/groups/foo' );

    $john = new \BeeHub_User( '/system/users/john' );
    $authJohn = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJohn->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( $john ) );
    \BeeHub::setAuth( $authJohn );

    $this->assertTrue( $foo->is_admin() );

    $jane = new \BeeHub_User( '/system/users/jane' );
    $authJane = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJane->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( $jane ) );
    \BeeHub::setAuth( $authJane );

    $this->assertFalse( $foo->is_admin() );

    \BeeHub::setAuth( \BeeHub_Auth::inst() );
  }


  public function testIs_invited() {
    $foo = new \BeeHub_Group( '/system/groups/foo' );

    $john = new \BeeHub_User( '/system/users/john' );
    $authJohn = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJohn->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( $john ) );
    \BeeHub::setAuth( $authJohn );

    $this->assertFalse( $foo->is_invited() );

    $jane = new \BeeHub_User( '/system/users/jane' );
    $authJane = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJane->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( $jane ) );
    \BeeHub::setAuth( $authJane );

    $this->assertTrue( $foo->is_invited() );

    // Let's also test the case where the user has requested the membership, but has not yet been accepted
    $bar = new \BeeHub_Group( '/system/groups/bar' );
    $this->assertFalse( $bar->is_invited() );

    \BeeHub::setAuth( \BeeHub_Auth::inst() );
  }


  public function testIs_member() {
    $foo = new \BeeHub_Group( '/system/groups/foo' );

    $john = new \BeeHub_User( '/system/users/john' );
    $authJohn = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJohn->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( $john ) );
    \BeeHub::setAuth( $authJohn );

    $this->assertTrue( $foo->is_member() );

    $jane = new \BeeHub_User( '/system/users/jane' );
    $authJane = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJane->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( $jane ) );
    \BeeHub::setAuth( $authJane );

    $this->assertFalse( $foo->is_member() );

    // Let's also test the case where the user has requested the membership, but has not yet been accepted
    $bar = new \BeeHub_Group( '/system/groups/bar' );
    $this->assertFalse( $bar->is_member() );

    \BeeHub::setAuth( \BeeHub_Auth::inst() );
  }


  public function testIs_requested() {
    $foo = new \BeeHub_Group( '/system/groups/foo' );

    $john = new \BeeHub_User( '/system/users/john' );
    $authJohn = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJohn->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( $john ) );
    \BeeHub::setAuth( $authJohn );

    $this->assertFalse( $foo->is_requested() );

    $jane = new \BeeHub_User( '/system/users/jane' );
    $authJane = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJane->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( $jane ) );
    \BeeHub::setAuth( $authJane );

    $this->assertFalse( $foo->is_requested() );

    // Let's also test the case where the user has requested the membership, but has not yet been accepted
    $bar = new \BeeHub_Group( '/system/groups/bar' );
    $this->assertTrue( $bar->is_requested() );

    \BeeHub::setAuth( \BeeHub_Auth::inst() );
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
    $foo->method_GET();
  }


//  public function method_POST() {
//    $auth = BeeHub_Auth::inst();
//    if (!$auth->is_authenticated()) {
//      throw DAV::forbidden();
//    }
//    $admin_functions = array('add_members', 'add_admins', 'delete_admins', 'delete_members');
//    if (!$this->is_admin()) {
//      foreach ($admin_functions as $function) {
//        if (isset($_POST[$function])) {
//          throw DAV::forbidden();
//        }
//      }
//    }
//
//    // Allow users to request or remove membership
//    $current_user = $auth->current_user();
//    if (isset($_POST['leave'])) {
//      $this->delete_members(array($current_user->name));
//    }
//    if (isset($_POST['join'])) {
//      $statement = BeeHub_DB::execute('SELECT `is_invited` FROM `beehub_group_members` WHERE `user_name`=? AND `group_name`=?',
//                                      'ss', $current_user->name, $this->name);
//      $message = null;
//      if ( !( $row = $statement->fetch_row() ) || ( $row[0] != 1 ) ) { // This user is not invited for this group, so sent the administrators an e-mail with this request
//        $message =
//'Dear group administrator,
//
//' . $current_user->prop(DAV::PROP_DISPLAYNAME) . ' (' . $current_user->prop(BeeHub::PROP_EMAIL) . ') wants to join the group \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\'. One of the group administrators needs to either accept or reject this membership request. Please see your notifications in BeeHub to do this:
//
//' . BeeHub::urlbase(true) . '/system/?show_notifications=1
//
//Best regards,
//
//BeeHub';
//        $recipients = array();
//        foreach ($this->users as $user => $attributes) {
//          if ($attributes['is_admin']) {
//            $user = BeeHub::user($user);
//            $recipients[] = $user->prop(DAV::PROP_DISPLAYNAME) . ' <' . $user->prop(BeeHub::PROP_EMAIL) . '>';
//          }
//        }
//      }
//      $this->change_memberships(array($current_user->name), false, true, false, null, true);
//      if (!is_null($message)) {
//        BeeHub::email($recipients,
//                      'BeeHub notification: membership request for group ' . $this->prop(DAV::PROP_DISPLAYNAME),
//                      $message);
//      }
//    }
//
//    // Run administrator actions: add members, admins and requests
//    foreach ($admin_functions as $key) {
//      if (isset($_POST[$key])) {
//        $members = array();
//        if (!is_array($_POST[$key])) {
//          throw new DAV_Status(DAV::HTTP_BAD_REQUEST);
//        }
//        foreach ($_POST[$key] as $uri) {
//          $members[] = DAV::parseURI($uri, false);
//        }
//        $members = array_map(array('BeeHub_Group', 'get_user_name'), $members);
//        switch ($key) {
//          case 'add_members':
//            foreach ($members as $member) {
//              $user = BeeHub::user($member);
//              $statement = BeeHub_DB::execute('SELECT `is_requested` FROM `beehub_group_members` WHERE `user_name`=? AND `group_name`=?',
//                                              'ss', $user->name, $this->name);
//              if ( !( $row = $statement->fetch_row() ) || ( $row[0] != 1 ) ) { // This user did not request for this group, so sent him/her an e-mail with this invitation
//                $message =
//'Dear ' . $user->prop(DAV::PROP_DISPLAYNAME) . ',
//
//You are invited to join the group \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\'. You need to accept this invitation before your membership is activated. Please see your notifications in BeeHub to do this:
//
//' . BeeHub::urlbase(true) . '/system/?show_notifications=1
//
//Best regards,
//
//BeeHub';
//              }else{ // The user requested this membership, so now he/she is really a member
//                $message =
//'Dear ' . $user->prop(DAV::PROP_DISPLAYNAME) . ',
//
//Your membership of the group \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\' is accepted by a group administrator. You are now a member of this group.
//
//Best regards,
//
//BeeHub';
//              }
//              BeeHub::email($user->prop(DAV::PROP_DISPLAYNAME) . ' <' . $user->prop(BeeHub::PROP_EMAIL) . '>',
//                            'BeeHub notification: membership accepted for group ' . $this->prop(DAV::PROP_DISPLAYNAME),
//                            $message);
//            }
//            $this->change_memberships($members, true, false, false, true);
//            break;
//          case 'add_admins':
//            $this->change_memberships($members, true, false, true, true, null, true);
//            break;
//          case 'delete_admins':
//            $this->check_admin_remove($members);
//            $this->change_memberships($members, true, false, false, null, null, false);
//            break;
//          case 'delete_members':
//            $this->delete_members($members);
//            foreach ($members as $member) {
//              $user = BeeHub::user($member);
//              $message =
//'Dear ' . $user->prop(DAV::PROP_DISPLAYNAME) . ',
//
//Group administrator ' . $current_user->prop(DAV::PROP_DISPLAYNAME) . ' removed you from the group \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\'. If you believe you should be a member of this group, please contact one of the group administrators.
//
//Best regards,
//
//BeeHub';
//              BeeHub::email($user->prop(DAV::PROP_DISPLAYNAME) . ' <' . $user->prop(BeeHub::PROP_EMAIL) . '>',
//                            'BeeHub notification: removed from group ' . $this->prop(DAV::PROP_DISPLAYNAME),
//                            $message);
//            }
//            break;
//          default: //Should/could never happen
//            throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
//          break;
//        }
//      }
//    }
//  }


  public function testStoreProperties() {
    $foo = new \BeeHub_Group( '/system/groups/foo' );
    $foo->method_PROPPATCH( \DAV::PROP_DISPLAYNAME, 'Fu' );
    $foo->method_PROPPATCH( \BeeHub::PROP_DESCRIPTION, 'Same name, different spelling' );
    $foo->storeProperties();

    $realTable = $this->getConnection()->createQueryTable( 'beehub_groups', "SELECT * FROM `beehub_groups` WHERE `group_name`='foo'" );
    $expectedTable = $this->createXMLDataSet( $this->getDatasetPath() . 'beehub_groupTest' . \DIRECTORY_SEPARATOR . 'testStoreProperties.xml' )
            ->getTable( 'beehub_groups' );
    $this->assertTablesEqual( $expectedTable, $realTable );
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