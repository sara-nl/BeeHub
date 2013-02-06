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
 * @TODO Checken of de properties in de juiste gevallen afschermd worden
 * @package BeeHub
 */
class BeeHub_Sponsor extends BeeHub_Principal {

  const RESOURCETYPE = '<sponsor xmlns="http://beehub.nl/" />';

  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET() {
    $query = <<<EOS
    SELECT `user_name`,
           `displayname`,
           `is_admin`,
           `is_accepted`
      FROM `beehub_users`
INNER JOIN `beehub_sponsor_members`
     USING (`user_name`)
     WHERE `beehub_sponsor_members`.`sponsor_name` = ?;
EOS;
    $statement = BeeHub::mysqli()->prepare($query);
    $statement->bind_param('s', $this->name);
    $r_user_name = null;
    $r_displayname = null;
    $r_is_admin = null;
    $r_is_accepted = null;
    $statement->bind_result($r_user_name, $r_displayname, $r_is_admin, $r_is_accepted);
    $statement->execute();
    $members = array();
    while ($statement->fetch()) {
      $members[] = Array(
        'user_name' => $r_user_name,
        'displayname' => $r_displayname,
        'is_admin' => ($r_is_admin == 1),
        'is_accepted' => ($r_is_accepted == 1)
      );
    }
    $this->include_view( null, array( 'members' => $members ) );
  }

  public function method_HEAD() {
    // Test if the current user is admin of this sponsor, only those are allowed to HEAD and GET.
    if (!$this->is_admin())
      throw DAV::forbidden();
    return array( 'Cache-Control' => 'no-cache' );
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
            $this->change_memberships($members, true, false, true);
            break;
          case 'add_admins':
            $this->change_memberships($members, true, true, true, true);
            break;
          case 'add_requests':
            $this->change_memberships($members, false, false, false, false);
            break;
          case 'delete_admins':
            $this->change_memberships($members, true, false, true, false);
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
   * Adds member requests or sets them to be an accepted member or an administrator
   *
   * @param   Array    $members           An array with paths to the principals to add
   * @param   Boolean  $newAccepted       The value the 'accepted' field should have if the membership had to be added to the database
   * @param   Boolean  $newAdmin          The value the 'admin' field should have if the membership had to be added to the database
   * @param   Boolean  $existingAccepted  Optionally; The value the 'accepted' field should have if the membership is already in the database. If ommited values will not be changed for existing memberships
   * @param   Boolean  $existingAdmin     Optionally; The value the 'admin' field should have if the membership is already in the database. If ommited values will not be changed for existing membership
   * @return  void
   */
  protected function change_memberships($members, $newAccepted, $newAdmin, $existingAccepted = null, $existingAdmin = null){
    if (count($members) == 0) {
      return;
    }
    $newAccepted = ($newAccepted ? 1 : 0);
    $newAdmin = ($newAdmin ? 1 : 0);
    if (is_null($existingAccepted)) {
      $existingAccepted = "`is_accepted`";
    }else{
      $existingAccepted = ($existingAccepted ? 1 : 0);
    }
    if (is_null($existingAdmin)) {
      $existingAdmin = "`is_admin`";
    }else{
      $existingAdmin = ($existingAdmin ? 1 : 0);
    }
    $statement = BeeHub::mysqli()->prepare(
            'INSERT INTO `beehub_sponsor_members` (`sponsor_name`, `user_name`, `is_accepted`, `is_admin`)
                  VALUES (?, ?, ' . $newAccepted . ', ' . $newAdmin . ')
 ON DUPLICATE KEY UPDATE `is_accepted`=' . $existingAccepted . ', `is_admin`=' . $existingAdmin);
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
    $statement = BeeHub::mysqli()->prepare('DELETE FROM `beehub_sponsor_members` WHERE `sponsor_name`=? AND `user_name`=?');
    $user_name = null;
    $statement->bind_param('ss', $this->name, $user_name);
    foreach ($members as $member) {
      $user_name = rawurldecode(basename($path));
      $statement->execute();
    }
  }


  private $users = null;


  protected function init_props() {
    static $statement_props = null,
           $statement_users = null,
           $param_sponsor_name = null,
           $result_displayname = null,
           $result_description = null,
           $result_user_name = null,
           $result_is_admin = null,
           $result_is_accepted = null;
    # Lazy initialization:
    if (null === $statement_props) {
      $statement_props = BeeHub::mysqli()->prepare(
        'SELECT
          `displayname`,
          `description`
         FROM `beehub_sponsors`
         WHERE `sponsor_name` = ?;'
      );
      $statement_props->bind_param('s', $param_sponsor_name);
      $statement_props->bind_result(
              $result_displayname, $result_description
      );
      $statement_users = BeeHub::mysqli()->prepare(
        'SELECT `user_name`, `is_admin`, `is_accepted`
         FROM `beehub_sponsor_members`
         WHERE `sponsor_name` = ?'
      );
      $statement_users->bind_param('s', $param_sponsor_name);
      $statement_users->bind_result(
        $result_user_name, $result_is_admin, $result_is_accepted );
    }

    if (is_null($this->stored_props)) {
      $param_sponsor_name = $this->name;
      $this->stored_props = array();

      if ( ! $statement_props->execute() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_props->error );
      if ( ! $statement_props->store_result() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_props->error );
      $fetch_result = $statement_props->fetch();
      if ( $fetch_result === false )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_props->error );
      if ( is_null($fetch_result) )
        throw new DAV_Status( DAV::HTTP_NOT_FOUND );

      $this->stored_props[DAV::PROP_DISPLAYNAME] = $result_displayname;
      $this->stored_props[BeeHub::PROP_DESCRIPTION] = $result_description;
      $statement_props->free_result();

      if ( ! $statement_users->execute() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_users->error );
      if ( ! $statement_users->store_result() )
        throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, $statement_users->error );
      $this->users = array();
      $members = array();
      while ( $statement_users->fetch() ) {
        $user_path = BeeHub::$CONFIG['namespace']['users_path'] .
          rawurlencode($result_user_name);
        $this->users[$user_path] = array(
          'is_accepted' => $result_is_accepted,
          'is_admin' => $result_is_admin
        );
        if ($result_is_accepted)
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
    if (!$this->touched) {
      return;
    }

    // Are database properties set? If so, get the value and unset them
    if (isset($this->stored_props[DAV::PROP_DISPLAYNAME])) {
      $displayname = $this->stored_props[DAV::PROP_DISPLAYNAME];
      unset($this->stored_props[DAV::PROP_DISPLAYNAME]);
    } else {
      $displayname = '';
    }
    if (isset($this->stored_props[BeeHub::PROP_DESCRIPTION])) {
      $description = $this->stored_props[BeeHub::PROP_DESCRIPTION];
      unset($this->stored_props[BeeHub::PROP_DESCRIPTION]);
    } else {
      $description = null;
    }

    // Write all data to database
    $updateStatement = BeeHub::mysqli()->prepare('UPDATE `beehub_sponsors` SET `displayname`=?, `description`=? WHERE `sponsor_name`=?');
    $updateStatement->bind_param('sss', $displayname, $description, $this->name);
    $updateStatement->execute();

    // Store all other properties
    parent::storeProperties();

    // And set the database properties again
    $this->stored_props[DAV::PROP_DISPLAYNAME] = $displayname;
    if (!is_null($description)) {
      $this->stored_props[BeeHub::PROP_DESCRIPTION] = $description;
    }
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


  public function user_prop_group_member_set() {
    return $this->user_prop(DAV::PROP_GROUP_MEMBER_SET);
  }


  private $is_admin_cache;

  /**
   * Determines whether the currently logged in user is an administrator of this sponsor or not.
   *
   * @return  boolean  True if the currently logged in user is an administrator of this sponsor, false otherwise
   */
  public function is_admin() {
    if (is_null($this->is_admin_cache)) {
      $result = null;
      $username = rawurldecode(basename(BeeHub::current_user()));
      $statement = BeeHub::mysqli()->prepare('SELECT `user_name` FROM `beehub_sponsor_members` WHERE `sponsor_name`=? AND `user_name`=? AND `is_admin`=1');
      $statement->bind_param('ss', $this->name, $username);
      $statement->bind_result($result);
      $statement->execute();
      $response = $statement->fetch();
      $this->is_admin_cache = !is_null($response);
    }
    return $this->is_admin_cache;
  }

} // class BeeHub_Sponsor