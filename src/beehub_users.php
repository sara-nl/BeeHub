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
 * Some class.
 * @package BeeHub
 */
class BeeHub_Users extends BeeHub_Principal_Collection {

  /**
   * Returns either a form to register a new user or a form to verify your e-mail address. No authentication required.
   * @see DAV_Resource::method_GET
   */
  public function method_GET() {
    if ( empty($_SERVER['HTTPS']) &&
         APPLICATION_ENV != BeeHub::ENVIRONMENT_DEVELOPMENT ) {
      throw new DAV_Status(
        DAV::HTTP_MOVED_PERMANENTLY,
        BeeHub::urlbase(true) . $_SERVER['REQUEST_URI']
      );
    }
    if (!isset($_GET['verify']) && !isset($_GET['verify_user'])) {
      $this->include_view('new_user');
    }else{
      $setPassword = isset($_GET['verify_user']);
      $this->include_view('verify_email', array('setPassword'=>$setPassword));
    }
  }


  public function method_HEAD() {
    $retval = array();
    $retval['Cache-Control'] = 'no-cache';
    return $retval;
  }


  /**
   * Handles both the form to register a new user and the form to verify an e-mail address. No authentication required.
   * @see DAV_Resource::method_POST()
   */
  public function method_POST(&$headers) {
    if (!isset($_POST['verification_code'])) {
      // TODO: translate user_name to ASCII and check for double usernames
      $user_name = $_POST['user_name'];
      $displayname = $_POST['displayname'];
      $email = $_POST['email'];

      // Store in the database
      $statement = BeeHub_DB::execute(
        'INSERT INTO `beehub_users`
           (`user_name`, `surfconext_id`)
         VALUES (?, ?)',
        'ss', $user_name, $surfconext_id
      );

      // Fetch the user and store extra properties
      $user = BeeHub_Registry::inst()->resource(
        BeeHub::$CONFIG['namespace']['users_path'] . rawurlencode($user_name)
      );
      $user->user_set(DAV::PROP_DISPLAYNAME, $displayname);
      $user->user_set(BeeHub::PROP_EMAIL, $email);
      $user->storeProperties();
    }else{
      // TODO: Check whether the POST field is filled out correctly
      $verification_code = $_POST['verification_code'];
      $user_name = $_POST['user_name'];
      $user = BeeHub_Registry::inst()->resource(BeeHub::$CONFIG['namespace']['users_path'] . $user_name);
      $old_email = $user->prop(BeeHub::PROP_EMAIL);

      // Now verify the e-mail address
      if (!$user->verify_email_address($verification_code)){
        throw DAV::HTTP_UNAUTHORIZED;
      }

      // If the user doesn't have an e-mail address set yet, it is a new account. So allow setting the password or X509 certificate here
      if (empty($old_email)) {
        $password = $_POST['password'];
        $user->user_set(BeeHub::PROP_PASSWORD, $password);
        $x509 = $_POST['x509'];
        $user->user_set(BeeHub::PROP_X509, $x509);
        $user->storeProperties();
      }
    }
  }


  public function report_principal_property_search($properties) {
    if ( 1 != count( $properties ) ||
         ! isset( $properties[DAV::PROP_DISPLAYNAME] ) ||
         1 != count( $properties[DAV::PROP_DISPLAYNAME] ) )
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST,
        'You\'re searching for a property which cannot be searched.'
      );
    $match = $properties[DAV::PROP_DISPLAYNAME][0];
    $match = str_replace(array('_', '%'), array('\\_', '\\%'), $match) . '%';
    $stmt = BeeHub_DB::execute(
      'SELECT `user_name`
       FROM `beehub_users`
       WHERE `displayname` LIKE ?', 's', $match
    );
    $retval = array();
    while ($row = $stmt->fetch_row()) {
      $retval[] = BeeHub::$CONFIG['namespace']['users_path'] .
        rawurlencode($row[0]);
    }
    $stmt->free_result();
    return $retval;
  }


  protected function init_members() {
    $stmt = BeeHub_DB::execute('SELECT `user_name` FROM `beehub_users`');
    $this->members = array();
    while ($row = $stmt->fetch_row()) {
      $this->members[] = rawurlencode($row[0]);
    }
    $stmt->free_result();
  }


} // class BeeHub_Users