<?php

/*·************************************************************************
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
    if ( is_null( self::$inst ) )
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
    if ( $path === '/' ) {
      $retval = new BeeHub_Directory($path);
    }elseif ($path === $systemPath) {
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
    }else{$unslashifiedPath = $path;
      if ( substr( $unslashifiedPath, 0, 1 ) === '/' ) {
        $unslashifiedPath = substr( $unslashifiedPath, 1 );
      }
      $collection = BeeHub::getNoSQL()->files;
      $document = $collection->findOne( array( 'path' => $unslashifiedPath ));
      if ( ! is_null( $document ) ) {
        if ( isset( $document['collection'] ) && $document['collection'] ) {
          $retval = new BeeHub_Directory($path);
        }else {
          $retval = new BeeHub_File($path);
        }
      }
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
  private $readLockedPaths = array();
          

  /**
   * Puts shallow read and/or write locks on files
   * 
   * Resources with a shallow lock on it can only be modified in the same
   * request as they were placed and not by parallel requests (in different
   * server threads/processes). However, as soon as the request ends, the lock
   * will be released.
   * 
   * Write locks can not be set if there is already a read or write lock set.
   * Read locks can not be set if there is already a write lock set. There can
   * be multiple read locks on the same resource!
   * 
   * @param  array  $write  paths to write-lock, use an empty array to skip
   * @param  array  $read   paths to read-lock
   * @return  void
   */
  public function shallowLock( $write, $read = array() ) {
    if ( is_null( $this->lockerId ) ) {
      $this->lockerId = uniqid( gethostname(), true );
    }

    // Prepare the lock (sub-)documents
    $lockDocuments = array(
        'write' => array(
            '$set' => array(
                'shallowWriteLock' => array(
                    'lockerId' => $this->lockerId,
                    'time' => time(),
                )
            )
        ),
        'read' => array(
            '$inc' => array( 'shallowReadLock.counter' => 1 ),
            '$set' => array( 'shallowReadLock.lastest_lock' => time() ),
        ),
    );
    
    // Prepare the query documents
    $queryDocuments = array(
        'write' => array(
            'path' => '',
            'shallowWriteLock' => array( '$exists' => false ),
            '$or' => array (
                array( 'shallowReadLock' => array( '$exists' => false ) ),
                array( 'shallowReadLock.counter' => array( '$lt' => 1 ) ),
            ),
        ),
        'read' => array(
            'path' => '',
            'shallowWriteLock' => array( '$exists' => false ),
        ),
    );
    
    // Prepare for the database calls
    $returnFields = array( '_id' );
    $options = array(
        'new' => true,
        'upsert' => false,
    );
    $lockTypes = array( 'write' => array(), 'read' => array() );
    foreach ( $write as $path ) {
      if ( substr( $path, 0, 1 ) === '/' ) {
        $path = substr( $path, 1 );
      }
      $path = DAV::unslashify( $path );
      $lockTypes['write'][] = $path;
    }
    foreach ( $read as $path ) {
      if ( substr( $path, 0, 1 ) === '/' ) {
        $path = substr( $path, 1 );
      }
      $path = DAV::unslashify( $path );
      if ( ! in_array( $path, $this->readLockedPaths ) ) { // Do not set another read lock if we already have a read lock on this resource
        $lockTypes['read'][] = $path;
      }
    }
    $filesCollection = BeeHub::getNoSQL()->selectCollection( 'files' );

    // Make sure each path has a document in the database
    $upsertOptions = array( 'upsert' => true );
    foreach ( $lockTypes as $lockType => $paths ) {
      foreach ( $paths as $key => $path ) {
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
      foreach ( $lockTypes as $lockType => $paths ) {
        foreach ( $paths as $key => $path ) {
          $queryDocuments[ $lockType ][ 'path' ] = $path;
          $result = $filesCollection->findAndModify(
              $queryDocuments[ $lockType ],
              $lockDocuments[ $lockType ],
              $returnFields,
              $options
          );

          // If it worked, remove this path from the list so it isn't tried again
          if ( count( $result ) > 0 ) {
            if ( $lockType === 'read' ) {
              $this->readLockedPaths[] = $path;
            }
            unset( $paths[ $key ] );
            unset( $lockTypes[$lockType][ $key ] );
          }
        }
      }

      // If we still have locks to set, wait before trying again
      if ( ( count( $lockTypes['write'] ) > 0 ) || ( count( $lockTypes['read'] ) > 0 ) ) {
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

  /**
   * Releases all shallow locks set within this request
   */
  public function shallowUnlock() {
    // Prepare the database calls
    $filesCollection = BeeHub::getNoSQL()->selectCollection( 'files' );
    $options = array(
        'upsert' => false,
        'multiple' => true,
    );
    
    // And loose all locks from this locker with two update calls
    if ( ! is_null( $this->lockerId ) ) {
      $filesCollection->update(
          array( 'shallowWriteLock.lockerId' => $this->lockerId ),
          array( '$unset' => array( 'shallowWriteLock' => true ) ),
          $options
      );
      $this->lockerId = null;
    }
    if ( count( $this->readLockedPaths ) > 0 ) {
      $filesCollection->update(
          array( 'path' => array( '$in' => $this->readLockedPaths ), 'shallowReadLock.counter' => array( '$gt' => 0 ) ),
          array( '$inc' => array( 'shallowReadLock.counter' => -1 ) ),
          $options
      );
      $this->readLockedPaths = array();
    }
  }

} // class

// Because our shallow locks are not automatically deleted when the script ends, let's make sure shallowUnlock is always called!
register_shutdown_function( array( BeeHub_Registry::inst(), 'shallowUnlock' ) );
