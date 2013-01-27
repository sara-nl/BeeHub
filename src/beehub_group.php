<?php

/* ·************************************************************************
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
 * ************************************************************************ */

/**
 * File documentation (who cares)
 * @package BeeHub
 */

/**
 * A group principal
 *
 * @TODO Fix ACL!
 * @package BeeHub
 */
class BeeHub_Group extends BeeHub_Principal {

  private static $statement_props = null;
  private static $param_slug = null;
  private static $result_group_id = null;
  private static $result_display_name = null;
  private static $result_description = null;

  protected $id = null;

  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET($headers) {
    $view = new BeeHub_View('group.php');
    $view->setVar('group', $this);
    return ((BeeHub::best_xhtml_type() != 'text/html') ? DAV::xml_header() : '' ) . $view->getParsedView();
  }

  public function method_HEAD() {
    // TODO: See if this can't be arranged with regular (though protected) ACL's (see user_prop_acl() function)
//    if ($this->path != BeeHub::current_user()) {
//      throw new DAV_Status(
//              DAV::HTTP_FORBIDDEN,
//              DAV::COND_NEED_PRIVILEGES
//      );
//    }
    $this->assert(DAVACL::PRIV_READ);
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
                  `group_id`,
                  `display_name`,
                  `description`
                 FROM `beehub_groups`
                 WHERE `groupname` = ?;'
        );
        self::$statement_props->bind_param('s', self::$param_slug);
        self::$statement_props->bind_result(
                self::$result_group_id, self::$result_display_name, self::$result_description
        );
      }
      self::$param_slug = $this->prop(BeeHub::PROP_NAME);
      self::$statement_props->execute();
      self::$result_group_id = null;
      self::$result_display_name = null;
      self::$result_description = null;
      self::$statement_props->fetch();
      $this->id = self::$result_group_id;
      $this->writable_props[DAV::PROP_DISPLAYNAME] = self::$result_display_name;
      $this->writable_props[BeeHub::PROP_DESCRIPTION] = self::$result_description;
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
    } else {
      $displayname = '';
    }
    if (isset($this->writable_props[BeeHub::PROP_DESCRIPTION])) {
      $description = $this->writable_props[BeeHub::PROP_DESCRIPTION];
      unset($this->writable_props[BeeHub::PROP_DESCRIPTION]);
    } else {
      $description = null;
    }

    // Write all data to database
    $updateStatement = BeeHub::mysqli()->prepare('UPDATE `beehub_groups` SET `display_name`=?, `description`=? WHERE `group_id`=?');
    $id = $this->id;
    $updateStatement->bind_param('ssd', $displayname, $description, $id);
    $updateStatement->execute();

    // Store all other properties
    parent::storeProperties();

    // And set the database properties again
    $this->writable_props[DAV::PROP_DISPLAYNAME] = $displayname;
    if (!is_null($description)) {
      $this->writable_props[BeeHub::PROP_DESCRIPTION] = $description;
    }
  }

  public function user_prop_group_membership() {
    return array();
  }

  public function user_set_group_member_set($set) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

  public function user_prop_group_member_set() {
    $query = <<<EOS
SELECT `users`.`username`
FROM `beehub_users` AS `users`
INNER JOIN `beehub_group_members` AS `memberships`
  USING (`user_id`)
WHERE `memberships`.`group_id` = ?;
EOS;
    $statement = BeeHub::mysqli()->prepare($query);
    $group_id = $this->id;
    $statement->bind_param('d', $group_id);
    $username = null;
    $statement->bind_result($username);
    $statement->execute();

    $retval = array();
    while ($statement->fetch()) {
      $retval[] = BeeHub::$CONFIG['webdav_namespace']['users_path'] . rawurlencode($username);
    }
    $statement->free_result();

    return $retval;
  }

} // class BeeHub_Group