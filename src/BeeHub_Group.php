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
    $this->assert( BeeHub::PRIV_READ_CONTENT );
    $members = array();
    if ( $this->is_member() ) {
      $this->init_props();
      $usersCollection = BeeHub::getNoSQL()->users;

      $names = array();
      foreach ( $this->users as $userPath => $membership ) {
        $names[] = basename( $userPath );
      }

      $resultset = $usersCollection->find( array( 'name' => array( '$in' => $names ) ), array( 'name' => true, 'displayname' => true ) );
      $members = array();
      foreach ( $resultset as $result ) {
        $members[ $result['name'] ] = Array(
          'user_name'    => $result['name'],
          'displayname'  => $result['displayname'],
          'is_admin'     => $this->users[ BeeHub::USERS_PATH . rawurldecode( $result['name'] ) ][ 'is_admin' ],
          'is_invited'   => $this->users[ BeeHub::USERS_PATH . rawurldecode( $result['name'] ) ][ 'is_invited' ],
          'is_requested' => $this->users[ BeeHub::USERS_PATH . rawurldecode( $result['name'] ) ][ 'is_requested' ]
        );
      }
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
      $this->change_memberships( $current_user, self::DELETE_MEMBER );
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
      $this->change_memberships(array($current_user->name), self::USER_ACCEPT);
      if (!is_null($message)) {
        BeeHub::email($recipients,
                      'BeeHub notification: membership request for group ' . $this->prop(DAV::PROP_DISPLAYNAME),
                      $message);
      }
    }

    // Run administrator actions: add members, admins and requests
    foreach ($admin_functions as $key) {
      if (isset($_POST[$key])) {
        if (!is_array($_POST[$key])) {
          throw new DAV_Status(DAV::HTTP_BAD_REQUEST);
        }
        $members = array_map( array( 'BeeHub_Group', 'get_user_name' ), $_POST[$key] );
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
            $this->change_memberships($members, self::ADMIN_ACCEPT);
            break;
          case 'add_admins':
            $this->change_memberships($members, self::SET_ADMIN);
            break;
          case 'delete_admins':
            $this->change_memberships($members, self::UNSET_ADMIN);
            break;
          case 'delete_members':
            $this->change_memberships( $members, self::DELETE_MEMBER );
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
   * Sets a membership to be accepted by the user
   *
   * If the membership is still unknown, it will be added. If the membership is
   * already accepted by an admin, it will become a full membership. If the user
   * is an admin, nothing will change.
   *
   * @param   string  $user_name     The user name to add
   * @param   array   $userDocument  The user's Mongo document
   * @return  void
   */
  private function user_accept_membership( &$user_name, &$userDocument ) {
    // Check what we need to do and accept the membership if needed
    if ( $this->is_invited( $user_name ) ) {
      // This user is already accepted by an admin, so now it is a full membership

      // Change the user document
      if ( ! in_array( $this->name, $userDocument['groups'] ) ) {
        $userDocument['groups'][] = $this->name;
        BeeHub::getNoSQL()->users->save( $userDocument );
      }
    }

    // And finaly, also the properties of this object
    if ( !isset( $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] ) || !is_array( $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] ) ) {
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] = array(
        'is_admin' => false,
        'is_invited' => false,
        'is_requested' => true
      );
    }else{
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ]['is_requested'] = true;
    }
  }


  /**
   * Sets a membership to be accepted by an admin
   *
   * If the membership is still unknown, it will be added. If the membership is
   * already accepted by the user, it will become a full membership. If the user
   * is an admin, nothing will change.
   *
   * @param   string  $user_name     The user name to add
   * @param   array   $userDocument  The user's Mongo document
   * @return  void
   */
  private function admin_accept_membership( &$user_name, &$userDocument ) {
    // Check what we need to do and accept the membership if needed
    if ( $this->is_requested( $user_name ) ) {
      // This user is already accepted by the user, so now it is a full membership
      // Change the user document
      if ( ! in_array( $this->name, $userDocument['groups'] ) ) {
        $userDocument['groups'][] = $this->name;
        BeeHub::getNoSQL()->users->save( $userDocument );
      }
    }

    // And finaly, also the properties of this object
    if ( !isset( $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] ) || !is_array( $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] ) ) {
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] = array(
        'is_admin' => false,
        'is_invited' => true,
        'is_requested' => false
      );
    }else{
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ]['is_invited'] = true;
    }
  }


  /**
   * Sets a member to be admin
   *
   * @param   string  $user_name                   The user name to add
   * @return  void
   */
  private function set_admin( &$user_name ) {
    // Update the properties of this object
    if ( !isset( $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] ) || !is_array( $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] ) ) {
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] = array(
        'is_admin' => true,
        'is_invited' => true,
        'is_requested' => false
      );
    }else{
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ]['is_admin'] = true;
    }
  }


  /**
   * Sets an admin to become a relugar member
   *
   * @param   string  $user_name  The user name to add
   * @return  void
   */
  private function unset_admin( &$user_name ) {
    // Update the properties of this object
    if ( !isset( $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] ) || !is_array( $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] ) ) {
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] = array(
        'is_admin' => false,
        'is_invited' => false,
        'is_requested' => false
      );
    }else{
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ]['is_admin'] = false;
    }
  }


  /**
   * Sets an admin to become a relugar member
   *
   * @param   string  $user_name     The user name to add
   * @param   array   $userDocument  The user's Mongo document
   * @return  void
   */
  private function delete_member( &$user_name, &$userDocument ) {
    // Change the user document
    $user_key = array_search( $this->name, $userDocument['groups'] );
    if ( $user_key !== false ) {
      if ( count($userDocument['groups'] ) > 1 ) {
        unset( $userDocument['groups'][$user_key] );
        $userDocument['groups'] = array_values( $userDocument['groups'] );
      }else{
        unset( $userDocument['groups'] );
      }
      BeeHub::getNoSQL()->users->save( $userDocument );
    }

    // And unset the necessary properties of this object
    if ( isset( $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] ) ) {
      unset( $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] );
    }
  }


  const USER_ACCEPT = 1;
  const ADMIN_ACCEPT = 2;
  const SET_ADMIN = 3;
  const UNSET_ADMIN = 4;
  const DELETE_MEMBER = 5;


  /**
   * Adds member requests or sets them to be an invited member or an administrator
   *
   * @param   mixed    $users  A user, username or an array if users or usernames
   * @param   flag     $type   What to do with this membership, use one of the class constants DELETE_MEMBER, USER_ACCEPT, ADMIN_ACCEPT, SET_ADMIN or UNSET_ADMIN
   * @return  void
   */
  public function change_memberships($users, $type){
    if ( !is_array( $users ) ) {
      $users = array( $users );
    }
    if ( count($users) === 0 ) {
      return;
    }

    // Get all users as User objects
    $users = array_map(array( 'BeeHub_Group', 'get_user_name' ), $users );

    // Load the Mongo document and reinitialize the memberships (just to be sure)
    $collection = BeeHub::getNoSQL()->groups;
    $userCollection = BeeHub::getNoSQL()->users;
    $document = $collection->findOne( array( 'name' => $this->name ) );
    $this->init_memberships( $document );

    // If we remove or demote an admin, check whether this is allowed
    if ( ( $type === self::UNSET_ADMIN ) ||
         ( $type === self::DELETE_MEMBER ) ) {
      $this->check_admin_remove( $users );
    }

    foreach ($users as $user_name) {
      // Check if the user exists
      $userDocument = $userCollection->findOne( array( 'name' => $user_name ) );
      if ( is_null( $userDocument ) ) {
        throw new DAV_Status(DAV::HTTP_CONFLICT, "Not all users exist: " . $user_name);
      }
      if ( !isset( $userDocument['groups'] ) || ! is_array( $userDocument['groups'] ) ) {
        $userDocument['groups'] = array();
      }

      // Check what we need to do and accept the membership if needed
      switch ( $type ) {
        case self::USER_ACCEPT:
          $this->user_accept_membership( $user_name, $userDocument );
          break;
        case self::ADMIN_ACCEPT:
          $this->admin_accept_membership( $user_name, $userDocument );
          break;
        case self::SET_ADMIN:
          $this->set_admin( $user_name );
          break;
        case self::UNSET_ADMIN:
          $this->unset_admin( $user_name );
          break;
        case self::DELETE_MEMBER:
          $this->delete_member( $user_name, $userDocument );
        break;
      }
    }

    // Update the DAV: group-member-set property and prepare the document
    // Prepare the document
    $membershipKeys = array( 'admin_accepted_admins', 'admins', 'members', 'admin_accepted_memberships', 'user_accepted_memberships' );
    foreach( $membershipKeys as $key ) {
      $document[$key] = array();
    }
    $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] = array();

    foreach( $this->users as $userPath => $membershipDetails ) {
      $savableUsername = rawurldecode( basename( $userPath ) );
      if ( $membershipDetails['is_admin'] && ! $membershipDetails['is_requested'] ) {
        $document['admin_accepted_admins'][] = $savableUsername;
      } elseif ( $membershipDetails['is_admin'] ) {
        $document['admins'][] = $savableUsername;
        $this->stored_props[DAV::PROP_GROUP_MEMBER_SET][] = $userPath;
      } elseif ( $membershipDetails['is_requested'] && $membershipDetails['is_invited'] ) {
        $document['members'][] = $savableUsername;
        $this->stored_props[DAV::PROP_GROUP_MEMBER_SET][] = $userPath;
      } elseif ( $membershipDetails['is_invited'] ) {
        $document['admin_accepted_memberships'][] = $savableUsername;
      } elseif ( $membershipDetails['is_requested'] ) {
        $document['user_accepted_memberships'][] = $savableUsername;
      }
    }

    // Clean up all empty arrays
    foreach( $membershipKeys as $key ) {
      if ( count( $document[$key] ) === 0 ) {
        unset( $document[$key] );
      }
    }

    // And do the actual saving
    $collection->save( $document );

    DAV::$REGISTRY->forget( BeeHub::USERS_PATH . urlencode( $user_name ) );
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


  private static function get_user_name($user) {
    if ( $user instanceof BeeHub_User ) {
      return $user->name;
    }else{
      return rawurldecode( basename( $user ) );
    }
  }


  protected function init_props() {
    // Check the cache
    if (is_null($this->stored_props)) {

      //Load the properties from the database
      $this->stored_props = array();

      $collection = BeeHub::getNoSQL()->groups;
      $document = $collection->findOne( array( 'name' => $this->name ) );
      if ( is_null( $document ) ) {
        throw new DAV_Status( DAV::HTTP_NOT_FOUND );
      }

      $this->stored_props[DAV::PROP_DISPLAYNAME] = @$document['displayname'];
      $this->stored_props[BeeHub::PROP_DESCRIPTION] = @$document['description'];

      $this->init_memberships( $document );
    }
  }


  /**
   * Set all internal variables describing memberships correctly
   *
   * @param   array  $document  The Mongo document of this group
   * @return  void
   */
  private function init_memberships( $document ) {
    // Memberships are a bit more difficult, first get all the regular members
    $this->users = array();
    $members = array();
    if ( !isset( $document['members'] ) ) {
      $document['members'] = array();
    }
    foreach ( $document['members'] as $username ) {
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $username ) ] = array(
        'is_invited' => true,
        'is_requested' => true,
        'is_admin' => false
      );
      $members[] = BeeHub::USERS_PATH . rawurlencode( $username );
    }

    // Then all the full administrators
    if ( !isset( $document['admins'] ) ) {
      $document['admins'] = array();
    }
    foreach ( $document['admins'] as $username ) {
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $username ) ] = array(
        'is_invited' => true,
        'is_requested' => true,
        'is_admin' => true
      );
      $members[] = BeeHub::USERS_PATH . rawurlencode( $username );
    }

    // Set the DAV: group-member-set property
    $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] = $members;

    // Find all memberships which are accepted by the admin, but not by the user. I.e. The user is invited
    if ( !isset( $document['admin_accepted_memberships'] ) ) {
      $document['admin_accepted_memberships'] = array();
    }
    foreach ( $document['admin_accepted_memberships'] as $username ) {
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $username ) ] = array(
        'is_invited' => true,
        'is_requested' => false,
        'is_admin' => false
      );
    }

    // A special form of this: the user is invited and promoted to admin, but did not yet accept his/her membership
    if ( !isset( $document['admin_accepted_admins'] ) ) {
      $document['admin_accepted_admins'] = array();
    }
    foreach ( $document['admin_accepted_admins'] as $username ) {
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $username ) ] = array(
        'is_invited' => true,
        'is_requested' => false,
        'is_admin' => true
      );
    }

    // Find all memberships which are accepted by the user, but not by the admin. I.e. The user requested membership
    if ( !isset( $document['user_accepted_memberships'] ) ) {
      $document['user_accepted_memberships'] = array();
    }
    foreach ( $document['user_accepted_memberships'] as $username ) {
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $username ) ] = array(
        'is_invited' => false,
        'is_requested' => true,
        'is_admin' => false
      );
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
        'description' => @$this->stored_props[BeeHub::PROP_DESCRIPTION]
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
  public function is_admin( $user = null ) {
    $this->init_props();
    if ( is_null($user) ) {
      $user = BeeHub::getAuth()->current_user();
    }elseif ( ! ( $user instanceof BeeHub_User ) ) {
      $user = BeeHub::user( $user );
    }
    return ( $tmp = @$this->users[$user->path] ) &&
           $tmp['is_admin'];
  }


  public function is_member( $user = null ) {
    $this->init_props();
    if ( is_null($user) ) {
      $user = BeeHub::getAuth()->current_user();
    }elseif ( ! ( $user instanceof BeeHub_User ) ) {
      $user = BeeHub::user( $user );
    }
    return ( $tmp = @$this->users[$user->path] ) &&
           $tmp['is_invited'] && $tmp['is_requested'];
  }


  public function is_invited( $user = null ) {
    $this->init_props();
    if ( is_null($user) ) {
      $user = BeeHub::getAuth()->current_user();
    }elseif ( ! ( $user instanceof BeeHub_User ) ) {
      $user = BeeHub::user( $user );
    }
    return ( $tmp = @$this->users[$user->path] ) &&
           $tmp['is_invited'] && !$tmp['is_requested'];
  }


  public function is_requested( $user = null ) {
    $this->init_props();
    if ( is_null($user) ) {
      $user = BeeHub::getAuth()->current_user();
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
