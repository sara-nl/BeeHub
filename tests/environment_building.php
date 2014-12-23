<?php
/**
 * Contains several functions to build the test environment
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
 * Resets the $_SERVER super global to a fixed state
 *
 * @return  void
 */
function reset_SERVER() {
  $_SERVER = array();
  $_SERVER['HTTPS'] = true;
  $_SERVER['REQUEST_URI'] = '/';
  $_SERVER['SCRIPT_NAME'] = 'bootstrap.php'; // Strange enough, PHPunit seems to use this, so let's set it to some value
  $_SERVER['REQUEST_METHOD'] = 'GET';
  $_SERVER['PHP_AUTH_USER'] = 'user';
  $_SERVER['PHP_AUTH_PW'] = 'password';
  $_SERVER['HTTP_REFERER'] = 'http://www.example.org/';
  $_SERVER['SERVER_NAME'] = 'beehub.nl';
  $_SERVER['SERVER_PORT'] = 443;
  $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:24.0) Gecko/20100101 Firefox/24.0';
  $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
  $_SERVER['QUERY_STRING'] = '';
  $_SERVER['CONTENT_LENGTH'] = 100;
  $_SERVER['HTTP_X_EXPECTED_ENTITY_LENGTH'] = '100';
  $_SERVER['HTTP_DESTINATION'] = 'http://beehub.nl/destination';
  $_SERVER['HTTP_OVERWRITE'] = 'F';
  $_SERVER['HTTP_DEPTH'] = '0';
  $_SERVER['HTTP_RANGE'] = '';
  $_SERVER['HTTP_CONTENT_RANGE'] = '';
  $_SERVER['HTTP_TIMEOUT'] = '';
  $_SERVER['HTTP_IF'] = '';
  $_SERVER['HTTP_IF_MATCH'] = '';
  $_SERVER['HTTP_IF_UNMODIFIED_SINCE'] = '';
  $_SERVER['HTTP_IF_MODIFIED_SINCE'] = '';
  $_SERVER['HTTP_IF_UNMODIFIED_SINCE'] = '';
  $_SERVER['HTTP_IF_MATCH'] = '';
  $_SERVER['HTTP_IF_NONE_MATCH'] = '';
  $_SERVER['HTTP_CONTENT_LANGUAGE'] = 'en';
  $_SERVER['HTTP_LOCK_TOKEN'] = '';
  $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] = '';
  $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] = '';
  $_SERVER['HTTP_ORIGIN'] = '';
  $_SERVER['SERVER_PROTOCOL'] = 'https';
  $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

  $_COOKIE = array();
  $_FILES = array();
  $_GET = array();
  $_POST = array();
  $_REQUEST = array();
  $_SESSION = array();
}


/**
 * Delete a complete tree
 *
 * Adapted this function from the PHP documentation, currently to be found at
 * http://nl3.php.net/manual/en/function.rmdir.php#110489
 *
 * @return  boolean  True on success, false otherwise
 */
function delTreeContents( $dir ) {
   $files = \array_diff( \scandir( $dir ), array( '.', '..' ) );
    foreach ( $files as $file ) {
      $filePath = $dir . \DIRECTORY_SEPARATOR . $file;
      if ( \is_dir( $filePath ) ) {
        delTreeContents( $filePath );
        \rmdir( $filePath );
      }else{
        \unlink( $filePath );
      }
    }
  }


/**
 * Initialize the storage backend
 *
 * @return  boolean  False if there is no storage backend specified, true otherwise
 */
function setUpStorageBackend() {
  $config = \BeeHub::config();
  if ( empty( $config['environment']['datadir'] ) || ! \is_dir( $config['environment']['datadir'] ) || ! @\touch( $config['environment']['datadir'] . 'tempfile' ) ) {
    return false;
  }

  // Remove everything in the datadir
  delTreeContents( $config['environment']['datadir'] );

  // Set extended attributes and create a controlled environment
  $basePath = $config['environment']['datadir'];
  \umask( 0007 );
  \mkdir( $basePath . 'home' );
  \mkdir( $basePath . 'home' . \DIRECTORY_SEPARATOR . 'john' );
  \mkdir( $basePath . 'home' . \DIRECTORY_SEPARATOR . 'johny' );
  \mkdir( $basePath . 'home' . \DIRECTORY_SEPARATOR . 'jane' );
  \mkdir( $basePath . 'foo' );
  \file_put_contents( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file.txt', 'Some contents of this file' );
  \touch( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file.txt', 1388576096 );
  \file_put_contents( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file2.txt', 'Lorem ipsum' );
  \mkdir( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'directory' );
  \mkdir( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'directory2' );
  \mkdir( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' );
  \file_put_contents( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file.txt', 'Some contents of this file' );
  \touch( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file.txt', 1388576096 );
  \file_put_contents( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file2.txt', 'Lorem ipsum' );
  \mkdir( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'directory' );
  \mkdir( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'directory2' );
  \mkdir( $basePath . 'bar' );
  \mkdir( $basePath . 'system' );
  \mkdir( $basePath . 'system' . \DIRECTORY_SEPARATOR . 'groups' );
  \mkdir( $basePath . 'system' . \DIRECTORY_SEPARATOR . 'sponsors' );
  \mkdir( $basePath . 'system' . \DIRECTORY_SEPARATOR . 'users' );

  // Environment for system tests
  \mkdir( $basePath . 'denyAll' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowRead' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowWrite' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowWriteAcl' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowReadWrite' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowReadWriteAcl' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowWriteWriteAcl' );
  \mkdir( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowReadDir' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowReadDir' . \DIRECTORY_SEPARATOR . 'resource' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowReadDir' . \DIRECTORY_SEPARATOR . 'allowWrite' );
  \mkdir( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowWriteDir' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowWriteDir' . \DIRECTORY_SEPARATOR . 'resource' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowWriteDir' . \DIRECTORY_SEPARATOR . 'allowRead' );
  \mkdir( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowReadWriteDir' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowReadWriteDir' . \DIRECTORY_SEPARATOR . 'resource' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowReadWriteDir' . \DIRECTORY_SEPARATOR . 'denyRead' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowReadWriteDir' . \DIRECTORY_SEPARATOR . 'denyWrite' );
  \mkdir( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowWriteAclDir' );
  \touch( $basePath . 'denyAll' . \DIRECTORY_SEPARATOR . 'allowWriteAclDir' . \DIRECTORY_SEPARATOR . 'resource' );
  \mkdir( $basePath . 'allowAll' );
  \touch( $basePath . 'allowAll' . \DIRECTORY_SEPARATOR . 'denyRead' );
  \touch( $basePath . 'allowAll' . \DIRECTORY_SEPARATOR . 'denyWrite' );
  \touch( $basePath . 'allowAll' . \DIRECTORY_SEPARATOR . 'denyWriteAcl' );
  \mkdir( $basePath . 'allowAll' . \DIRECTORY_SEPARATOR . 'denyReadDir' );
  \touch( $basePath . 'allowAll' . \DIRECTORY_SEPARATOR . 'denyReadDir' . \DIRECTORY_SEPARATOR . 'resource' );
  \mkdir( $basePath . 'allowAll' . \DIRECTORY_SEPARATOR . 'denyWriteDir' );
  \touch( $basePath . 'allowAll' . \DIRECTORY_SEPARATOR . 'denyWriteDir' . \DIRECTORY_SEPARATOR . 'resource' );
  \touch( $basePath . 'allowAll' . \DIRECTORY_SEPARATOR . 'denyWriteDir' . \DIRECTORY_SEPARATOR . 'allowWrite' );
  \mkdir( $basePath . 'allowAll' . \DIRECTORY_SEPARATOR . 'denyWriteAclDir' );
  \touch( $basePath . 'allowAll' . \DIRECTORY_SEPARATOR . 'denyWriteAclDir' . \DIRECTORY_SEPARATOR . 'resource' );

  // Return true to indicate everything went well
  return true;
}


function setUpDatabase() {
  $db = \BeeHub::getNoSQL();
  $collections = $db->listCollections();
  foreach ($collections as $collection) {
    $collection->drop();
  }

  $newCollections = \json_decode( \file_get_contents( \dirname( __FILE__ ) . \DIRECTORY_SEPARATOR . 'dbtests_data' . \DIRECTORY_SEPARATOR . 'basicDataset.json' ), true );
  foreach ( $newCollections as $collectionName => $documents ) {
    $collection = $db->createCollection( $collectionName );
    $collection->batchInsert( $documents );
  }

  $filesCollection = $db->selectCollection( 'files' );
  $filesCollection->ensureIndex( array( 'props.http://beehub%2Enl/ sponsor' => 1 ) );
  $filesCollection->ensureIndex( array( 'props.DAV: owner' => 1 ) );
  $filesCollection->ensureIndex( array( 'path' => 1 ), array( 'unique' => 1 ) );
  $locksCollection = $db->selectCollection( 'locks' );
  $locksCollection->ensureIndex( array( 'path' => 1 ), array( 'unique' => 1 ) );
  $groupsCollection = $db->selectCollection( 'groups' );
  $groupsCollection->ensureIndex( array( 'name' => 1 ), array( 'unique' => 1 ) );
  $sponsorsCollection = $db->selectCollection( 'sponsors' );
  $sponsorsCollection->ensureIndex( array( 'name' => 1 ), array( 'unique' => 1 ) );
  $usersCollection = $db->selectCollection( 'users' );
  $usersCollection->ensureIndex( array( 'name' => 1 ), array( 'unique' => 1 ) );

  \BeeHub_Principal::update_principals_json();
}


function loadTestConfig() {
  $configFile = \dirname( __FILE__ ) . \DIRECTORY_SEPARATOR . 'config.ini';
  if ( !\file_exists( $configFile ) ) {
    print( 'No configuration file exists. Please copy ' . \dirname(  __DIR__ ) . \DIRECTORY_SEPARATOR . 'config_example.ini to ' . $configFile . " and edit it to set the right configuration options\n" );
    die( 1 );
  }
  \BeeHub::loadConfig( $configFile );
  \BeeHub::changeConfigField( 'namespace', 'admin_group', '/system/groups/admin' );
}

// End of file
