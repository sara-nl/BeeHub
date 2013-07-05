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
 * A sponsor principal
 *
 * @package BeeHub
 */
class BeeHub_Sponsor extends BeeHub_Principal {

  const RESOURCETYPE = '<sponsor xmlns="http://beehub.nl/" />';

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
      $this->init_props();
      $collection = BeeHub::getNoSQL()->users;
      
      $resultset = $collection->find( array( 'sponsors' => $this->name ), array( 'name' => true, 'displayname' => true ) );
      $members = array();
      foreach ( $resultset as $result ) {
        $members[ $result['name'] ] = Array(
          'user_name' => $result['name'],
          'displayname' => $result['displayname'],
          'is_admin' => $this->users[ $result['name'] ][ 'is_admin' ],
          'is_accepted' => $this->users[ $result['name'] ][ 'is_accepted' ]
        );
      }
    }
    $this->include_view( null, array( 'members' => $members ) );
  }


  public function method_POST ( &$headers ) {
    $auth = BeeHub_Auth::inst();
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
      $statement = BeeHub_DB::execute('SELECT `is_accepted` FROM `beehub_sponsor_members` WHERE `user_name`=? AND `sponsor_name`=?',
                                      'ss', $current_user->name, $this->name);
      $message = null;
      if ( !( $row = $statement->fetch_row() ) || ( $row[0] != 1 ) ) { // This user is not invited for this group, so sent the administrators an e-mail with this request
        $message =
'Dear sponsor administrator,

' . $current_user->prop(DAV::PROP_DISPLAYNAME) . ' (' . $current_user->prop(BeeHub::PROP_EMAIL) . ') wants to join the sponsor \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\'. One of the sponsor administrators needs to either accept or reject this membership request. Please see your notifications in BeeHub to do this:

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
      $this->change_memberships(array($current_user->name), false, false, false, null);
      if (!is_null($message)) {
        BeeHub::email($recipients,
                      'BeeHub notification: membership request for sponsor ' . $this->prop(DAV::PROP_DISPLAYNAME),
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
        $members = array_map(array('BeeHub_Sponsor', 'get_user_name'), $members);
        switch ($key) {
          case 'add_members':
            foreach ($members as $member) {
              $user = BeeHub::user($member);
              $statement = BeeHub_DB::execute('SELECT `is_accepted` FROM `beehub_sponsor_members` WHERE `user_name`=? AND `sponsor_name`=?',
                                              'ss', $user->name, $this->name);
              if ( !( $row = $statement->fetch_row() ) || ( $row[0] == 0 ) ) { // The user was not a member of this sponsor yet, so notify him/her
                $message =
'Dear ' . $user->prop(DAV::PROP_DISPLAYNAME) . ',

You are now sponsored by \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\'.

Best regards,

BeeHub';
                BeeHub::email($user->prop(DAV::PROP_DISPLAYNAME) . ' <' . $user->prop(BeeHub::PROP_EMAIL) . '>',
                              'BeeHub notification: new sponsor ' . $this->prop(DAV::PROP_DISPLAYNAME),
                              $message);
              }
            }
            $this->change_memberships($members, true, false, true);
            break;
          case 'add_admins':
            $this->change_memberships($members, true, true, true, true);
            break;
          case 'delete_admins':
            $this->check_admin_remove($members);
            $this->change_memberships($members, false, false, null, false);
            break;
          case 'delete_members':
            $this->delete_members($members);
            foreach ($members as $member) {
              $user = BeeHub::user($member);
              $message =
'Dear ' . $user->prop(DAV::PROP_DISPLAYNAME) . ',

Sponsor administrator ' . $current_user->prop(DAV::PROP_DISPLAYNAME) . ' removed you from the sponsor \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\'. If you believe you should be a member of this sponsor, please contact one of the sponsor administrators.

Best regards,

BeeHub';
              BeeHub::email($user->prop(DAV::PROP_DISPLAYNAME) . ' <' . $user->prop(BeeHub::PROP_EMAIL) . '>',
                            'BeeHub notification: removed from sponsor ' . $this->prop(DAV::PROP_DISPLAYNAME),
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
   * Adds member requests or sets them to be an accepted member or an administrator
   *
   * @param   Array    $members           An array with paths to the principals to add
   * @param   Boolean  $newAccepted       The value the 'accepted' field should have if the membership had to be added to the database
   * @param   Boolean  $newAdmin          The value the 'admin' field should have if the membership had to be added to the database
   * @param   Boolean  $existingAccepted  Optionally; The value the 'accepted' field should have if the membership is already in the database. If ommited values will not be changed for existing memberships
   * @param   Boolean  $existingAdmin     Optionally; The value the 'admin' field should have if the membership is already in the database. If ommited values will not be changed for existing membership
   * @return  void
   */
  public function change_memberships($members, $newAccepted, $newAdmin, $existingAccepted = null, $existingAdmin = null){
    if (count($members) === 0) {
      return;
    }
    $newAccepted = ($newAccepted ? 1 : 0);
    $newAdmin = ($newAdmin ? 1 : 0);
    if (is_null($existingAccepted)) {
      $existingAccepted = "`is_accepted`";
    } else {
      $existingAccepted = ($existingAccepted ? 1 : 0);
    }
    if (is_null($existingAdmin)) {
      $existingAdmin = "`is_admin`";
    } else {
      $existingAdmin = ($existingAdmin ? 1 : 0);
    }
    foreach ($members as $user_name) {
      BeeHub_DB::execute(
        'INSERT INTO `beehub_sponsor_members`
           (`sponsor_name`, `user_name`, `is_accepted`, `is_admin`)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY
           UPDATE `is_accepted` = ' . $existingAccepted . ',
                  `is_admin` = ' . $existingAdmin,
        'ssii',
        $this->name,
        $user_name,
        $newAccepted,
        $newAdmin
      );
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
        'DELETE FROM `beehub_sponsor_members`
         WHERE `sponsor_name` = ?
           AND `user_name` = ?',
        'ss', $this->name, $user_name
      );
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
         FROM `beehub_sponsor_members`
        WHERE `is_admin` = 1 AND
              `sponsor_name` = ? AND
              `user_name` NOT IN ('" . implode("','", $escaped_members) . "')",
      's', $this->name
    );
    $row = $check_admin_statement->fetch_row();
    if ($row[0] === 0) {
      throw new DAV_Status(DAV::HTTP_CONFLICT, 'You are not allowed to remove all the sponsor administrators from a group. Leave at least one sponsor administrator in the group or appoint a new sponsor administrator!');
    }
  }


  private static function get_user_name($user_name) {
    return rawurldecode(basename($user_name));
  }


  protected function init_props() {
    if (is_null($this->stored_props)) {
      $this->stored_props = array();

      $statement_props = BeeHub_DB::execute(
        'SELECT
          `displayname`,
          `description`
         FROM `beehub_sponsors`
         WHERE `sponsor_name` = ?',
        's', $this->name
      );
      $row = $statement_props->fetch_row();
      if ( is_null($row) )
        throw new DAV_Status( DAV::HTTP_NOT_FOUND );

      $this->stored_props[DAV::PROP_DISPLAYNAME] = $row[0];
      $this->stored_props[BeeHub::PROP_DESCRIPTION] =
        DAV::xmlescape($row[1]);
      $statement_props->free_result();

      $statement_users = BeeHub_DB::execute(
        'SELECT `user_name`, `is_admin`, `is_accepted`
         FROM `beehub_sponsor_members`
         WHERE `sponsor_name` = ?',
        's', $this->name
      );
      $this->users = array();
      $members = array();
      while ( $row = $statement_users->fetch_row() ) {
        $user_path = BeeHub::USERS_PATH .
          rawurlencode($row[0]);
        $this->users[$user_path] = array(
          'is_admin' => !!$row[1],
          'is_accepted' => !!$row[2]
        );
        if (!!$row[2])
          $members[] = $user_path;
      }
      $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] = $members;
      $statement_users->free_result();
    }
  }

  /**
   * Stores properties set earlier by set().
   * @return void
   * @throws DAV_Status in particular 507 (Insufficient Storage)
   */
  public function storeProperties() {
    if (!$this->touched)
      return;
    $statement_update = BeeHub_DB::execute(
      'UPDATE `beehub_sponsors`
          SET `displayname` = ?,
              `description` = ?
        WHERE `sponsor_name` = ?',
      'sss',
      @$this->stored_props[DAV::PROP_DISPLAYNAME],
      DAV::xmlunescape( @$this->stored_props[BeeHub::PROP_DESCRIPTION] ),
      $this->name
    );
    // Update the json file containing all displaynames of all privileges
    self::update_principals_json();
    $this->touched = false;
  }


  public function user_prop_group_member_set() {
    return $this->user_prop( DAV::PROP_GROUP_MEMBER_SET );
  }


  /**
   * Determines whether the currently logged in user is an administrator of this sponsor or not.
   *
   * @return  boolean  True if the currently logged in user is an administrator of this sponsor, false otherwise
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
           $tmp['is_accepted'];
  }


  public function is_requested() {
    $this->init_props();
    return ( $current_user = BeeHub_Auth::inst()->current_user() ) &&
           ( $tmp = @$this->users[$current_user->path] ) &&
           !$tmp['is_accepted'];
  }


  public function user_propname() {
    return BeeHub::$SPONSOR_PROPS;
  }


  /**
   * @param array $properties
   * @return array an array of (property => isReadable) pairs.
   */
  public function property_priv_read($properties) {
    $retval = parent::property_priv_read($properties);
    if ( @$retval[DAV::PROP_GROUP_MEMBER_SET] )
      $retval[DAV::PROP_GROUP_MEMBER_SET] = $this->is_admin();
    return $retval;
  }


} // class BeeHub_Sponsor
