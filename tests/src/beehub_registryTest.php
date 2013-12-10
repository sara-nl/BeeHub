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
class BeeHub_RegistryTest extends BeeHub_Tests_Db_Test_Case {

  public function setUp() {
    parent::setUp();
    reset_SERVER();
    $dirStructure = array(
        'directory' => array(
            'file.txt' => 'file contents'
        ),
        'system' => array(
            'groups' => array( 'foo' => '' ),
            'sponsors' => array( 'sponsor_a' => '' ),
            'users' => array( 'john' => '' ),
        )
    );
    \org\bovigo\vfs\vfsStream::setup( 'registry_test', null, $dirStructure );
    \BeeHub::$CONFIG['environment']['datadir'] = \org\bovigo\vfs\vfsStream::url( 'registry_test/' );
  }


  public function testResource() {
    $registry = \BeeHub_Registry::inst();
    $resourceFile = $registry->resource( '/directory/file.txt' );
    $this->assertInstanceOf( '\BeeHub_File', $resourceFile );

    $resourceDir = $registry->resource( '/directory/' );
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


//  public function shallowLock($write, $read) {
//    $whashes = $rhashes = array();
//    foreach ($write as $value)
//      $whashes[] = BeeHub_DB::escape_string(hash('sha256', $value, true));
//    foreach ($read as $value)
//      $rhashes[] = BeeHub_DB::escape_string(hash('sha256', $value, true));
//    sort($whashes, SORT_STRING);
//    sort($rhashes, SORT_STRING);
//    if (!empty($whashes)) {
//      BeeHub_DB::query(
//        'INSERT IGNORE INTO `shallowLocks` VALUES (' .
//        implode('),(', $whashes) . ');'
//      );
//      $whashes = implode(',', $whashes);
//      $whashes = "SELECT * FROM `shallowLocks` WHERE `pathhash` IN ($whashes) FOR UPDATE;";
//    }
//    else
//      $whashes = null;
//    if (!empty($rhashes)) {
//      BeeHub_DB::query(
//        'INSERT IGNORE INTO `shallowLocks` VALUES (' .
//        implode('),(', $rhashes) . ');'
//      );
//      $rhashes = implode(',', $rhashes);
//      $rhashes = "SELECT * FROM `shallowLocks` WHERE `pathhash` IN ($rhashes) LOCK IN SHARE MODE;";
//    }
//    else
//      $rhashes = null;
//    $microsleeptimer = 10000; // also functions as success flag
//    while ($microsleeptimer) {
//      if ($microsleeptimer > 1280000)
//        $microsleeptimer = 1280000;
//      BeeHub_DB::query('START TRANSACTION');
//      if ($whashes)
//        try {
//          BeeHub_DB::query($whashes)->free_result();
//        } catch (BeeHub_Deadlock $e) {
//          BeeHub_DB::query('ROLLBACK');
//          usleep($microsleeptimer);
//          $microsleeptimer *= 2;
//          continue;
//        } catch (BeeHub_Timeout $e) {
//          BeeHub_DB::query('ROLLBACK');
//          throw new DAV_Status(DAV::HTTP_SERVICE_UNAVAILABLE);
//        }
//      if ($rhashes)
//        try {
//          BeeHub_DB::query($rhashes)->free_result();
//        } catch (BeeHub_Deadlock $e) {
//          BeeHub_DB::query('ROLLBACK');
//          usleep($microsleeptimer);
//          $microsleeptimer *= 2;
//          continue;
//        } catch (BeeHub_Timeout $e) {
//          BeeHub_DB::query('ROLLBACK');
//          throw new DAV_Status(DAV::HTTP_SERVICE_UNAVAILABLE);
//        }
//      $microsleeptimer = 0;
//    }
//  }
//
//  /**
//   * @param array $write paths to write-lock.
//   * @param array $read paths to read-lock
//   */
//  public function shallowUnlock() {
//    BeeHub_DB::query('COMMIT;');
//  }

} // class BeeHub_RegistryTest