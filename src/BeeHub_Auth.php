<?php
/**
 * Contains the BeeHub_Auth class
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
   * @var  SimpleSAML_Auth_Simple  The SimpleSAML_Auth_Simple instance used for authentication with surfconext
   */
  private $simpleSAML_surfconext;

  /**
   * @var  SimpleSAML_Auth_Simple  The SimpleSAML_Auth_Simple instance used for authentication with the CUA
   */
  private $simpleSAML_cua;


  /**
   * @var  BeeHub_User  The user currently logged in, or null if no user is logged in
   */
  private $currentUserPrincipal = null;

  /**
   * This class is a singleton, so the constructor is private. Instantiate through BeeHub_Auth::inst()
   */
  private function __construct( SimpleSAML_Auth_Simple $surfconext = null, SimpleSAML_Auth_Simple $cua = null ) {
    if ( is_null( $surfconext ) ) {
      $this->simpleSAML_surfconext = new SimpleSAML_Auth_Simple('BeeHub');
    }else{
      $this->simpleSAML_surfconext = $surfconext
    }

    if ( is_null( $cua ) ) {
      $this->simpleSAML_cua = new SimpleSAML_Auth_Simple('CUA');
    }else{
      $this->simpleSAML_cua = $cua
    }
  }

  /**
   * Gets the only instance of this class
   *
   * @return  BeeHub_Auth  The only instance of this class
   */
  public static function inst( SimpleSAML_Auth_Simple $simpleSAML = null ) {
    if ( is_null( $simpleSAML ) ) {
      $simpleSAML = new SimpleSAML_Auth_Simple( 'BeeHub' );
    }
    static $inst = null;
    if (is_null($inst)) {
      $inst = new BeeHub_Auth( $simpleSAML );
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
    // We start with assuming nobody is logged in
    $this->set_user( null );
    $this->SURFconext = false;

    // If the client sends credentials using HTTP Basic Auth, we use that and ignore the simplesaml authentication
    if ( isset($_SERVER['PHP_AUTH_PW']) ) {
      if (!$allowDoubleLogin) { // If double login is not allowed, log out from all simplesaml authsources
        $this->simpleSAML_surfconext->logout();
        $this->simpleSAML_cua->logout();
      }
      // The user already sent username and password: check them!
      $password_verified = false;
      try{
        $user = BeeHub::user($_SERVER['PHP_AUTH_USER']);
        $password_verified = $user->check_password($_SERVER['PHP_AUTH_PW']);
      }catch (DAV_Status $status) {
        if ( $status->getCode() !== DAV::HTTP_FORBIDDEN ) {
          throw $status;
        }
      }
      if ( ! $password_verified && $requireAuth ) {
        // If authentication fails, respond accordingly
        $this->unauthorized();
      }

      // Authentication succeeded: store credentials!
      $this->set_user(rawurlencode( $_SERVER['PHP_AUTH_USER'] ));
      return;
    }
    // If we require authentication and the client is not a browser; send an 'UNAUTHORIZED' header
    if ( ! $this->is_browser() && $requireAuth ) {
      $this->unauthorized();
      exit;
    }

    // The user did not login through HTTP Basic Auth, so check the browser authentication options

    // In a browser you can choose to log out, so check for that first
    if (isset($_GET['logout'])) {
      $this->simpleSAML_surfconext->logout();
      $this->simpleSAML_cua->logout();
      if (!empty($_SERVER['HTTPS'])) {
        DAV::redirect(DAV::HTTP_SEE_OTHER, BeeHub::urlbase(false) . '/system/');
        return;
      }
    }

    // If you request that you authenticate, you should tell us how you want to do this
    if ( ( 'conext' === @$_GET['login'] ) && !$this->simpleSAML_surfconext->isAuthenticated() ) {
      $this->simpleSAML_cua->logout();
      $this->simpleSAML_surfconext->login();
    } elseif ( !$this->simpleSAML_cua->isAuthenticated() &&
               (
                 ( 'passwd' === @$_GET['login'] ) ||
                 ( $requireAuth && !$this->simpleSAML_surfconext->isAuthenticated() )
               )
             ) {
      $this->simpleSAML_surfconext->logout();
      $this->simpleSAML_cua->login();
    }

    if ( $this->simpleSAML_surfconext->isAuthenticated() ) {
      $surfId = $this->simpleSAML_surfconext->getAuthData("saml:sp:NameID");
      $surfId = $surfId['Value'];
      $statement = BeeHub_DB::execute('SELECT `user_name` FROM `beehub_users` WHERE `surfconext_id`=?', 's', $surfId);
      if ( $row = $statement->fetch_row() ) { // We found a user, this is the one that's logged in!
        $this->SURFconext = true;
        $this->set_user( $row[0] );
      }
      // TODO: this is not true anymore: if we don't recognize your surfconext ID, what should we do?
      elseif ($_SERVER['REQUEST_URI'] !== BeeHub::USERS_PATH ) {
        throw new DAV_Status(
          DAV::HTTP_TEMPORARY_REDIRECT,
          BeeHub::urlbase(true) . BeeHub::USERS_PATH
        );
      }
    } elseif ( $this->simpleSAML_cua->isAuthenticated() ) {
      $attrs = $this->simpleSAML_cua->getAttributes();
      $this->set_user( $attrs['uid'][0] );
    }

// TODO: This should also be done if you don't use a browser!
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

  /**
   * Sets the current user
   *
   * @param   string  $user_name  The user name, or null to indicate no user is logged in
   * @return  void
   */
  private function set_user($user_name) {
    if ( is_null( $user_name ) ) {
      $this->currentUserPrincipal = null;
    }else{
      $this->currentUserPrincipal = BeeHub::user( $user_name );
    }
  }


  /**
   * Gives the currently logged in user
   *
   * @return  BeeHub_User  The currently logged in user or NULL if no user is
   *   logged in.
   */
  public function current_user() {
    return $this->currentUserPrincipal;
  }


  /**
   * Is the current user authenticated?
   *
   * @return  boolean  True if the user is authenticated, false otherwise
   */
  public function is_authenticated() {
    return ! is_null( $this->current_user() );
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
    return $this->simpleSAML_surfconext;
  }


  /**
   * This method is called when DAV receives an 401 Unauthenticated exception.
   * @return  bool  True if a response has been sent to the user.
   */
  public function unauthorized() {
    DAV::header( array(
      'WWW-Authenticate' => 'Basic realm="' . BeeHub::$CONFIG['authentication']['realm'] . '"',
      'Content-Type' => BeeHub::best_xhtml_type()
    ) );
    BeeHub::htmlError(
            file_get_contents( dirname( dirname ( __FILE__ ) ) . '/views/error_unauthorized.html' ) ,
            DAV::HTTP_UNAUTHORIZED
    );
  }


  /**
   * Determines whether the current client is a browser or not
   * @return  bool  True if the current client is a recognized browser, false otherwise
   * @todo make it work
   */
  public function is_browser() {
    // TODO: Make this actually check the client
    return true;
  }


  /**
   * Determines whether you need to authenticate based on the method and URL of the request
   * @return  boolean  True if authentication is required, false otherwise
   */
  public static function is_authentication_required() {
    $path = DAV::unslashify( DAV::getPath() );
    /**
     * You don't need to authenticate when:
     * - GET (or HEAD) or POST on the users collection (required to create a new user)
     * - GET (or HEAD) on the system collection (required to read the 'homepage')
     * In other cases you do need to authenticate
     */
    $noRequireAuth = (
      (
        $path === DAV::unslashify( BeeHub::USERS_PATH ) &&
        in_array( $_SERVER['REQUEST_METHOD'], array('GET', 'POST', 'HEAD') )
      ) ||
      (
        $path === DAV::unslashify( BeeHub::SYSTEM_PATH ) &&
        in_array($_SERVER['REQUEST_METHOD'], array('GET', 'HEAD') )
      )
    );

    return ! $noRequireAuth;
  }

} // class BeeHub_Auth

// End of file
