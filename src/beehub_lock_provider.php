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
const MAXTIMEOUT = 604800;


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
  $match = str_replace(
    array('_', '%'),
    array('\\_', '\\%'),
    DAV::slashify( $path )
  ) . '_%';
  $stmt = BeeHub_DB::execute(
    'SELECT `lock_token`, `lock_root`, `lock_owner`, `lock_depth`, `lock_timeout`
       FROM `Locks`
      WHERE `lock_root` LIKE ?', 's', $match
  );
  $retval = array();
  while ($row = $stmt->fetch_row())
    if ( 0 === $row[4] || $row[4] > time() )
      $retval[$row[0]] = new DAV_Element_activelock(array(
        'locktoken' => $row[0],
        'lockroot'  => $row[1],
        'owner'     => $row[2],
        'depth'     => $row[3],
        'timeout'   => $row[4],
      ));
  $stmt->free_result();
  return $retval;
}


public function getlock($path) {
  $retval = null;
  $stmt = BeeHub_DB::execute(
    'SELECT `lock_token`, `lock_root`, `lock_owner`, `lock_depth`, `lock_timeout`
       FROM `Locks`
      WHERE `lock_root` = ?', 's', $path
  );
  if ( $row = $stmt->fetch_row() and
       0 === $row[4] || $row[4] > time() )
    $retval = new DAV_Element_activelock(array(
      'locktoken' => $row[0],
      'lockroot'  => $row[1],
      'owner'     => $row[2],
      'depth'     => $row[3],
      'timeout'   => $row[4],
    ));
  $stmt->free_result();
  return $retval;
}


public function setlock($lockroot, $depth, $owner, $timeout) {
//   if (preg_match("@^(?:{BeeHub::$USERS_PATH}|{BeeHub::$GROUPS_PATH}).+\$@", $lockroot))
//     throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  if ('/' === substr($lockroot, -1) && $depth)
    throw new DAV_Status(
      DAV::HTTP_NOT_IMPLEMENTED,
      "Depth-infinity locks on collections are not supported."
    );
  $timeout = self::timeout($timeout);
  $stmt = BeeHub_DB::execute('SELECT UUID()');
  $row = $stmt->fetch_row();
  $locktoken = 'opaquelocktoken:' . $row[0];
  $stmt->free_result();
  BeeHub_DB::execute(
    'DELETE FROM `Locks` WHERE `lock_timeout` > 0 AND `lock_timeout` <= ?',
    'i', time()
  );
  BeeHub_DB::execute(
    'INSERT INTO `Locks`
       (`lock_token`, `lock_root`, `lock_owner`, `lock_depth`, `lock_timeout`)
     VALUES( ?, ?, ?, ?, ? )', 'sssii',
     $locktoken, $lockroot, $owner, $depth, $timeout
  );
  return $locktoken;
}


public function refresh($path, $locktoken, $timeout) {
  $timeout = self::timeout($timeout);
  $stmt = BeeHub_DB::execute(
    'UPDATE `Locks`
        SET `lock_timeout` = ?
      WHERE `lock_root` = ?
        AND `lock_token` = ?
        AND ( `lock_timeout` = 0 OR `lock_timeout` > ? )',
    'issi', $timeout, $path, $locktoken, time()
  );
  return $stmt->affected_rows > 0;
}


public function unlock($path) {
  $stmt = BeeHub_DB::execute(
    'DELETE FROM `Locks`
      WHERE `lock_root` = ?
        AND ( `lock_timeout` = 0 OR `lock_timeout` > ? )',
    'si', $path, time()
  );
  return $stmt->affected_rows > 0;
}


}
