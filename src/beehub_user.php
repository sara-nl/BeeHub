<?php

/*************************************************************************
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
 * BeeHub::PROP_USER_ID
 * BeeHub::PROP_USERNAME
 * BeeHub::PROP_PASSWD
 * BeeHub::PROP_EMAIL
 * BeeHub::PROP_X509
 *
 * @TODO make user_id and username protected properties
 * @package BeeHub
 */
class BeeHub_User extends BeeHub_Principal {
  private static $statement_props = null;
  private static $param_user_login = null;
  private static $result_user_id = null;
  private static $result_display_name = null;
  private static $result_password = null;
  private static $result_email = null;
  private static $result_x509 = null;

  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET() {
    if ($this->path != BeeHub::current_user()) {
      throw new DAV_Status(
              DAV::HTTP_FORBIDDEN,
              DAV::COND_NEED_PRIVILEGES
      );
    }
    $view = new BeeHub_View('users.php');
    $view->setVar('user', $this);
    return ((BeeHub::best_xhtml_type() != 'text/html') ? DAV::xml_header() : '' ) . $view->getParsedView();
  }

  public function method_HEAD() {
    if ($this->path != BeeHub::current_user()) {
      throw new DAV_Status(
              DAV::HTTP_FORBIDDEN,
              DAV::COND_NEED_PRIVILEGES
      );
    }
    return array(
        'Content-Type' => BeeHub::best_xhtml_type() . '; charset="utf-8"',
        'Cache-Control' => 'no-cache'
    );
  }

  protected function init_props() {
    if (is_null($this->writable_props)) {
      parent::init_props();
      $this->protected_props[BeeHub::PROP_USERNAME] = basename($this->path);

      if (null === self::$statement_props) {
        self::$statement_props = BeeHub::mysqli()->prepare(
                'SELECT
                  `user_id`,
                  `display_name`,
                  `email`,
                  `password` IS NOT NULL,
                  `x509`
                 FROM `beehub_users`
                 WHERE `username` = ?;'
        );
        self::$statement_props->bind_param('s', self::$param_user_login);
        self::$statement_props->bind_result(
                self::$result_user_id,
                self::$result_display_name,
                self::$result_email,
                self::$result_password,
                self::$result_x509
        );
      }
      self::$param_user_login = $this->prop(BeeHub::PROP_USERNAME);
      self::$statement_props->execute();
      self::$result_user_id = null;
      self::$result_email = null;
      self::$result_password = null;
      self::$result_x509 = null;
      self::$result_display_name = null;
      self::$statement_props->fetch();
      $this->protected_props[BeeHub::PROP_USER_ID] = self::$result_user_id;
      $this->writable_props[DAV::PROP_DISPLAYNAME] = self::$result_display_name;
      $this->writable_props[BeeHub::PROP_EMAIL] = self::$result_email;
      if (!is_null(self::$result_x509)) {
        $this->writable_props[BeeHub::PROP_X509] = self::$result_x509;
      }
      if (self::$result_password) {
        $this->writable_props[BeeHub::PROP_PASSWD] = true; // Nobody should have read access to this property. But just in case, we always set it to true.
      }
      self::$statement_props->free_result();
    }
  }

  /**
   * Stores properties set earlier by set().
   * @return void
   * @throws DAV_Status in particular 507 (Insufficient Storage)
   */
  public function storeProperties() {
    if ($this->path != BeeHub::current_user()) {
      throw new DAV_Status(
              DAV::HTTP_FORBIDDEN,
              DAV::COND_NEED_PRIVILEGES
      );
    }
    if (!$this->touched) {
      return;
    }

    // Are database properties set? If so, get the value and unset them
    if (isset($this->writable_props[BeeHub::PROP_EMAIL])) {
      $email = $this->writable_props[BeeHub::PROP_EMAIL];
      unset($this->writable_props[BeeHub::PROP_EMAIL]);
    }else{
      $email = '';
    }
    if (isset($this->writable_props[BeeHub::PROP_PASSWD])) {
      if ($this->writable_props[BeeHub::PROP_PASSWD] === true) { //true means there is a password, but it hasn't been changed!
        $password = true;
      }else{
        $password = crypt($this->writable_props[BeeHub::PROP_PASSWD], '$5$rounds=5000$' . md5(time() . rand(0, 99999)) . '$');
      }
      unset($this->writable_props[BeeHub::PROP_PASSWD]);
    }else{
      $password = null;
    }
    if (isset($this->writable_props[BeeHub::PROP_X509])) {
      $x509 = $this->writable_props[BeeHub::PROP_X509];
      unset($this->writable_props[BeeHub::PROP_X509]);
    }else{
      $x509 = null;
    }

    // Write all data to database
    $updateStatement = BeeHub::mysqli()->prepare('UPDATE `beehub_users` SET `email`=?, `x509`=?' . (($password === true) ? '' : ', `password`=?') . ' WHERE `user_id`=?');
    $id = $this->prop(BeeHub::PROP_USER_ID);
    if ($password === true) {
      $updateStatement->bind_param('ssd',
              $email,
              $x509,
              $id
              );
    }else{
      $updateStatement->bind_param('sssd',
              $email,
              $x509,
              $password,
              $id
              );
    }
    $updateStatement->execute();

    // Store all other properties
    parent::storeProperties();

    // And set the database properties again
    if (!is_null($password)) {
      $this->writable_props[BeeHub::PROP_PASSWD] = true; // Nobody should have read access to this property. But just in case, we always set it to true.
    }
    $this->writable_props[BeeHub::PROP_EMAIL] = $email;
    if (!is_null($x509)) {
      $this->writable_props[BeeHub::PROP_X509] = $x509;
    }
  }

  public function user_prop_acl() {
    $default = parent::user_prop_acl();
    $protected = array(
        new DAVACL_Element_ace(
                DAVACL::PRINCIPAL_SELF, false, array(DAVACL::PRIV_WRITE), false, true, null
        )
    );
    return array_merge($protected, $default);
  }

  // TODO: Remove this method and make the client page (returned with a GET request) change the properties through AJAX PROPPATCH calls
  public function method_POST() {
//      $db = BeeHub::mysqli();
//      $query = "UPDATE `beehub_users` SET `display_name`=?, `email`=? WHERE `username`='" . basename($this->path) . "'";
//      if (!isset($_POST['change_password']) || ($_POST['change_password'] != 'true')) {
//        $statement = $db->prepare($query . ';');
//        $statement->bind_param('ss', $_POST['displayname'], $_POST['email']);
//      } else {
//        if ($_POST['password1'] != $_POST['password2']) {
//          die('Passwords are not the same!');
//        }
//        $password = hash($_POST['password1']);
//        $query .= ', `password`=?';
//        $statement = $db->prepare($query . ' WHERE `user_id`=?;');
//        $statement->bind_param('sssd', $_POST['displayname'], $_POST['email'], $password,
////Dit gaat nog niet werken: hoe herken ik een gebruiker?
//                $attributes['uid']);
//      }
//      if (!$statement->execute()) {
      $this->user_set(BeeHub::PROP_EMAIL, $_POST['email']);
//      $this->user_set(BeeHub::PROP_PASSWD, 'test');
      $this->storeProperties();
  }

  public function user_prop_group_membership() {
    $esclogin = BeeHub::escape_string(basename($this->path));
    $query = <<<EOS
SELECT `g`.`slug`
FROM `bh_users` AS `u`
INNER JOIN `bh_bp_groups_members` AS `gm`
  ON `gm`.`user_id` = `u`.`ID`
INNER JOIN `bh_bp_groups` AS `g`
  ON `g`.`id` = `gm`.`group_id`
WHERE `u`.`user_login` = $esclogin;
EOS;
    $result = BeeHub::query($query);
    $retval = array();
    while (($row = $result->fetch_row()))
      $retval[] = BeeHub::$CONFIG['webdav_namespace']['groups_path'] . rawurlencode($row[0]);
    $result->free();
    return $retval;
  }

  public function user_prop_group_member_set() {
    return array();
  }

  public function user_set_group_member_set($set) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

} // class BeeHub_User