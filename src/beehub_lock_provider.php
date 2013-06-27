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


public function memberLocks($path) {
  $resource = BeeHub_Registry::inst()->resource( $path );
  $result = $resource->get_members_with_prop( DAV::PROP_LOCKDISCOVERY );
  $retval = array();
  foreach($result as $memberPath => $lockdiscovery) {
    $l = json_decode($lockdiscovery, true);
    if ( 0 == $l['timeout'] || $l['timeout'] > time() )
      $retval[$l['locktoken']] = new DAV_Element_activelock( $l );
    else {
      $member_resource = BeeHub_Registry::inst()->resource( $memberPath );
      $member_resource->user_set( DAV::PROP_LOCKDISCOVERY, null );
      $member_resource->storeProperties();
    }
  }
  return $retval;
}


public function getlock($path) {
  $resource = BeeHub_Registry::inst()->resource( $path );
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


public function setlock($lockroot, $depth, $owner, $timeout) {
  if ( DAV::DEPTH_0 !== $depth )
    throw new DAV_Status(
      DAV::HTTP_NOT_IMPLEMENTED,
      'Locks of depth infinity are not implemented.'
    );
  $timeout = self::timeout($timeout);
  $stmt = BeeHub_DB::execute('SELECT UUID()');
  $row = $stmt->fetch_row();
  $locktoken = 'opaquelocktoken:' . $row[0];
  $stmt->free_result();
  $activelock = new DAV_Element_activelock( array(
    'lockroot'  => $lockroot,
    'depth'     => $depth,
    'locktoken' => $locktoken,
    'owner'     => $owner,
    'timeout'   => $timeout
  ) );
  $resource = BeeHub_Registry::inst()->resource( $lockroot );
  $resource->user_set( DAV::PROP_LOCKDISCOVERY, json_encode( $activelock ) );
  $resource->storeProperties();
  return $locktoken;
}


public function refresh($path, $locktoken, $timeout) {
  $timeout = self::timeout($timeout);
  $resource = BeeHub_Registry::inst()->resource( $path );
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
  $lock_root = BeeHub_Registry::inst()->resource( $lock->lockroot );
  $lock_root->user_set( DAV::PROP_LOCKDISCOVERY, json_encode( $lock ) );
  $lock_root->storeProperties();
  return true;
}


public function unlock($path) {
  $resource = BeeHub_Registry::inst()->resource( $path );
  $value = $resource->user_prop( DAV::PROP_LOCKDISCOVERY );
  if (!$value) return false;
  $value = json_decode($value, true);
  $retval = $value['timeout'] >= time() || 0 == $value['timeout'];
  $resource->user_set( DAV::PROP_LOCKDISCOVERY, null );
  $resource->storeProperties();
  return $retval;
}


}
