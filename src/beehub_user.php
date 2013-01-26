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
  private static $statement_props = null;
  private static $param_user_login = null;
  private static $result_user_id = null;
  private static $result_display_name = null;
  private static $result_password = null;
  private static $result_email = null;
  private static $result_x509 = null;

  protected $id = null;

  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET() {
    $view = new BeeHub_View('user.php');
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
      $this->protected_props[BeeHub::PROP_NAME] = basename($this->path);

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
      self::$param_user_login = $this->prop(BeeHub::PROP_NAME);
      self::$statement_props->execute();
      self::$result_user_id = null;
      self::$result_email = null;
      self::$result_password = null;
      self::$result_x509 = null;
      self::$result_display_name = null;
      self::$statement_props->fetch();
      $this->id = self::$result_user_id;
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
    if (!$this->touched) {
      return;
    }

    // Are database properties set? If so, get the value and unset them
    if (isset($this->writable_props[DAV::PROP_DISPLAYNAME])) {
      $displayname = $this->writable_props[DAV::PROP_DISPLAYNAME];
      unset($this->writable_props[DAV::PROP_DISPLAYNAME]);
    }else{
      $displayname = '';
    }
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
        $password = crypt($this->writable_props[BeeHub::PROP_PASSWD], '$6$rounds=5000$' . md5(time() . rand(0, 99999)) . '$');
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
    $updateStatement = BeeHub::mysqli()->prepare('UPDATE `beehub_users` SET `display_name`=?, `email`=?, `x509`=?' . (($password === true) ? '' : ', `password`=?') . ' WHERE `user_id`=?');
    $id = $this->id;
    if ($password === true) {
      $updateStatement->bind_param('sssd',
              $displayname,
              $email,
              $x509,
              $id
              );
    }else{
      $updateStatement->bind_param('ssssd',
              $displayname,
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
    $this->writable_props[DAV::PROP_DISPLAYNAME] = $displayname;
    $this->writable_props[BeeHub::PROP_EMAIL] = $email;
    if (!is_null($x509)) {
      $this->writable_props[BeeHub::PROP_X509] = $x509;
    }
  }

  // We allow everybody to do everything with this object in the ACL, so we can handle all privileges hard-coded without ACL's interfering
  public function user_prop_acl() {
    return array(new DAVACL_Element_ace('DAV: all', false, array('DAV: all'), false, true, null));
  }

  public function user_prop_group_membership() {
    $query = <<<EOS
SELECT `groups`.`groupname`
FROM `beehub_groups` AS `groups`
INNER JOIN `beehub_group_members` AS `memberships`
  USING (`group_id`)
WHERE `memberships`.`user_id` = ?;
EOS;
    $statement = BeeHub::mysqli()->prepare($query);
    $user_id = $this->id;
    $statement->bind_param('d', $user_id);
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

  public function user_prop_group_member_set() {
    return array();
  }

  public function user_set_group_member_set($set) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

  /**
   * Gets the (database) ID of the user
   * @return  int  The (database) ID of this user
   */
  public function getId() {
    $this->init_props();
    return $this->id;
  }

  // These methods are only available for a limited range of users!
//@TODO: Dit is geen functie, maar PROPFIND moet wel beperkt worden!
//  public function method_PROPFIND($propname, $value = null) {
//    self: all
//    others: only display_name
//  }
//  public function method_REPORT(???) {
//    self: all
//    others: only display_name
//  }

  public function method_PROPPATCH($propname, $value = null) {
    if ($this->path != BeeHub::current_user()) {
      throw new DAV_Status(
              DAV::HTTP_FORBIDDEN,
              DAV::COND_NEED_PRIVILEGES
      );
    }
    return parent::method_PROPPATCH($propname, $value);
  }

  // All these methods are forbidden:
  public function method_ACL($aces) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

  public function method_COPY($path) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

  public function method_COPY_external($destination, $overwrite) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

  public function method_POST(&$headers) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

  public function method_PUT($stream) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

  public function method_PUT_range($stream, $start, $end, $total) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

} // class BeeHub_User