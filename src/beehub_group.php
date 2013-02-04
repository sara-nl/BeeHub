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
  public function method_GET() {
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
    $this->include_view( null, array( 'members' => $members ) );
  }


  public function method_HEAD() {
    // Only group admins are allowed to HEAD and GET.
    if (!$this->is_admin()) {
      throw new DAV_Status( DAV::HTTP_FORBIDDEN, DAV::COND_NEED_PRIVILEGES );
    }
    return array('Cache-Control' => 'no-cache');
  }


  public function method_POST ( &$headers ) {
    //First add members, admins and requests
    foreach (array('add_requests', 'add_members', 'add_admins', 'delete_admins', 'delete_members', 'delete_requests') as $key) {
      if (isset($_POST[$key])) {
        $members = array();
        if (!is_array($_POST[$key])) {
          throw new DAV_Status(DAV::HTTP_BAD_REQUEST);
        }
        foreach ($_POST[$key] as $uri) {
          $members[] = DAV::parseURI($uri, false);
        }
        switch ($key) {
          case 'add_members':
            $this->change_memberships($members, true, false, false, true);
            break;
          case 'add_admins':
            $this->change_memberships($members, true, false, true, true, null, true);
            break;
          case 'add_requests':
            $this->change_memberships($members, false, true, false, null, true);
            break;
          case 'delete_admins':
            $this->change_memberships($members, true, false, false, null, null, false);
            break;
          case 'delete_members':
          case 'delete_requests':
            $this->delete_members($members);
            break;
          default: //Should/cloud never happen
            throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
          break;
        }
      }
    }
  }

  /**
   * Adds member requests or sets them to be an invited member or an administrator
   *
   * @param   Array    $members            An array with paths to the principals to add
   * @param   Boolean  $newInvited         The value the 'is_invited' field should have if the membership had to be added to the database
   * @param   Boolean  $newRequested       The value the 'is_requested' field should have if the membership had to be added to the database
   * @param   Boolean  $newAdmin           The value the 'is_admin' field should have if the membership had to be added to the database
   * @param   Boolean  $existingInvited    Optionally; The value the 'is_invited' field should have if the membership is already in the database. If ommited values will not be changed for existing memberships
   * @param   Boolean  $existingRequested  Optionally; The value the 'is_requested' field should have if the membership is already in the database. If ommited values will not be changed for existing memberships
   * @param   Boolean  $existingAdmin      Optionally; The value the 'is_admin' field should have if the membership is already in the database. If ommited values will not be changed for existing memberships
   * @return  void
   */
  public function change_memberships($members, $newInvited, $newRequested, $newAdmin, $existingInvited = null, $existingRequested = null, $existingAdmin = null){
    if (count($members) == 0) {
      return;
    }
    $newInvited = ($newInvited ? 1 : 0);
    $newRequested = ($newRequested ? 1 : 0);
    $newAdmin = ($newAdmin ? 1 : 0);
    if (is_null($existingInvited)) {
      $existingInvited = "`is_invited`";
    }else{
      $existingInvited = ($existingInvited ? 1 : 0);
    }
    if (is_null($existingRequested)) {
      $existingRequested = "`is_requested`";
    }else{
      $existingRequested = ($existingRequested ? 1 : 0);
    }
    if (is_null($existingAdmin)) {
      $existingAdmin = "`is_admin`";
    }else{
      $existingAdmin = ($existingAdmin ? 1 : 0);
    }
    $statement = BeeHub::mysqli()->prepare(
            'INSERT INTO `beehub_group_members` (`group_name`, `user_name`, `is_invited`, `is_requested`, `is_admin`)
                  VALUES (?, ?, ' . $newInvited . ', ' . $newRequested . ', ' . $newAdmin . ')
 ON DUPLICATE KEY UPDATE `is_invited`=' . $existingInvited . ', `is_requested`=' . $existingRequested . ', `is_admin`=' . $existingAdmin);
    $user_name = null;
    $statement->bind_param('ss', $this->name, $user_name);
    foreach ($members as $member) {
      $user_name = rawurldecode(basename($member));
      $statement->execute();
      // TODO: sent the user an e-mail
    }
  }

  /**
   * Delete memberships
   *
   * @param   Array    $members           An array with paths to the principals to add
   * @return  void
   */
  protected function delete_members($members) {
    if (count($members) == 0) {
      return;
    }
    $statement = BeeHub::mysqli()->prepare('DELETE FROM `beehub_group_members` WHERE `group_name`=? AND `user_name`=?');
    $user_name = null;
    $statement->bind_param('ss', $this->name, $user_name);
    foreach ($members as $member) {
      $user_name = rawurldecode(basename($path));
      $statement->execute();
    }
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
      $this->sql_props = array();
      $param_group_name = $this->name;

      # Query table `beehub_groups`
      if ( ! $statement_props->execute() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_props->error );
      if ( ! $statement_props->store_result() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_props->error );
      if ( ! $statement_props->fetch() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_props->error );
      $this->sql_props[DAV::PROP_DISPLAYNAME] = $result_displayname;
      $this->sql_props[BeeHub::PROP_DESCRIPTION] = $result_description;
      $statement_props->free_result();

      if ( ! $statement_members->execute() ||
           ! $statement_members->store_result() ) {
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR );
      }

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
