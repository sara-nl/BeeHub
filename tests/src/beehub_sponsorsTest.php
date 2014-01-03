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
    $this->obj = new \BeeHub_Sponsors( '/system/sponsors/' );
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