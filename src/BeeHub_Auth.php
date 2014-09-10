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
   * @var  SimpleSAML_Auth_Simple  The SimpleSAML_Auth_Simple instance used for authentication
   */
  private $simpleSAML_authentication;


  /**
   * @var  BeeHub_User  The user currently logged in, or null if no user is logged in
   */
  private $currentUserPrincipal = null;

  /**
   * This class is a singleton, so the constructor is private. Instantiate through BeeHub_Auth::inst()
   */
  protected function __construct( SimpleSAML_Auth_Simple $simpleSAML ) {
    $this->simpleSAML_authentication = $simpleSAML;
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

      // Update the http://beehub.nl/ last-activity property
      $user->user_set( BeeHub::PROP_LAST_ACTIVITY, date( 'Y-m-d\TH:i:sP') );
      $user->storeProperties();
      
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
    return $this->simpleSAML_authentication;
  }


  /**
   * This method is called when DAV receives an 401 Unauthenticated exception.
   * @return bool true if a response has been sent to the user.
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


  /**
   * The key of the $_SESSION array that stores the POST authentication code
   *
   * This variable should be considered a class constant. I feel that it is
   * better to create a private static variable than a public constant, because
   * the chance of any documentation creation software (like phpDocumentor)
   * will put it in the public API documentation is less.
   *
   * @internal
   */
  private static $SESSION_KEY = 'beehub_postAuthCode';


  /**
   * Gets the currently valid POST authentication code.
   *
   * The POST authentication code is used to validate that the client is allowed
   * to submit POST requests. The most important example of a client that is not
   * allowed to do this is a JavaScript from another website. It should not be
   * possible for such a script to submit a (hidden) HTML POST form that can
   * change settings for this user. To prevent this attach, the POST requests
   * should include a field 'POST_auth_code' with the value returned by this
   * method. Other websites can not retrieve this code because of 'Cross Origin
   * Resource Sharing' limitations normal browsers have. (And if your browser
   * does not have this, I would suggest to quickly stop using such an insecure
   * browser).
   *
   * Do NOT compare the user submitted value with this value, instead, let the
   * checkPostAuthCode() method do that. This will ensure each code is only used
   * once and empty codes are not allowed.
   *
   * @api
   * @return  string  The POST authentication code
   */
  public function getPostAuthCode() {
    BeeHub::startSession();

    if ( ! isset( $_SESSION[ self::$SESSION_KEY ] ) || empty( $_SESSION[ self::$SESSION_KEY ] ) ) {
      $_SESSION[ self::$SESSION_KEY ] = bin2hex( openssl_random_pseudo_bytes( 16 ) );
    }
    return $_SESSION[ self::$SESSION_KEY ];
  }


  /**
   * Checks whether the user submitted a correct POST authentication code and sets a new code when authentication succeeded or too many attempts have been done.
   *
   * Using this method instead of checking it yourself. This to ensure the
   * following:
   * - Enforce a consistent API (always the same POST field: POST_auth_code)
   * - Refresh the code after a successful check
   * - Refresh the code after five failed attempts
   *
   * @see getPostAuthCode()
   * @api
   * @return  boolean  True of the code was correct, false otherwise
   */
  public function checkPostAuthCode() {
    $postField = 'POST_auth_code';
    // The key of the $_SESSION array field with the number of failed attempts to check a POST authentication code
    $postAuthAttempts = 'POST_auth_attempts';
    BeeHub::startSession();

    if ( ! isset( $_SESSION[ $postAuthAttempts ] ) ) {
      $_SESSION[ $postAuthAttempts ] = 0;
    }
    
    if (
      ! isset( $_POST[ $postField ] ) ||
      empty( $_POST[ $postField ] ) ||
      $_POST[ $postField ] != $_SESSION[ self::$SESSION_KEY ]
    ){
      $_SESSION[ $postAuthAttempts ]++;
      if ( $_SESSION[ $postAuthAttempts ] >= 5 ) {
        unset( $_SESSION[ self::$SESSION_KEY ] );
        $_SESSION[ $postAuthAttempts ] = 0;
      }
      return false;
    }

    unset( $_SESSION[ self::$SESSION_KEY ] );
    $_SESSION[ $postAuthAttempts ] = 0;

    return true;
  }

} // class BeeHub_Auth

// End of file
