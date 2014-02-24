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


const PROPNAME   = 'DAV%3A%20lockdiscovery';
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
  exec( 'getfattr --absolute-names -n "user.' . self::PROPNAME . '" -R ' . BeeHub::escapeshellarg(BeeHub::localPath($path)) . ' 2>/dev/null', $output );
  $result = array();
  $filename = null;
  foreach ($output as $line)
    if (preg_match('@^# file: (.*)$@', $line, $matches))
      $filename = stripcslashes($matches[1]);
    elseif ( $filename &&
             preg_match(
               '@^user\\.DAV%3A%20lockdiscovery="((?:\\\\.|[^"\\\\])*)"$@',
               $line, $matches
             ) )
      $result[$filename] = stripcslashes($matches[1]);
  unset ($result[BeeHub::localPath($path)]);
  $retval = array();
  foreach($result as $localPath => $lockdiscovery) {
    $l = json_decode($lockdiscovery, true);
    if ( 0 == $l['timeout'] || $l['timeout'] > time() )
      $retval[$l['locktoken']] = new DAV_Element_activelock( $l );
    else
      xattr_remove($localPath, self::PROPNAME);
  }
  return $retval;
}


public function getlock($path) {
  if ( $value = json_decode(
         @xattr_get( BeeHub::localPath($path), self::PROPNAME ),
         true
       ) )
    if ($value['timeout'] && $value['timeout'] < time())
      xattr_remove(BeeHub::localPath($path), self::PROPNAME);
    else
      return new DAV_Element_activelock( $value );
#  do {
#    $path = dirname($path);
#    if ($value = json_decode(@xattr_get(BeeHub::localPath($path), self::PROPNAME), true))
#      if ($value['timeout'] && $value['timeout'] < time())
#        xattr_remove(BeeHub::localPath($path), self::PROPNAME);
#      elseif( DAV::DEPTH_INF === $value['depth'] )
#        return new DAV_Element_activelock( $value );
#  } while ('/' != $path);
  return null;
}


public function setlock($lockroot, $depth, $owner, $timeout) {
  $resource = DAV::$REGISTRY->resource( $lockroot );
  if ( ( $resource->prop_resourcetype() === DAV_Collection::RESOURCETYPE ) && ( DAV::DEPTH_0 !== $depth ) ) {
    throw new DAV_Status(
      DAV::HTTP_NOT_IMPLEMENTED,
      'Locks of depth infinity are not implemented.'
    );
  }
//   if (preg_match("@^(?:{BeeHub::$USERS_PATH}|{BeeHub::$GROUPS_PATH}).+\$@", $lockroot))
//     throw new DAV_Status(DAV::HTTP_FORBIDDEN);
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
  xattr_set(
    BeeHub::localpath($lockroot), rawurlencode(DAV::PROP_LOCKDISCOVERY),
    json_encode($activelock)
  );
  return $locktoken;
}


public function refresh($path, $locktoken, $timeout) {
  $timeout = self::timeout($timeout);
  $lock = @xattr_get( BeeHub::localPath($path), self::PROPNAME );
  if (!$lock) return false;
  $lock = new DAV_Element_activelock( json_decode($lock, true) );
  if ( $lock->timeout && $lock->timeout < time() ) {
    xattr_remove( BeeHub::localPath($path), self::PROPNAME );
    return false;
  }
  if ( $locktoken != $lock->locktoken )
    return false;
  $lock->timeout = $timeout;
  xattr_set( BeeHub::localPath($lock->lockroot), self::PROPNAME, json_encode($lock) );
  return true;
}


public function unlock($path) {
  $value = @xattr_get( BeeHub::localPath($path), self::PROPNAME );
  if (!$value) return false;
  $value = json_decode($value, true);
  $retval = $value['timeout'] >= time() || 0 == $value['timeout'];
  xattr_remove( BeeHub::localPath($path), self::PROPNAME );
  return $retval;
}


}
