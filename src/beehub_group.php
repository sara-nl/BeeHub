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
 * A group principal
 *
 * @package BeeHub
 */
class BeeHub_Group extends BeeHub_Principal {


  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET($headers) {
    $query = <<<EOS
    SELECT `user_name`,
           `displayname`,
           `is_admin`,
           `is_invited`,
           `is_requested`
      FROM `beehub_users`
INNER JOIN `beehub_group_members`
     USING (`user_name`)
     WHERE `group_name` = ?;
EOS;
    $statement = BeeHub::mysqli()->prepare($query);
    $statement->bind_param('s', $this->name);
    $user_name = null;
    $displayname = null;
    $admin = null;
    $invited = null;
    $requested = null;
    $statement->bind_result($user_name, $displayname, $admin, $invited, $requested);
    $statement->execute();
    $members = array();
    while ($statement->fetch()) {
      $members[] = Array(
        'user_name' => $user_name,
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
    if (!$this->is_admin()) {
      throw new DAV_Status( DAV::HTTP_FORBIDDEN, DAV::COND_NEED_PRIVILEGES );
    }
    return array('Cache-Control' => 'no-cache');
  }


  protected function init_props() {
    static $param_group_name = null,
           $result_user_name = null,
           $result_displayname = null,
           $result_description = null,
           $statement_props = null,
           $statement_members = null,
           $statement_admins = null;

    // Lazy initialization of prepared statements:
    if (null === $statement_props) {
      $statement_props = BeeHub::mysqli()->prepare(
'SELECT `displayname`, `description`
 FROM `beehub_groups`
 WHERE `group_name` = ?'
      );
      $statement_props->bind_param( 's', $param_group_name );
      $statement_props->bind_result(
        $result_displayname, $result_description
      );

      $statement_members = BeeHub::mysqli()->prepare(
 'SELECT `user_name`
  FROM `beehub_group_members`
 WHERE `group_name` = ?
   AND `is_invited` = 1
   AND `is_requested` = 1'
      );
      $statement_members->bind_param( 's', $param_group_name );
      $statement_members->bind_result( $result_user_name );
    }

    if (is_null($this->sql_props)) {

      $param_group_name = rawurldecode(basename($this->path));

      # Query table `beehub_groups`
      if ( ! $statement_props->execute() ||
           ! $statement_props->store_result() ||
           ! $statement_props->fetch() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR );
      $this->sql_props[DAV::PROP_DISPLAYNAME] = $result_displayname;
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

    static $statement_update = null;
    $p_displayname = $p_description = $p_group_name = '';
    if (null === $statement_update) {
      $statement_update = BeeHub::mysqli()->prepare(
 'UPDATE `beehub_groups`
     SET `displayname` = ?,
         `description` = ?
   WHERE `group_name` = ?'
      );
      $statement_update->bind_param('sss', $p_displayname, $p_description, $p_group_name);
    }

    $p_displayname = $this->sql_props[DAV::PROP_DISPLAYNAME];
    $p_description = $this->sql_props[BeeHub::PROP_DESCRIPTION];
    $p_group_name = $this->name;
    if ( ! $statement_update->execute() )
      throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR );
    $this->touched = false;
  }


  public function method_POST(&$headers) {
    // Test if the current user is admin of this group, only those are allowed to add users.
    if (!$this->is_admin()) {
      throw new DAV_Status(
              DAV::HTTP_FORBIDDEN,
              DAV::COND_CANNOT_MODIFY_PROTECTED_PROPERTY
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
      BeeHub::mysqli()->query('INSERT INTO `beehub_group_members` (`group_id`, `user_id`, `is_invited`) VALUES ' . implode(',', $idQueryParts) . ' ON DUPLICATE KEY UPDATE `is_invited`=1');
    }
  }


  public function user_prop_group_member_set() {
    return $this->user_prop(DAV::PROP_GROUP_MEMBER_SET);
  }


  // We allow everybody to do everything with this object in the ACL, so we can handle all privileges hard-coded without ACL's interfering
  public function user_prop_acl() {
    return array(
      new DAVACL_Element_ace('DAV: all', false, array('DAV: all'), false, true)
    );
  }


  /**
   * @see DAV_Resource::user_set()
   * @TODO extract text content from the 'description' XML fragment.
   */
  protected function user_set($propname, $value = null) {
    if (!$this->is_admin()) {
      throw new DAV_Status(
              DAV::HTTP_FORBIDDEN,
              DAV::COND_NEED_PRIVILEGES
      );
    }
    $this->init_props();
    if ( $propname !== BeeHub::PROP_DESCRIPTION || null === $value )
      throw new DAV_Status(DAV::HTTP_FORBIDDEN);
    $this->sql_props[$propname] = $value;
  }


  private $isAdminCache;


  /**
   * Determines whether the currently logged in user is an administrator of this group or not.
   *
   * @return  boolean  True if the currently logged in user is an administrator of this group, false otherwise
   */
  public function is_admin() {
    if (is_null($this->isAdminCache)) {
      $result = null;
      $p_user_name = rawurldecode(basename(BeeHub::current_user()));
      $groupName = $this->name;
      $statement = BeeHub::mysqli()->prepare(
 'SELECT `is_admin`
  FROM `beehub_group_members`
  WHERE `group_name` = ? AND `user_name` = ?
    AND `is_admin` = 1');
      $statement->bind_param('ss', $groupName, $p_user_name);
      $statement->bind_result($result);
      $statement->execute();
      $response = $statement->fetch();
      $this->isAdminCache = !is_null($response);
    }
    return $this->isAdminCache;
  }


  // All these methods are forbidden:
  public function method_ACL($aces) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

} // class BeeHub_Group
