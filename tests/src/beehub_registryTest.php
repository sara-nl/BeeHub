<?php
/**
 * Contains tests for the class BeeHub_Registry
 *
 * Copyright ©2007-2013 SURFsara b.v., Amsterdam, The Netherlands
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
 * @package     BeeHub
 * @subpackage  tests
 */

declare( encoding = 'UTF-8' );
namespace BeeHub\tests;

/**
 * Tests for the class BeeHub_Registry
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_RegistryTest extends \PHPUnit_Framework_TestCase {

  public function setUp() {
    parent::setUp();
    setUp();
    if ( ! setUpStorageBackend() ) {
      $this->markTestSkipped( 'No storage backend specified; all tests depending on the storage backend are skipped' );
      return;
    }
  }


  public function testResource() {
    $registry = \BeeHub_Registry::inst();
    $resourceFile = $registry->resource( '/foo/file.txt' );
    $this->assertInstanceOf( '\BeeHub_File', $resourceFile );

    $resourceDir = $registry->resource( '/foo/' );
    $this->assertInstanceOf( '\BeeHub_Directory', $resourceDir );

    $resourceSystem = $registry->resource( '/system/' );
    $this->assertInstanceOf( '\BeeHub_System_Collection', $resourceSystem );

    $resourceUsers = $registry->resource( '/system/users/' );
    $this->assertInstanceOf( '\BeeHub_Users', $resourceUsers );

    $resourceUser = $registry->resource( '/system/users/john' );
    $this->assertInstanceOf( '\BeeHub_User', $resourceUser );

    $resourceGroups = $registry->resource( '/system/groups/' );
    $this->assertInstanceOf( '\BeeHub_Groups', $resourceGroups );

    $resourceGroup = $registry->resource( '/system/groups/foo' );
    $this->assertInstanceOf( '\BeeHub_Group', $resourceGroup );

    $resourceSponsors = $registry->resource( '/system/sponsors/' );
    $this->assertInstanceOf( '\BeeHub_Sponsors', $resourceSponsors );

    $resourceSponsor = $registry->resource( '/system/sponsors/sponsor_a' );
    $this->assertInstanceOf( '\BeeHub_Sponsor', $resourceSponsor );
  }


  public function testShallowReadLock() {
    declare( ticks = 1 ) {
      @\unlink( 'locksAreSet.deleteMe' );
      @\unlink( 'readLockDidNotHang.deleteMe' );
      @\unlink( 'allowWriteLocks.deleteMe' );
      @\unlink( 'writeLockHangsLongEnough.deleteMe' );
      @\unlink( 'writeLockSet.deleteMe' );
      $registry = \BeeHub_Registry::inst();
  
      $pid = pcntl_fork();
      if ($pid === -1) {
        $this->markTestSkipped( "Unable to fork process" );
        return;
      }
      \BeeHub_DB::forceReconnect();

      if ( $pid !== 0 ) {
        // This is the parent process. I could put this code after the if statement,
        // But now everything in the code is in the same (chronological) order as
        // how it is supposed to run.

        // Set a shallow read and write lock should just happen
        $timeBeforeLocks = time();
        $registry->shallowLock( array( '/foo/file.txt' ), array( '/foo/file2.txt' ) );
        $this->assertGreaterThanOrEqual( -1, $timeBeforeLocks - time() ); // Let's assert that it takes less than a second to set the locks, else something is seriously wrong

        \touch( 'locksAreSet.deleteMe' );
      } elseif ( $pid === 0 ) {
        // we are the child
        $counter = 0;  
        while( ! \file_exists( 'locksAreSet.deleteMe' ) ) {
          \sleep( 1 );
          if ( $counter++ > 10 ) {
            $this->assertTrue( false, 'Waited for 10 seconds for the initial locks to be set. This is really much much much too long' );
          }
        }

        $timeBeforeReadLock = \time();
        $registry->shallowLock( array(), array( '/foo/file2.txt' ) );
        if ( ( \time() - $timeBeforeReadLock ) <= 1 ) {
          \touch( 'readLockDidNotHang.deleteMe' ); // Let's assert that it takes less than a second to set the locks, else something is seriously wrong
        }

        // From another thread: Setting a shallow write lock on a resource with a read lock should hang until the read lock is released
        $registry->shallowLock( array( '/foo/file2.txt' ) );
        if ( \file_exists( 'allowWriteLocks.deleteMe' ) ) {
          \touch( 'writeLockHangsLongEnough.deleteMe' );
        }

        \touch( 'writeLockSet.deleteMe' );
        \BeeHub_DB::mysqli()->close();
        exit();
      }
      // Only the parent process will get to this

      // Then let's wait a second
      \sleep( 2 );
      \touch( 'allowWriteLocks.deleteMe' );
      $registry->shallowUnlock();

      $counter = 0;  
      while( ! \file_exists( 'writeLockSet.deleteMe' ) ) {
        \sleep( 1 );
        if ( $counter++ > 10 ) {
          $this->assertTrue( false, 'Waited for 10 seconds for the write lock to be set. This is really much much much too long' );
        }
      }
      
      $this->assertTrue( \file_exists( 'readLockDidNotHang.deleteMe' ), 'A shallow read lock can be set immediately on resources with just another shallow read lock' );
      $this->assertTrue( \file_exists( 'writeLockHangsLongEnough.deleteMe' ), 'A shallow write lock can not be set on resources with a shallow read lock' );
  
      @\unlink( 'locksAreSet.deleteMe' );
      @\unlink( 'readLockDidNotHang.deleteMe' );
      @\unlink( 'allowWriteLocks.deleteMe' );
      @\unlink( 'writeLockHangsLongEnough.deleteMe' );
      @\unlink( 'writeLockSet.deleteMe' );
    }
  }


  public function testShallowReadLockOnWriteLock() {
    declare( ticks = 1 ) {
      @\unlink( 'locksAreSet.deleteMe' );
      @\unlink( 'allowReadLocks.deleteMe' );
      @\unlink( 'readLockHangsLongEnough.deleteMe' );
      @\unlink( 'readLockSet.deleteMe' );
      $registry = \BeeHub_Registry::inst();
  
      $pid = pcntl_fork();
      if ($pid === -1) {
        $this->markTestSkipped( "Unable to fork process" );
        return;
      }
      \BeeHub_DB::forceReconnect();

      if ( $pid !== 0 ) {
        // This is the parent process. I could put this code after the if statement,
        // But now everything in the code is in the same (chronological) order as
        // how it is supposed to run.

        // Set a shallow read and write lock should just happen
        $timeBeforeLocks = time();
        $registry->shallowLock( array( '/foo/file.txt' ), array( '/foo/file2.txt' ) );
        $this->assertGreaterThanOrEqual( -1, $timeBeforeLocks - time() ); // Let's assert that it takes less than a second to set the locks, else something is seriously wrong

        \touch( 'locksAreSet.deleteMe' );
      } elseif ( $pid === 0 ) {
        // we are the child
        $counter = 0;  
        while( ! \file_exists( 'locksAreSet.deleteMe' ) ) {
          \sleep( 1 );
          if ( $counter++ > 10 ) {
            $this->assertTrue( false, 'Waited for 10 seconds for the initial locks to be set. This is really much much much too long' );
          }
        }

        // From another thread: Setting a shallow read lock on a resource with a write lock should hang until the write lock is released
        $registry->shallowLock( array(), array( '/foo/file.txt' ) );
        if ( \file_exists( 'allowReadLocks.deleteMe' ) ) {
          \touch( 'readLockHangsLongEnough.deleteMe' );
        }

        \touch( 'readLockSet.deleteMe' );
        \BeeHub_DB::mysqli()->close();
        exit();
      }
      // Only the parent process will get to this

      // Then let's wait a second
      \sleep( 2 );
      \touch( 'allowReadLocks.deleteMe' );
      $registry->shallowUnlock();

      $counter = 0;  
      while( ! \file_exists( 'readLockSet.deleteMe' ) ) {
        \sleep( 1 );
        if ( $counter++ > 10 ) {
          $this->assertTrue( false, 'Waited for 10 seconds for the read lock to be set. This is really much much much too long' );
        }
      }
      
      $this->assertTrue( \file_exists( 'readLockHangsLongEnough.deleteMe' ), 'A shallow read lock can not be set on resources with a shallow write lock' );
  
      @\unlink( 'locksAreSet.deleteMe' );
      @\unlink( 'allowReadLocks.deleteMe' );
      @\unlink( 'readLockHangsLongEnough.deleteMe' );
      @\unlink( 'readLockSet.deleteMe' );
    }
  }


  public function testShallowWriteLock() {
    declare( ticks = 1 ) {
      @\unlink( 'locksAreSet.deleteMe' );
      @\unlink( 'allowWriteLocks.deleteMe' );
      @\unlink( 'writeLockHangsLongEnough.deleteMe' );
      @\unlink( 'writeLockSet.deleteMe' );
      $registry = \BeeHub_Registry::inst();
  
      $pid = pcntl_fork();
      if ($pid === -1) {
        $this->markTestSkipped( "Unable to fork process" );
        return;
      }
      \BeeHub_DB::forceReconnect();

      if ( $pid !== 0 ) {
        // This is the parent process. I could put this code after the if statement,
        // But now everything in the code is in the same (chronological) order as
        // how it is supposed to run.

        // Set a shallow read and write lock should just happen
        $timeBeforeLocks = time();
        $registry->shallowLock( array( '/foo/file.txt' ), array( '/foo/file2.txt' ) );
        $this->assertGreaterThanOrEqual( -1, $timeBeforeLocks - time() ); // Let's assert that it takes less than a second to set the locks, else something is seriously wrong

        \touch( 'locksAreSet.deleteMe' );
      } elseif ( $pid === 0 ) {
        // we are the child
        $counter = 0;  
        while( ! \file_exists( 'locksAreSet.deleteMe' ) ) {
          \sleep( 1 );
          if ( $counter++ > 10 ) {
            $this->assertTrue( false, 'Waited for 10 seconds for the initial locks to be set. This is really much much much too long' );
          }
        }
    
        // From another thread: Setting a shallow write lock on a resource with another write lock should hang until the other write lock is released
        $registry->shallowLock( array( '/foo/file.txt' ) );
        if ( \file_exists( 'allowWriteLocks.deleteMe' ) ) {
          \touch( 'writeLockHangsLongEnough.deleteMe' );
        }

        \touch( 'writeLockSet.deleteMe' );
        \BeeHub_DB::mysqli()->close();
        exit();
      }
      // Only the parent process will get to this

      // Then let's wait a second
      \sleep( 2 );
      \touch( 'allowWriteLocks.deleteMe' );
      $registry->shallowUnlock();

      $counter = 0;  
      while( ! \file_exists( 'writeLockSet.deleteMe' ) ) {
        \sleep( 1 );
        if ( $counter++ > 10 ) {
          $this->assertTrue( false, 'Waited for 10 seconds for the write lock to be set. This is really much much much too long' );
        }
      }
      
      $this->assertTrue( \file_exists( 'writeLockHangsLongEnough.deleteMe' ), 'A shallow write lock can not be set on resources with another shallow write lock' );
  
      @\unlink( 'locksAreSet.deleteMe' );
      @\unlink( 'allowWriteLocks.deleteMe' );
      @\unlink( 'writeLockHangsLongEnough.deleteMe' );
      @\unlink( 'writeLockSet.deleteMe' );
    }
  }

} // class BeeHub_RegistryTest

// End of file
