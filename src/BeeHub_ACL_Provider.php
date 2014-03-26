<?php
/**
 * Contains the BeeHub_ACL_Provider class
 *
 * Copyright ©2007-2014 SURFsara b.v., Amsterdam, The Netherlands
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
   * Returns the cached instance of this class
   *
   * @return  BeeHub_ACL_Provider         The cached instance of this class
   */
  static public function inst() {
    static $inst = null;
    if (!$inst) $inst = new BeeHub_ACL_Provider();
    return $inst;
  }


/**
 * @see DAVACL_ACL_Provider::user_prop_current_user_principal
 */
public function user_prop_current_user_principal() {
  $auth = BeeHub::getAuth();
  $currentUser = $auth->current_user();
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
  $user = BeeHub::getAuth()->current_user();
  return ! is_null( $user ) && in_array( BeeHub::$CONFIG['namespace']['admin_group'], $user->user_prop_group_membership() );
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

  $unbind    = new DAVACL_Element_supported_privilege(
    DAVACL::PRIV_UNBIND, false, 'Remove child resources from collections (this also requires write privilege on resource itself)'
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
  $write->add_supported_privilege($unbind);
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
