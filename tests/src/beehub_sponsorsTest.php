<?php
/**
 * Contains tests for the class BeeHub_Sponsors
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
 * Tests for the class BeeHub_Sponsors
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_SponsorsTest extends BeeHub_Tests_Db_Test_Case {

  /**
   * @var  \BeeHub_Sponsors  The unit under test
   */
  private $obj;


  public function setUp() {
    parent::setUp();
    setUp();
    $this->obj = new \BeeHub_Sponsors( '/system/sponsors/' );
  }


  public function testMethod_POST_withoutSponsor() {
    $this->setCurrentUser( '/system/users/jane' );

    $_POST['displayname'] = 'Some test sponsor of Jane';
    $_POST['description'] = "This is the description of Jane's test sponsor";
    $_POST['sponsor_name'] = 'janesponsor';
    $headers = array();

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_FORBIDDEN );
    $this->obj->method_POST( $headers );
  }


  public function testMethod_POST_illegalSponsorName() {
    $this->setCurrentUser( '/system/users/john' );

    $_POST['displayname'] = 'Some test sponsor of John';
    $_POST['description'] = "This is the description of John's test sponsor";
    $_POST['sponsor_name'] = '.johnsponsor with illegal characters like space and !@#$%*()';
    $headers = array();

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->method_POST( $headers );
  }


  public function testMethod_POST_emptyDisplayname() {
    $this->setCurrentUser( '/system/users/john' );

    $_POST['displayname'] = '';
    $_POST['description'] = "This is the description of John's test sponsor";
    $_POST['sponsor_name'] = 'johnsponsor';
    $headers = array();

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->method_POST( $headers );
  }


  public function testMethod_POST_existingName() {
    $this->setCurrentUser( '/system/users/john' );

    $_POST['displayname'] = 'Some test sponsor of John';
    $_POST['description'] = "This is the description of John's test sponsor";
    $_POST['sponsor_name'] = 'sponsor_a';
    $headers = array();

    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_CONFLICT );
    $this->obj->method_POST( $headers );
  }


  public function testMethod_POST() {
    $this->setCurrentUser( '/system/users/john' );

    $_POST['displayname'] = 'Some test sponsor of John';
    $_POST['description'] = "This is the description of John's test sponsor";
    $_POST['sponsor_name'] = 'johnsponsor';
    $headers = array();

    $this->expectOutputRegex( '/https 303 See Other/' );
    $this->obj->method_POST( $headers );

    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/johnsponsor' );
    $this->assertSame( $_POST['displayname'], $sponsor->user_prop( \DAV::PROP_DISPLAYNAME ) );
    $this->assertSame( $_POST['description'], $sponsor->user_prop( \BeeHub::PROP_DESCRIPTION ) );
  }


  public function testReport_principal_property_search() {
    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $this->obj->report_principal_property_search( array( \DAV::PROP_DISPLAYNAME => array( 'a' ) ) );
  }


  public function testReport_principal_search_property_set() {
    $this->assertSame( array(), $this->obj->report_principal_search_property_set() );
  }

}// class BeeHub_SponsorsTest

// End of file