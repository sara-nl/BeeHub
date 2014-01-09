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


  // TODO: To test shallow locks, we need a multi threaded test

} // class BeeHub_RegistryTest

// End of file