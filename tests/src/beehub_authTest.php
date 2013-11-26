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

  public function setUp() {
    parent::setUp();
    reset_SERVER();
    \DAV::$REGISTRY = \BeeHub_Registry::inst();
  }


  public function testInst() {
    $this->assertInstanceOf( 'BeeHub_Auth', BeeHub_Auth::inst(), 'BeeHub_Auth::inst() should return an instance of BeeHub_Auth' );
  }


  public function testHandle_authenticationLogout() {
    $simpleSaml = $this->getMock( 'SimpleSAML_Auth_Simple', array( 'isAuthenticated', 'logout' ), array( 'BeeHub' ) );
    $simpleSaml->expects( $this->any() )
               ->method( 'isAuthenticated' )
               ->will( $this->returnValue( true ) );
    $simpleSaml->expects( $this->once() )
               ->method( 'logout' );

    $_GET['logout'] = 1;
    unset( $_SERVER['HTTPS'] );
    unset( $_SERVER['PHP_AUTH_USER'] );
    unset( $_SERVER['PHP_AUTH_PW'] );
    $_SERVER['REQUEST_URI'] = \BeeHub::USERS_PATH;
    $obj = new BeeHub_Auth( $simpleSaml );
    $obj->handle_authentication();
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

    // No need to put an explicit assertion here; if things go wrong, handle_authentication() is made to generate error messages

    // And check for wrong passwords
    $_SERVER['PHP_AUTH_USER'] = 'john';
    $_SERVER['PHP_AUTH_PW'] = 'wrong password';

    $objUnauthorized = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'unauthorized' ), array( $simpleSaml ) );
    $objUnauthorized->expects( $this->once() )
                    ->method( 'unauthorized' );
    $objUnauthorized->handle_authentication( true, true); //Do not stumble on a double login (SimpleSaml looks like it's logged in)
  }

  public function ttestHandle_authenticationHTTP2() {
    if ( ( 'passwd' !== @$_GET['login'] ) && $this->simpleSAML_authentication->isAuthenticated() ) {
      $surfId = $this->simpleSAML_authentication->getAuthData("saml:sp:NameID");
      $surfId = $surfId['Value'];
      $statement = BeeHub_DB::execute('SELECT `user_name` FROM `beehub_users` WHERE `surfconext_id`=?', 's', $surfId);
      if ( $row = $statement->fetch_row() ) { // We found a user, this is the one that's logged in!
        $this->SURFconext = true;
        $this->set_user( $row[0] );
      } elseif ($_SERVER['REQUEST_URI'] !== BeeHub::USERS_PATH) {
        throw new DAV_Status(
          DAV::HTTP_TEMPORARY_REDIRECT,
          BeeHub::urlbase(true) . BeeHub::USERS_PATH
        );
      }
    } elseif ( ('conext' === @$_GET['login']) ) { // We don't know this SURFconext ID, this is a new user
        $this->simpleSAML_authentication->login();
    } elseif ( ( 'passwd' === @$_GET['login'] ) || $requireAuth ) {
      // If the user didn't send any credentials, but we require authentication, ask for it!
      $this->unauthorized();
    }

    // If the current user is logged in, but has no verified e-mail address.
    // He/she is not authorized to do anything, but will get a message that we
    // want a verified e-mail address. Although he has to be able to verify
    // his e-mail address of course (so GET and POST on /system/users/<name>
    // is allowed)
    $user = $this->current_user();
    if (!is_null($user)) {
      $email = $user->prop(BeeHub::PROP_EMAIL);
      if ( empty($email) &&
           DAV::unslashify( DAV::getPath() ) != DAV::unslashify($user->path) ) {
        $message = file_get_contents( dirname( dirname ( __FILE__ ) ) . '/views/error_no_verified_email.html' );
        $message = str_replace( '%USER_PATH%', BeeHub::urlbase(true) . $user->path, $message );
        BeeHub::htmlError( $message, DAV::HTTP_FORBIDDEN );
      }
    }
  }


//  /**
//   * Sets the current user
//   *
//   * @param   string  $user_name  The user name
//   * @return  void
//   */
//  private function set_user($user_name) {
//    $this->currentUserPrincipal = BeeHub::user( $user_name );
//  }
//
//
//  /**
//   * Gives the currently logged in user
//   *
//   * @return  BeeHub_User  The currently logged in user or NULL if no user is
//   *   logged in.
//   */
//  public function current_user() {
//    return $this->currentUserPrincipal;
//  }
//
//
//  /**
//   * Is the current user authenticated?
//   *
//   * @return  boolean  True if the user is authenticated, false otherwise
//   */
//  public function is_authenticated() {
//    $cup = BeeHub_ACL_Provider::inst()->user_prop_current_user_principal();
//    return (boolean) $cup;
//  }
//
//
//  /**
//   * Checks if this user is logged in through SURFconext
//   * @return  boolean  True if the user is logged in through SURFconext, false otherwise
//   */
//  public function surfconext() {
//    return $this->SURFconext;
//  }
//
//
//  /**
//   * Fetches the SimpleSaml object
//   * @return  SimpleSAML_Auth_Simple  The SimpleSAML_Auth_Simple instance used for authentication
//   */
//  public function simpleSaml() {
//    return $this->simpleSAML_authentication;
//  }
//
//
//  /**
//   * This method is called when DAV receives an 401 Unauthenticated exception.
//   * @return bool true if a response has been sent to the user.
//   */
//  public function unauthorized() {
//    DAV::header( array(
//      'WWW-Authenticate' => 'Basic realm="' . BeeHub::$CONFIG['authentication']['realm'] . '"',
//      'Content-Type' => BeeHub::best_xhtml_type()
//    ) );
//    BeeHub::htmlError(
//            file_get_contents( dirname( dirname ( __FILE__ ) ) . '/views/error_unauthorized.html' ) ,
//            DAV::HTTP_UNAUTHORIZED
//    );
//  }
//
//
//  /**
//   * Determines whether you need to authenticate based on the method and URL of the request
//   * @return  boolean  True if authentication is required, false otherwise
//   */
//  public static function is_authentication_required() {
//    $path = DAV::unslashify( DAV::getPath() );
//    /**
//     * You don't need to authenticate when:
//     * - GET (or HEAD) or POST on the users collection (required to create a new user)
//     * - GET (or HEAD) on the system collection (required to read the 'homepage')
//     * In other cases you do need to authenticate
//     */
//    $noRequireAuth = (
//      (
//        $path === DAV::unslashify( BeeHub::USERS_PATH ) &&
//        in_array( $_SERVER['REQUEST_METHOD'], array('GET', 'POST', 'HEAD') )
//      ) ||
//      (
//        $path === DAV::unslashify( BeeHub::SYSTEM_PATH ) &&
//        in_array($_SERVER['REQUEST_METHOD'], array('GET', 'HEAD') )
//      )
//    );
//
//    return ! $noRequireAuth;
//  }

} // class BeeHub_AuthTest

// End of file