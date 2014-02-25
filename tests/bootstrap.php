<?php
/**
 * Sets up an environment to emulate a webserver environment
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

require_once( \dirname( __FILE__ ) . \DIRECTORY_SEPARATOR . 'environment_building.php' );
reset_SERVER();

// Load dependencies
function loadMocks() {
  $mockPath = \realpath( \dirname( __FILE__ ) ) . \DIRECTORY_SEPARATOR . 'mocks' . \DIRECTORY_SEPARATOR;
  $dir = new \DirectoryIterator( $mockPath );
  foreach ( $dir as $file ) {
    if ( ( substr( $file, 0, 1 ) !== '.' ) && ( substr( $file, -4 ) === '.php' ) ) {
      require_once( $mockPath . $file );
    }
  }
}
loadMocks();

require_once( \dirname( __DIR__ ) . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'autoload.php' );
require_once( __DIR__ . \DIRECTORY_SEPARATOR . 'dbtests_data' . \DIRECTORY_SEPARATOR . 'db_testcase.php' );
require_once( \dirname( __DIR__ ) . \DIRECTORY_SEPARATOR . 'src' . \DIRECTORY_SEPARATOR . 'beehub_bootstrap.php' );
\DAV::$testMode = true;

// Check for configuration file
loadTestConfig();
if ( \BeeHub_DB::createDbTables() === false ) {
  die( 'Unable to create database structure' );
}


/**
 * Because we can't be sure we're using PHP 5.4 or higher, we can't use traits.
 * Instead, we use this global function to do the general setup for tests
 *
 * @return  void
 */
function setUp() {
  reset_SERVER();
  \DAV::$REGISTRY = new \BeeHub_Registry();
  \DAV::$LOCKPROVIDER = new \BeeHub_Lock_Provider();
  \DAV::$ACLPROVIDER  = new \BeeHub_ACL_Provider();
  \BeeHub::setAuth( new BeeHub_Auth( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
}

// End of file
