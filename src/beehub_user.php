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
 * BeeHub::PROP_PASSWORD
 * BeeHub::PROP_EMAIL
 * BeeHub::PROP_X509
 *
 * We won't allow user data to be sent (GET, PROPFIND) or manipulated (PROPPATCH) over regular HTTP, so we require HTTPS! But this is arranged, because only an authenticated user can perform this GET request and you can only be authenticated over HTTPS.
 *
 * @TODO Checken of de properties in de juiste gevallen afschermd worden
 * @package BeeHub
 */
class BeeHub_User extends BeeHub_Principal {
  /**
   * @var  string  The original e-mail address as specified in the database. This is used to check if the user wants to change his/her e-mail address.
   */
  private $original_email = null;


  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET() {
    $this->include_view();
  }

  public function method_HEAD() {
    if (!$this->is_admin()) {
      throw new DAV_Status(
              DAV::HTTP_FORBIDDEN,
              DAV::COND_NEED_PRIVILEGES
      );
    }
    return array( 'Cache-Control' => 'no-cache' );
  }

  protected function init_props() {
    static $statement_props = null,
           $statement_groups = null,
           $p_user_name = null,
           $r_displayname = null,
           $r_email = null,
           $r_new_email = null,
           $r_password = null,
           $r_x509 = null,
           $result_group_name = null;

    # Lazy initialization of prepared statement:
    if (null === $statement_props) {
      $statement_props = BeeHub::mysqli()->prepare(
        'SELECT
          `displayname`,
          `email`,
          `password`,
          `x509`
         FROM `beehub_users`
         WHERE `user_name` = ?;'
      );
      $statement_props->bind_param('s', $p_user_name);
      $statement_props->bind_result(
        $r_displayname,
        $r_email,
        $r_password,
        $r_x509
      );

      $statement_groups = BeeHub::mysqli()->prepare(
        'SELECT `group_name`
           FROM `beehub_group_members`
          WHERE `user_name` = ?
            AND `is_invited` = 1
            AND `is_requested` = 1'
      );
      $statement_groups->bind_param('s', $p_user_name);
      $statement_groups->bind_result($result_group_name);
    }

    if (is_null($this->stored_props)) {
      $this->stored_props = array();
      $p_user_name = $this->name;

      if ( ! $statement_props->execute() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_props->error );
      if ( ! $statement_props->store_result() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_props->error );
      $fetch_result = $statement_props->fetch();
      if ( $fetch_result === false )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_props->error );
      if ( is_null($fetch_result) )
        throw new DAV_Status( DAV::HTTP_NOT_FOUND );
      
      $this->stored_props[DAV::PROP_DISPLAYNAME] = $r_displayname;
      if (!is_null($r_email)) {
        $this->stored_props[BeeHub::PROP_EMAIL]  = $r_email;
        $this->original_email                    = $r_email;
      }
      if (!is_null($r_x509)) {
        $this->stored_props[BeeHub::PROP_X509]   = $r_x509;
      }
      if ($r_password) {
        $this->stored_props[BeeHub::PROP_PASSWORD] = true; // Nobody should have read access to this property. But just in case, we always set it to true.
      }
      $statement_props->free_result();

      if ( ! $statement_groups->execute() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_groups->error );
      if ( ! $statement_groups->store_result() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_groups->error );
      $groups = array();
      while ($statement_groups->fetch()) {
        $groups[] = BeeHub::$CONFIG['namespace']['groups_path'] . rawurlencode($result_group_name);
      }
      $statement_groups->free_result();
      $this->stored_props[DAV::PROP_GROUP_MEMBERSHIP] = $groups;
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

    if (isset($this->stored_props[BeeHub::PROP_PASSWORD])) {
      if ($this->stored_props[BeeHub::PROP_PASSWORD] === true) { //true means there is a password, but it hasn't been changed!
        $p_password = true;
      } else {
        $p_password = crypt($this->stored_props[BeeHub::PROP_PASSWORD], '$6$rounds=5000$' . md5(time() . rand(0, 99999)) . '$');
      }
      $this->stored_props[BeeHub::PROP_PASSWORD] = true;
    }else{
      $p_password = null;
    }

    $p_displayname = @$this->stored_props[DAV::PROP_DISPLAYNAME];
    $p_x509        = @$this->stored_props[BeeHub::PROP_X509];

    $change_email = false;
    if (@$this->stored_props[BeeHub::PROP_EMAIL] !== $this->original_email) {
      $change_email = true;
      $p_email = @$this->stored_props[BeeHub::PROP_EMAIL];
      $p_verification_code = md5(time() . '0-c934q2089#$#%@#$jcq2iojc43q9  i1d' . rand(0, 10000));
    }

    // Write all data to database
    $updateStatement = BeeHub::mysqli()->prepare(
      'UPDATE `beehub_users`
          SET `displayname` = ?,
              `x509` = ?' .
              ($change_email ? ',`unverified_email`=?,`verification_code`=?,`verification_expiration`=NOW() + INTERVAL 1 DAY' : '') .
              (($p_password !== true) ? ',`password`=?' : '') .
      ' WHERE `user_name` = ?'
    );
    if ($p_password === true) {
      if ($change_email) { // No new password, but there is a new e-mail address
        $updateStatement->bind_param(
          'sssss',
          $p_displayname,
          $p_x509,
          $p_email,
          $p_verification_code,
          $this->name
        );
      }else{ // No new password, no new e-mail address
        $updateStatement->bind_param(
          'sss',
          $p_displayname,
          $p_x509,
          $this->name
        );
      }
    }else{
      if ($change_email) { // A new password, and a new e-mail address
        $updateStatement->bind_param(
          'ssssss',
          $p_displayname,
          $p_x509,
          $p_email,
          $p_verification_code,
          $p_password,
          $this->name
        );
      }else{ // A new password, but no new e-mail address
        $updateStatement->bind_param(
          'ssss',
          $p_displayname,
          $p_x509,
          $p_password,
          $this->name
        );
      }
    }
    if (!$updateStatement->execute()) {
      // TODO: check for duplicate keys!
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Notify the user if needed
    if ($change_email) {
      //TODO: send e-mail
      die($p_verification_code);
    }

    $this->touched = false;
  }


  public function user_prop_acl_internal() {
    return array(
      new DAVACL_Element_ace(
        DAVACL::PRINCIPAL_SELF, false, array(
          DAVACL::PRIV_READ, DAVACL::PRIV_WRITE
        ), false, false
      ),
      new DAVACL_Element_ace(
        DAVACL::PRINCIPAL_ALL, false, array(
          DAVACL::PRIV_READ
        ), true, false
      ),
      new DAVACL_Element_ace(
        DAVACL::PRINCIPAL_ALL, false, array(
          DAVACL::PRIV_READ_ACL
        ), false, false
      )
    );
  }


  /**
   * Checks the verification code and verifies the e-mail address if the code is correct
   * @param   string  $code  The verification code
   * @return  boolean        True if the code verified correctly, false if the code was wrong
   */
  public function verify_email_address($code) {
    $updateStatement = BeeHub::mysqli()->prepare(
      'UPDATE `beehub_users`
          SET `email`=`unverified_email`
       WHERE `user_name`=? AND `verification_code`=? AND `verification_expiration`>NOW()'
    );
    $updateStatement->bind_param('ss', $this->name, $code);
    if (!$updateStatement->execute()) {
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }
    if ($updateStatement->affected_rows > 0) {
      $updateStatement = BeeHub::mysqli()->prepare(
        'UPDATE `beehub_users`
            SET `unverified_email`=null,
                `verification_code`=null,
                `verification_expiration`=null
        WHERE `user_name` = ?'
      );
      $updateStatement->bind_param('s', $this->name);
      if (!$updateStatement->execute()) {
        throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
      }
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

  
  public function is_admin() {
    return BeeHub_ACL_Provider::inst()->wheel() ||
      ( $this->path == BeeHub::current_user() );
  }


  /**
   * @param array $properties
   * @return array an array of (property => isReadable) pairs.
   */
  public function property_priv_read($properties) {
    $retval = parent::property_priv_read($properties);
    $is_admin = $this->is_admin();
    $retval[BeeHub::PROP_EMAIL]         = $is_admin;
    $retval[BeeHub::PROP_X509]          = $is_admin;
    $retval[DAV::PROP_GROUP_MEMBERSHIP] = $is_admin;
    $retval[BeeHub::PROP_PASSWORD]      = false;
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
