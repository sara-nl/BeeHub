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
 * Lock provider.
 * @package BeeHub
 */
class BeeHub_ACL_Provider implements DAVACL_ACL_Provider {


/**
 * @return BeeHub_ACL_Provider
 */
static public function inst() {
  static $inst = null;
  if (!$inst) $inst = new BeeHub_ACL_Provider();
  return $inst;
}


// TODO: Deprecate.
public $CURRENT_USER_PRINCIPAL = null;


public function user_prop_current_user_principal() {
  // return BeeHub::current_user();
  return $this->CURRENT_USER_PRINCIPAL;
}


/**
 * @return boolean is the current user an administrator?
 */
public function wheel() {
  return BeeHub::$CONFIG['webdav_namespace']['wheel_path'] == $this->CURRENT_USER_PRINCIPAL;
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

  $retval    = new DAVACL_Element_supported_privilege(DAVACL::PRIV_ALL, false, 'All');
  $read      = new DAVACL_Element_supported_privilege(DAVACL::PRIV_READ, false, 'Read');
  $write     = new DAVACL_Element_supported_privilege(DAVACL::PRIV_WRITE, false, 'Write');

  $read_acl  = new DAVACL_Element_supported_privilege(DAVACL::PRIV_READ_ACL, false, 'Read ACL');
  $write_acl = new DAVACL_Element_supported_privilege(DAVACL::PRIV_WRITE_ACL, false, 'Write ACL');

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
  return array(BeeHub::$CONFIG['webdav_namespace']['groups_path'], BeeHub::$CONFIG['webdav_namespace']['users_path']);
}


/**
 * This method is called when DAV receives an 401 Unauthenticated exception.
 * @return bool true if a response has been sent to the user.
 */
public function unauthorized() {
  return false;
  DAV::redirect(
    DAV::HTTP_TEMPORARY_REDIRECT,
    'webdav.php' . DAV::$PATH
  );
  return true;
}


}
