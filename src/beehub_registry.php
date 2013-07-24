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
  public function resource( $path ) {
    $path = DAV::unslashify( $path );
    $systemPath   = DAV::unslashify( BeeHub::SYSTEM_PATH   );
    $usersPath    = DAV::unslashify( BeeHub::USERS_PATH    );
    $groupsPath   = DAV::unslashify( BeeHub::GROUPS_PATH   );
    $sponsorsPath = DAV::unslashify( BeeHub::SPONSORS_PATH );
    if (isset($this->resourceCache[$path])) {
      return $this->resourceCache[$path];
    }
    $localPath = BeeHub::localPath($path);
    $retval = null;
    if ($path === $systemPath) {
      $retval = new BeeHub_System_Collection($path);
    } elseif ( substr( $path, 0, strlen( $usersPath ) ) === $usersPath ) {
      if ( $path === $usersPath )
        $retval = new BeeHub_Users($path);
      else {
        try {
          $retval = new BeeHub_User($path);
        } catch( Exception $e ) {}
      }
    }elseif(substr($path, 0, strlen($groupsPath)) === $groupsPath) {
      if ($path === $groupsPath)
        $retval = new BeeHub_Groups($path);
      else {
        try {
          $retval = new BeeHub_Group($path);
        }catch(Exception $e){}
      }
    }elseif(substr($path, 0, strlen($sponsorsPath)) === $sponsorsPath) {
      if ($path === $sponsorsPath) {
        $retval = new BeeHub_Sponsors($path);
      }else {
        try {
          $retval = new BeeHub_Sponsor($path);
        }catch(Exception $e){}
      }
    }elseif (is_dir($localPath)) {
      $retval = new BeeHub_Directory($path);
    }elseif (file_exists($localPath)) {
      $retval = new BeeHub_File($path);
    }
    return ( $this->resourceCache[$path] = $retval );
  }

  /**
   * @param string $path always unslashified!
   */
  public function forget($path) {
    unset($this->resourceCache[$path]);
  }
  
  
  private $lockerId = null;
          

  /**
   * @param array $write paths to write-lock.
   * @param array $read paths to read-lock
   */
  public function shallowLock($write, $read) {
    if ( is_null( $this->lockerId ) ) {
      $this->lockerId = uniqid( gethostname(), true );
    }
    
    // Prepare the lock (sub-)documents
    $lockDocument = array(
        'lockerId' => $this->lockerId,
        'time' => time(),
    );
    $lockDocuments = array(
        'write' => array( '$set' => array( 'shallowWriteLock' => $lockDocument ) ),
        'read' => array( '$set' => array( 'shallowReadLock' => $lockDocument ) ),
    );
    
    // Prepare the query documents
    $queryDocuments = array(
        'write' => array(
            'path' => '',
            'shallowWriteLock' => null,
            'shallowReadLock' => null,
        ),
        'read' => array(
            'path' => '',
            'shallowWriteLock' => null,
        ),
    );
    
    // Prepare for the database calls
    $returnFields = array( '_id' );
    $options = array(
        'new' => true,
        'upsert' => false,
    );
    $lockTypes = array( 'write', 'read' );
    $filesCollection = BeeHub::getNoSQL()->selectCollection( 'files' );
    
    // Make sure each path has a document in the database
    $upsertOptions = array( 'upsert' => true );
    foreach ( $lockTypes as $lockType ) {
      foreach ( ${$lockType} as $key => $path ) {
        $pathArray = array( 'path' => $path );
        $filesCollection->findAndModify(
            $pathArray,
            array( '$set' => $pathArray ),
            $returnFields,
            $upsertOptions
        );
      }
    }
    
    // Perform the modify action for all write locks
    $microsleeptimer = 10000;
    while ( true ) {
      // Try to set as much locks as possible
      foreach ( $lockTypes as $lockType ) {
        $pathsCopy = ${$lockType};
        foreach ( ${$lockType} as $key => $path ) {
          $queryDocuments[ $lockType ][ 'path' ] = $path;
          $result = $filesCollection->findAndModify(
              $queryDocuments[ $lockType ],
              $lockDocuments[ $lockType ],
              $returnFields,
              $options
          );

          // If it worked, remove this path from the list so it isn't tried again
          if ( count( $result ) > 0 ) {
            unset( $pathsCopy[ $key ] );
          }
        }
        ${$lockType} = $pathsCopy;
      }

      // If we still have locks to set, wait before trying again
      if ( ( count( $write ) > 0 ) || ( count( $read ) > 0 ) ) {
        usleep($microsleeptimer);
        // And increase the wait time each time to some maximum value
        if ($microsleeptimer >= 640000) {
          $microsleeptimer = 128000;
        }else{
          $microsleeptimer *= 2;
        }
      }else{
        // No more locks to set? Break out of this 'while ( true )' loop!
        break;
      }
    }
  }
  
  public function shallowUnlock() {
    // If no lockerId has been generated, there are no locks set, so nothing to unlock
    if ( is_null( $this->lockerId ) ) {
      return;
    }
    
    // Prepare the database calls
    $filesCollection = BeeHub::getNoSQL()->selectCollection( 'files' );
    $options = array(
        'upsert' => false,
        'multiple' => true,
    );
    
    // And loose all locks from this locker with two update calls
    $filesCollection->update(
        array( 'shallowWriteLock.lockerId' => $this->lockerId ),
        array( '$unset' => array( 'shallowWriteLock' => true ) ),
        $options
    );
    $filesCollection->update(
        array( 'shallowReadLock.lockerId' => $this->lockerId ),
        array( '$unset' => array( 'shallowReadLock' => true ) ),
        $options
    );
  }

} // class

// Because our shallow locks are not automatically deleted when the script ends, let's make sure shallowUnlock is always called!
register_shutdown_function( array( BeeHub_Registry::inst(), 'shallowUnlock' ) );