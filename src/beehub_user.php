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
 * BeeHub user
 *
 * There are a few properties defined which are stored in the database instead
 * of as xfs attribute. These properties are credentials and user contact info:
 * BeeHub::PROP_NAME
 * BeeHub::PROP_EMAIL
 * BeeHub::PROP_X509
 *
 * We won't allow user data to be sent (GET, PROPFIND) or manipulated (PROPPATCH) over regular HTTP, so we require HTTPS! But this is arranged, because only an authenticated user can perform this GET request and you can only be authenticated over HTTPS.
 *
 * @TODO Checken of de properties in de juiste gevallen afschermd worden
 * @TODO toevoegen user_prop_sponsor();
 * @package BeeHub
 */
class BeeHub_User extends BeeHub_Principal {
  /**
   * @var  string  The original e-mail address as specified in the database. This is used to check if the user wants to change his/her e-mail address.
   */
  private $original_email = null;
    
  /**
   * @var  string  The (encrypted) password
   */
  private $password = null;


  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET() {
    $unverified_address = null;
    $statement = BeeHub_DB::execute(
      'SELECT `unverified_email`
          FROM `beehub_users`
        WHERE `user_name` = ? AND
              `verification_expiration` > NOW()',
      's', $this->name
    );
    if ($row = $statement->fetch_row()) {
      $unverified_address = $row[0];
    }elseif ( isset( $_GET['verification_code'] ) ) {
      unset($_GET['verification_code']);
    }
    $this->include_view(null, array('unverified_address'=>$unverified_address));
  }


  public function method_POST() {
    $this->init_props();

    // For all POST requests, you need to send a POST field 'password' with the current password
    if (!isset($_POST['password']) || !$this->check_password($_POST['password'])) {
      throw DAV::forbidden();
    }

    if (isset($_POST['verification_code'])) { // Now verify the e-mail address
      if (!$this->verify_email_address($_POST['verification_code'])){
        throw DAV::forbidden();
      }
      DAV::redirect(DAV::HTTP_SEE_OTHER, $this->path);
      return;
    }elseif (isset($_POST['new_password'])) {
      $this->set_password($_POST['new_password']);
      DAV::redirect(DAV::HTTP_SEE_OTHER, $this->path);
      return;
    }
    throw new DAV_Status(DAV::HTTP_BAD_REQUEST);
  }


  protected function init_props() {
    if (is_null($this->stored_props)) {
      $this->stored_props = array();

      $statement_props = BeeHub_DB::execute(
        'SELECT
          `displayname`,
          `email`,
          `password`,
          `surfconext_id`,
          `surfconext_description`,
          `x509`,
          `sponsor_name`
         FROM `beehub_users`
         WHERE `user_name` = ?', 's', $this->name
      );
      $row = $statement_props->fetch_row();
      if ( is_null($row) )
        throw new DAV_Status( DAV::HTTP_NOT_FOUND );

      $this->stored_props[DAV::PROP_DISPLAYNAME] = $row[0];
      if (!is_null($row[1])) {
        $this->stored_props[BeeHub::PROP_EMAIL]  = $row[1];
        $this->original_email                    = $row[1];
      }
      if (!is_null($row[3])) {
        $this->stored_props[BeeHub::PROP_SURFCONEXT] = $row[3];
      }
      if (!is_null($row[4])) {
        $this->stored_props[BeeHub::PROP_SURFCONEXT_DESCRIPTION] = $row[4];
      }
      if (!is_null($row[5])) {
        $this->stored_props[BeeHub::PROP_X509]   = $row[5];
      }
      if (!is_null($row[6])) {
        $this->stored_props[BeeHub::PROP_SPONSOR]   = $row[6];
      }
      if (!empty($row[2])) {
        $this->password = $row[2];
      }else{
        $this->password = null;
      }
      $statement_props->free_result();

      // Fetch all group memberships
      $statement_groups = BeeHub_DB::execute(
        'SELECT `group_name`
           FROM `beehub_group_members`
          WHERE `user_name` = ?
            AND `is_invited` = 1
            AND `is_requested` = 1', 's', $this->name
      );
      $groups = array();
      while ($row = $statement_groups->fetch_row()) {
        $groups[] = BeeHub::$CONFIG['namespace']['groups_path'] .
          rawurlencode($row[0]);
      }
      $statement_groups->free_result();
      $this->stored_props[DAV::PROP_GROUP_MEMBERSHIP] = $groups;

      // Fetch all sponsor memberships
      $statement_sponsors = BeeHub_DB::execute(
        'SELECT `sponsor_name`
           FROM `beehub_sponsor_members`
          WHERE `user_name` = ?
            AND `is_accepted` = 1', 's', $this->name
      );
      $sponsors = array();
      while ($row = $statement_sponsors->fetch_row()) {
        $sponsors[] = BeeHub::$CONFIG['namespace']['sponsors_path'] .
          rawurlencode($row[0]);
      }
      $statement_sponsors->free_result();
      $this->stored_props[BeeHub::PROP_SPONSOR_MEMBERSHIP] = $sponsors;
    }
  }

  /**
   * Stores properties set earlier by set().
   * @return void
   * @throws DAV_Status in particular 507 (Insufficient Storage)
   */
  public function storeProperties() {
    if (!$this->touched) {
      return;
    }

    $p_displayname     = @$this->stored_props[DAV::PROP_DISPLAYNAME];
    $p_surfconext      = @$this->stored_props[BeeHub::PROP_SURFCONEXT];
    $p_surfconext_desc = @$this->stored_props[BeeHub::PROP_SURFCONEXT_DESCRIPTION];
    $p_x509            = @$this->stored_props[BeeHub::PROP_X509];
    // TODO: Check if sponsor exists!
    $p_sponsor         = @$this->stored_props[BeeHub::PROP_SPONSOR];

    $change_email = false;
    if (@$this->stored_props[BeeHub::PROP_EMAIL] !== $this->original_email) {
      $change_email = true;
      $p_email = @$this->stored_props[BeeHub::PROP_EMAIL];
      $p_verification_code = md5(time() . '0-c934q2089#$#%@#$jcq2iojc43q9  i1d' . rand(0, 10000));
    }

    // Write all data to database
    $updateStatement = BeeHub_DB::mysqli()->prepare(
      'UPDATE `beehub_users`
          SET `displayname` = ?,
              `surfconext_id` = ?,
              `surfconext_description` = ?,
              `x509` = ?,
              `sponsor_name` = ?' .
              ($change_email ? ',`unverified_email`=?,`verification_code`=?,`verification_expiration`=NOW() + INTERVAL 1 DAY' : '') .
      ' WHERE `user_name` = ?'
    );
    if ($change_email) { // No new password, but there is a new e-mail address
      $updateStatement->bind_param(
        'ssssssss',
        $p_displayname,
        $p_surfconext,
        $p_surfconext_desc,
        $p_x509,
        $p_sponsor,
        $p_email,
        $p_verification_code,
        $this->name
      );
    }else{ // No new password, no new e-mail address
      $updateStatement->bind_param(
        'ssssss',
        $p_displayname,
        $p_surfconext,
        $p_surfconext_desc,
        $p_x509,
        $p_sponsor,
        $this->name
      );
    }
    if (!$updateStatement->execute()) {
      if ($exception->getCode() === 1062) { // Duplicate key: bad request!
        throw new DAV_Status(DAV::HTTP_CONFLICT, "The following properties should be unique, at least one of them is not: e-mail address, SURFconext id");
      }else{
        throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
      }
    }

    // Notify the user if needed
    if ($change_email) {
      $activation_link = BeeHub::urlbase(true) . $this->path . '?verification_code=' . $p_verification_code;
      $message =
'Dear ' . $p_displayname . ',

This e-mail address (' . $p_email . ') is added to the BeeHub account \'' . $this->name . '\'. You need to confirm this action by following this link:

' . $activation_link . '

Note that you\'re verification code is only valid for 24 hours. Also, for new users, if you don\'t have a validated e-mail address, your account will automatically be removed after 24 hours.

If this was a mistake, or you do not want to add this e-mail address to this BeeHub account, you don\'t have to do anything.

Best regards,

BeeHub';
      BeeHub::email($p_displayname . ' <' . $p_email . '>',
                    'Verify e-mail address for BeeHub',
                    $message);
    }

    // Update the json file containing all displaynames of all privileges
    self::update_principals_json();
    $this->touched = false;
  }


  public function user_prop_acl_internal() {
    return array(
      new DAVACL_Element_ace(
        DAVACL::PRINCIPAL_SELF, false, array(
          DAVACL::PRIV_READ, DAVACL::PRIV_WRITE
        ), false, true
      )
    );
  }


  /**
   * Checks a password
   *
   * @param   string   $password  The password to check
   * @return  boolean             True if the supplied password matches the user's password, false otherwise
   */
  public function check_password($password) {
    if ( is_null($this->password) ||
         $this->password !== crypt($password, $this->password) ) {
      return false;
    }else{
      return true;
    }
  }


  /**
   * Sets a new password
   *
   * @param   string   $password  The new password
   * @return  void
   */
  public function set_password($password) {
    $encrypted_password = crypt($password, '$6$rounds=5000$' . md5(time() . rand(0, 99999)) . '$');

    BeeHub_DB::execute( 'UPDATE `beehub_users` SET `password`=? WHERE `user_name`=?',
                        'ss', $encrypted_password, $this->name);

    $this->password = $encrypted_password;
  }


  /**
   * Checks the verification code and verifies the e-mail address if the code is correct
   * @param   string  $code  The verification code
   * @return  boolean        True if the code verified correctly, false if the code was wrong
   */
  public function verify_email_address($code) {
    $updateStatement = BeeHub_DB::execute(
      'UPDATE `beehub_users`
          SET `email` = `unverified_email`,
              `unverified_email` = null,
              `verification_code` = null,
              `verification_expiration` = null
        WHERE `user_name` = ?
          AND `verification_code` = ?
          AND `verification_expiration` > NOW()',
      'ss', $this->name, $code
    );
    if ($updateStatement->affected_rows > 0) {
      $propStatement = BeeHub_DB::execute(
        'SELECT `email` FROM `beehub_users` WHERE `user_name`=?',
        's', $this->name
      );
      $row = $propStatement->fetch_row();
      $this->stored_props[BeeHub::PROP_EMAIL]  = $row[0];
      $this->original_email                    = $row[0];
      return true;
    }else{
      return false;
    }
  }


  /**
   * @todo move the initialization into init_props()
   */
  public function user_prop_group_membership() {
    $this->init_props();
    return $this->stored_props[DAV::PROP_GROUP_MEMBERSHIP];
  }


  public function user_set_sponsor($sponsor) {
    $this->user_set(BeeHub::PROP_SPONSOR, $sponsor);
  }


  public function is_admin() {
    return BeeHub_ACL_Provider::inst()->wheel() ||
      ( $this->path == $this->user_prop_current_user_principal() );
  }


  /**
   * @param array $properties
   * @return array an array of (property => isReadable) pairs.
   */
  public function property_priv_read($properties) {
    $retval = parent::property_priv_read($properties);
    $is_admin = $this->is_admin();
    $retval[BeeHub::PROP_EMAIL]                  = $is_admin;
    $retval[BeeHub::PROP_SURFCONEXT]             = $is_admin;
    $retval[BeeHub::PROP_SURFCONEXT_DESCRIPTION] = $is_admin;
    $retval[BeeHub::PROP_X509]                   = $is_admin;
    $retval[BeeHub::PROP_SPONSOR]                = $is_admin;
    $retval[DAV::PROP_GROUP_MEMBERSHIP]          = $is_admin;
    return $retval;
  }


  /**
  * The user has write privileges on all properties if he is the administrator of this principal
  * @param array $properties
  * @return array an array of (property => isWritable) pairs.
  */
  public function property_priv_write($properties) {
    $retval = parent::property_priv_read($properties);
    $is_admin = $this->is_admin();
    $retval[BeeHub::PROP_EMAIL]                  = $is_admin;
    $retval[BeeHub::PROP_SURFCONEXT]             = $is_admin;
    $retval[BeeHub::PROP_SURFCONEXT_DESCRIPTION] = $is_admin;
    $retval[BeeHub::PROP_X509]                   = $is_admin;
    $retval[BeeHub::PROP_SPONSOR]                = $is_admin;
    $retval[DAV::PROP_GROUP_MEMBERSHIP]          = false;
    $retval[BeeHub::PROP_SPONSOR_MEMBERSHIP]     = false;
    return $retval;
  }


  public function user_propname() {
    return BeeHub::$USER_PROPS;
  }


  /**
   * @param $name string
   * @param $value string XML
   */
  public function user_set($name, $value = null) {
    switch($name) {
      case BeeHub::PROP_EMAIL:
        //TODO: check e-mail format
    }
    //TODO: check this implementation
    return parent::user_set($name, $value);
  }


} // class BeeHub_User
