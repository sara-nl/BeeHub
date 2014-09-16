<?php
/**
 * Bootstrap; set up the environment so dependecies are met, classes are accessible and requests can be handled
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
 * @package BeeHub
 */

// Prepare the environment: where is the configuration file and are we in development or production mode? Different values are defined in BeeHub::ENVIRONMENT_* constants
defined('APPLICATION_ENV') || define(
  'APPLICATION_ENV',
  ( getenv('APPLICATION_ENV') ? strtolower(getenv('APPLICATION_ENV')) : 'production' )
);
defined('ENT_HTML5') || define('ENT_HTML5', 0);

// Set the include path, so BeeHub* classes are automatically loaded
set_include_path(
  realpath( dirname( dirname(__FILE__) ) ) . PATH_SEPARATOR .
  dirname(__FILE__) . PATH_SEPARATOR .
  get_include_path()
);

require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR .'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' );

DAV::bootstrap();
set_exception_handler( array( 'BeeHub', 'exception_handler' ) );

// We need SimpleSamlPHP
require_once( BeeHub::$CONFIG['environment']['simplesamlphp'] . 'lib' . DIRECTORY_SEPARATOR . '_autoload.php' );

if ( isset( $_SERVER['HTTP_ORIGIN'] ) && !empty( $_SERVER['HTTP_ORIGIN'] ) && ( parse_url( $_SERVER['HTTP_ORIGIN'], PHP_URL_HOST ) != $_SERVER['SERVER_NAME'] ) ) {
  die( 'Cross Origin Resourc Sharing prohibited!' );
}

DAV::$PROTECTED_PROPERTIES[ DAV::PROP_GROUP_MEMBER_SET ] = true;
DAV::$ACL_PROPERTIES[BeeHub::PROP_SPONSOR] = 'sponsor';
DAV::addSupported_Properties( BeeHub::PROP_SPONSOR, 'sponsor' );

BeeHub::handle_method_spoofing();

DAV::$REGISTRY     = BeeHub_Registry::inst();
DAV::$LOCKPROVIDER = BeeHub_Lock_Provider::inst();
DAV::$ACLPROVIDER  = BeeHub_ACL_Provider::inst();
DAV::$UNAUTHORIZED = array( BeeHub::getAuth(), 'unauthorized' );

// In case of POST requests, we can already check the POST authentication code
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
  if ( ! BeeHub::getAuth()->checkPostAuthCode() ) {
    throw new DAV_Status( DAV::HTTP_FORBIDDEN, 'POST authentication code (POST_auth_code) was incorrect. The correct code can be obtained with a GET request to /system/?POST_auth_code' );
  }
}

// Prepare test environments if needed
if ( ( APPLICATION_ENV === BeeHub::ENVIRONMENT_TEST ) && isset( $_GET['test'] ) ) {
  if ( substr( $_SERVER['REQUEST_URI'], 0, 19 ) !== '/foo/client_tests/?' ) {
    header( 'Location: /foo/client_tests/?' . $_SERVER['QUERY_STRING'] );
    die();
  }
  define( 'RUN_CLIENT_TESTS', true );
}else{
  define( 'RUN_CLIENT_TESTS', false );
}

// If we want to run the client tests, load the test configuration and reset the storage backend (of the test environment)
if ( APPLICATION_ENV === BeeHub::ENVIRONMENT_TEST ) {
  require_once( dirname( dirname ( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'environment_building.php' );
  \BeeHub\tests\loadTestConfig();
  if ( \BeeHub_DB::createDbTables() === false ) {
    die( 'Unable to create database structure' );
  }
  \BeeHub\tests\setUpDatabase();
  \BeeHub\tests\setUpStorageBackend();
  $_SERVER['PHP_AUTH_USER'] = 'john';
  $_SERVER['PHP_AUTH_PW'] = 'password_of_john';
}

// End of file
