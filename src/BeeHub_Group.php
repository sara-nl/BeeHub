<?php

/*·************************************************************************
 * Copyright ©2007-2014 SARA b.v., Amsterdam, The Netherlands
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
    $members = array();
    if ( $this->is_member() ) {
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
      while ($row = $statement->fetch_row()) {
        $members[$row[0]] = Array(
          'user_name' => $row[0],
          'displayname' => $row[1],
          'is_admin' => ($row[2] === 1),
          'is_invited' => ($row[3] === 1),
          'is_requested' => ($row[4] === 1)
        );
      }
      $statement->free_result();
    }
    $this->include_view( null, array( 'members' => $members ) );
  }


  public function method_POST ( &$headers ) {
    $auth = BeeHub::getAuth();
    if (!$auth->is_authenticated()) {
      throw DAV::forbidden();
    }
    $admin_functions = array('add_members', 'add_admins', 'delete_admins', 'delete_members');
    if (!$this->is_admin()) {
      foreach ($admin_functions as $function) {
        if (isset($_POST[$function])) {
          throw DAV::forbidden();
        }
      }
    }

    // Allow users to request or remove membership
    $current_user = $auth->current_user();
    if (isset($_POST['leave'])) {
      $this->delete_members(array($current_user->name));
    }
    if (isset($_POST['join'])) {
      $statement = BeeHub_DB::execute('SELECT `is_invited` FROM `beehub_group_members` WHERE `user_name`=? AND `group_name`=?',
                                      'ss', $current_user->name, $this->name);
      $message = null;
      if ( !( $row = $statement->fetch_row() ) || ( $row[0] != 1 ) ) { // This user is not invited for this group, so sent the administrators an e-mail with this request
        $message =
'Dear group administrator,

' . $current_user->prop(DAV::PROP_DISPLAYNAME) . ' (' . $current_user->prop(BeeHub::PROP_EMAIL) . ') wants to join the group \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\'. One of the group administrators needs to either accept or reject this membership request. Please see your notifications in BeeHub to do this:

' . BeeHub::urlbase(true) . '/system/?show_notifications=1

Best regards,

BeeHub';
        $recipients = array();
        foreach ($this->users as $user => $attributes) {
          if ($attributes['is_admin']) {
            $user = BeeHub::user($user);
            $recipients[] = $user->prop(DAV::PROP_DISPLAYNAME) . ' <' . $user->prop(BeeHub::PROP_EMAIL) . '>';
          }
        }
      }
      $this->change_memberships(array($current_user->name), false, true, false, null, true);
      if (!is_null($message)) {
        BeeHub::email($recipients,
                      'BeeHub notification: membership request for group ' . $this->prop(DAV::PROP_DISPLAYNAME),
                      $message);
      }
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
        $members = array_map(array('BeeHub_Group', 'get_user_name'), $members);
        switch ($key) {
          case 'add_members':
            foreach ($members as $member) {
              $user = BeeHub::user($member);
              $statement = BeeHub_DB::execute('SELECT `is_requested` FROM `beehub_group_members` WHERE `user_name`=? AND `group_name`=?',
                                              'ss', $user->name, $this->name);
              if ( !( $row = $statement->fetch_row() ) || ( $row[0] != 1 ) ) { // This user did not request for this group, so sent him/her an e-mail with this invitation
                $message =
'Dear ' . $user->prop(DAV::PROP_DISPLAYNAME) . ',

You are invited to join the group \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\'. You need to accept this invitation before your membership is activated. Please see your notifications in BeeHub to do this:

' . BeeHub::urlbase(true) . '/system/?show_notifications=1

Best regards,

BeeHub';
              }else{ // The user requested this membership, so now he/she is really a member
                $message =
'Dear ' . $user->prop(DAV::PROP_DISPLAYNAME) . ',

Your membership of the group \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\' is accepted by a group administrator. You are now a member of this group.

Best regards,

BeeHub';
              }
              BeeHub::email($user->prop(DAV::PROP_DISPLAYNAME) . ' <' . $user->prop(BeeHub::PROP_EMAIL) . '>',
                            'BeeHub notification: membership accepted for group ' . $this->prop(DAV::PROP_DISPLAYNAME),
                            $message);
            }
            $this->change_memberships($members, true, false, false, true);
            break;
          case 'add_admins':
            $this->change_memberships($members, true, false, true, true, null, true);
            break;
          case 'delete_admins':
            $this->check_admin_remove($members);
            $this->change_memberships($members, true, false, false, null, null, false);
            break;
          case 'delete_members':
            $this->delete_members($members);
            foreach ($members as $member) {
              $user = BeeHub::user($member);
              $message =
'Dear ' . $user->prop(DAV::PROP_DISPLAYNAME) . ',

Group administrator ' . $current_user->prop(DAV::PROP_DISPLAYNAME) . ' removed you from the group \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\'. If you believe you should be a member of this group, please contact one of the group administrators.

Best regards,

BeeHub';
              BeeHub::email($user->prop(DAV::PROP_DISPLAYNAME) . ' <' . $user->prop(BeeHub::PROP_EMAIL) . '>',
                            'BeeHub notification: removed from group ' . $this->prop(DAV::PROP_DISPLAYNAME),
                            $message);
            }
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
   * @param   Array    $members            An array with usernames of the principals to add
   * @param   Boolean  $newInvited         The value the 'is_invited' field should have if the membership had to be added to the database
   * @param   Boolean  $newRequested       The value the 'is_requested' field should have if the membership had to be added to the database
   * @param   Boolean  $newAdmin           The value the 'is_admin' field should have if the membership had to be added to the database
   * @param   Boolean  $existingInvited    Optionally; The value the 'is_invited' field should have if the membership is already in the database. If ommited values will not be changed for existing memberships
   * @param   Boolean  $existingRequested  Optionally; The value the 'is_requested' field should have if the membership is already in the database. If ommited values will not be changed for existing memberships
   * @param   Boolean  $existingAdmin      Optionally; The value the 'is_admin' field should have if the membership is already in the database. If ommited values will not be changed for existing memberships
   * @return  void
   */
  public function change_memberships($members, $newInvited, $newRequested, $newAdmin, $existingInvited = null, $existingRequested = null, $existingAdmin = null){
    if (count($members) === 0) {
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
    foreach ($members as $user_name) {
      $statement = BeeHub_DB::execute(
        'INSERT INTO `beehub_group_members` (
           `group_name`, `user_name`, `is_invited`,
           `is_requested`, `is_admin`
         )
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY
           UPDATE `is_invited`   = ' . $existingInvited . ',
                  `is_requested` = ' . $existingRequested . ',
                  `is_admin`     = ' . $existingAdmin,
        'ssiii',
        $this->name, $user_name, $newInvited,
        $newRequested, $newAdmin
      );

      // And change local cache
      if ( isset( $this->users[ BeeHub::USERS_PATH . $user_name ] ) ) {
        if ( $existingInvited !== "`is_invited`" ) {
          $this->users[ BeeHub::USERS_PATH . $user_name ]['is_invited'] = (bool) $existingInvited;
        }
        if ( $existingRequested !== "`is_requested`" ) {
          $this->users[ BeeHub::USERS_PATH . $user_name ]['is_requested'] = (bool) $existingRequested;
        }
        if ( $existingAdmin !== "`is_admin`" ) {
          $this->users[ BeeHub::USERS_PATH . $user_name ]['is_admin'] = (bool) $existingAdmin;
        }
      }else{
        $this->users[ BeeHub::USERS_PATH . $user_name ] = array(
            'is_invited' => (bool) $newInvited,
            'is_requested' => (bool) $newRequested,
            'is_admin' => (bool) $newAdmin
        );
      }

      $key = array_search( BeeHub::USERS_PATH . $user_name, $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] );
      if ( $this->users[ BeeHub::USERS_PATH . $user_name ]['is_invited'] && $this->users[ BeeHub::USERS_PATH . $user_name ]['is_requested'] && ( $key === false ) ) {
        $this->stored_props[DAV::PROP_GROUP_MEMBER_SET][] = BeeHub::USERS_PATH . $user_name;
      }elseif ( ( ! $this->users[ BeeHub::USERS_PATH . $user_name ]['is_invited'] || ! $this->users[ BeeHub::USERS_PATH . $user_name ]['is_requested'] ) && ( $key !== false ) ) {
        unset( $this->stored_props[DAV::PROP_GROUP_MEMBER_SET][ $key ] );
      }
    }
  }

  /**
   * Delete memberships
   *
   * @param   Array    $members           An array with paths to the principals to add
   * @return  void
   */
  protected function delete_members($members) {
    if (count($members) === 0) {
      return;
    }
    $this->check_admin_remove($members);

    // Then delete all the members
    foreach ($members as $user_name) {
      BeeHub_DB::execute(
        'DELETE FROM `beehub_group_members`
         WHERE `group_name` = ?
           AND `user_name`  = ?',
        'ss', $this->name, $user_name
      );

      if ( isset( $this->users[ BeeHub::USERS_PATH . $user_name ] ) ) {
        unset( $this->users[ BeeHub::USERS_PATH . $user_name ] );
      }

      $key = array_search( BeeHub::USERS_PATH . $user_name, $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] );
      if ( $key !== false ) {
        unset( $this->stored_props[DAV::PROP_GROUP_MEMBER_SET][ $key ] );
      }
    }
  }


  private function check_admin_remove($members) {
    if (count($members) === 0) {
      return;
    }
    // Check if this request is not removing all administrators from this group
    $escaped_members = array_map(array(BeeHub_DB::mysqli(), 'real_escape_string'), $members);
    $check_admin_statement = BeeHub_DB::execute(
      "SELECT COUNT(`user_name`)
         FROM `beehub_group_members`
        WHERE `is_admin` = 1 AND
              `group_name` = ? AND
              `user_name` NOT IN ('" . implode("','", $escaped_members) . "')",
      's', $this->name
    );
    $row = $check_admin_statement->fetch_row();
    if ($row[0] === 0) {
      throw new DAV_Status(DAV::HTTP_CONFLICT, 'You are not allowed to remove all the group administrators from a group. Leave at least one group administrator in the group or appoint a new group administrator!');
    }
  }


  private static function get_user_name($user_name) {
    return rawurldecode(basename($user_name));
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
      $this->stored_props[BeeHub::PROP_DESCRIPTION] = $row[1];
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
        $user_path = BeeHub::USERS_PATH .
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
    $p_description = $this->stored_props[BeeHub::PROP_DESCRIPTION];
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
   * Determines whether the currently logged in user is an administrator of this group or not.
   *
   * @return  boolean  True if the currently logged in user is an administrator of this group, false otherwise
   */
  public function is_admin() {
    if ( DAV::$ACLPROVIDER->wheel() ) return true;
    $this->init_props();
    return ( $current_user = BeeHub::getAuth()->current_user() ) &&
           ( $tmp = @$this->users[$current_user->path] ) &&
           $tmp['is_admin'];
  }


  public function is_member() {
    $this->init_props();
    return ( $current_user = BeeHub::getAuth()->current_user() ) &&
           ( $tmp = @$this->users[$current_user->path] ) &&
           $tmp['is_invited'] && $tmp['is_requested'];
  }


  public function is_invited() {
    $this->init_props();
    return ( $current_user = BeeHub::getAuth()->current_user() ) &&
           ( $tmp = @$this->users[$current_user->path] ) &&
           $tmp['is_invited'] && !$tmp['is_requested'];
  }


  public function is_requested() {
    $this->init_props();
    return ( $current_user = BeeHub::getAuth()->current_user() ) &&
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


  public function user_set($name, $value = null) {
    if ( $name === BeeHub::PROP_DESCRIPTION ) {
      $this->user_set_description( $value );
    }else{
      parent::user_set( $name, $value );
    }
  }


  public function user_set_description( $description ) {
    parent::user_set( BeeHub::PROP_DESCRIPTION, DAV::xmlunescape( $description ) );
  }

} // class BeeHub_Group
