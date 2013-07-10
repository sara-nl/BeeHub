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
    $members = array();
    if ( $this->is_member() ) {
      $this->init_props();
      $collection = BeeHub::getNoSQL()->users;
      
      $resultset = $collection->find( array( 'groups' => $this->name ), array( 'name' => true, 'displayname' => true ) );
      $members = array();
      foreach ( $resultset as $result ) {
        $members[ $result['name'] ] = Array(
          'user_name'    => $result['name'],
          'displayname'  => $result['displayname'],
          'is_admin'     => $this->users[ BeeHub::USERS_PATH . $result['name'] ][ 'is_admin' ],
          'is_invited'   => $this->users[ BeeHub::USERS_PATH . $result['name'] ][ 'is_invited' ],
          'is_requested' => $this->users[ BeeHub::USERS_PATH . $result['name'] ][ 'is_requested' ]
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
      $message = null;
      if ( ! $this->is_invited() && ! $this->is_member() ) { // This user is not invited for this group, so sent the administrators an e-mail with this request
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
              $message = null;
              if ( ! $this->is_requested($user) && ! $this->is_member($user) ) { // This user did not request for this group, so sent him/her an e-mail with this invitation
                $message =
'Dear ' . $user->prop(DAV::PROP_DISPLAYNAME) . ',

You are invited to join the group \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\'. You need to accept this invitation before your membership is activated. Please see your notifications in BeeHub to do this:

' . BeeHub::urlbase(true) . '/system/?show_notifications=1

Best regards,

BeeHub';
              }elseif ( $this->is_requested($user) ) { // The user requested this membership, so now he/she is really a member
                $message =
'Dear ' . $user->prop(DAV::PROP_DISPLAYNAME) . ',

Your membership of the group \'' . $this->prop(DAV::PROP_DISPLAYNAME) . '\' is accepted by a group administrator. You are now a member of this group.

Best regards,

BeeHub';
              }
              if ( ! is_null( $message ) ) {
                BeeHub::email($user->prop(DAV::PROP_DISPLAYNAME) . ' <' . $user->prop(BeeHub::PROP_EMAIL) . '>',
                              'BeeHub notification: membership accepted for group ' . $this->prop(DAV::PROP_DISPLAYNAME),
                              $message);
              }
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
    $collection = BeeHub::getNoSQL()->groups;
    $userCollection = BeeHub::getNoSQL()->users;
    $document = $collection->findOne( array( 'name' => $this->name ) );
    
    foreach ($members as $user_name) {
      // Check if the user exists
      $user = $userCollection->findOne( array( 'name' => $user_name ) );
      if ( is_null( $user ) ) {
        throw new DAV_Status(DAV::HTTP_CONFLICT, "Not all users exist!");
      }
      
      // Change or add the membership details to the group document
      if ( array_key_exists( $user_name, $document['members'] ) ) {
        if ( !is_null( $existingInvited ) ) {
          $document['members'][$user_name]['is_invited'] = ($existingInvited ? 1 : 0);
        }
        if ( !is_null( $existingRequested ) ) {
          $document['members'][$user_name]['is_requested'] = ($existingRequested ? 1 : 0);
        }
        if ( !is_null( $existingAdmin ) ) {
          $document['members'][$user_name]['is_admin'] = ($existingAdmin ? 1 : 0);
        }
      }else{
        $document['members'][$user_name] = array(
            'is_invited' => ($newInvited ? 1 : 0),
            'is_requested' => ($newRequested ? 1 : 0),
            'is_admin' => ($newAdmin ? 1 : 0)
        );
      }
      
      // Also change the user document if the membership is accepted
      if ( ( $document['members'][$user_name]['is_invited'] === 1 ) &&
           ( $document['members'][$user_name]['is_requested'] === 1 ) &&
           ! in_array( $this->name, $user['groups'] ) ) {
        $user['groups'][] = $this->name;
        $userCollection->save( $user );
      }
      
      // And finaly, also the properties of this object
      $this->users[ BeeHub::USERS_PATH . $user_name ] = array(
        'is_admin' => !!$document['members'][$user_name]['is_admin'],
        'is_invited' => !!$document['members'][$user_name]['is_invited'],
        'is_requested' => !!$document['members'][$user_name]['is_requested']
      );
      if ( ( $document['members'][$user_name]['is_invited'] === 1 ) &&
           ( $document['members'][$user_name]['is_requested'] === 1 ) &&
           ! in_array( $user_name, $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] ) ) {
        $this->stored_props[DAV::PROP_GROUP_MEMBER_SET][] = BeeHub::USERS_PATH . rawurlencode( $user_name );
      }
    }
    
    // And save the group document
    $collection->save( $document );
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
    $collection = BeeHub::getNoSQL()->groups;
    $userCollection = BeeHub::getNoSQL()->users;
    $document = $collection->findOne( array( 'name' => $this->name ) );

    // Then delete all the members
    foreach ($members as $user_name) {
      unset( $document['members'][$user_name] );
      
      // Also change the user document
      $user = $userCollection->findOne( array( 'name' => $user_name ) );
      if ( ! is_null( $user ) ) {
        $key = array_search( $this->name, $user['groups'] );
        if ( $key !== false ) {
          unset( $user['groups'][$key] );
          $userCollection->save( $user );
        }
      }
      
      // And unset the necessary properties of this object
      unset( $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] );
      $key = array_search( BeeHub::USERS_PATH . rawurlencode( $user_name ), $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] );
      if ( $key !== false ) {
        unset( $this->stored_props[DAV::PROP_GROUP_MEMBER_SET][$key] );
      }
    }
    
    // And finally save the group document
    $collection->save( $document );
  }


  private function check_admin_remove($members) {
    if (count($members) === 0) {
      return;
    }
    
    // Check if this request is not removing all administrators from this group
    foreach ( $this->users as $user_name => $membership ) {
      if ( ( $membership['is_admin'] ) &&
           ! in_array( self::get_user_name($user_name), $members ) ) {
        return;
      }
    }
    
    throw new DAV_Status(DAV::HTTP_CONFLICT, 'You are not allowed to remove all the group administrators from a group. Leave at least one group administrator in the group or appoint a new group administrator!');
  }


  private static function get_user_name($user_name) {
    return rawurldecode(basename($user_name));
  }


  protected function init_props() {
    if (is_null($this->stored_props)) {
      $this->stored_props = array();
      
      $collection = BeeHub::getNoSQL()->groups;
      $document = $collection->findOne( array( 'name' => $this->name ) );
      if ( is_null( $document ) ) {
        throw new DAV_Status( DAV::HTTP_NOT_FOUND );
      }
      
      $this->stored_props[DAV::PROP_DISPLAYNAME] = @$document['displayname'];
      $this->stored_props[BeeHub::PROP_DESCRIPTION] = DAV::xmlescape(@$document['description']);

      $this->users = array();
      $members = array();
      foreach ( $document['members'] as $username => $membership ) {
        $this->users[ BeeHub::USERS_PATH . rawurlencode( $username ) ] = array(
          'is_invited' => !!$membership['is_invited'],
          'is_requested' => !!$membership['is_requested'],
          'is_admin' => !!$membership['is_admin']
        );
        if ( !!$membership['is_invited'] && !!$membership['is_requested'] ) {
          $members[] = BeeHub::USERS_PATH . rawurlencode( $username );
        }
      }
      $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] = $members;
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
    
    $collection = BeeHub::getNoSQL()->groups;
    $update_document = array(
        'displayname' => @$this->stored_props[DAV::PROP_DISPLAYNAME],
        'description' => DAV::xmlunescape( $this->stored_props[BeeHub::PROP_DESCRIPTION] )
    );
    $collection->update( array( 'name' => $this->name ), array( '$set' => $update_document ) );
            
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
    $this->init_props();
    if ( is_null($user) ) {
      if ( BeeHub_ACL_Provider::inst()->wheel() ) {
        return true;
      }
      $user = BeeHub_Auth::inst()->current_user();
    }elseif ( ! ( $user instanceof BeeHub_User ) ) {
      $user = BeeHub::user( $user );
    }
    return ( $tmp = @$this->users[$user->path] ) &&
           $tmp['is_admin'];
  }


  public function is_member() {
    $this->init_props();
    if ( is_null($user) ) {
      $user = BeeHub_Auth::inst()->current_user();
    }elseif ( ! ( $user instanceof BeeHub_User ) ) {
      $user = BeeHub::user( $user );
    }
    return ( $tmp = @$this->users[$user->path] ) &&
           $tmp['is_invited'] && $tmp['is_requested'];
  }


  public function is_invited() {
    $this->init_props();
    if ( is_null($user) ) {
      $user = BeeHub_Auth::inst()->current_user();
    }elseif ( ! ( $user instanceof BeeHub_User ) ) {
      $user = BeeHub::user( $user );
    }
    return ( $tmp = @$this->users[$user->path] ) &&
           $tmp['is_invited'] && !$tmp['is_requested'];
  }


  public function is_requested() {
    $this->init_props();
    if ( is_null($user) ) {
      $user = BeeHub_Auth::inst()->current_user();
    }elseif ( ! ( $user instanceof BeeHub_User ) ) {
      $user = BeeHub::user( $user );
    }
    return ( $tmp = @$this->users[$user->path] ) &&
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
