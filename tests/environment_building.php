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
  if ( empty( $config['environment']['datadir'] ) || ! is_dir( $config['environment']['datadir'] ) ) {
    return false;
  }

  // Remove everything in the datadir
  delTreeContents( $config['environment']['datadir'] );

  // Remove all extended attributes from the datadir
  $attributes = \xattr_list( $config['environment']['datadir'] );
  foreach ( $attributes as $attribute ) {
    \xattr_remove( $config['environment']['datadir'], $attribute );
  }

  // Set extended attributes and create a controlled environment
  $basePath = $config['environment']['datadir'] . \DIRECTORY_SEPARATOR;
  \umask( 0007 );
  \xattr_set( $config['environment']['datadir'], \rawurlencode( \DAV::PROP_OWNER ), $config['namespace']['wheel_path'] );
  \mkdir( $basePath . 'home' );
  \xattr_set( $basePath . 'home', \rawurlencode( \DAV::PROP_OWNER ), $config['namespace']['wheel_path'] );
  \mkdir( $basePath . 'home' . \DIRECTORY_SEPARATOR . 'john' );
  \xattr_set( $basePath . 'home' . \DIRECTORY_SEPARATOR . 'john', \rawurlencode( \DAV::PROP_OWNER ), '/system/users/john' );
  \xattr_set( $basePath . 'home' . \DIRECTORY_SEPARATOR . 'john', \rawurlencode( \BeeHub::PROP_SPONSOR ), '/system/sponsors/sponsor_a' );
  \mkdir( $basePath . 'home' . \DIRECTORY_SEPARATOR . 'johny' );
  \xattr_set( $basePath . 'home' . \DIRECTORY_SEPARATOR . 'johny', \rawurlencode( \DAV::PROP_OWNER ), '/system/users/johny' );
  \mkdir( $basePath . 'home' . \DIRECTORY_SEPARATOR . 'jane' );
  \xattr_set( $basePath . 'home' . \DIRECTORY_SEPARATOR . 'jane', \rawurlencode( \DAV::PROP_OWNER ), '/system/users/jane' );
  \mkdir( $basePath . 'foo' );
  \xattr_set( $basePath . 'foo', \rawurlencode( \DAV::PROP_OWNER ), $config['namespace']['wheel_path'] );
  \xattr_set( $basePath . 'foo', \rawurlencode( \DAV::PROP_ACL ), "[[\"/system/groups/foo\",false,[\"DAV: read\", \"DAV: write\"],false]]" );
  \xattr_set( $basePath . 'foo', \rawurlencode( \BeeHub::PROP_SPONSOR ), '/system/sponsors/sponsor_a' );
  \file_put_contents( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file.txt', 'Some contents of this file' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file.txt', \rawurlencode( \DAV::PROP_ACL ), "[[\"/system/groups/bar\",false,[\"DAV: read\"],false]]" );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file.txt', \rawurlencode( \DAV::PROP_OWNER ), '/system/users/john' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file.txt', \rawurlencode( \BeeHub::PROP_SPONSOR ), '/system/sponsors/sponsor_a' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file.txt', \rawurlencode( \DAV::PROP_GETCONTENTTYPE ), "text/plain; charset=UTF-8" );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file.txt', \rawurlencode( \DAV::PROP_GETETAG ), '"EA"' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file.txt', \rawurlencode( 'test_namespace test_property' ), 'this is a random dead property' );
  \touch( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file.txt', 1388576096 );
  \file_put_contents( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file2.txt', 'Lorem ipsum' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file2.txt', \rawurlencode( \DAV::PROP_OWNER ), '/system/users/john' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file2.txt', \rawurlencode( \BeeHub::PROP_SPONSOR ), '/system/sponsors/sponsor_a' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file2.txt', \rawurlencode( \DAV::PROP_GETCONTENTTYPE ), "text/plain; charset=UTF-8" );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'file2.txt', \rawurlencode( \DAV::PROP_GETETAG ), '"IA"' );
  \mkdir( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'directory' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'directory', \rawurlencode( \DAV::PROP_ACL ), "[[\"/system/users/johny\",false,[\"DAV: read\"],true]]" );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'directory', \rawurlencode( \DAV::PROP_OWNER ), '/system/users/john' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'directory', \rawurlencode( \BeeHub::PROP_SPONSOR ), '/system/sponsors/sponsor_a' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'directory', \rawurlencode( 'test_namespace test_property' ), 'this is a random dead property' );
  \mkdir( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'directory2' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'directory2', \rawurlencode( \DAV::PROP_OWNER ), '/system/users/john' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'directory2', \rawurlencode( \BeeHub::PROP_SPONSOR ), '/system/sponsors/sponsor_a' );
  \mkdir( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests', \rawurlencode( \DAV::PROP_ACL ), "[[\"/system/groups/foo\",false,[\"DAV: write\",\"DAV: write-acl\"],true],[\"DAV: authenticated\",false,[\"DAV: read\"],false],[\"DAV: all\",false,[\"DAV: read\"],false]]" );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests', \rawurlencode( \DAV::PROP_OWNER ), '/system/users/john' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests', \rawurlencode( \BeeHub::PROP_SPONSOR ), '/system/sponsors/sponsor_a' );
  \file_put_contents( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file.txt', 'Some contents of this file' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file.txt', \rawurlencode( \DAV::PROP_ACL ), "[[\"/system/groups/bar\",false,[\"DAV: read\"],false]]" );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file.txt', \rawurlencode( \DAV::PROP_OWNER ), '/system/users/john' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file.txt', \rawurlencode( \BeeHub::PROP_SPONSOR ), '/system/sponsors/sponsor_a' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file.txt', \rawurlencode( \DAV::PROP_GETCONTENTTYPE ), "text/plain; charset=UTF-8" );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file.txt', \rawurlencode( \DAV::PROP_GETETAG ), '"EA"' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file.txt', \rawurlencode( 'test_namespace test_property' ), 'this is a random dead property' );
  \touch( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file.txt', 1388576096 );
  \file_put_contents( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file2.txt', 'Lorem ipsum' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file2.txt', \rawurlencode( \DAV::PROP_OWNER ), '/system/users/john' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file2.txt', \rawurlencode( \BeeHub::PROP_SPONSOR ), '/system/sponsors/sponsor_a' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file2.txt', \rawurlencode( \DAV::PROP_GETCONTENTTYPE ), "text/plain; charset=UTF-8" );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'file2.txt', \rawurlencode( \DAV::PROP_GETETAG ), '"IA"' );
  \mkdir( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'directory' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'directory', \rawurlencode( \DAV::PROP_ACL ), "[[\"/system/users/johny\",false,[\"DAV: read\"],true]]" );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'directory', \rawurlencode( \DAV::PROP_OWNER ), '/system/users/john' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'directory', \rawurlencode( \BeeHub::PROP_SPONSOR ), '/system/sponsors/sponsor_a' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'directory', \rawurlencode( 'test_namespace test_property' ), 'this is a random dead property' );
  \mkdir( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'directory2' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'directory2', \rawurlencode( \DAV::PROP_OWNER ), '/system/users/john' );
  \xattr_set( $basePath . 'foo' . \DIRECTORY_SEPARATOR . 'client_tests' . \DIRECTORY_SEPARATOR . 'directory2', \rawurlencode( \BeeHub::PROP_SPONSOR ), '/system/sponsors/sponsor_a' );
  \mkdir( $basePath . 'bar' );
  \xattr_set( $basePath . 'bar', \rawurlencode( \DAV::PROP_OWNER ), $config['namespace']['wheel_path'] );
  \xattr_set( $basePath . 'bar', \rawurlencode( \DAV::PROP_ACL ), "[[\"/system/groups/bar\",false,[\"DAV: read\", \"DAV: write\"],false]]" );
  \xattr_set( $basePath . 'bar', \rawurlencode( \BeeHub::PROP_SPONSOR ), '/system/sponsors/sponsor_b' );
  \mkdir( $basePath . 'system' );
  \xattr_set( $basePath . 'system', \rawurlencode( \DAV::PROP_OWNER ), $config['namespace']['wheel_path'] );
  \mkdir( $basePath . 'system' . \DIRECTORY_SEPARATOR . 'groups' );
  \xattr_set( $basePath . 'system' . \DIRECTORY_SEPARATOR . 'groups', \rawurlencode( \DAV::PROP_OWNER ), $config['namespace']['wheel_path'] );
  \mkdir( $basePath . 'system' . \DIRECTORY_SEPARATOR . 'sponsors' );
  \xattr_set( $basePath . 'system' . \DIRECTORY_SEPARATOR . 'sponsors', \rawurlencode( \DAV::PROP_OWNER ), $config['namespace']['wheel_path'] );
  \mkdir( $basePath . 'system' . \DIRECTORY_SEPARATOR . 'users' );
  \xattr_set( $basePath . 'system' . \DIRECTORY_SEPARATOR . 'users', \rawurlencode( \DAV::PROP_OWNER ), $config['namespace']['wheel_path'] );

  // Return true to indicate everything went well
  return true;
}


function setUpDatabase() {
  $lines = \file( \dirname( __FILE__ ) . \DIRECTORY_SEPARATOR . 'dbtests_data' . \DIRECTORY_SEPARATOR . 'basicDataset.sql', \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES );
  $queries = array();
  $query = '';
  foreach ( $lines as $line ) {
    $query .= \rtrim( $line );
    if ( substr( $line, -1 ) === ';' ) {
      $queries[] = $query;
      $query = '';
    }else{
      $query .= ' ';
    }
  }
  foreach ( $queries as $query ) {
    \BeeHub_DB::mysqli()->query( $query );
  }
}


function loadTestConfig() {
  $configFile = \dirname( __FILE__ ) . \DIRECTORY_SEPARATOR . 'config.ini';
  if ( !\file_exists( $configFile ) ) {
    print( 'No configuration file exists. Please copy ' . \dirname( \dirname( __FILE__ ) ) . \DIRECTORY_SEPARATOR . 'config_example.ini to ' . $configFile . ' and edit it to set the right configuration options\n' );
    die( 1 );
  }
  \BeeHub::loadConfig( $configFile );
}

// End of file
