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
          'is_admin'     => $this->users[ BeeHub::USERS_PATH . rawurldecode( $result['name'] ) ][ 'is_admin' ],
          'is_invited'   => $this->users[ BeeHub::USERS_PATH . rawurldecode( $result['name'] ) ][ 'is_invited' ],
          'is_requested' => $this->users[ BeeHub::USERS_PATH . rawurldecode( $result['name'] ) ][ 'is_requested' ]
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
   * @param   string  $user_name                   The user name to add
   * @param   array   $admins                      All admins (username as key)
   * @param   array   $members                     All full memberships (username as key)
   * @param   array   $admin_accepted_memberships  All memberships accepted by an admin (username as key)
   * @param   array   $user_accepted_memberships   All memberships accepted by the user (username as key)
   * @return  void
   */
  private function user_accept_membership( &$user_name, &$user, &$admins, &$members, &$admin_accepted_memberships, &$user_accepted_memberships ) {
    // Check what we need to do and accept the membership if needed
    if ( array_key_exists( $user_name, $admins ) ) {
      return; // Don't do anything if it is an admin
    } elseif ( array_key_exists( $user_name, $admin_accepted_memberships ) && !array_key_exists( $user_name, $members ) ) {

      // This user is already accepted by an admin, so now it is a full membership
      unset( $admin_accepted_memberships[$user_name] );
      $members[$user_name] = 1;

      // Also change the user document
      if ( ! is_array( $user['groups'] ) ) {
        $user['groups'] = array();
      }
      if ( ! in_array( $this->name, $user['groups'] ) ) {
        $user['groups'][] = $this->name;
        BeeHub::getNoSQL()->users->save( $user );
      }

      // And finaly, also the properties of this object
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] = array(
        'is_admin' => false,
        'is_invited' => true,
        'is_requested' => true
      );
      if ( ! in_array( $user_name, $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] ) ) {
        $this->stored_props[DAV::PROP_GROUP_MEMBER_SET][] = BeeHub::USERS_PATH . rawurlencode( $user_name );
      }

    } elseif ( !array_key_exists( $user_name, $user_accepted_memberships ) ) {

      // This is a new membership
      $user_accepted_memberships[$user_name] = 1;

      // And finaly, also the properties of this object
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] = array(
        'is_admin' => false,
        'is_invited' => false,
        'is_requested' => true
      );

    }
  }
  
  
  /**
   * Sets a membership to be accepted by an admin
   * 
   * If the membership is still unknown, it will be added. If the membership is
   * already accepted by the user, it will become a full membership. If the user
   * is an admin, nothing will change.
   * 
   * @param   string  $user_name                   The user name to add
   * @param   array   $admins                      All admins (username as key)
   * @param   array   $members                     All full memberships (username as key)
   * @param   array   $admin_accepted_memberships  All memberships accepted by an admin (username as key)
   * @param   array   $user_accepted_memberships   All memberships accepted by the user (username as key)
   * @return  void
   */
  private function admin_accept_membership( &$user_name, &$user, &$admins, &$members, &$admin_accepted_memberships, &$user_accepted_memberships ) {
    // Check what we need to do and accept the membership if needed
    if ( array_key_exists( $user_name, $admins ) ) {
      return; // Don't do anything if it is an admin
    } elseif ( array_key_exists( $user_name, $user_accepted_memberships ) && !array_key_exists( $user_name, $members ) ) {

      // This user is already accepted by the user, so now it is a full membership
      unset( $user_accepted_memberships[$user_name] );
      $members[$user_name] = 1;

      // Also change the user document
      if ( ! is_array( $user['groups'] ) ) {
        $user['groups'] = array();
      }
      if ( ! in_array( $this->name, $user['groups'] ) ) {
        $user['groups'][] = $this->name;
        BeeHub::getNoSQL()->users->save( $user );
      }

      // And finaly, also the properties of this object
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] = array(
        'is_admin' => false,
        'is_invited' => true,
        'is_requested' => true
      );
      if ( ! in_array( $user_name, $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] ) ) {
        $this->stored_props[DAV::PROP_GROUP_MEMBER_SET][] = BeeHub::USERS_PATH . rawurlencode( $user_name );
      }

    } elseif ( !array_key_exists( $user_name, $admin_accepted_memberships ) ) {

      // This is a new membership
      $admin_accepted_memberships[$user_name] = 1;

      // And finaly, also the properties of this object
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] = array(
        'is_admin' => false,
        'is_invited' => false,
        'is_requested' => true
      );

    }
  }
  
  
  /**
   * Sets a member to be admin
   * 
   * @param   string  $user_name                   The user name to add
   * @param   array   $admins                      All admins (username as key)
   * @param   array   $members                     All full memberships (username as key)
   * @return  void
   */
  private function set_admin( &$user_name, &$admins, &$members ) {
    // Check what we need to do and accept the membership if needed
    if ( array_key_exists( $user_name, $admins ) ) {
      return; // Don't do anything if it is an admin
    } elseif ( array_key_exists( $user_name, $members ) ) {

      // Update the membership to an admin membership
      unset( $members[$user_name] );
      $admins[$user_name] = 1;

      // And finaly, also the properties of this object
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] = array(
        'is_admin' => true,
        'is_invited' => true,
        'is_requested' => true
      );

    } else {
      throw new DAV_Status( DAV::HTTP_CONFLICT, 'Not all users are member of this group. You can\'t become an administrator of you don\'t have a membership.');
    }
  }
  
  
  /**
   * Sets an admin to become a relugar member
   * 
   * @param   string  $user_name                   The user name to add
   * @param   array   $admins                      All admins (username as key)
   * @param   array   $members                     All full memberships (username as key)
   * @return  void
   */
  private function unset_admin( &$user_name, &$admins, &$members ) {
    // Check what we need to do and accept the membership if needed
    if ( array_key_exists( $user_name, $admins ) ) {

      // Demote the membership to a regular membership
      unset( $admins[$user_name] );
      $members[$user_name] = 1;

      // And finaly, also the properties of this object
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] = array(
        'is_admin' => false,
        'is_invited' => true,
        'is_requested' => true
      );

    }
  }
  
  
  /**
   * Sets an admin to become a relugar member
   * 
   * @param   string  $user_name                   The user name to add
   * @param   array   $admins                      All admins (username as key)
   * @param   array   $members                     All full memberships (username as key)
   * @param   array   $admin_accepted_memberships  All memberships accepted by an admin (username as key)
   * @param   array   $user_accepted_memberships   All memberships accepted by the user (username as key)
   * @return  void
   */
  private function delete_member( &$user_name, &$user, &$admins, &$members, &$admin_accepted_memberships, &$user_accepted_memberships ) {
    unset( $admins[$user_name], $members[$user_name], $admin_accepted_memberships[$user_name], $user_accepted_memberships[$user_name] );

    // Also change the user document
    $user_key = array_search( $this->name, $user['groups'] );
    if ( $user_key !== false ) {
      if ( count($user['groups'] ) > 1 ) {
        unset( $user['groups'][$user_key] );
        $user['groups'] = array_values( $user['groups'] );
      }else{
        unset( $user['groups'] );
      }
      BeeHub::getNoSQL()->users->save( $user );
    }

    // And unset the necessary properties of this object
    unset( $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] );
    $key = array_search( BeeHub::USERS_PATH . rawurlencode( $user_name ), $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] );
    if ( $key !== false ) {
      unset( $this->stored_props[DAV::PROP_GROUP_MEMBER_SET][$key] );
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
   * @param   flag     $type   What to do with this membership, use one of the class constants USER_ACCEPT, ADMIN_ACCEPT, SET_ADMIN or UNSET_ADMIN
   * @return  void
   */
  public function change_memberships($users, $type){
    if ( !is_array( $users ) ) {
      $users = array( $users );
    }
    if ( count($users) === 0 ) {
      return;
    }
    $users = array_map(array( 'BeeHub_Group', 'get_user_name' ), $users );
    $collection = BeeHub::getNoSQL()->groups;
    $userCollection = BeeHub::getNoSQL()->users;
    $document = $collection->findOne( array( 'name' => $this->name ) );
    
    // We flip all the membership arrays so PHP will index them and speed up searches later on
    $admins = ( isset( $document['admins'] ) ? array_flip( $document['admins'] ) : array() );
    $members = ( isset( $document['members'] ) ? array_flip( $document['members'] ) : array() );
    $admin_accepted_memberships = ( isset( $document['admin_accepted_memberships'] ) ? array_flip( $document['admin_accepted_memberships'] ) : array() );
    $user_accepted_memberships = ( isset( $document['user_accepted_memberships'] ) ? array_flip( $document['user_accepted_memberships'] ) : array() );
      
    // If we remove or demote an admin, check whether this is allowed
    if ( ( $type === self::UNSET_ADMIN ) || 
         ( $type === self::DELETE_MEMBER ) ) {
      $this->check_admin_remove( $users );
    }
    
    foreach ($users as $user_name) {
      // Check if the user exists
      $user = $userCollection->findOne( array( 'name' => $user_name ) );
      if ( is_null( $user ) ) {
        throw new DAV_Status(DAV::HTTP_CONFLICT, "Not all users exist! " . $user_name);
      }
      
      // Check what we need to do and accept the membership if needed
      switch ( $type ) {
        case self::USER_ACCEPT:
          $this->user_accept_membership( $user_name, $user, $admins, $members, $admin_accepted_memberships, $user_accepted_memberships );
          break;
        case self::ADMIN_ACCEPT:
          $this->admin_accept_membership( $user_name, $user, $admins, $members, $admin_accepted_memberships, $user_accepted_memberships );
          break;
        case self::SET_ADMIN:
          $this->set_admin( $user_name, $admins, $members );
          break;
        case self::UNSET_ADMIN:
          $this->unset_admin( $user_name, $admins, $members );
          break;
        case self::DELETE_MEMBER:
          $this->delete_member( $user_name, $user, $admins, $members, $admin_accepted_memberships, $user_accepted_memberships );
        break;
      }
    }
    
    // And save the group document
    if ( count($admins) > 0 ) {
      $document['admins'] = array_keys( $admins );
    } elseif ( isset( $document['admins'] ) ) {
      unset( $document['admins'] );
    }
    if ( count($members) > 0 ) {
      $document['members'] = array_keys( $members );
    } elseif ( isset( $document['members'] ) ) {
      unset( $document['members'] );
    }
    if ( count($admin_accepted_memberships) > 0 ) {
      $document['admin_accepted_memberships'] = array_keys( $admin_accepted_memberships );
    } elseif ( isset( $document['admin_accepted_memberships'] ) ) {
      unset( $document['admin_accepted_memberships'] );
    }
    if ( count($user_accepted_memberships) > 0 ) {
      $document['user_accepted_memberships'] = array_keys( $user_accepted_memberships );
    } elseif ( isset( $document['user_accepted_memberships'] ) ) {
      unset( $document['user_accepted_memberships'] );
    }
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


  private static function get_user_name($user) {
    if ( $user instanceof BeeHub_User ) {
      return $user->name;
    }else{
      return rawurldecode( basename( $user ) );
    }
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
      $this->stored_props[BeeHub::PROP_DESCRIPTION] = @$document['description'];

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
      $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] = $members;
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


  public function is_member( $user = null ) {
    $this->init_props();
    if ( is_null($user) ) {
      $user = BeeHub_Auth::inst()->current_user();
    }elseif ( ! ( $user instanceof BeeHub_User ) ) {
      $user = BeeHub::user( $user );
    }
    return ( $tmp = @$this->users[$user->path] ) &&
           $tmp['is_invited'] && $tmp['is_requested'];
  }


  public function is_invited( $user = null ) {
    $this->init_props();
    if ( is_null($user) ) {
      $user = BeeHub_Auth::inst()->current_user();
    }elseif ( ! ( $user instanceof BeeHub_User ) ) {
      $user = BeeHub::user( $user );
    }
    return ( $tmp = @$this->users[$user->path] ) &&
           $tmp['is_invited'] && !$tmp['is_requested'];
  }


  public function is_requested( $user = null ) {
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
