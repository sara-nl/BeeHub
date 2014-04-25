<?php
/**
 * Contains tests for the class BeeHub_Lock_Provider
 *
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
 *
 * @package     BeeHub
 * @subpackage  tests
 */

declare( encoding = 'UTF-8' );
namespace BeeHub\tests;

/**
 * Tests for the class BeeHub_Lock_Provider
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_Lock_ProviderTest extends BeeHub_Tests_Db_Test_Case {

  public function setUp() {
    parent::setUp();
    if ( ! setUpStorageBackend() ) {
      $this->markTestSkipped( 'No storage backend specified; all tests depending on the storage backend are skipped' );
      return;
    }

    $this->setCurrentUser( '/system/users/john' );
  }
  
  
  private function assertEqualLocks( $expectedLockroot, $expectedDepth, $expectedLocktoken, $expectedOwner, $expectedTimeoutDuration, $actual ) {
    $expectedTimeout = $expectedTimeoutDuration + time();
    $this->assertSame( $expectedLockroot, $actual->lockroot );
    $this->assertSame( $expectedDepth, $actual->depth );
    $this->assertSame( $expectedLocktoken, $actual->locktoken );
    $this->assertSame( $expectedOwner, $actual->owner );
    // A timeout may be off by 1 seconds because of the duration of the tests
    // (for the tests this is quite a large offset, but on the total duration of
    // a lock this will not be that much)
    $this->assertGreaterThanOrEqual( 0, $expectedTimeout - $actual->timeout );
    $this->assertGreaterThanOrEqual( -1, $actual->timeout - $expectedTimeout );
  }


  public function testSetAndGetLock() {
    $this->assertNull( \DAV::$LOCKPROVIDER->getlock( '/foo/' ) );
    $lockToken = \DAV::$LOCKPROVIDER->setlock( '/foo/', \DAV::DEPTH_0, 'phpUnit is the owner', array( 1 ) );
    
    $this->assertEqualLocks(
      '/foo/',
      \DAV::DEPTH_0,
      $lockToken,
      'phpUnit is the owner',
      1,
      \DAV::$LOCKPROVIDER->getlock( '/foo/' ) 
    );
    
    // Test if the timeout works as expected; the lock should exist for only 1
    // second, so after 2 seconds it should have disappeared
    \sleep( 2 );
    $this->assertNull( \DAV::$LOCKPROVIDER->getlock( '/foo/' ) );
  }
  
  
  public function testMemberLocks() {
    $this->assertSame( array(), \DAV::$LOCKPROVIDER->memberLocks( '/foo/' ) );
    $lockToken1 = \DAV::$LOCKPROVIDER->setlock( '/foo/file.txt', \DAV::DEPTH_0, 'phpUnit is the first owner', array( 1 ) );
    $lockToken2 = \DAV::$LOCKPROVIDER->setlock( '/foo/file2.txt', \DAV::DEPTH_0, 'phpUnit is the second owner', array( 10 ) );
    
    $memberLocks = \DAV::$LOCKPROVIDER->memberLocks( '/foo/' );
    foreach( $memberLocks as $lockToken => $lock ) {
      if ( $lockToken === $lockToken1 ) {
        $this->assertEqualLocks(
          '/foo/file.txt',
          \DAV::DEPTH_0,
          $lockToken1,
          'phpUnit is the first owner',
          1,
          $lock
        );
      }elseif ( $lockToken === $lockToken2 ){
        $this->assertEqualLocks(
          '/foo/file2.txt',
          \DAV::DEPTH_0,
          $lockToken2,
          'phpUnit is the second owner',
          10,
          $lock
        );
      }else{
        $this->assertTrue( false, 'Unexpected lock returned by BeeHub_Lock_Provider::memberLocks()' );
      }
    }
    
    // And of course, this method should also check the timeout
    \sleep( 2 );
    $memberLocksAfterTimeout = \DAV::$LOCKPROVIDER->memberLocks( '/foo/' );
    foreach( $memberLocksAfterTimeout as $lockToken => $lock ) {
      if ( $lockToken === $lockToken2 ){
        $this->assertEqualLocks(
          '/foo/file2.txt',
          \DAV::DEPTH_0,
          $lockToken2,
          'phpUnit is the second owner',
          8, // I've decreased this by 2 as we waited 2 seconds before.
          $lock
        );
      }else{
        $this->assertTrue( false, 'Unexpected lock returned by BeeHub_Lock_Provider::memberLocks()' );
      }
    }
  }


  public function testRefreshUnexistingLock() {
    $this->assertNull( \DAV::$LOCKPROVIDER->getlock( '/foo/' ) );
    $this->assertFalse( \DAV::$LOCKPROVIDER->refresh( '/foo/', 'Some invalid lock token', array( 10 ) ) );
  }


  public function testRefresh() {
    $this->assertNull( \DAV::$LOCKPROVIDER->getlock( '/foo/' ) );
    $lockToken = \DAV::$LOCKPROVIDER->setlock( '/foo/', \DAV::DEPTH_0, 'phpUnit is the owner', array( 1 ) );
    
    $this->assertEqualLocks(
      '/foo/',
      \DAV::DEPTH_0,
      $lockToken,
      'phpUnit is the owner',
      1,
      \DAV::$LOCKPROVIDER->getlock( '/foo/' ) 
    );
    
    $this->assertTrue( \DAV::$LOCKPROVIDER->refresh( '/foo/', $lockToken, array( 10 ) ) );
    $this->assertEqualLocks(
      '/foo/',
      \DAV::DEPTH_0,
      $lockToken,
      'phpUnit is the owner',
      10,
      \DAV::$LOCKPROVIDER->getlock( '/foo/' ) 
    );
    
    // But is should not work if the locktoken is wrong
    $this->assertFalse( \DAV::$LOCKPROVIDER->refresh( '/foo/', $lockToken . ' lock token is now invalid', array( 10 ) ) );
  }


  public function testRefreshExpiredLock() {
    $this->assertNull( \DAV::$LOCKPROVIDER->getlock( '/foo/' ) );
    $lockToken = \DAV::$LOCKPROVIDER->setlock( '/foo/', \DAV::DEPTH_0, 'phpUnit is the owner', array( 1 ) );
    
    $this->assertEqualLocks(
      '/foo/',
      \DAV::DEPTH_0,
      $lockToken,
      'phpUnit is the owner',
      1,
      \DAV::$LOCKPROVIDER->getlock( '/foo/' ) 
    );
    
    \sleep( 2 );
    $this->assertFalse( \DAV::$LOCKPROVIDER->refresh( '/foo/', $lockToken, array( 10 ) ) );
  }
  
  
  public function testUnlockUnexistingLock() {
    $this->assertNull( \DAV::$LOCKPROVIDER->getlock( '/foo/' ) );
    $this->assertFalse( \DAV::$LOCKPROVIDER->unlock( '/foo/' ) );
  }


  public function testUnlockExpiredLock() {
    $this->assertNull( \DAV::$LOCKPROVIDER->getlock( '/foo/' ) );
    \DAV::$LOCKPROVIDER->setlock( '/foo/', \DAV::DEPTH_0, 'phpUnit is the owner', array( 1 ) );
    \sleep( 2 );
    
    $this->assertFalse( \DAV::$LOCKPROVIDER->unlock( '/foo/' ) );
  }


  public function testUnlock() {
    $this->assertNull( \DAV::$LOCKPROVIDER->getlock( '/foo/' ) );
    \DAV::$LOCKPROVIDER->setlock( '/foo/', \DAV::DEPTH_0, 'phpUnit is the owner', array( 1 ) );
    $this->assertTrue( \DAV::$LOCKPROVIDER->unlock( '/foo/' ) );
  }

} // Class BeeHub_Lock_ProviderTest

// End of file