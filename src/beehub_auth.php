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
   * @var  BeeHub_Auth  The only instance of this class
   */
  private static $self = null;

  /**
   * @var  boolean  True if the user is logged in through SURFconext, false otherwise
   */
  private $SURFconext = false;

  /**
   * This class is a singleton, so the constructor is private. Instantiate through BeeHub_Auth::inst()
   */
  private function __construct() {
  }

  /**
   * Gets the only instance of this class
   *
   * @return  BeeHub_Auth  The only instance of this class
   */
  public static function inst() {
    if (is_null(BeeHub_Auth::$self)) {
      BeeHub_Auth::$self = new BeeHub_Auth();
    }
    return BeeHub_Auth::$self;
  }

  /**
   * Authenticates the user through one of the authentication mechanisms
   *
   * @param   boolean  $requireAuth  Optionally; if set to false and authentication fails, the user will continue as an unauthenticated user. If set to true (default), a 403 AUTHENTICATION REQUIRED header will be sent upon authentication failure.
   * @return  void
   */
  public function handle_authentication($requireAuth = true) {
    if ( isset($_SERVER['PHP_AUTH_PW'])) {
      // The user already sent username and password: check them!
      $stmt = BeeHub_DB::execute(
        'SELECT `password`
         FROM `beehub_users`
         WHERE `user_name` = ?',
        's', $_SERVER['PHP_AUTH_USER']
      );
      if ( !( $row = $stmt->fetch_row() ) ||
           $row[0] != crypt($_SERVER['PHP_AUTH_PW'], $row[0]) ) {
        // If authentication fails, respond accordingly
        if ($requireAuth) {
          $stmt->free_result();
          // User could not be authenticated with supplied credentials, but we
          // require authentication, so we ask again!
          BeeHub_ACL_Provider::inst()->unauthorized();
          return;
        }
      } else { // Authentication succeeded: store credentials!
        $this->set_user(rawurlencode( $_SERVER['PHP_AUTH_USER'] ));
      }
      $stmt->free_result();
    } // end of: if (user sent username/passwd)
    else {
      // Try SimpleSaml:
      require_once(BeeHub::$CONFIG['environment']['simplesamlphp_autoloader']);
      $as = new SimpleSAML_Auth_Simple('SURFconext');
  
      if (isset($_GET['logout']) && $as->isAuthenticated()) {
        $as->logout();
      }
      if ('conext' === @$_GET['login'] && !$as->isAuthenticated()) {
        $as->login();
      }
  
      if ( $as->isAuthenticated() ) { // Retrieve and store the correct user (name) when authenticated through SimpleSamlPHP
        $this->SURFconext = true;
        $surfId = $as->getAuthData("saml:sp:NameID");
        $surfId = $surfId['Value'];
        $statement = BeeHub_DB::execute('SELECT `user_name` FROM `beehub_surfconext_ids` WHERE `surfconext_id`=?', 's', $surfId);
        if ( $row = $statement->fetch_row() ) { // We found a user, this is the one that's logged in!
          $this->set_user( $row[0] );
        }elseif (!$this->match_conext_on_email($as)) { // We don't know this SURFconext ID and can't match based on e-mail address; this is a new user
          $this->new_conext_user($as);
        }
      } else {
        // If we are not authenticated through SimpleSamlPHP,
        // see if HTTP basic authentication is required:
        if ( $requireAuth || 'passwd' === @$_GET['login'] ) {
          // If the user didn't send any credentials, but we require authentication, ask for it!
          BeeHub_ACL_Provider::inst()->unauthorized();
          return;
        }
      }
    }
  }

  /**
   * See if we can match the SURFconext user based on his e-mail address
   *
   * @param   SimpleSAML_Auth_Simple  $simpleSAML_authentication  The SimpleSAML_Auth_Simple instance used for authentication
   * @return  boolean                                             True if a match was found, false otherwise
   */
  private function match_conext_on_email(SimpleSAML_Auth_Simple $simpleSAML_authentication) {
    $attributes = $simpleSAML_authentication->getAttributes();
    $email_addresses = $attributes['urn:mace:dir:attribute-def:mail'];
    $surfId = $simpleSAML_authentication->getAuthData("saml:sp:NameID");
    $surfId = $surfId['Value'];

    // Check if one of the e-mail addresses is known
    foreach ($email_addresses as $email) {
      $statement = BeeHub_DB::execute('SELECT `user_name` FROM `beehub_users` WHERE `email`=?', 's', $email);
      if ( $row = $statement->fetch_row() ) { // We found a user with this e-mail address
        BeeHub_DB::execute('INSERT INTO `beehub_surfconext_ids` (`user_name`, `surfconext_id`) VALUES (?, ?)', 'ss', $row[0], $surfId);
        $this->set_user( $row[0] );
        return true;
      }
    }

    return false;
  }

  /**
   * Creates a new user based on SURFconext details
   * @param   SimpleSAML_Auth_Simple  $simpleSAML_authentication  The SimpleSAML_Auth_Simple instance used for authentication
   * @return  void
   */
  private function new_conext_user(SimpleSAML_Auth_Simple $simpleSAML_authentication){
    throw new DAV_Status(DAV::HTTP_NOT_IMPLEMENTED);
  }

  /**
   * Sets the current user
   *
   * @param   string  $user_name  The user name
   * @return  void
   */
  private function set_user($user_name) {
    BeeHub_ACL_Provider::inst()->CURRENT_USER_PRINCIPAL = BeeHub::$CONFIG['namespace']['users_path'] . $user_name;
  }

  /**
   * Gives the currently logged in user
   *
   * @return  BeeHub_User  The currently logged in user or NULL if no user is logged in.
   */
  public function current_user() {
    $cup = BeeHub_ACL_Provider::inst()->current_user_principal();
    return $cup ? BeeHub::user($cup) : null;
  }

  /**
   * Checks if this user is logged in through SURFconext
   * @return  boolean  True if the user is logged in through SURFconext, false otherwise
   */
  public function surfconext() {
    return $this->SURFconext;
  }

} // class BeeHub_Auth