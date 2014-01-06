<?php
/**
 * Contains tests for the class BeeHub_Users
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
 * Tests for the class BeeHub_Users
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_UsersTest extends BeeHub_Tests_Db_Test_Case {

  /**
   * @var  \BeeHub_Groups  The unit under test
   */
  private $obj;


  public function setUp() {
    parent::setUp();
    $this->obj = new \BeeHub_Users( '/system/users/' );
  }


  public function testMethod_POST_illegalUserName() {
    $_POST['user_name'] = '%$$#@FC4 b5vjdoe';
    $_POST['displayname'] = 'J Doe';
    $_POST['email'] = "j.doe@somedomain.com";
    $_POST['password'] = 'anew password for a new user';
    $headers = array();

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->method_POST( $headers );
  }


  public function testMethod_POST_emptyDisplayname() {
    $_POST['user_name'] = 'jdoe';
    $_POST['displayname'] = '';
    $_POST['email'] = "j.doe@somedomain.com";
    $_POST['password'] = 'anew password for a new user';
    $headers = array();

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->method_POST( $headers );
  }


  public function testMethod_POST_existingName() {
    $_POST['user_name'] = 'jane';
    $_POST['displayname'] = 'J Doe';
    $_POST['email'] = "j.doe@somedomain.com";
    $_POST['password'] = 'anew password for a new user';
    $headers = array();

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_CONFLICT );
    $this->obj->method_POST( $headers );
  }


// This test doesn't work yet in a non-production environment. The implementation
// of the code (not the test, but the unit under test) should improve so it is
// less dependent on the environment. In other words; it should be easier to
// spoof the environment
//  public function testMethod_POST() {
//    $_POST['user_name'] = 'jdoe';
//    $_POST['displayname'] = 'J Doe';
//    $_POST['email'] = "j.doe@somedomain.com";
//    $_POST['password'] = 'anew password for a new user';
//    $headers = array();
//
//    $this->expectOutputRegex( '/https 303 See Other/' );
//    $this->obj->method_POST();
//
//    $user = new \BeeHub_User( '/system/users/jdoe' );
//    $this->assertSame( $_POST['displayname'], $user->user_prop( \DAV::PROP_DISPLAYNAME ) );
//    $this->assertSame( $_POST['email'], $user->user_prop( \Beehub::PROP_EMAIL ) );
//    $this->assertTrue( $user->check_password( $_POST['password'] ) );
//
//    $userFolder = \DAV::$REGISTRY->resource( '/home/' . $_POST['user_name'] );
//    $beehubConfig = \BeeHub::config();
//    $this->assertSame( $user->path, $userFolder->user_prop( \DAV::PROP_OWNER ) );
//  }


  public function testReport_principal_property_search_invalidProperty() {
    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->report_principal_property_search( array( \DAV::PROP_CREATIONDATE => array( 'some date' ) ) );
  }


  public function testReport_principal_property_search() {
    $expected = array( '/system/users/jane' );
    $results = $this->obj->report_principal_property_search( array( \DAV::PROP_DISPLAYNAME => array( 'ja' ) ) );
    $this->assertSame( $expected, $results );
  }


  public function testUser_prop_acl_internal() {
    $expected = array( new \DAVACL_Element_ace( \DAVACL::PRINCIPAL_ALL, false, array( \DAVACL::PRIV_READ ), false, true ) );
    $this->assertEquals( $expected, $this->obj->user_prop_acl_internal() );
  }

} // class BeeHub_UsersTest

// End of file