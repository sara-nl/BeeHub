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

declare( encoding = 'UTF-8' );
namespace BeeHub\tests;

/**
 * Tests for the class BeeHub
 * @package     BeeHub
 * @subpackage  tests
 */
class beehubTest extends BeeHub_Tests_Db_Test_Case {

  public function testBest_xhtml_type() {
    // For now it should always return 'text/html'
    $this->assertEquals( \BeeHub::best_xhtml_type(), 'text/html', 'BeeHub::best_xml_type() should return correct value' );
  }


  public function testConfig() {
    // Because BeeHub::config() does very little, this test is more a configuration file syntax check, but at least it's something
    $this->assertFileExists( \dirname( \dirname( \dirname( __FILE__ ) ) ) . \DIRECTORY_SEPARATOR . 'config.ini', 'Configuration file is missing!' );
    $config = \BeeHub::config();
    // I only check for the section names; else I'm checking parse_ini_file().
    $this->assertArrayHasKey( 'environment'   , $config, 'BeeHub::config() should contain the key \'environment\'' );
    $this->assertArrayHasKey( 'namespace'     , $config, 'BeeHub::config() should contain the key \'namespace\'' );
    $this->assertArrayHasKey( 'mysql'         , $config, 'BeeHub::config() should contain the key \'mysql\'' );
    $this->assertArrayHasKey( 'authentication', $config, 'BeeHub::config() should contain the key \'authentication\'' );
    $this->assertArrayHasKey( 'email'         , $config, 'BeeHub::config() should contain the key \'email\'' );
  }


  public function testEscapeshellarg() {
    $this->assertEquals( \BeeHub::escapeshellarg( 'some text \' with quotes in it' ), "'" . 'some text \'\\\'\' with quotes in it' . "'", 'BeeHub::best_xml_type() should return correct value' );
  }


  public function testException_handler() {
    $status = $this->getMock( 'DAV_Status', array( 'output' ), array( \DAV::HTTP_FORBIDDEN ) );
    $status->expects( $this->once() )
           ->method( 'output' );
    \BeeHub::exception_handler( $status );

    $this->setExpectedException( 'PHPUnit_Framework_Error_Warning', 'Some message' );
    \BeeHub::exception_handler( new \Exception( 'Some message' ) );
  }


  public function testGroup() {
    $group = $this->getMock( 'BeeHub_Group', array( 'init_props' ), array( '/system/groups/test_group' ) );
    $group->expects( $this->any() )
          ->method( 'init_props' );
    $registryMock = $this->getMock( 'BeeHub_Registry', array( 'resource' ) );
    $registryMock->expects( $this->once() )
                 ->method( 'resource' )
                 ->will( $this->returnValue( $group ) );
    \DAV::$REGISTRY = $registryMock;

    $this->assertSame( $group, \BeeHub::group( '/system/groups/test_group' ), 'BeeHub::group() should return a group if the path is correct' );

    $registryMockNull = $this->getMock( 'BeeHub_Registry', array( 'resource' ) );
    $registryMockNull->expects( $this->once() )
                     ->method( 'resource' )
                     ->will( $this->returnValue( null ) );
    \DAV::$REGISTRY = $registryMockNull;

    $this->setExpectedException( 'DAV_Status' );
    \BeeHub::group( '/system/groups/test_group', null, \DAV::HTTP_FORBIDDEN );
  }


  public function testHandle_method_spoofing() {
    $_GET = array();
    $_GET['_method'] = 'PROPFIND';
    $_GET['other_variable'] = 'some value';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['QUERY_STRING'] = \http_build_query( $_GET );
    $_SERVER['REQUEST_URI'] = '/some/path?' . $_SERVER['QUERY_STRING'];

    \BeeHub::handle_method_spoofing();
    $this->assertSame( 'GET'     , $_SERVER['REQUEST_METHOD'], 'Method spoofing should only be possible when the original method was POST' );
    $this->assertSame( 'PROPFIND', $_GET['_method']          , "No method spoofing? Then \$_GET['_method'] should stay as it was" );

    $_SERVER['REQUEST_METHOD'] = 'POST';
    \BeeHub::handle_method_spoofing();
    $this->assertSame( 'POST'                                , $_SERVER['ORIGINAL_REQUEST_METHOD'], "\$_SERVER['ORIGINAL_REQUEST_METHOD'] should be set when doing method spoofing" );
    $this->assertSame( 'PROPFIND'                            , $_SERVER['REQUEST_METHOD']         , 'Method should be spoofed to PROPFIND' );
    $this->assertSame( 'other_variable=some+value'           , $_SERVER['QUERY_STRING']           , "\$_SERVER['QUERY_STRING'] should not contain the _method part anymore" );
    $this->assertSame( '/some/path?other_variable=some+value', $_SERVER['REQUEST_URI']            , "\$_SERVER['REQUEST_URI'] should not contain the _method part anymore" );
    $this->assertNull(                                         @$_GET['_method']                  , "\$_GET['_method'] should be cleared when doing method spoofing" );
    $this->assertSame( 'some value'                          , $_GET['other_variable']            , "Other \$_GET keys should remain when doing method spoofing" );


    $_GET = array();
    $_GET['_method'] = 'GET';
    $_GET['other_variable'] = 'some value';
    $originalPost = array();
    $originalPost['post_variable'] = 'also has some value';
    $_POST = $originalPost;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['QUERY_STRING'] = \http_build_query( $_GET );
    $_SERVER['REQUEST_URI'] = '/some/path?' . $_SERVER['QUERY_STRING'];
    \BeeHub::handle_method_spoofing();
    $this->assertSame( 'GET'                                         , $_SERVER['REQUEST_METHOD'], 'Method should be spoofed to GET' );
    $this->assertSame( array()                                       , $_POST                    , "\$_POST should be cleared when method is spoofed to GET" );
    $this->assertSame( $originalPost                                 , $_GET                     , "\$_GET should contain all the variables originally POSTed");
    $this->assertSame( 'post_variable=also+has+some+value'           , $_SERVER['QUERY_STRING']  , "\$_SERVER['QUERY_STRING'] should reflect the original \$_POST" );
    $this->assertSame( '/some/path?post_variable=also+has+some+value', $_SERVER['REQUEST_URI']   , "\$_SERVER['REQUEST_URI'] should reflect the original \$_POST" );
  }


  public function testLocalPath() {
    $config = \BeeHub::config();
    $this->assertSame( $config['environment']['datadir'] . '/some/path', \BeeHub::localPath( '/some/path' ), 'BeeHub::localPath() should prepend the path of the data dir to the path provided' );
  }


  public function testNotifications() {
    // First check the notifications for John
    $authJohn = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'is_authenticated', 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJohn->expects( $this->any() )
             ->method( 'is_authenticated' )
             ->will( $this->returnValue( true ) );
    $authJohn->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( new \BeeHub_User( '/system/users/john' ) ) );

    $expectedJohn = array(
        array(
            'type' => 'group_request',
            'data' => array(
                'group' => '/system/groups/bar',
                'group_displayname' => 'Bar',
                'user' => '/system/users/jane',
                'user_displayname' => 'Jane Doe',
                'user_email' => 'jane.doe@mailservice.com'
            )
        ),
        array(
            'type' => 'sponsor_request',
            'data' => array(
                'sponsor' => '/system/sponsors/sponsor_b',
                'sponsor_displayname' => 'Company B',
                'user' => '/system/users/jane',
                'user_displayname' => 'Jane Doe',
                'user_email' => 'jane.doe@mailservice.com'
            )
        )
    );

    $this->assertSame( \BeeHub::notifications( $authJohn ), $expectedJohn, 'BeeHub::notifications() should return an array with the correct notifications for John Doe' );

    // And check the notifications for Jane
    $authJane = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'is_authenticated', 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $authJane->expects( $this->any() )
             ->method( 'is_authenticated' )
             ->will( $this->returnValue( true ) );
    $authJane->expects( $this->any() )
             ->method( 'current_user' )
             ->will( $this->returnValue( new \BeeHub_User( '/system/users/jane' ) ) );

    $expectedJane = array(
        array(
            'type' => 'group_invitation',
            'data' => array(
                'group' => '/system/groups/foo',
                'displayname' => 'Foo'
            )
        ),
        array(
            'type' => 'no_sponsor',
            'data' => array()
        )
    );
    
    $this->assertSame( \BeeHub::notifications( $authJane ), $expectedJane, 'BeeHub::notifications() should return an array with the correct notifications for Jane Doe' );
  }


  public function testSponsor() {
    $sponsor = $this->getMock( 'BeeHub_Sponsor', array( 'init_props' ), array( '/system/sponsors/test_sponsor' ) );
    $sponsor->expects( $this->any() )
          ->method( 'init_props' );
    $registryMock = $this->getMock( 'BeeHub_Registry', array( 'resource' ) );
    $registryMock->expects( $this->once() )
                 ->method( 'resource' )
                 ->will( $this->returnValue( $sponsor ) );
    \DAV::$REGISTRY = $registryMock;

    $this->assertSame( $sponsor, \BeeHub::sponsor( '/system/sponsors/test_sponsor' ), 'BeeHub::sponsor() should return a sponsor if the path is correct' );

    $registryMockNull = $this->getMock( 'BeeHub_Registry', array( 'resource' ) );
    $registryMockNull->expects( $this->once() )
                     ->method( 'resource' )
                     ->will( $this->returnValue( null ) );
    \DAV::$REGISTRY = $registryMockNull;

    $this->setExpectedException( 'DAV_Status' );
    \BeeHub::group( '/system/sponsors/test_sponsor', null, \DAV::HTTP_FORBIDDEN );
  }

  
  public function testUrlbase() {
    $this->assertSame( 'https://beehub.nl', \BeeHub::urlbase(), 'BeeHub::urlbase() should return the correct base URL' );

    $_SERVER['SERVER_NAME'] = 'www.beehub.nl';
    unset( $_SERVER['HTTPS'] );
    $this->assertSame( 'http://www.beehub.nl:443', \BeeHub::urlbase(), 'BeeHub::urlbase() should return the correct base URL for none HTTPS requests' );
  }


  public function testUser() {
    $user = $this->getMock( 'BeeHub_User', array( 'init_props' ), array( '/system/users/test_user' ) );
    $user->expects( $this->any() )
          ->method( 'init_props' );
    $registryMock = $this->getMock( 'BeeHub_Registry', array( 'resource' ) );
    $registryMock->expects( $this->once() )
                 ->method( 'resource' )
                 ->will( $this->returnValue( $user ) );
    \DAV::$REGISTRY = $registryMock;

    $this->assertSame( $user, \BeeHub::user( '/system/users/test_user' ), 'BeeHub::user() should return a user if the path is correct' );

    $registryMockNull = $this->getMock( 'BeeHub_Registry', array( 'resource' ) );
    $registryMockNull->expects( $this->once() )
                     ->method( 'resource' )
                     ->will( $this->returnValue( null ) );
    \DAV::$REGISTRY = $registryMockNull;

    $this->setExpectedException( 'DAV_Status' );
    \BeeHub::group( '/system/users/test_user', null, \DAV::HTTP_FORBIDDEN );
  }
  
} // Class beehubTest

// End of file