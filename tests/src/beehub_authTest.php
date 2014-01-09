<?php
/**
 * Contains tests for the class BeeHub_Auth
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
 * Tests for the class BeeHub_Auth
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_AuthTest extends BeeHub_Tests_Db_Test_Case {

  public function testInst() {
    $this->assertInstanceOf( 'BeeHub_Auth', BeeHub_Auth::inst(), 'BeeHub_Auth::inst() should return an instance of BeeHub_Auth' );
  }


  public function testHandle_authenticationLogout() {
    $simpleSaml = $this->getMock( 'SimpleSAML_Auth_Simple', array( 'getAuthData', 'isAuthenticated', 'logout' ), array( 'BeeHub' ) );
    $simpleSaml->expects( $this->once() )
               ->method( 'getAuthData' )
               ->with( $this->equalTo( 'saml:sp:NameID' ) )
               ->will( $this->returnValue( array( 'Value' => 'qwertyuiop' ) ) );
    $simpleSaml->expects( $this->any() )
               ->method( 'isAuthenticated' )
               ->will( $this->onConsecutiveCalls( true, true, false ) );
    $simpleSaml->expects( $this->once() )
               ->method( 'logout' );

    unset( $_SERVER['HTTPS'], $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );
    $_SERVER['REQUEST_URI'] = \BeeHub::USERS_PATH;
    $obj = new BeeHub_Auth( $simpleSaml );
    $obj->handle_authentication( false );
    $this->assertSame( '/system/users/jane', $obj->current_user()->path, 'BeeHub_Auth::current_user() should be set to the principal path of Jane Doe before logout' );
    $this->assertTrue( $obj->is_authenticated(), 'BeeHub_Auth::is_authenticated() should be true before logout' );
    $this->assertTrue( $obj->surfconext(), 'BeeHub_Auth::surfconext() should return the true before logout' );

    $_GET['logout'] = 1;
    $obj->handle_authentication( false );
    $this->assertNull( $obj->current_user(), 'BeeHub_Auth::current_user() should be null after logout' );
    $this->assertFalse( $obj->is_authenticated(), 'BeeHub_Auth::is_authenticated() should be false after logout' );
    $this->assertFalse( $obj->surfconext(), 'BeeHub_Auth::surfconext() should return the false after logout' );
  }


  public function testHandle_authenticationHTTP() {
    $simpleSaml = $this->getMock( 'SimpleSAML_Auth_Simple', array( 'isAuthenticated', 'logout' ), array( 'BeeHub' ) );
    $simpleSaml->expects( $this->any() )
               ->method( 'isAuthenticated' )
               ->will( $this->returnValue( true ) );
    $simpleSaml->expects( $this->once() )
               ->method( 'logout' );

    $_SERVER['PHP_AUTH_USER'] = 'john';
    $_SERVER['PHP_AUTH_PW'] = 'password_of_john';

    $obj = new BeeHub_Auth( $simpleSaml );
    $obj->handle_authentication();
    $this->assertSame( '/system/users/john', $obj->current_user()->path, 'BeeHub_Auth::current_user() should now be set to the principal path of John Doe' );
    $this->assertTrue( $obj->is_authenticated(), 'BeeHub_Auth::is_authenticated() should be true after HTTP login' );
    $this->assertFalse( $obj->surfconext(), 'BeeHub_Auth::surfconext() should return the false after HTTP login' );

    // And check for wrong passwords
    $simpleSamlWrongPwd = $this->getMock( 'SimpleSAML_Auth_Simple', array( 'getAuthData', 'isAuthenticated', 'logout' ), array( 'BeeHub' ) );
    $simpleSamlWrongPwd->expects( $this->once() )
                       ->method( 'getAuthData' )
                       ->with( $this->equalTo( 'saml:sp:NameID' ) )
                       ->will( $this->returnValue( array( 'Value' => 'qwertyuiop' ) ) );
    $simpleSamlWrongPwd->expects( $this->any() )
                       ->method( 'isAuthenticated' )
                       ->will( $this->onConsecutiveCalls( true, true, false ) );
    $simpleSamlWrongPwd->expects( $this->once() )
                       ->method( 'logout' );
    $objUnauthorized = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'unauthorized' ), array( $simpleSamlWrongPwd ) );
    $objUnauthorized->expects( $this->once() )
                    ->method( 'unauthorized' );

    unset( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );
    $objUnauthorized->handle_authentication();
    $this->assertSame( '/system/users/jane', $objUnauthorized->current_user()->path, 'BeeHub_Auth::current_user() should be set to the principal path of Jane Doe before attempting a password login with the wrong password' );
    $this->assertTrue( $objUnauthorized->is_authenticated(), 'BeeHub_Auth::is_authenticated() should be true before attempting a password login with the wrong password' );
    $this->assertTrue( $objUnauthorized->surfconext(), 'BeeHub_Auth::surfconext() should return the true before attempting a password login with the wrong password' );

    $_SERVER['PHP_AUTH_USER'] = 'john';
    $_SERVER['PHP_AUTH_PW'] = 'wrong password';

    $objUnauthorized->handle_authentication();
    $this->assertNull( $objUnauthorized->current_user(), 'BeeHub_Auth::current_user() should be null after using a wrong password' );
    $this->assertFalse( $objUnauthorized->is_authenticated(), 'BeeHub_Auth::is_authenticated() should be false after using a wrong password' );
    $this->assertfalse( $objUnauthorized->surfconext(), 'BeeHub_Auth::surfconext() should return the false after using a wrong password' );
  }

  public function testHandle_authenticationSimpleSaml() {
    unset( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );

    // First test when not logged in yet, but when we want to login using SimpleSaml
    $simpleSamlLogin = $this->getMock( 'SimpleSAML_Auth_Simple', array( 'login', 'isAuthenticated' ), array( 'BeeHub' ) );
    $simpleSamlLogin->expects( $this->once() )
                    ->method( 'login' );
    $simpleSamlLogin->expects( $this->any() )
                    ->method( 'isAuthenticated' )
                    ->will( $this->returnValue( false ) );

    $_GET['login'] = 'conext';

    $objLogin = new BeeHub_Auth( $simpleSamlLogin );
    $objLogin->handle_authentication();

    // And test once when simpleSaml is logged in
    $simpleSaml = $this->getMock( 'SimpleSAML_Auth_Simple', array( 'getAuthData', 'isAuthenticated' ), array( 'BeeHub' ) );
    $simpleSaml->expects( $this->once() )
               ->method( 'getAuthData' )
               ->with( $this->equalTo( 'saml:sp:NameID' ) )
               ->will( $this->returnValue( array( 'Value' => 'qwertyuiop' ) ) );
    $simpleSaml->expects( $this->any() )
               ->method( 'isAuthenticated' )
               ->will( $this->returnValue( true ) );

    $_GET['login'] = 'conext';

    $obj = new BeeHub_Auth( $simpleSaml );
    $obj->handle_authentication();
    $this->assertSame( '/system/users/jane', $obj->current_user()->path, 'BeeHub_Auth::current_user() should now be set to the principal path of Jane Doe' );
    $this->assertTrue( $obj->is_authenticated(), 'BeeHub_Auth::is_authenticated() should be true when Jane Doe is logged in' );
    $this->assertTrue( $obj->surfconext(), 'BeeHub_Auth::surfconext() should return the true when Jane Doe logs in through SimpleSaml' );

    // And test once when simpleSaml is logged in
    $simpleSamlUnknown = $this->getMock( 'SimpleSAML_Auth_Simple', array( 'login', 'getAuthData', 'isAuthenticated' ), array( 'BeeHub' ) );
    $simpleSamlUnknown->expects( $this->once() )
                      ->method( 'getAuthData' )
                      ->with( $this->equalTo( 'saml:sp:NameID' ) )
                      ->will( $this->returnValue( array( 'Value' => 'unknown id' ) ) );
    $simpleSamlUnknown->expects( $this->any() )
                      ->method( 'isAuthenticated' )
                      ->will( $this->returnValue( true ) );

    $_GET['login'] = 'conext';

    $objUnknown = new BeeHub_Auth( $simpleSamlUnknown );
    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_TEMPORARY_REDIRECT );
    $objUnknown->handle_authentication();
  }


  public function testSimpleSaml() {
    $simpleSaml = $this->getMock( 'SimpleSAML_Auth_Simple', null, array( 'BeeHub' ) );
    $obj = new BeeHub_Auth( $simpleSaml );
    $this->assertSame( $simpleSaml, $obj->simpleSaml(), 'BeeHub_Auth::simpleSaml() should return the simpleSaml instance provided to the constructor' );
  }


  public function testIs_authentication_required() {
    $_SERVER['REQUEST_URI'] = \BeeHub::USERS_PATH;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $this->assertFalse( \BeeHub_Auth::is_authentication_required(), 'BeeHub_Auth::is_authentication_required() should return false when doing GET on the users collection (required to create a new user)' );
    $_SERVER['REQUEST_METHOD'] = 'HEAD';
    $this->assertFalse( \BeeHub_Auth::is_authentication_required(), 'BeeHub_Auth::is_authentication_required() should return false when doing HEAD on the users collection (required to create a new user)' );
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $this->assertFalse( \BeeHub_Auth::is_authentication_required(), 'BeeHub_Auth::is_authentication_required() should return false when doing POST on the users collection (required to create a new user)' );
    $_SERVER['REQUEST_METHOD'] = 'PUT';
    $this->assertTrue ( \BeeHub_Auth::is_authentication_required(), 'BeeHub_Auth::is_authentication_required() should return true when using another request method on the users collection (required to create a new user)' );

    $_SERVER['REQUEST_URI'] = \BeeHub::SYSTEM_PATH;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $this->assertFalse( \BeeHub_Auth::is_authentication_required(), 'BeeHub_Auth::is_authentication_required() should return false when doing GET on the system collection (required to read the homepage)' );
    $_SERVER['REQUEST_METHOD'] = 'HEAD';
    $this->assertFalse( \BeeHub_Auth::is_authentication_required(), 'BeeHub_Auth::is_authentication_required() should return false when doing HEAD on the system collection (required to read the homepage)' );
    $_SERVER['REQUEST_METHOD'] = 'PUT';
    $this->assertTrue ( \BeeHub_Auth::is_authentication_required(), 'BeeHub_Auth::is_authentication_required() should return false when using another request method on the system collection (required to read the homepage)' );

    $_SERVER['REQUEST_URI'] = '/some/other/path';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $this->assertTrue ( \BeeHub_Auth::is_authentication_required(), 'BeeHub_Auth::is_authentication_required() should return true on all other locations' );
  }

} // class BeeHub_AuthTest

// End of file