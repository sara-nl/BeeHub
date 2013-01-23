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
 * Resource Registry
 * @package BeeHub
 */
class BeeHub_Registry implements DAV_Registry {


/**
 * @var BeeHub_Registry
 */
private static $inst = null;


/**
 * Singleton factory.
 * @return BeeHub_Registry
 */
public static function inst() {
  if (null === self::$inst)
    self::$inst = new BeeHub_Registry();
  return self::$inst;
}


/**
 * Array of DAV_Resource objects, indexed by path.
 * @var array
 */
public $resourceCache = array();


/**
 * @param string $path
 */
public function resource($path) {
  $path = DAV::unslashify($path);
  if ( isset( $this->resourceCache[$path] ) )
    return $this->resourceCache[$path];
  $localPath = BeeHub::localPath($path);
  $retval = null;
  //TODO: Change this expression so it uses the configuration options instead of this hard coded path!
  preg_match( '@^/(users|groups|sponsors)(?:/[^/]+)?$@', $path, $match );
  if ( !$match ) {
    if (is_dir($localPath))
      $retval = new BeeHub_Directory($path);
    elseif (file_exists($localPath))
      $retval = new BeeHub_File($path);
  }
  elseif ( 'users' == $match[1] ) {
    if ( @is_dir( $localPath) )
      $retval = new BeeHub_Users($path);
    else {
      try {
        $retval = new BeeHub_User($path);
      }
      catch(Exception $e) {}
    }
  }
  elseif ( 'groups' == $match[1] ) {
    if ( @is_dir($localPath) )
      $retval = new BeeHub_Groups($path);
    else {
      try {
        $retval = new BeeHub_Group($path);
      }
      catch(Exception $e) {}
    }
  }
  elseif ( 'sponsors' == $match[1] ) {
    if ( @is_dir($localPath) )
      $retval = new BeeHub_Directory($path);
    else {
      try {
        $retval = new BeeHub_Sponsor($path);
      }
      catch(Exception $e) {}
    }
  }
  return ( $this->resourceCache[$path] = $retval );
}


/**
 * @param string $path always unslashified!
 */
public function forget($path) {
  unset( $this->resourceCache[$path] );
}


/**
 * @param array $write paths to write-lock.
 * @param array $read paths to read-lock
 */
public function shallowLock($write, $read) {
  $whashes = $rhashes = array();
  foreach ($write as $value)
    $whashes[] = BeeHub::escape_string( hash( 'sha256', $value, true ) );
  foreach ($read as $value)
    $rhashes[] = BeeHub::escape_string( hash( 'sha256', $value, true ) );
  sort( $whashes, SORT_STRING );
  sort( $rhashes, SORT_STRING );
  if (!empty($whashes)) {
    BeeHub::query('INSERT IGNORE INTO `shallowLocks` VALUES (' . implode('),(', $whashes) . ');');
    $whashes = implode(',', $whashes);
    $whashes = BeeHub::mysqli()->prepare(
      "SELECT * FROM `shallowLocks` WHERE `pathhash` IN ($whashes) FOR UPDATE;"
    );
  }
  else
    $whashes = null;
  if (!empty($rhashes)) {
    BeeHub::query('INSERT IGNORE INTO `shallowLocks` VALUES (' . implode('),(', $rhashes) . ');');
    $rhashes = implode(',', $rhashes);
    $rhashes = BeeHub::mysqli()->prepare(
      "SELECT * FROM `shallowLocks` WHERE `pathhash` IN ($rhashes) LOCK IN SHARE MODE;"
    );
  }
  else
    $rhashes = null;
  $microsleeptimer = 10000; // also functions as success flag
  while ($microsleeptimer) {
    if ($microsleeptimer > 1280000) $microsleeptimer = 1280000;
    BeeHub::query('START TRANSACTION');
    if ($whashes)
      try {
        $whashes->execute();
        $whashes->free_result();
      }
      catch (BeeHub_Deadlock $e) {
        BeeHub::query('ROLLBACK');
        usleep( $microsleeptimer );
        $microsleeptimer *= 2;
        continue;
      }
      catch (BeeHub_Timeout $e) {
        BeeHub::query('ROLLBACK');
        throw new DAV_Status(DAV::HTTP_SERVICE_UNAVAILABLE);
      }
    if ($rhashes)
      try {
        $rhashes->execute();
        $rhashes->free_result();
      }
      catch (BeeHub_Deadlock $e) {
        BeeHub::query('ROLLBACK');
        usleep( $microsleeptimer );
        $microsleeptimer *= 2;
        continue;
      }
      catch (BeeHub_Timeout $e) {
        BeeHub::query('ROLLBACK');
        throw new DAV_Status(DAV::HTTP_SERVICE_UNAVAILABLE);
      }
    $microsleeptimer = 0;
  }
}


/**
 * @param array $write paths to write-lock.
 * @param array $read paths to read-lock
 */
public function shallowUnlock() {
  BeeHub::query('COMMIT;');
}


} // class
