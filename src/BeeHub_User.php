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
 * BeeHub user
 *
 * There are a few properties defined which are stored in the database instead
 * of as file attribute. These properties are credentials and user contact info:
 * BeeHub::PROP_NAME
 * BeeHub::PROP_EMAIL
 * BeeHub::PROP_X509
 *
 * We won't allow user data to be sent (GET, PROPFIND) or manipulated (PROPPATCH) over regular HTTP, so we require HTTPS! But this is arranged, because only an authenticated user can perform this GET request and you can only be authenticated over HTTPS.
 *
 * @TODO Checken of de properties in de juiste gevallen afschermd worden
 * @TODO toevoegen user_prop_sponsor();
 * @package BeeHub
 */
class BeeHub_User extends BeeHub_Principal {
  /**
   * @var  string  The (encrypted) password
   */
  private $password = null;
  
  /**
   * @var  string  The unverified e-mail address (if one is set)
   */
  protected $unverified_address = null;


  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET() {
    if ( isset( $_GET['usage'] ) && $this->is_admin() ) {
      // If the usage is requested, this is gathered by a seperate method
      return $this->method_GET_usage();
    }
    
    // Else prepare the profile page
    $this->assert( BeeHub::PRIV_READ_CONTENT );
    $this->init_props();
    if ($this->is_admin()) {
      if ( is_null( $this->unverified_address ) && isset( $_GET['verification_code'] ) ) {
        unset($_GET['verification_code']);
      }
      $this->include_view( null, array( 'unverified_address' => $this->unverified_address ) );
    }else{
      //TODO: Show a (non-editable) profile page
      throw DAV::forbidden();
    }
  }
  
  
  /**
   * Gathers the usage statistics for this sponsors and return it to the client
   * in json format
   * 
   * @return  string  Usage statistics JSON encoded
   */
  private function method_GET_usage() {
    $db = BeeHub::getNoSQL();
    
    $stats = $db->command(
      array(
        'mapReduce' => 'files',
        'map' => 
//        '
//          function() {
//            if ( this.props === undefined || this.props[ "DAV: getcontentlength" ] === undefined || this.props[ "DAV: getcontentlength" ] < 1 ) {
//              return;
//            }
//            var path = "/" + this.path;
//            var parent = path.substr( 0, path.lastIndexOf( "/" ) );
//            emit( parent, this.props["DAV: getcontentlength"] );
//          }
//        ',
        '
          function() {
            if ( this.props === undefined || this.props[ "DAV: getcontentlength" ] === undefined || this.props[ "DAV: getcontentlength" ] < 1 ) {
              return;
            }
            var pathParts = this.path.split("/");
            pathParts.pop();
            pathParts.unshift( "" );
            var path = "";
            for ( var key in pathParts ) {
              path +=  pathParts[key] + "/";
              emit( path, this.props["DAV: getcontentlength"] );
            }
          }
        ',
        'reduce' => '
          function( id, values ) {
            return Array.sum( values );
          }
        ',
        'out' => array( 'inline' => 1 ),
        'query' => array( 'props.DAV: owner' => $this->name )
      )
    );
    
    if ( ! $stats['ok'] ) {
      throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR, 'Unable to retrieve usage statistics due to an unknown error' );
    }
    
    return json_encode(
      array(
        array(
          'user' => $this->path,
          'time' => date( 'c' ),
          'usage' => $stats['results']
        )
      )
    );
  }


  public function method_POST( &$headers ) {
    $this->init_props();

    // For all POST requests, you need to send a POST field 'password' with the current password
    if (!isset($_POST['password']) || !$this->check_password($_POST['password'])) {
      throw DAV::forbidden();
    }

    if (isset($_POST['verification_code'])) { // Now verify the e-mail address
      if (!$this->verify_email_address($_POST['verification_code'])){
        throw DAV::forbidden();
      }
      DAV::redirect(DAV::HTTP_SEE_OTHER, $this->path);
      return;
    }elseif (isset($_POST['new_password'])) {
      $this->set_password($_POST['new_password']);
      DAV::redirect(DAV::HTTP_SEE_OTHER, $this->path);
      return;
    }
    throw new DAV_Status(DAV::HTTP_BAD_REQUEST);
  }


  protected function init_props() {
    if (is_null($this->stored_props)) {
      $this->stored_props = array();
      
      $collection = BeeHub::getNoSQL()->users;
      $result = $collection->findOne( array( 'name' => $this->name ) );
      if ( is_null( $result ) ) {
        throw new DAV_Status( DAV::HTTP_NOT_FOUND );
      }

      $this->stored_props[DAV::PROP_DISPLAYNAME] = @$result['displayname'];
      if ( isset( $result['email'] ) ) {
        $this->stored_props[BeeHub::PROP_EMAIL]  = $result['email'];
      }
      if ( isset( $result['surfconext_id'] ) ) {
        $this->stored_props[BeeHub::PROP_SURFCONEXT] = $result['surfconext_id'];
      }
      if ( isset( $result['surfconext_description'] ) ) {
        $this->stored_props[BeeHub::PROP_SURFCONEXT_DESCRIPTION] = $result['surfconext_description'];
      }
      if ( isset( $result['x509'] ) ) {
        $this->stored_props[BeeHub::PROP_X509] = $result['x509'];
      }
      if ( isset( $result['default_sponsor'] ) ) {
        $this->stored_props[BeeHub::PROP_SPONSOR] = BeeHub::SPONSORS_PATH . rawurlencode( $result['default_sponsor'] );
      }
      if ( isset( $result['password'] ) ) {
        $this->password = $result['password'];
      }else{
        $this->password = null;
      }
      
      if ( isset( $result['unverified_email'] ) && isset( $result['verification_expiration'] ) && ( $result['verification_expiration'] > time() ) ) {
        $this->unverified_address = $result['unverified_email'];
      }

      // Fetch all group memberships
      $groups = array();
      if ( isset( $result['groups'] ) ) {
        foreach ( $result['groups'] as $group ) {
          $groups[] = BeeHub::GROUPS_PATH . rawurlencode( $group );
        }
      }
      $this->stored_props[DAV::PROP_GROUP_MEMBERSHIP] = $groups;

      // Fetch all sponsor memberships
      $sponsors = array();
      if ( isset( $result['sponsors'] ) ) {
        foreach ( $result['sponsors'] as $sponsor ) {
          $sponsors[] = BeeHub::SPONSORS_PATH . rawurlencode( $sponsor );
        }
      }
      $this->stored_props[BeeHub::PROP_SPONSOR_MEMBERSHIP] = $sponsors;
      
      // If there is no default sponsor set, but the user has sponsors, we have to set one now. In other words, it is impossible to have sponsors but not a default sponsor
      // We also check if the user is still sponsored by his/her default sponsor
      if ( count( $this->stored_props[BeeHub::PROP_SPONSOR_MEMBERSHIP] ) > 0 ) {
        if ( ! isset( $this->stored_props[BeeHub::PROP_SPONSOR] ) || empty( $this->stored_props[BeeHub::PROP_SPONSOR] ) || ( ! in_array( $this->stored_props[BeeHub::PROP_SPONSOR], $this->stored_props[BeeHub::PROP_SPONSOR_MEMBERSHIP] ) ) ) {
          $this->user_set_sponsor( $this->stored_props[BeeHub::PROP_SPONSOR_MEMBERSHIP][0] );
          $this->storeProperties();
        }
      }elseif ( isset( $this->stored_props[BeeHub::PROP_SPONSOR] ) && ( ! empty( $this->stored_props[BeeHub::PROP_SPONSOR] ) ) ) {
        // And the reverse; you can't have a default sponsor if you have no sponsors
        $this->user_set_sponsor( null );
        $this->storeProperties();
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
    
    $collection = BeeHub::getNoSQL()->users;
    $document = $collection->findOne( array( 'name' => $this->name ) );
    
    if ( isset( $this->stored_props[DAV::PROP_DISPLAYNAME] ) ) {
      $document['displayname'] = $this->stored_props[DAV::PROP_DISPLAYNAME];
    }else{
      unset( $document['displayname'] );
    }
    if ( isset( $this->stored_props[BeeHub::PROP_X509] ) ) {
      $document['x509'] = $this->stored_props[BeeHub::PROP_X509];
    }else{
      unset( $document['x509'] );
    }
    
    // Check whether the SURFconext ID already exists
    if ( isset( $this->stored_props[BeeHub::PROP_SURFCONEXT] ) ) {
      $conextDuplicate = $collection->findOne( array( 'surfconext_id' => $this->stored_props[BeeHub::PROP_SURFCONEXT] ), array( 'name' => true ) );
      if ( !is_null($conextDuplicate ) && ( $conextDuplicate['name'] !== $this->name ) ) {
        throw new DAV_Status(DAV::HTTP_CONFLICT, "This SURFconext id is already used by a different BeeHub user.");
      }
      $document['surfconext_id']          = @$this->stored_props[BeeHub::PROP_SURFCONEXT];
      $document['surfconext_description'] = @$this->stored_props[BeeHub::PROP_SURFCONEXT_DESCRIPTION];
    }else{
      unset( $document['surfconext_id'], $document['surfconext_description']);
    }
    
    $p_sponsor = rawurldecode( basename( @$this->stored_props[BeeHub::PROP_SPONSOR] ) );
    if ( isset( $document['sponsors'] ) && is_array( $document['sponsors'] ) && in_array( $p_sponsor, $document['sponsors'] ) ) {
      $document['default_sponsor'] = $p_sponsor;
    }

    $change_email = false;
    if ( @$this->stored_props[BeeHub::PROP_EMAIL] !== @$document['email'] ) {
      $change_email = true;
      $document['unverified_email'] = @$this->stored_props[BeeHub::PROP_EMAIL];
      $document['verification_code'] = md5(time() . '0-c934q2089#$#%@#$jcq2iojc43q9  i1d' . rand(0, 10000));
      $document['verification_expiration'] = time() + (60 * 60 * 24);
    }
    
    // Write all data to database
    $saveResult = $collection->save( $document );
    if ( ! $saveResult['ok'] ) {
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Notify the user if needed
    if ($change_email) {
      $activation_link = BeeHub::urlbase(true) . $this->path . '?verification_code=' . $document['verification_code'];
      $message =
'Dear ' . $document['displayname'] . ',

This e-mail address (' . $document['unverified_email'] . ') is added to the BeeHub account \'' . $this->name . '\'. You need to confirm this action by following this link:

' . $activation_link . '

If this link doesn\'t work, on your profile page go to the tab \'Verify e-mail address\' and fill out the following verification code:

' . $document['verification_code'] . '

Note that your verification code is only valid for 24 hours. Also, for new users, if you don\'t have a validated e-mail address, your account will automatically be removed after 24 hours.

If this was a mistake, or you do not want to add this e-mail address to this BeeHub account, you don\'t have to do anything.

Best regards,

BeeHub';
      BeeHub::email($document['displayname'] . ' <' . $document['unverified_email'] . '>',
                    'Verify e-mail address for BeeHub',
                    $message);
    }

    // Update the json file containing all displaynames of all privileges
    self::update_principals_json();
    $this->touched = false;
  }


  public function user_prop_acl_internal() {
    return array(
      new DAVACL_Element_ace(
        DAVACL::PRINCIPAL_SELF, false, array(
          DAVACL::PRIV_READ, DAVACL::PRIV_WRITE
        ), false, true
      ),
      new DAVACL_Element_ace(
        DAVACL::PRINCIPAL_AUTHENTICATED , false, array(
          DAVACL::PRIV_READ
        ), false, true
      )
    );
  }


  /**
   * Checks a password
   *
   * @param   string   $password  The password to check
   * @return  boolean             True if the supplied password matches the user's password, false otherwise
   */
  public function check_password($password) {
    if ( is_null($this->password) ||
         $this->password !== crypt($password, $this->password) ) {
      return false;
    }else{
      return true;
    }
  }


  /**
   * Sets a new password
   *
   * @param   string   $password  The new password
   * @return  void
   */
  public function set_password($password) {
    $encrypted_password = crypt($password, '$6$rounds=5000$' . md5(time() . rand(0, 99999)) . '$');

    $collection = BeeHub::getNoSQL()->users;
    $document = $collection->findOne( array( 'name' => $this->name ) );
    $document['password'] = $encrypted_password;
    $saveResult = $collection->save( $document );
    if ( ! $saveResult['ok'] ) {
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }

    $this->password = $encrypted_password;
  }


  /**
   * Checks the verification code and verifies the e-mail address if the code is correct
   * @param   string  $code  The verification code
   * @return  boolean        True if the code verified correctly, false if the code was wrong
   */
  public function verify_email_address($code) {
    $collection = BeeHub::getNoSQL()->users;
    $document = $collection->findOne( array( 'name' => $this->name ) );
    
    if ( ( $document['verification_code'] === $code ) &&
         ( $document['verification_expiration'] > time() ) ) {
      
      $document['email'] = $document['unverified_email'];
      unset( $document['unverified_email'], $document['verification_code'], $document['verification_expiration'] );
      $saveResult = $collection->save( $document );
      if ( ! $saveResult['ok'] ) {
        throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
      }
      $this->stored_props[BeeHub::PROP_EMAIL]  = $document['email'];
      return true;
      
    }else{
      return false;
    }
  }


  /**
   * Returns an array with all sponsors of the current user
   *
   * @return  array  An array of paths of the user's sponsors
   */
  public function user_prop_sponsor_membership() {
    $this->init_props();
    return $this->stored_props[BeeHub::PROP_SPONSOR_MEMBERSHIP];
  }


  public function user_prop_group_membership() {
    $this->init_props();
    return $this->stored_props[DAV::PROP_GROUP_MEMBERSHIP];
  }


  public function user_set_sponsor($sponsor) {
    if ( is_null( $sponsor ) || in_array( $sponsor, $this->user_prop_sponsor_membership() ) ) {
      $this->user_set(BeeHub::PROP_SPONSOR, $sponsor);
    }else{
      throw new DAV_Status( DAV::HTTP_CONFLICT, 'You can not make a sponsor your default sponsor if you are not sponsored by it yet' );
    }
  }


  public function is_admin() {
    return ( $this->path === $this->user_prop_current_user_principal() );
  }


  /**
   * @param array $properties
   * @return array an array of (property => isReadable) pairs.
   */
  public function property_priv_read($properties) {
    $retval = parent::property_priv_read($properties);
    $is_admin = $this->is_admin();
    $retval[BeeHub::PROP_EMAIL]                  = $is_admin;
    $retval[BeeHub::PROP_SURFCONEXT]             = $is_admin;
    $retval[BeeHub::PROP_SURFCONEXT_DESCRIPTION] = $is_admin;
    $retval[BeeHub::PROP_X509]                   = $is_admin;
    $retval[BeeHub::PROP_SPONSOR]                = $is_admin;
    $retval[DAV::PROP_GROUP_MEMBERSHIP]          = $is_admin;
    $retval[BeeHub::PROP_LAST_ACTIVITY]          = $is_admin;
    return $retval;
  }


  /**
  * The user has write privileges on all properties if he is the administrator of this principal
  * @param array $properties
  * @return array an array of (property => isWritable) pairs.
  */
  public function property_priv_write($properties) {
    $retval = parent::property_priv_read($properties);
    $is_admin = $this->is_admin();
    $retval[BeeHub::PROP_EMAIL]                  = $is_admin;
    $retval[BeeHub::PROP_SURFCONEXT]             = $is_admin;
    $retval[BeeHub::PROP_SURFCONEXT_DESCRIPTION] = $is_admin;
    $retval[BeeHub::PROP_X509]                   = $is_admin;
    $retval[BeeHub::PROP_SPONSOR]                = $is_admin;
    $retval[DAV::PROP_GROUP_MEMBERSHIP]          = false;
    $retval[BeeHub::PROP_SPONSOR_MEMBERSHIP]     = false;
    $retval[BeeHub::PROP_LAST_ACTIVITY]          = false;
    return $retval;
  }


  public function user_propname() {
    return BeeHub::$USER_PROPS;
  }


  /**
   * @param $name string
   * @param $value string XML
   */
  public function user_set($name, $value = null) {
    switch($name) {
      case BeeHub::PROP_EMAIL:
        if ( filter_var($value, FILTER_VALIDATE_EMAIL) === false ) {
          throw new DAV_Status(DAV::HTTP_BAD_REQUEST, 'Incorrect e-mail address format');
        }
        break;
    }
    return parent::user_set($name, $value);
  }
  
  
  public function create_password_reset_code() {
    $collection = BeeHub::getNoSQL()->users;
    $document = $collection->findOne( array( 'name' => $this->name ) );
    $document['password_reset_code'] = md5(time() . ' yrn 67%$ V 4e eshgbJGEc43y5f*INTj67rbf3cw rv' . rand(0, 10000));
    $document['password_reset_expiration'] = time() + ( 60 * 60 );
    $saveResult = $collection->save( $document );
    if ( ! $saveResult['ok'] ) {
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }
    return $document['password_reset_code'];
  }
  
  
  public function check_password_reset_code($reset_code) {
    $collection = BeeHub::getNoSQL()->users;
    $document = $collection->findOne( array( 'name' => $this->name ) );
    
    if ( ( $document['password_reset_code'] === $reset_code ) &&
         ( $document['password_reset_expiration'] > time() ) ) {
      
      unset( $document['password_reset_code'], $document['password_reset_expiration'] );
      $saveResult = $collection->save( $document );
      if ( ! $saveResult['ok'] ) {
        throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
      }
      return true;
      
    }else{
      return false;
    }
  }


} // class BeeHub_User
