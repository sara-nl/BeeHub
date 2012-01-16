<?php

/*·************************************************************************
 * Copyright ©2007-2011 Pieter van Beek, Almere, The Netherlands
 * 		    <http://purl.org/net/6086052759deb18f4c0c9fb2c3d3e83e>
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
 * $Id: sd_acl_provider.php 3364 2011-08-04 14:11:03Z pieterb $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package SD
 */

/**
 * Lock provider.
 * @package SD
 */
class SD_ACL_Provider implements DAVACL_ACL_Provider {
  
  
/**
 * @return SD_ACL_Provider
 */
static public function inst() {
  static $inst = null;
  if (!$inst) $inst = new SD_ACL_Provider();
  return $inst;
}


public static $CURRENT_USER_PRINCIPAL = null;


public function user_prop_current_user_principal() {
  return self::$CURRENT_USER_PRINCIPAL;
}


private $wheelCache = null;
public function wheel() {
  if ($this->wheelCache === null)
    $this->wheelCache = (
      ($cup = $this->user_prop_current_user_principal()) &&
      ($cup = SD_Registry::inst()->resource($cup)) &&
      in_array('/groups/wheel', $cup->current_user_principals())
    );
  return $this->wheelCache;
}


public function user_prop_supported_privilege_set() {
  static $retval = null;
  if (!is_null($retval)) return $retval;
  
  $retval    = new DAVACL_Element_supported_privilege(DAVACL::PRIV_ALL, false, 'All');
  
  $read_cups       = new DAVACL_Element_supported_privilege(DAVACL::PRIV_READ_CURRENT_USER_PRIVILEGE_SET, true, 'Read current user’s privileges');
  $read_properties = new DAVACL_Element_supported_privilege(SD::PRIV_READ_PROPERTIES, false, 'Read properties');
  $read_properties->add_supported_privilege( $read_cups );
  
  $read_content    = new DAVACL_Element_supported_privilege(SD::PRIV_READ_CONTENT,    false, 'Read content');
  $read            = new DAVACL_Element_supported_privilege(DAVACL::PRIV_READ, false, 'Read');
  $read->add_supported_privilege( $read_properties )
       ->add_supported_privilege( $read_content );
  
  $write_properties = new DAVACL_Element_supported_privilege(DAVACL::PRIV_WRITE_PROPERTIES, false, 'Write properties');
  $write_content    = new DAVACL_Element_supported_privilege(DAVACL::PRIV_WRITE_CONTENT,    false, 'Write content');
  $bind             = new DAVACL_Element_supported_privilege(DAVACL::PRIV_BIND,   false, 'Bind');
  $unbind           = new DAVACL_Element_supported_privilege(DAVACL::PRIV_UNBIND, false, 'Unbind');
  $unlock           = new DAVACL_Element_supported_privilege(DAVACL::PRIV_UNLOCK, false, 'Unlock');
  $write            = new DAVACL_Element_supported_privilege(DAVACL::PRIV_WRITE, false, 'Write');
  $write->add_supported_privilege($write_properties)
        ->add_supported_privilege($write_content)
        ->add_supported_privilege($bind)
        ->add_supported_privilege($unbind)
        ->add_supported_privilege($unlock);
  
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
  return array('/users/', '/groups/');
}


/**
 * This method is called when DAV receives an 401 Unauthenticated exception.
 * @return bool true if a response has been sent to the user.
 */
public function unauthorized() {
  header(
    'WWW-Authenticate: Digest realm="' . SD::REALM . '",qop="auth",nonce="' .
    uniqid() . '",opaque="' . md5( SD::REALM ) . '"'
  );
  return false;
  
  if (80 == $_SERVER['SERVER_PORT']) {
    DAV::redirect(
      DAV::HTTP_TEMPORARY_REDIRECT,
      preg_replace('@^http://([^/:]+)(:\d+)/@', 'https://$1/', DAV::$PATH)
    );
    return true;
  }
  header('WWW-Authenticate: Basic realm="BDrive"');
  return false;
}


}
