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
 * A user principal
 *
 * There are a few properties defined which are stored in the database instead
 * of as xfs attribute. These properties are credentials and user contact info:
 * BeeHub::PROP_NAME
 * BeeHub::PROP_PASSWD
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
  public function method_GET($headers) {
    $view = new BeeHub_View('user.php');
    $view->setVar('user', $this);
    return ((BeeHub::best_xhtml_type() != 'text/html') ? DAV::xml_header() : '' ) . $view->getParsedView();
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
           $r_password = null,
           $r_x509 = null;

    # Lazy initialization of prepared statement:
    if (null === $statement_props) {
      $statement_props = BeeHub::mysqli()->prepare(
        'SELECT
          `displayname`,
          `email`,
          `password` IS NOT NULL,
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
    }

    if (is_null($this->sql_props)) {
      $p_user_name = $this->name;
      $statement_props->execute();
      $statement_props->fetch();
      $this->sql_props[DAV::PROP_DISPLAYNAME] = $r_displayname;
      $this->sql_props[BeeHub::PROP_EMAIL]    = $r_email;
      if (!is_null($r_x509)) {
        $this->sql_props[BeeHub::PROP_X509]   = $r_x509;
      }
      if ($r_password) {
        $this->sql_props[BeeHub::PROP_PASSWD] = true; // Nobody should have read access to this property. But just in case, we always set it to true.
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

    if (isset($this->sql_props[BeeHub::PROP_PASSWD])) {
      if ($this->sql_props[BeeHub::PROP_PASSWD] === true) { //true means there is a password, but it hasn't been changed!
        $p_password = true;
      } else {
        $p_password = crypt($this->sql_props[BeeHub::PROP_PASSWD], '$6$rounds=5000$' . md5(time() . rand(0, 99999)) . '$');
      }
      $this->sql_props[BeeHub::PROP_PASSWD] = true;
    }else{
      $p_password = null;
    }

    $p_displayname = @$this->sql_props[DAV::PROP_DISPLAYNAME];
    $p_email       = @$this->sql_props[BeeHub::PROP_EMAIL];
    $p_x509        = @$this->sql_props[BeeHub::PROP_X509];

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

  // We allow everybody to do everything with this object in the ACL, so we can handle all privileges hard-coded without ACL's interfering
  public function user_prop_acl() {
    return array(new DAVACL_Element_ace('DAV: all', false, array('DAV: all'), false, true, null));
  }


  public function verify_email_address($code) {
    //TODO: e-mail verification
  }


  /**
   * @todo move the initialization into init_props()
   * @todo remove user_id
   */
  public function user_prop_group_membership() {
    $query = <<<EOS
SELECT `group_name`
FROM `beehub_group_members`
WHERE `user_name` = ?;
EOS;
    $statement = BeeHub::mysqli()->prepare($query);
    $user_name = $this->name;
    $statement->bind_param('s', $user_name);
    $groupname = null;
    $statement->bind_result($groupname);
    $statement->execute();

    $retval = array();
    while ($statement->fetch()) {
      $retval[] = BeeHub::$CONFIG['webdav_namespace']['groups_path'] . rawurlencode($groupname);
    }
    $statement->free_result();

    return $retval;
  }


  /**
   * @see DAVACL::user_prop_group_member_set()
   */
  public function user_prop_group_member_set() {
    return array();
  }

  public function is_admin() {
    return ($this->path == BeeHub::current_user());
  }

  // These methods are only available for a limited range of users!
//@TODO: Dit is geen functie, maar PROPFIND moet wel beperkt worden!
//  public function method_PROPFIND($propname, $value = null) {
//    self: all
//    others: only display_name
//  }

} // class BeeHub_User
