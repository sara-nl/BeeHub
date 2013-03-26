<?php

/*·************************************************************************
 * Copyright ©2007-2012 SARA b.v., Amsterdam, The Netherlands
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
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package BeeHub
 */

/**
 * Handles authentication
 * @package BeeHub
 */
class BeeHub_Auth {


  /**
   * @var  boolean  True if the user is logged in through SURFconext, false otherwise
   */
  private $SURFconext = false;

  /**
   * @var  SimpleSAML_Auth_Simple  The SimpleSAML_Auth_Simple instance used for authentication
   */
  private $simpleSAML_authentication;

  /**
   * This class is a singleton, so the constructor is private. Instantiate through BeeHub_Auth::inst()
   */
  private function __construct() {
    $this->simpleSAML_authentication = new SimpleSAML_Auth_Simple('BeeHub');
  }

  /**
   * Gets the only instance of this class
   *
   * @return  BeeHub_Auth  The only instance of this class
   */
  public static function inst() {
    static $inst = null;
    if (is_null($inst)) {
      $inst = new BeeHub_Auth();
    }
    return $inst;
  }

  /**
   * Authenticates the user through one of the authentication mechanisms.
   * @param  boolean $requireAuth  If set to false and authentication fails,
   *   the user will continue as an unauthenticated user. If set to true
   *   (default), status 401 UNAUTHORIZED will be returned upon authentication
   *   failure.
   * @param  boolean  $allowDoubleLogin  TODO documentation
   */
  public function handle_authentication($requireAuth = true, $allowDoubleLogin = false) {
    if (isset($_GET['logout'])) {
      if ($this->simpleSAML_authentication->isAuthenticated()) {
        $this->simpleSAML_authentication->logout();
      }
      if (!empty($_SERVER['HTTPS'])) {
        DAV::redirect(DAV::HTTP_SEE_OTHER, BeeHub::urlbase(false) . '/system/');
        return;
      }
    }

    if ( isset($_SERVER['PHP_AUTH_PW'])) {
      if (!$allowDoubleLogin) {
        if ( $this->simpleSAML_authentication->isAuthenticated() ) { // You can't be logged in through SURFconext and HTTP Basic at the same time!
          $this->simpleSAML_authentication->logout();
        }
        if ('conext' === @$_GET['login']) {
          throw new DAV_Status(DAV::HTTP_BAD_REQUEST, "You are already logged in using your username/password. Therefore you are not allowed to login using SURFconext. Unfortunately the only way to logout with your username and password is to close all browser windows. Hit the 'back' button in your browser and login using username/password.");
        }
      }
      // The user already sent username and password: check them!
      try{
        $user = BeeHub::user($_SERVER['PHP_AUTH_USER']);
        $password_verified = $user->check_password($_SERVER['PHP_AUTH_PW']);
      }catch (DAV_Status $status) {
        if ( $status->getCode() === DAV::HTTP_FORBIDDEN ) {
          $password_verified = false;
        }
      }
      if ( ! $password_verified ) {
        // If authentication fails, respond accordingly
        if ( ( 'passwd' === @$_GET['login'] ) || $requireAuth ) {
          // User could not be authenticated with supplied credentials, but we
          // require authentication, so we ask again!
          $this->unauthorized();
          exit;
        }
      } else { // Authentication succeeded: store credentials!
        $this->set_user(rawurlencode( $_SERVER['PHP_AUTH_USER'] ));
      }
      // end of: if (user sent username/passwd)
    } elseif ( ( 'passwd' !== @$_GET['login'] ) && $this->simpleSAML_authentication->isAuthenticated() ) {
      $surfId = $this->simpleSAML_authentication->getAuthData("saml:sp:NameID");
      $surfId = $surfId['Value'];
      $statement = BeeHub_DB::execute('SELECT `user_name` FROM `beehub_users` WHERE `surfconext_id`=?', 's', $surfId);
      if ( $row = $statement->fetch_row() ) { // We found a user, this is the one that's logged in!
        $this->SURFconext = true;
        $this->set_user( $row[0] );
      } elseif ($_SERVER['REQUEST_URI'] !== BeeHub::$CONFIG['namespace']['users_path']) {
        throw new DAV_Status(
          DAV::HTTP_TEMPORARY_REDIRECT,
          BeeHub::urlbase(true) . BeeHub::$CONFIG['namespace']['users_path']
        );
      }
    } elseif ( ('conext' === @$_GET['login']) ) { // We don't know this SURFconext ID, this is a new user
        $this->simpleSAML_authentication->login();
    } elseif ( ( 'passwd' === @$_GET['login'] ) || $requireAuth ) {
      // If the user didn't send any credentials, but we require authentication, ask for it!
      $this->unauthorized();
      exit;
    }

    // If the current user is logged in, but has no verified e-mail address.
    // He/she is not authorized to do anything, but will get a message that we
    // want a verified e-mail address. Although he has to be able to verify
    // his e-mail address of course (so GET and POST on /system/users/<name>
    // is allowed
    $user = $this->current_user();
    if (!is_null($user)) {
      $email = $user->prop(BeeHub::PROP_EMAIL);
      if ( empty($email) &&
           DAV::unslashify(DAV::$PATH) != DAV::unslashify($user->path) ) {
        // TODO: how to sent this message with this status code in a webDAV/Pieter friendly way?
        // TODO: Kick Niek in the ass for this prutswerkje. [PvB]
        header('HTTP/1.1 ' . DAV::status_code(DAV::HTTP_FORBIDDEN));
        die("Your e-mail address has not been verified yet. If you don't verify your e-mail address within 24 hours after creating your account, your account will be deleted. Please check your mailbox for the verification e-mail with instructions on how to proceed. Or copy the verification code from that e-mail and fill it out in your profile page: " . BeeHub::urlbase(true) . $user->path);
      }
    }
  }

  /**
   * Sets the current user
   *
   * @param   string  $user_name  The user name
   * @return  void
   */
  private function set_user($user_name) {
    BeeHub_ACL_Provider::inst()->CURRENT_USER_PRINCIPAL =
      BeeHub::$CONFIG['namespace']['users_path'] . $user_name;
  }


  /**
   * Gives the currently logged in user
   *
   * @return  BeeHub_User  The currently logged in user or NULL if no user is
   *   logged in.
   */
  public function current_user() {
    $cup = BeeHub_ACL_Provider::inst()->user_prop_current_user_principal();
    return $cup ? BeeHub::user($cup) : null;
  }


  /**
   * Is the current user authenticated?
   *
   * @return  boolean  True if the user is authenticated, false otherwise
   */
  public function is_authenticated() {
    $cup = BeeHub_ACL_Provider::inst()->user_prop_current_user_principal();
    return (boolean) $cup;
  }


  /**
   * Checks if this user is logged in through SURFconext
   * @return  boolean  True if the user is logged in through SURFconext, false otherwise
   */
  public function surfconext() {
    return $this->SURFconext;
  }


  /**
   * Fetches the SimpleSaml object
   * @return  SimpleSAML_Auth_Simple  The SimpleSAML_Auth_Simple instance used for authentication
   */
  public function simpleSaml() {
    return $this->simpleSAML_authentication;
  }


  /**
   * This method is called when DAV receives an 401 Unauthenticated exception.
   * @return bool true if a response has been sent to the user.
   */
  public function unauthorized() {
    DAV::header( array(
      'status' => DAV::HTTP_UNAUTHORIZED,
      'WWW-Authenticate' => 'Basic realm="' . BeeHub::$CONFIG['authentication']['realm'] . '"',
      'Content-Type' => BeeHub::best_xhtml_type()
    ) );
    // TODO: Shouldn't this HTML text contain a link to alternative means of
    // authentication, such as SURFconext? [PvB]
    echo <<<EOS
<html><head>
  <title>HTTP/1.1 Unauthorized</title>
</head><body>
  <h1>HTTP/1.1 Unauthorized</h1>
  <p>Sorry…</p>
</body></html>
EOS;
  }


} // class BeeHub_Auth
