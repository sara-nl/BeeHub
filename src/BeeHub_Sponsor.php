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
    if ( isset( $_GET['usage'] ) && $this->is_admin() ) {
      // If the usage is requested, this is gathered by a seperate method
      return $this->method_GET_usage();
    }
    
    // Else prepare the sponsor administration page
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
          'user_name' => $result['name'],
          'displayname' => $result['displayname'],
          'is_admin' => $this->users[ BeeHub::USERS_PATH . $result['name'] ][ 'is_admin' ],
          'is_accepted' => $this->users[ BeeHub::USERS_PATH . $result['name'] ][ 'is_accepted' ]
        );
      }
    }
    $this->include_view( null, array( 'members' => $members ) );
  }
  
  
  /**
   * Gathers the usage statistics for this sponsors and return it to the client
   * in json format
   * 
   * @return  string  Usage statistics JSON encoded
   */
  private function method_GET_usage() {
    $collection = BeeHub::getNoSQL()->files;
    $stats = $collection->group(
      array( 'props.DAV: owner' => 1 ),
      array( 'usage' => 0 ),
      'function( file, stats ) {
         if ( file.props !== undefined && file.props["DAV: getcontentlength"] !== undefined )
           stats.usage += file.props["DAV: getcontentlength"];
      }',
      array(
        'condition' => array(
          "props.http://beehub%2Enl/ sponsor" => $this->name
        )
      )
    );
    
    if ( ! $stats['ok'] ) {
      throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, 'Unable to retrieve usage statistics due to an unknown error' );
    }

    return json_encode(
      array(
        array(
          "sponsor" => $this->path,
          "time" => date( 'c' ),
          "usage" => $stats['retval'],
        )
      )
    );
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
      if ( ! $this->is_member() ) { // This user is not invited for this group, so sent the administrators an e-mail with this request
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
      $this->change_memberships( array( $current_user->name ), self::USER_ACCEPT );
      if (!is_null($message)) {
        BeeHub::email($recipients,
                      'BeeHub notification: membership request for sponsor ' . $this->prop(DAV::PROP_DISPLAYNAME),
                      $message);
      }
    }

    // Run administrator actions: add members, admins and requests
    foreach ($admin_functions as $key) {
      if (isset($_POST[$key])) {
        if (!is_array($_POST[$key])) {
          throw new DAV_Status(DAV::HTTP_BAD_REQUEST);
        }
        $members = array_map( array( 'BeeHub_Sponsor', 'get_user_name' ), $_POST[$key] );
        switch ($key) {
          case 'add_members':
            foreach ($members as $member) {
              $user = BeeHub::user($member);
              if ( ! $this->is_member( $user ) ) { // The user was not a member of this sponsor yet, so notify him/her
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
            $this->change_memberships( $members, self::ADMIN_ACCEPT );
            break;
          case 'add_admins':
            $this->change_memberships( $members, self::SET_ADMIN );
            break;
          case 'delete_admins':
            $this->check_admin_remove($members);
            $this->change_memberships( $members, self::UNSET_ADMIN );
            break;
          case 'delete_members':
            $this->change_memberships( $members, self::DELETE_MEMBER );
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
   * Sets a membership to be accepted by the user
   * 
   * If the membership is still unknown, it will be added. Else it will be
   * ignored.
   * 
   * @param   string  $user_name                   The user name to add
   * @param   array   $admins                      All admins (username as key)
   * @param   array   $members                     All full memberships (username as key)
   * @param   array   $user_accepted_memberships   All memberships accepted by the user (username as key)
   * @return  void
   */
  private function user_accept_membership( &$user_name, &$admins, &$members, &$user_accepted_memberships ) {
    // Check what we need to do and accept the membership if needed
    if ( array_key_exists( $user_name, $admins ) || array_key_exists( $user_name, $members ) ) {
      return; // Don't do anything if it is an admin or an existing user
    } elseif ( !array_key_exists( $user_name, $user_accepted_memberships ) ) {

      // This is a new membership
      $user_accepted_memberships[$user_name] = 1;

      // And finaly, also the properties of this object
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] = array(
        'is_admin' => false,
        'is_accepted' => false
      );
    }
  }
  
  
  /**
   * Sets a membership to be accepted by an admin
   * 
   * If the membership is still unknown, it will be added as a full membership.
   * If the user is an admin, nothing will change.
   * 
   * @param   string  $user_name                   The user name to add
   * @param   array   $admins                      All admins (username as key)
   * @param   array   $members                     All full memberships (username as key)
   * @param   array   $user_accepted_memberships   All memberships accepted by the user (username as key)
   * @return  void
   */
  private function admin_accept_membership( &$user_name, &$user, &$admins, &$members, &$user_accepted_memberships ) {
    // Check what we need to do and accept the membership if needed
    if ( array_key_exists( $user_name, $admins ) ) {
      return; // Don't do anything if it is an admin
    } elseif ( !array_key_exists( $user_name, $members ) ) {

      if ( array_key_exists( $user_name, $user_accepted_memberships ) ) {
        unset( $user_accepted_memberships[$user_name] );
      }
      $members[$user_name] = 1;

      // Also change the user document
      if ( ! in_array( $this->name, $user['sponsors'] ) ) {
        $user['sponsors'][] = $this->name;
        BeeHub::getNoSQL()->users->save( $user );
      }

      // And finaly, also the properties of this object
      $this->users[ BeeHub::USERS_PATH . rawurlencode( $user_name ) ] = array(
        'is_admin' => false,
        'is_accepted' => true
      );
      if ( ! in_array( $user_name, $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] ) ) {
        $this->stored_props[DAV::PROP_GROUP_MEMBER_SET][] = BeeHub::USERS_PATH . rawurlencode( $user_name );
      }

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
        'is_accepted' => true
      );
    } else {
      throw new DAV_Status( DAV::HTTP_CONFLICT, 'Not all users are member of this group. You can\'t become an administrator of a group you don\'t have a membership.');
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
        'is_accepted' => true
      );
    }
  }
  
  
  /**
   * Sets an admin to become a relugar member
   * 
   * @param   string  $user_name                   The user name to add
   * @param   array   $admins                      All admins (username as key)
   * @param   array   $members                     All full memberships (username as key)
   * @param   array   $user_accepted_memberships   All memberships accepted by the user (username as key)
   * @return  void
   */
  private function delete_member( &$user_name, &$user, &$admins, &$members, &$user_accepted_memberships ) {
    unset( $admins[$user_name], $members[$user_name], $user_accepted_memberships[$user_name] );

    // Also change the user document
    $user_key = array_search( $this->name, $user['sponsors'] );
    if ( $user_key !== false ) {
      if ( count($user['sponsors'] ) > 1 ) {
        unset( $user['sponsors'][$user_key] );
        $user['sponsors'] = array_values( $user['sponsors'] );
      }else{
        unset( $user['sponsors'] );
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
   * @param   flag     $type   What to do with this membership, use one of the class constants DELETE_MEMBER, ADMIN_ACCEPT, USER_ACCEPT, SET_ADMIN or UNSET_ADMIN
   * @return  void
   */
  public function change_memberships($users, $type){
    if ( !is_array( $users ) ) {
      $users = array( $users );
    }
    if ( count($users) === 0 ) {
      return;
    }
    $users = array_map(array( 'BeeHub_Sponsor', 'get_user_name' ), $users );
    $collection = BeeHub::getNoSQL()->sponsors;
    $userCollection = BeeHub::getNoSQL()->users;
    $document = $collection->findOne( array( 'name' => $this->name ) );
    
    // We flip all the membership arrays so PHP will index them and speed up searches later on
    $admins = ( isset( $document['admins'] ) ? array_flip( $document['admins'] ) : array() );
    $members = ( isset( $document['members'] ) ? array_flip( $document['members'] ) : array() );
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
        throw new DAV_Status(DAV::HTTP_CONFLICT, "Not all users exist: " . $user_name);
      }
      if ( !isset( $user['sponsors'] ) || ! is_array( $user['sponsors'] ) ) {
        $user['sponsors'] = array();
      }
      
      // Check what we need to do and accept the membership if needed
      switch ( $type ) {
        case self::USER_ACCEPT:
          $this->user_accept_membership( $user_name, $admins, $members, $user_accepted_memberships );
          break;
        case self::ADMIN_ACCEPT:
          $this->admin_accept_membership($user_name, $user, $admins, $members, $user_accepted_memberships);
          break;
        case self::SET_ADMIN:
          $this->set_admin( $user_name, $admins, $members );
          break;
        case self::UNSET_ADMIN:
          $this->unset_admin( $user_name, $admins, $members );
          break;
        case self::DELETE_MEMBER:
          $this->delete_member( $user_name, $user, $admins, $members, $user_accepted_memberships );
        break;
      }

      DAV::$REGISTRY->forget( BeeHub::USERS_PATH . $user_name );
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
    
    throw new DAV_Status(DAV::HTTP_CONFLICT, 'You are not allowed to remove all the sponsor administrators from a group. Leave at least one sponsor administrator in the group or appoint a new sponsor administrator!');
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
      
      $collection = BeeHub::getNoSQL()->sponsors;
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
          'is_accepted' => true,
          'is_admin' => false
        );
        $members[] = BeeHub::USERS_PATH . rawurlencode( $username );
      }
      if ( !isset( $document['admins'] ) ) {
        $document['admins'] = array();
      }
      foreach ( $document['admins'] as $username ) {
        $this->users[ BeeHub::USERS_PATH . rawurlencode( $username ) ] = array(
          'is_accepted' => true,
          'is_admin' => true
        );
        $members[] = BeeHub::USERS_PATH . rawurlencode( $username );
      }
      $this->stored_props[DAV::PROP_GROUP_MEMBER_SET] = $members;
      if ( !isset( $document['user_accepted_memberships'] ) ) {
        $document['user_accepted_memberships'] = array();
      }
      foreach ( $document['user_accepted_memberships'] as $username ) {
        $this->users[ BeeHub::USERS_PATH . rawurlencode( $username ) ] = array(
          'is_accepted' => false,
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
    
    $collection = BeeHub::getNoSQL()->sponsors;
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
   * Determines whether the currently logged in user is an administrator of this sponsor or not.
   *
   * @return  boolean  True if the currently logged in user is an administrator of this sponsor, false otherwise
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
           $tmp['is_accepted'];
  }


  public function is_requested( $user = null ) {
    $this->init_props();
    if ( is_null($user) ) {
      $user = BeeHub::getAuth()->current_user();
    }elseif ( ! ( $user instanceof BeeHub_User ) ) {
      $user = BeeHub::user( $user );
    }
    return ( $tmp = @$this->users[$user->path] ) &&
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


} // class BeeHub_Sponsor
