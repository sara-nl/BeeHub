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
   * Returns a form to register a new user. No authentication required.
   * @see DAV_Resource::method_GET
   */
  public function method_GET() {
    if ( empty($_SERVER['HTTPS']) &&
         APPLICATION_ENV !== BeeHub::ENVIRONMENT_DEVELOPMENT ) {
      throw new DAV_Status(
        DAV::HTTP_MOVED_PERMANENTLY,
        BeeHub::urlbase(true) . $_SERVER['REQUEST_URI']
      );
    }
    $display_name = '';
    $email_address = '';
    if (BeeHub_Auth::inst()->simpleSaml()->isAuthenticated()) {
      $as = BeeHub_Auth::inst()->simpleSaml();
      $attrs = $as->getAttributes();
      $display_name = @$attrs['urn:mace:dir:attribute-def:displayName'][0];
      $email_address = @$attrs['urn:mace:dir:attribute-def:mail'][0];
    }
    $this->include_view('new_user', array('display_name'=>$display_name, 'email_address'=>$email_address));
  }


  /**
   * Handles the form to register a new user. No authentication required.
   * @see DAV_Resource::method_POST()
   */
  public function method_POST(&$headers) {
    $displayname = $_POST['displayname'];
    $email = $_POST['email'];
    $password = (!empty($_POST['password']) ? $_POST['password'] : null);
    $user_name = $_POST['user_name'];
    // User name must be one of the following characters a-zA-Z0-9_-., starting with an alphanumeric character and must be between 1 and 255 characters long
    if (empty($displayname) ||
        !preg_match('/^[a-zA-Z0-9]{1}[a-zA-Z0-9_\-\.]{0,254}$/D', $user_name)) {
      throw new DAV_Status(DAV::HTTP_BAD_REQUEST);
    }
    $userdir = DAV::unslashify(BeeHub::$CONFIG['environment']['datadir']) . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . $user_name;
    // Check for existing userdir
    if (file_exists($userdir)) {
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Store in the database
    try{
      $statement = BeeHub_DB::execute(
        'INSERT INTO `beehub_users`
            (`user_name`)
          VALUES (?)',
        's', $user_name
      );
    }catch (Exception $exception) {
      if ($exception->getCode() === 1062) { // Duplicate key: bad request!
        throw new DAV_Status(DAV::HTTP_CONFLICT, "User name already exists, please choose a different user name!");
      }else{
        throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
      }
    }

    // Fetch the user and store extra properties
    $user = BeeHub_Registry::inst()->resource(
      BeeHub::USERS_PATH . rawurlencode($user_name)
    );
    $user->set_password($password);
    $user->user_set(DAV::PROP_DISPLAYNAME, $displayname);
    $user->user_set(BeeHub::PROP_EMAIL, $email);
    // TODO: This should not be hard coded, a new user should not have a sponsor but request one after his account is created, but I want to inform the user about his through the not-yet-existing notification system
    $user->user_set(BeeHub::PROP_SPONSOR, '/system/sponsors/e-infra');
    BeeHub_DB::execute(
      'INSERT INTO `beehub_sponsor_members`
          (`sponsor_name`, `user_name`, `is_accepted`, `is_admin`)
        VALUES (\'e-infra\', ?, ?, ?)
        ON DUPLICATE KEY
          UPDATE `is_accepted` = 1',
      'sii', $user_name, 1, 0
    );
    // Just to be clear: the above lines will have to be deleted somewhere in the future, but the lines below should stay
    $auth = BeeHub_Auth::inst();
    if ($auth->simpleSaml()->isAuthenticated()) {
      $surfId = $auth->simpleSaml()->getAuthData("saml:sp:NameID");
      $surfId = $surfId['Value'];
      $attributes = $auth->simpleSaml()->getAttributes();
      $surfconext_description = @$attributes['urn:mace:terena.org:attribute-def:schacHomeOrganization'][0];
      if (empty($surfconext_description)) {
        $surfconext_description = 'Unknown account';
      }
      $user->user_set(BeeHub::PROP_SURFCONEXT, $surfId);;
      $user->user_set(BeeHub::PROP_SURFCONEXT_DESCRIPTION, $surfconext_description);
    }
    $user->storeProperties();

    // And create a user directory
    if (!mkdir($userdir)) {
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }
    $userdir_resource = BeeHub_Registry::inst()->resource('/home/' . $user_name);
    $userdir_resource->user_set( DAV::PROP_OWNER, $user->path );
    // TODO: this should not be hard coded. When a users is accepted by his/her first sponsor, this should automatically be set.
    $userdir_resource->user_set( BeeHub::PROP_SPONSOR, rawurlencode(BeeHub::PROP_SPONSOR), '/system/sponsors/e-infra' );
    $userdir_resource->storeProperties();

    // Show the confirmation
    $this->include_view('new_user_confirmation', array('email_address'=>$email));
  }

  public function report_principal_property_search($properties) {
    if ( 1 !== count( $properties ) ||
         ! isset( $properties[DAV::PROP_DISPLAYNAME] ) ||
         1 !== count( $properties[DAV::PROP_DISPLAYNAME] ) )
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
      $retval[] = BeeHub::USERS_PATH .
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


  /**
  * @see BeeHub_Resource::user_prop_acl_internal()
  */
  public function user_prop_acl_internal() {
    return array( new DAVACL_Element_ace(
      DAVACL::PRINCIPAL_ALL, false, array(
        DAVACL::PRIV_READ, DAVACL::PRIV_READ_ACL
      ), false, true
    ));
  }


} // class BeeHub_Users
