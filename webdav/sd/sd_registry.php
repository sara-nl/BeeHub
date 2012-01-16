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
 * $Id: sd_registry.php 3349 2011-07-28 13:04:24Z pieterb $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package SD
 */

/**
 * Resource Registry
 * @package SD
 */
class SD_Registry implements DAV_Registry {
  
  
/**
 * @var SD_Registry
 */
private static $inst = null;


/**
 * Singleton factory.
 * @return SD_Registry
 */
public static function inst() {
  if (null === self::$inst)
    self::$inst = new SD_Registry();
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
  $localPath = SD::localPath($path);
  $retval = null;
  preg_match( '@^/(users|groups)(?:/[^/]+)?$@', $path, $match );
  if ( !$match ) {
    if (is_dir($localPath))
      $retval = new SD_Directory($path);
    elseif (file_exists($localPath))
      $retval = new SD_File($path);
  }
  elseif ( 'users' == $match[1] ) {
    if ( @is_dir( $localPath) )
      $retval = new SD_Users($path);
    else {
      try {
        $retval = new SD_User($path);
      }
      catch(Exception $e) {}	
    }
  }
  elseif ( 'groups' == $match[1] ) {
    if ( @is_dir($localPath) )
      $retval = new SD_Groups($path);
    else {
      try {
        $retval = new SD_Group($path);
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
    $whashes[] = SD::escape_string( hash( 'sha256', $value, true ) );
  foreach ($read as $value)
    $rhashes[] = SD::escape_string( hash( 'sha256', $value, true ) );
  sort( $whashes, SORT_STRING );
  sort( $rhashes, SORT_STRING );
  if (!empty($whashes)) {
    SD::query('INSERT IGNORE INTO `shallowLocks` VALUES (' . implode('),(', $whashes) . ');');
    $whashes = implode(',', $whashes);
    $whashes = SD::mysqli()->prepare(
      "SELECT * FROM `shallowLocks` WHERE `pathhash` IN ($whashes) FOR UPDATE;"
    );
  }
  else
    $whashes = null;
  if (!empty($rhashes)) {
    SD::query('INSERT IGNORE INTO `shallowLocks` VALUES (' . implode('),(', $rhashes) . ');');
    $rhashes = implode(',', $rhashes);
    $rhashes = SD::mysqli()->prepare(
      "SELECT * FROM `shallowLocks` WHERE `pathhash` IN ($rhashes) LOCK IN SHARE MODE;"
    );
  }
  else
    $rhashes = null;
  $microsleeptimer = 10000; // also functions as success flag
  while ($microsleeptimer) {
    if ($microsleeptimer > 1280000) $microsleeptimer = 1280000;
    SD::query('START TRANSACTION');
    if ($whashes)
      try {
        $whashes->execute();
        $whashes->free_result();
      }
      catch (SD_Deadlock $e) {
        SD::query('ROLLBACK');
        usleep( $microsleeptimer );
        $microsleeptimer *= 2;
        continue;
      }
      catch (SD_Timeout $e) {
        SD::query('ROLLBACK');
        throw new DAV_Status(DAV::HTTP_SERVICE_UNAVAILABLE);
      }
    if ($rhashes)
      try {
        $rhashes->execute();
        $rhashes->free_result();
      }
      catch (SD_Deadlock $e) {
        SD::query('ROLLBACK');
        usleep( $microsleeptimer );
        $microsleeptimer *= 2;
        continue;
      }
      catch (SD_Timeout $e) {
        SD::query('ROLLBACK');
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
  SD::query('COMMIT;');
}


} // class
