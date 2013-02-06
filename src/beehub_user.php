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
           $p_user_name = null,
           $r_displayname = null,
           $r_email = null,
           $r_new_email = null,
           $r_password = null,
           $r_x509 = null;

    # Lazy initialization of prepared statement:
    if (null === $statement_props) {
      $statement_props = BeeHub::mysqli()->prepare(
        'SELECT
          `displayname`,
          `email`,
          `new_email`,
          `password`,
          `x509`
         FROM `beehub_users`
         WHERE `user_name` = ?;'
      );
      $statement_props->bind_param('s', $p_user_name);
      $statement_props->bind_result(
        $r_displayname,
        $r_email,
        $r_new_email,
        $r_password,
        $r_x509
      );
    }

    if (is_null($this->stored_props)) {
      $p_user_name = $this->name;
      $statement_props->execute();
      $statement_props->fetch();
      $this->stored_props[DAV::PROP_DISPLAYNAME]    = $r_displayname;
      if (!is_null($r_email))
        $this->stored_props[BeeHub::PROP_EMAIL]     = $r_email;
      if (!is_null($r_email))
        $this->stored_props[BeeHub::PROP_EMAIL]     = $r_email;

      $this->stored_props[BeeHub::PROP_NEW_EMAIL] = $r_new_email;
      if (!is_null($r_x509)) {
        $this->stored_props[BeeHub::PROP_X509]   = $r_x509;
      }
      if ($r_password) {
        $this->stored_props[BeeHub::PROP_PASSWORD] = true; // Nobody should have read access to this property. But just in case, we always set it to true.
      }
      $statement_props->free_result();
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
    $p_email       = @$this->stored_props[BeeHub::PROP_EMAIL];
    $p_x509        = @$this->stored_props[BeeHub::PROP_X509];

    // Write all data to database
    $updateStatement = BeeHub::mysqli()->prepare(
      'UPDATE `beehub_users`
          SET `displayname` = ?,
              `email` = ?,
              `x509` = ?' .
              (($p_password === true) ? '' : ', `password`=?') .
      ' WHERE `user_name` = ?'
    );
    if ($p_password === true) {
      $updateStatement->bind_param(
        'ssss',
        $p_displayname,
        $p_email,
        $p_x509,
        $this->name
      );
    } else {
      $updateStatement->bind_param(
        'sssss',
        $p_displayname,
        $p_email,
        $p_x509,
        $p_password,
        $this->name
      );
    }
    if (!$updateStatement->execute()) {
      // TODO: check for duplicate keys!
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
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


  public function verify_email_address($code) {
    //TODO: e-mail verification
  }


  /**
   * @todo move the initialization into init_props()
   * @todo remove user_id
   */
  public function user_prop_group_membership() {
    $statement = BeeHub::mysqli()->prepare(
     'SELECT `group_name`
      FROM `beehub_group_members`
      WHERE `user_name` = ?
        AND `is_invited` = 1
        AND `is_requested` = 1'
    );
    $statement->bind_param('s', $this->name);
    $groupname = null;
    $statement->bind_result($groupname);
    $statement->execute();

    $retval = array();
    while ($statement->fetch()) {
      $retval[] = BeeHub::$CONFIG['namespace']['groups_path'] . rawurlencode($groupname);
    }
    $statement->free_result();

    return $retval;
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
  protected function user_set($name, $value = null) {
    if ( ! $this->is_admin() )
      throw DAV::forbidden();
    if ( false !== strpos( "$value", '<' ) )
      throw new DAV_Status( DAV::HTTP_BAD_REQUEST );
    if ( ! is_null($value) )
      $value = htmlspecialchars_decode($value);
    return $this->user_set_internal($name, $value);
  }


  public function user_set_internal($name, $value = null) {
    switch($name) {
      case BeeHub::PROP_EMAIL:
        //TODO: check e-mail format
    }
  }


  // These methods are only available for a limited range of users!
//@TODO: Dit is geen functie, maar PROPFIND moet wel beperkt worden!
//  public function method_PROPFIND($propname, $value = null) {
//    self: all
//    others: only display_name
//  }

} // class BeeHub_User
