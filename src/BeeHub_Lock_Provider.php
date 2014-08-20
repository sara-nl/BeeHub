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
 * Lock provider.
 * @package BeeHub
 */
class BeeHub_Lock_Provider implements DAV_Lock_Provider {


const MAXTIMEOUT = 3600;


/**
 * @return BeeHub_Lock_Provider
 */
static public function inst() {
  static $inst = null;
  if (!$inst) $inst = new BeeHub_Lock_Provider();
  return $inst;
}


/**
 * @param $timeout array
 * @return int Unix Timestamp
 */
private static function timeout($timeout) {
  if (!$timeout || !$timeout[0] || $timeout[0] > self::MAXTIMEOUT)
    return time() + self::MAXTIMEOUT;
  return time() + $timeout[0];
}


/**
 * Get all locks on a resource and all it's members
 * 
 * @param   string  $path  The path to the resource
 * @return  array          An array with all locks (as \DAV_Element_activelock objects)
 */
public function memberLocks($path) {
  $resource = DAV::$REGISTRY->resource( $path );
  $result = $resource->get_members_with_prop( DAV::PROP_LOCKDISCOVERY );
  $retval = array();
  foreach($result as $memberPath => $lockdiscovery) {
    $l = json_decode($lockdiscovery, true);
    if ( 0 == $l['timeout'] || $l['timeout'] > time() )
      $retval[$l['locktoken']] = new DAV_Element_activelock( $l );
    else {
      $member_resource = DAV::$REGISTRY->resource( $memberPath );
      $member_resource->user_set( DAV::PROP_LOCKDISCOVERY, null );
      $member_resource->storeProperties();
    }
  }
  return $retval;
}


/**
 * Gets the lock set on a resource
 * 
 * Note that a lock with an expired timeout does not exist anymore and therefor
 * will not be returned!
 * 
 * @param   string                        $path  The path to the resource
 * @return  \DAV_Element_activelock|null         The lock on the resource, or null if none is set
 */
public function getlock($path) {
  $resource = \DAV::$REGISTRY->resource( $path );
  if ( $value = $resource->user_prop( DAV::PROP_LOCKDISCOVERY ) ) {
    $value = json_decode( $value, true );
    if ( $value['timeout'] && $value['timeout'] < time() ) {
      $resource->user_set( DAV::PROP_LOCKDISCOVERY, null );
      $resource->storeProperties();
    }else{
      return new DAV_Element_activelock( $value );
    }
  }
  return null;
}


/**
 * Sets a lock on a resource
 * 
 * @param   string      $lockroot  The path to the lock root (i.e. the resource on which the lock should be set)
 * @param   int         $depth     The depth of the lock. Can only be \DAV::DEPTH_0
 * @param   string      $owner     A (URL to a) description of the owner
 * @param   array       $timeout   An array with as first element the timeout duration in seconds
 * @return  string                 The lock token for the generated lock
 * @throws  DAV_Status             When trying to use a different depth than \DAV::DEPTH_0
 */
public function setlock($lockroot, $depth, $owner, $timeout) {
  $resource = DAV::$REGISTRY->resource( $lockroot );
  $resource->assert( DAVACL::PRIV_WRITE_CONTENT );

  if ( ( $resource->prop_resourcetype() === DAV_Collection::RESOURCETYPE ) && ( DAV::DEPTH_0 !== $depth ) ) {
    throw new DAV_Status(
      DAV::HTTP_NOT_IMPLEMENTED,
      'Locks of depth infinity are not implemented.'
    );
  }
  $timeout = self::timeout($timeout);
  $locktoken = 'opaquelocktoken:' . $this->getUUIDv4URN();
  $activelock = new DAV_Element_activelock( array(
    'lockroot'  => $lockroot,
    'depth'     => $depth,
    'locktoken' => $locktoken,
    'owner'     => $owner,
    'timeout'   => $timeout
  ) );
  $resource = DAV::$REGISTRY->resource( $lockroot );
  $resource->user_set( DAV::PROP_LOCKDISCOVERY, json_encode( $activelock ) );
  $resource->storeProperties();
  return $locktoken;
}


/**
 * Refreshes a lock, effectively extending it's lifetime
 * 
 * @param  string   $path       The path of the resource on which the lock is set
 * @param  string   $locktoken  The lock token identifying the lock
 * @param  array    $timeout    An array with as first element the timeout duration in seconds
 * @return boolean              True on success, false otherwise
 */
public function refresh($path, $locktoken, $timeout) {
  $timeout = self::timeout($timeout);
  $resource = DAV::$REGISTRY->resource( $path );
  $lock = $resource->user_prop( DAV::PROP_LOCKDISCOVERY );
  if (!$lock) return false;
  $lock = new DAV_Element_activelock( json_decode($lock, true) );
  if ( $lock->timeout && $lock->timeout < time() ) {
    $resource->user_set( DAV::PROP_LOCKDISCOVERY, null );
    $resource->storeProperties();
    return false;
  }
  if ( $locktoken != $lock->locktoken )
    return false;
  $lock->timeout = $timeout;
  $lock_root = DAV::$REGISTRY->resource( $lock->lockroot );
  $lock_root->user_set( DAV::PROP_LOCKDISCOVERY, json_encode( $lock ) );
  $lock_root->storeProperties();
  return true;
}


/**
 * Unlocks a resource
 * 
 * @param   string   $path  Path to the resource
 * @return  boolean         True on success, false on failure
 */
public function unlock($path) {
  $resource = DAV::$REGISTRY->resource( $path );
  $value = $resource->user_prop( DAV::PROP_LOCKDISCOVERY );
  if (!$value) return false;
  $value = json_decode($value, true);
  $retval = $value['timeout'] >= time() || 0 == $value['timeout'];
  $resource->user_set( DAV::PROP_LOCKDISCOVERY, null );
  $resource->storeProperties();
  return $retval;
}


private function getUUIDv4URN() {
  // Determine octets in pairs of 2 to prevent using mt_rand with max values > 2^32
  $octetPairs = array();
  for ($counter = 0; $counter < 8; $counter++) {
    $octetPairs[] = mt_rand( 0, 0xffff );
  }
  
  // octet 6 should start with 0100
  $octetPairs[3] = $octetPairs[3] & 0xfff | 0x4000 ;
  
  // octet 8 should start with 10
  $octetPairs[4] = $octetPairs[4] & 0x3fff | 0x8000 ;
  
  // Create the correct strings
  return 'urn:uuid:' . sprintf(
          '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
          $octetPairs[0],
          $octetPairs[1],
          $octetPairs[2],
          $octetPairs[3],
          $octetPairs[4],
          $octetPairs[5],
          $octetPairs[6],
          $octetPairs[7]
  );
}


}
