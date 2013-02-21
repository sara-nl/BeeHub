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


  private $users = null;


  public function user_prop_acl_internal() {
    $this->init_props();
    $retval = array();
    foreach($this->users as $user_path => $user_info) {
      if ($user_info['is_admin']) {
        $retval[] = new DAVACL_Element_ace(
          $user_path, false, array(
            DAVACL::PRIV_WRITE
          ), false, false
        );
      }
    }
    return $retval;
  }


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
    $statement = BeeHub_DB::execute($query, 's', $this->name);
    $members = array();
    while ($row = $statement->fetch_row()) {
      $members[] = Array(
        'user_name' => $row[0],
        'displayname' => $row[1],
        'is_admin' => ($row[2] == 1),
        'is_invited' => ($row[3] == 1),
        'is_requested' => ($row[4] == 1)
      );
    }
    $statement->free_result();
    $this->include_view( null, array( 'members' => $members ) );
  }


  public function method_POST ( &$headers ) {
    $auth = BeeHub_Auth::inst();
    if (!$auth->is_authenticated()) {
      throw DAV::forbidden();
    }
    $admin_functions = array('add_members', 'add_admins', 'delete_admins', 'delete_members', 'delete_requests');
    if (!$this->is_admin()) {
      foreach ($admin_functions as $function) {
        if (isset($_POST[$function])) {
          throw DAV::forbidden();
        }
      }
    }

    // Allow users to request or remove membership
    if (isset($_POST['leave'])) {
      $this->delete_members(array(BeeHub_Auth::inst()->current_user()->path));
    }
    if (isset($_POST['join'])) {
      $this->change_memberships(array(BeeHub_Auth::inst()->current_user()->path), false, true, false, null, true);
    }

    // Run administrator actions: add members, admins and requests
    foreach ($admin_functions as $key) {
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
          case 'delete_admins':
            $this->change_memberships($members, true, false, false, null, null, false);
            break;
          case 'delete_members':
          case 'delete_requests':
            $this->delete_members($members);
            break;
          default: //Should/could never happen
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
    foreach ($members as $member) {
      $user_name = rawurldecode(basename($member));
      $statement = BeeHub_DB::execute(
        'INSERT INTO `beehub_group_members` (
           `group_name`, `user_name`, `is_invited`,
           `is_requested`, `is_admin`
         )
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY
           UPDATE `is_invited`   = ?,
                  `is_requested` = ?,
                  `is_admin`     = ?',
        'ssiiiiii',
        $this->name, $user_name, $newInvited,
        $newRequested, $newAdmin,
        $existingInvited,
        $existingRequested,
        $existingAdmin
      );
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
    foreach ($members as $member) {
      $user_name = rawurldecode(basename($member));
      BeeHub_DB::execute(
        'DELETE FROM `beehub_group_members`
         WHERE `group_name` = ?
           AND `user_name`  = ?',
        'ss', $this->name, $user_name
      );
    }
  }


  protected function init_props() {
    if (is_null($this->stored_props)) {
      $this->stored_props = array();
      $stmt = BeeHub_DB::execute(
'SELECT `displayname`, `description`
 FROM `beehub_groups`
 WHERE `group_name` = ?', 's', $this->name
      );

      # Query table `beehub_groups`
      $row = $stmt->fetch_row();
      if ( $row === null )
        throw new DAV_Status( DAV::HTTP_NOT_FOUND );
      $this->stored_props[DAV::PROP_DISPLAYNAME] = $row[0];
      $this->stored_props[BeeHub::PROP_DESCRIPTION] =
        DAV::xmlescape($row[1]);
      $stmt->free_result();

      # Query table `beehub_group_members`
      $stmt = BeeHub_DB::execute(
 'SELECT `user_name`, `is_invited`, `is_requested`, `is_admin`
    FROM `beehub_group_members`
   WHERE `group_name` = ?',
        's', $this->name
      );
      $this->users = array();
      $members = array();
      while ( $row = $stmt->fetch_row() ) {
        $user_path = BeeHub::$CONFIG['namespace']['users_path'] .
          rawurlencode($row[0]);
        $this->users[$user_path] = array(
          'is_invited' => !!$row[1],
          'is_requested' => !!$row[2],
          'is_admin' => !!$row[3]
        );
        if (!!$row[1] && !!$row[2])
          $members[] = $user_path;
      }
      $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] = $members;
      $stmt->free_result();
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

    $p_displayname = $this->stored_props[DAV::PROP_DISPLAYNAME];
    $p_description = DAV::xmlunescape( $this->stored_props[BeeHub::PROP_DESCRIPTION] );
    $stmt = BeeHub_DB::execute(
      'UPDATE `beehub_groups`
          SET `displayname` = ?,
              `description` = ?
        WHERE `group_name` = ?',
      'sss', $p_displayname, $p_description, $this->name
    );
    // Update the json file containing all displaynames of all privileges
    self::update_principals_json();
    $this->touched = false;
  }


  public function user_prop_group_member_set() {
    return $this->user_prop( DAV::PROP_GROUP_MEMBER_SET );
  }


  /**
   * @see DAV_Resource::user_set()
   * @TODO extract text content from the 'description' XML fragment.
   */
  public function user_set($propname, $value = null) {
    if (!$this->is_admin())
      throw DAV::forbidden();
    // TODO: Is this the correct implementation?
    return parent::user_set($name, $value);
  }


  /**
   * Determines whether the currently logged in user is an administrator of this group or not.
   *
   * @return  boolean  True if the currently logged in user is an administrator of this group, false otherwise
   */
  public function is_admin() {
    if ( BeeHub_ACL_Provider::inst()->wheel() ) return true;
    $this->init_props();
    return ( $current_user = BeeHub_Auth::inst()->current_user() ) &&
           ( $tmp = @$this->users[$current_user->path] ) &&
           $tmp['is_admin'];
  }


  public function is_member() {
    $this->init_props();
    return ( $current_user = BeeHub_Auth::inst()->current_user() ) &&
           ( $tmp = @$this->users[$current_user->path] ) &&
           $tmp['is_invited'] && $tmp['is_requested'];
  }


  public function is_invited() {
    $this->init_props();
    return ( $current_user = BeeHub_Auth::inst()->current_user() ) &&
           ( $tmp = @$this->users[$current_user->path] ) &&
           $tmp['is_invited'] && !$tmp['is_requested'];
  }


  public function is_requested() {
    $this->init_props();
    return ( $current_user = BeeHub_Auth::inst()->current_user() ) &&
           ( $tmp = @$this->users[$current_user->path] ) &&
           !$tmp['is_invited'] && $tmp['is_requested'];
  }


  public function user_propname() {
    return BeeHub::$GROUP_PROPS;
  }


  /**
   * @param array $properties
   * @return array an array of (property => isReadable) pairs.
   */
  public function property_priv_read($properties) {
    $retval = parent::property_priv_read($properties);
    if ( @$retval[DAV::PROP_GROUP_MEMBER_SET] )
      $retval[DAV::PROP_GROUP_MEMBER_SET] = $this->is_member();
    return $retval;
  }

} // class BeeHub_Group
