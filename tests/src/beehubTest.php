<?php
/**
 * Contains tests for the class BeeHub
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

/**
 * Tests for the class BeeHub
 * @package     BeeHub
 * @subpackage  tests
 */
class beehubTest extends PHPUnit_Framework_TestCase {

  public function setUp() {
    reset_SERVER();
  }


  public function testBest_xhtml_type() {
    // For now it should always return 'text/html'
    $this->assertEquals( BeeHub::best_xhtml_type(), 'text/html', 'BeeHub::best_xml_type() should return correct value' );
  }


  public function testConfig() {
    // Because BeeHub::config() does very little, this test is more a configuration file syntax check, but at least it's something
    $this->assertFileExists( dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'config.ini', 'Configuration file is missing!' );
    $config = BeeHub::config();
    // I only check for the section names; else I'm checking parse_ini_file().
    $this->assertArrayHasKey( 'environment'   , $config, 'BeeHub::config() should contain the key \'environment\'' );
    $this->assertArrayHasKey( 'namespace'     , $config, 'BeeHub::config() should contain the key \'namespace\'' );
    $this->assertArrayHasKey( 'mysql'         , $config, 'BeeHub::config() should contain the key \'mysql\'' );
    $this->assertArrayHasKey( 'authentication', $config, 'BeeHub::config() should contain the key \'authentication\'' );
    $this->assertArrayHasKey( 'email'         , $config, 'BeeHub::config() should contain the key \'email\'' );
  }


  public function testEscapeshellarg() {
    $this->assertEquals( BeeHub::escapeshellarg( 'some text \' with quotes in it' ), "'" . 'some text \'\\\'\' with quotes in it' . "'", 'BeeHub::best_xml_type() should return correct value' );
  }


  public function testException_handler() {
    $status = $this->getMock( 'DAV_Status', array( 'output' ), array( DAV::HTTP_FORBIDDEN ) );
    $status->expects( $this->once() )
           ->method( 'output' );
    BeeHub::exception_handler( $status );

    $this->setExpectedException( 'PHPUnit_Framework_Error_Warning', 'Some message' );
    BeeHub::exception_handler( new Exception( 'Some message' ) );
  }

  
//  public function testUrlbase() {
//  }
//
//
//  public function testHtmlError() {
//  }
//
//
//  public function testLocalPath() {
//  }
//
//
//  public function testHandle_method_spoofing() {
//  }
//
//
//  public function testUser() {
//  }
//
//
//  public function testGroup() {
//  }
//
//
//  public function testSponsor() {
//  }
//
//
//  public function testNotifications() {
//  }
//
//
//  public function testEmail() {
//  }
//  
//  
//  public function testGetNoSQL() {
//  }
//
//  
//  public function testETag() {
//  }
  
} // Class beehubTest

// End of file