<?php
/**
 * Contains tests for the class BeeHub_Groups
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
 * Tests for the class BeeHub_Groups
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_GroupsTest extends BeeHub_Tests_Db_Test_Case {

  /**
   * @var  \BeeHub_Groups  The unit under test
   */
  private $obj;


  public function setUp() {
    parent::setUp();
    $this->obj = new \BeeHub_Groups( '/system/groups/' );
  }


  public function testMethod_POST_withoutSponsor() {
    $this->setCurrentUser( '/system/users/jane' );

    $_POST['displayname'] = 'Some test group of Jane';
    $_POST['description'] = "This is the description of Jane's test group";
    $_POST['group_name'] = 'janegroup';
    $headers = array();

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_POST( $headers );
  }


  public function testMethod_POST_illegalGroupName() {
    $this->setCurrentUser( '/system/users/john' );

    $_POST['displayname'] = 'Some test group of John';
    $_POST['description'] = "This is the description of John's test group";
    $_POST['group_name'] = '.johngroup with illegal characters like space and !@#$%*()';
    $headers = array();

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->method_POST( $headers );
  }


  public function testMethod_POST_emptyDisplayname() {
    $this->setCurrentUser( '/system/users/john' );

    $_POST['displayname'] = '';
    $_POST['description'] = "This is the description of John's test group";
    $_POST['group_name'] = 'johngroup';
    $headers = array();

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->method_POST( $headers );
  }


  public function testMethod_POST_existingName() {
    $this->setCurrentUser( '/system/users/john' );

    $_POST['displayname'] = 'Some test group of John';
    $_POST['description'] = "This is the description of John's test group";
    $_POST['group_name'] = 'foo';
    $headers = array();

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_CONFLICT );
    $this->obj->method_POST( $headers );
  }


  public function testMethod_POST() {
    if ( ! setUpStorageBackend() ) {
      $this->markTestSkipped( 'No storage backend specified; all tests depending on the storage backend are skipped' );
      return;
    }
    $this->setCurrentUser( '/system/users/john' );

    $_POST['displayname'] = 'Some test group of John';
    $_POST['description'] = "This is the description of John's test group";
    $_POST['group_name'] = 'johngroup';
    $headers = array();

    $this->expectOutputRegex( '/https 303 See Other/' );
    $this->obj->method_POST( $headers );

    $group = new \BeeHub_Group( '/system/groups/johngroup' );
    $this->assertSame( $_POST['displayname'], $group->user_prop( \DAV::PROP_DISPLAYNAME ) );
    $this->assertSame( $_POST['description'], $group->user_prop( \BeeHub::PROP_DESCRIPTION ) );

    $groupFolder = \DAV::$REGISTRY->resource( '/' . $_POST['group_name'] );
    $beehubConfig = \BeeHub::config();
    $expectedAcl = array( new \DAVACL_Element_ace( $group->path, false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false, false ) );
    $this->assertNull( $groupFolder->user_prop( \DAV::PROP_OWNER ) );
    $this->assertEquals( $expectedAcl, $groupFolder->user_prop_acl_internal() );
    $this->assertSame( '/system/sponsors/sponsor_a', $groupFolder->user_prop( \BeeHub::PROP_SPONSOR ) );
  }


  public function testReport_principal_property_search_invalidProperty() {
    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->report_principal_property_search( array( \DAV::PROP_CREATIONDATE => array( 'some date' ) ) );
  }


  public function testReport_principal_property_search() {
    $expected = array( '/system/groups/foo' );
    $results = $this->obj->report_principal_property_search( array( \DAV::PROP_DISPLAYNAME => array( 'fo' ) ) );
    $this->assertSame( $expected, $results );
  }

} // class BeeHub_GroupsTest

// End of file