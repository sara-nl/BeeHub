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
 * @package BeeHub
 */
class BeeHub_Group extends BeeHub_Principal {

  public $sql_props = null;

  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET($headers) {
    $query = <<<EOS
    SELECT `username`,
           `display_name`,
           `admin`,
           `invited`,
           `requested`
      FROM `beehub_users`
INNER JOIN `beehub_group_members`
     USING (`user_id`)
     WHERE `beehub_group_members`.`group_id` = ?;
EOS;
    $statement = BeeHub::mysqli()->prepare($query);
    $groupId = $this->getId();
    $statement->bind_param('d', $groupId);
    $username = null;
    $displayname = null;
    $admin = null;
    $invited = null;
    $requested = null;
    $statement->bind_result($username, $displayname, $admin, $invited, $requested);
    $statement->execute();
    $members = array();
    while ($statement->fetch()) {
      $members[] = Array(
        'username' => $username,
        'displayname' => $displayname,
        'admin' => ($admin == 1),
        'invited' => ($invited == 1),
        'requested' => ($requested == 1)
      );
    }
    $view = new BeeHub_View('group.php');
    $view->setVar('group', $this);
    $view->setVar('members', $members);
    return ((BeeHub::best_xhtml_type() != 'text/html') ? DAV::xml_header() : '' ) . $view->getParsedView();
  }


  public function method_HEAD() {
    // Only group admins are allowed to HEAD and GET.
    if (!$this->isAdmin()) {
      throw new DAV_Status( DAV::HTTP_FORBIDDEN, DAV::COND_NEED_PRIVILEGES );
    }
    return array('Cache-Control' => 'no-cache');
  }


  protected function init_props() {
    if (is_null($this->sql_props)) {
      parent::init_props();
      $this->protected_props[BeeHub::PROP_NAME] = basename($this->path);
      static $param_group_name = null,
             $result_user_name = null,
             $result_display_name = null,
             $result_description = null,
             $statement_props = null,
             $statement_members = null,
             $statement_admins = null;

      // Lazy initialization of prepared statements:
      if (null === $statement_props) {
        $statement_props = BeeHub::mysqli()->prepare(
'SELECT `display_name`, `description`
 FROM `beehub_groups`
 WHERE `group_name` = ?'
        );
        $statement_props->bind_param( 's', $param_group_name );
        $statement_props->bind_result(
          $result_display_name, $result_description
        );
      }
      if (null === $statement_members) {
        $statement_members = BeeHub::mysqli()->prepare(
 'SELECT `user_name`
    FROM `beehub_group_members`
   WHERE `group_name` = ?
     AND `invited` = 1
     AND `requested` = 1'
        );
        $statement_members->bind_param( 's', $param_group_name );
        $statement_members->bind_result( $result_user_name );
      }

      $param_group_name = rawurldecode(basename($this->path));

      # Query table `beehub_groups`
      if ( ! $statement_props->execute() ||
           ! $statement_props->store_result() ||
           ! $statement_props->fetch() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR );
      $this->sql_props[DAV::PROP_DISPLAYNAME] = $result_display_name;
      $this->sql_props[BeeHub::PROP_DESCRIPTION] = $result_description;
      $statement_props->free_result();

      if ( ! $statement_members->execute() ||
           ! $statement_members->store_result() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR );

      $members = array();
      while ( $statement_members->fetch() ) {
        $members[] = BeeHub::$CONFIG['webdav_namespace']['users_path'] .
          rawurlencode($result_user_name);
      }
      $statement_members->free_result();
      $this->sql_props[DAV::PROP_GROUP_MEMBER_SET] = new DAV_Element_href($members);
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
    if (isset($this->sql_props[DAV::PROP_DISPLAYNAME])) {
      $displayname = $this->sql_props[DAV::PROP_DISPLAYNAME];
      unset($this->sql_props[DAV::PROP_DISPLAYNAME]);
    } else {
      $displayname = '';
    }
    if (isset($this->sql_props[BeeHub::PROP_DESCRIPTION])) {
      $description = $this->sql_props[BeeHub::PROP_DESCRIPTION];
      unset($this->sql_props[BeeHub::PROP_DESCRIPTION]);
    } else {
      $description = null;
    }

    // Write all data to database
    $updateStatement = BeeHub::mysqli()->prepare('UPDATE `beehub_groups` SET `display_name`=?, `description`=? WHERE `group_name`=?');
    $id = $this->id;
    $updateStatement->bind_param('ssd', $displayname, $description, $id);
    $updateStatement->execute();

    // Store all other properties
    parent::storeProperties();

    // And set the database properties again
    $this->sql_props[DAV::PROP_DISPLAYNAME] = $displayname;
    if (!is_null($description)) {
      $this->sql_props[BeeHub::PROP_DESCRIPTION] = $description;
    }
  }


  public function user_prop_group_membership() {
    return array();
  }


  public function user_set_group_member_set($set) {
    // Test if the current user is admin of this group, only those are allowed to add users.
    if (!$this->isAdmin()) {
      throw new DAV_Status(
              DAV::HTTP_FORBIDDEN,
              DAV::COND_NEED_PRIVILEGES
      );
    }

    // Determine new users and users to be removed
    $currentUsers = $this->user_prop_group_member_set();
    $newUsers = array_diff($set, $currentUsers);
    $removedUsers = array_diff($currentUsers, $set);
    $groupId = intval($this->getId());

    // Remove users
    if (count($removedUsers) > 0) {
      $idQueryParts = array();
      foreach ($removedUsers as $path) {
        $user = BeeHub_Registry::inst()->resource($path);
        $idQueryParts[] = "(`group_id`='" . $groupId . "' AND `user_id`='" . intval($user->getId()) . "')";
      }
      BeeHub::mysqli()->query('DELETE FROM `beehub_group_members` WHERE ' . implode(' OR ', $idQueryParts));
    }

    // Insert all new ID's to the database
    if (count($newUsers) > 0) {
      $idQueryParts = array();
      foreach ($newUsers as $path) {
        $user = BeeHub_Registry::inst()->resource($path);
        $idQueryParts[] = "('" . $groupId . "', '" . intval($user->getId()) . "', 1)";
        // TODO: sent the user an e-mail
      }
      BeeHub::mysqli()->query('INSERT INTO `beehub_group_members` (`group_id`, `user_id`, `invited`) VALUES ' . implode(',', $idQueryParts) . ' ON DUPLICATE KEY UPDATE `invited`=1');
    }
  }

<<<<<<< HEAD
=======
  public function user_prop_group_member_set() {
    $query = <<<EOS
    SELECT `username`
      FROM `beehub_users`
INNER JOIN `beehub_group_members`
     USING (`user_id`)
     WHERE `beehub_group_members`.`group_id` = ?
       AND ((`invited`=1 AND `requested`=1) OR `admin`=1);
EOS;
    $statement = BeeHub::mysqli()->prepare($query);
    $group_id = $this->getId();
    $statement->bind_param('d', $group_id);
    $username = null;
    $statement->bind_result($username);
    $statement->execute();

    $retval = array();
    while ($statement->fetch()) {
      $retval[] = BeeHub::$CONFIG['webdav_namespace']['users_path'] . rawurlencode($username);
    }
    $statement->free_result();
>>>>>>> origin/niek_vanalles

  public function user_prop_group_member_set() {
    return $this->user_prop(DAV::PROP_GROUP_MEMBER_SET);
  }


  // We allow everybody to do everything with this object in the ACL, so we can handle all privileges hard-coded without ACL's interfering
  public function user_prop_acl() {
    return array(
      new DAVACL_Element_ace('DAV: all', false, array('DAV: all'), false, true, null)
    );
  }


  /**
   * @see DAV_Resource::user_prop()
   */
  public function user_prop($propname) {
    $this->init_props();
    return @$this->sql_props[$propname];
  }


  /**
   * Determines whether the currently logged in user is an administrator of this group or not.
   *
   * @return  boolean  True if the currently logged in user is an administrator of this group, false otherwise
   */
  public function isAdmin() {
    if (is_null($this->admin)) {
      $result = null;
      $userId = BeeHub_Registry::inst()->resource(BeeHub::current_user())->getId();
      $groupId = $this->getId();
      $statement = BeeHub::mysqli()->prepare('SELECT `user_id` FROM `beehub_group_members` WHERE `group_id`=? AND `user_id`=? AND `admin`=1');
      $statement->bind_param('dd', $groupId, $userId);
      $statement->bind_result($result);
      $statement->execute();
      $response = $statement->fetch();
      if (is_null($response) || !($result > 0)) {
        $this->admin = false;
      } else {
        $this->admin = true;
      }
    }
    return $this->admin;
  }

  // These methods are only available for a limited range of users!
  public function method_PROPPATCH($propname, $value = null) {
    if (!$this->isAdmin()) {
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

} // class BeeHub_Group
