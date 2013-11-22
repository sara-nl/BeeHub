<?php
/**
 * Contains the BeeHub_ACL_Provider class
 *
 * Copyright ©2007-2013 SURFsara b.v., Amsterdam, The Netherlands
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
 *
 * @package BeeHub
 */

/**
 * ACL provider.
 * @package BeeHub
 */
class BeeHub_ACL_Provider implements DAVACL_ACL_Provider {

  /**
   * @var  BeeHub_Auth  The authentication instance to check authentication against
   */
  private $auth;


  /**
   * Constructor just requires dependecies
   *
   * For backwards compatibility, the parameters can be omitted and the
   * constructor will set it to the default values
   *
   * @param  BeeHub_Auth  $auth  The authentication instance to check authentication against
   */
  public function __construct( BeeHub_Auth $auth = null ) {
    if ( is_null( $auth ) ) {
      $this->auth = BeeHub_Auth::inst();
    }else{
      $this->auth = $auth;
    }
  }


  /**
   * Returns the cached instance of this class
   *
   * For backwards compatibility, the parameters can be omitted and the method
   * will set it to the default values
   *
   * @param   BeeHub_Auth          $auth  The authentication instance to check authentication against
   * @return  BeeHub_ACL_Provider         The cached instance of this class
   */
  static public function inst( BeeHub_Auth $auth = null ) {
    if ( is_null( $auth ) ) {
      $auth = BeeHub_Auth::inst();
    }
    static $inst = null;
    if (!$inst) $inst = new BeeHub_ACL_Provider( $auth );
    return $inst;
  }


/**
 * @see DAVACL_ACL_Provider::user_prop_current_user_principal
 */
public function user_prop_current_user_principal() {
  $currentUser = $this->auth->current_user();
  if ( !is_null( $currentUser ) ) {
    return $currentUser->path;
  }else{
    return null;
  }
}


/**
 * @return boolean is the current user an administrator?
 */
public function wheel() {
  return BeeHub::$CONFIG['namespace']['wheel_path'] === $this->user_prop_current_user_principal();
//  if ($this->wheelCache === null)
//    $this->wheelCache = (
//      ($cup = $this->user_prop_current_user_principal()) &&
//      ($cup = BeeHub_Registry::inst()->resource($cup)) &&
//      in_array('/groups/wheel', $cup->current_user_principals())
//    );
//  return $this->wheelCache;
}


public function user_prop_supported_privilege_set() {
  static $retval = null;
  if (!is_null($retval)) return $retval;

  $read_acl    = new DAVACL_Element_supported_privilege(
    DAVACL::PRIV_READ_ACL, true, 'Read ACL'
  );
  $read_cups   = new DAVACL_Element_supported_privilege(
    DAVACL::PRIV_READ_CURRENT_USER_PRIVILEGE_SET,
    true, 'Read current user privilege set'
  );

  $read        = new DAVACL_Element_supported_privilege(
    DAVACL::PRIV_READ, false, 'Read'
  );
  $write       = new DAVACL_Element_supported_privilege(
    DAVACL::PRIV_WRITE, false, 'Write'
  );
  $write_acl   = new DAVACL_Element_supported_privilege(
    DAVACL::PRIV_WRITE_ACL, false, 'Manage'
  );


  $retval      = new DAVACL_Element_supported_privilege(
    DAVACL::PRIV_ALL, false, 'All'
  );

  $read->add_supported_privilege($read_acl);
  $read->add_supported_privilege($read_cups);
  $retval->add_supported_privilege($read)
         ->add_supported_privilege($write)
         ->add_supported_privilege($read_acl)
         ->add_supported_privilege($write_acl);
  $retval = array($retval);
  return $retval;
}


public function user_prop_acl_restrictions() {
  return array();
}


public function user_prop_principal_collection_set() {
  return array( BeeHub::GROUPS_PATH, BeeHub::USERS_PATH, BeeHub::SPONSORS_PATH );
}


}
