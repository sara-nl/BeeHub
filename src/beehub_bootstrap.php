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
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR .'webdav-php' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php' );

set_exception_handler( array( 'BeeHub', 'exception_handler' ) );

// We need SimpleSamlPHP
require_once( BeeHub::$CONFIG['environment']['simplesamlphp_autoloader'] );

DAV::$PROTECTED_PROPERTIES[ DAV::PROP_GROUP_MEMBER_SET ] = true;
DAV::$ACL_PROPERTIES[BeeHub::PROP_SPONSOR] = 'sponsor';
DAV::addSupported_Properties( BeeHub::PROP_SPONSOR, 'sponsor' );

BeeHub::handle_method_spoofing();

DAV::$REGISTRY     = BeeHub_Registry::inst();
DAV::$LOCKPROVIDER = BeeHub_Lock_Provider::inst();
DAV::$ACLPROVIDER  = BeeHub_ACL_Provider::inst();
DAV::$UNAUTHORIZED = array( BeeHub::getAuth(), 'unauthorized' );

if ( ( APPLICATION_ENV === BeeHub::ENVIRONMENT_TEST ) && isset( $_GET['test'] ) ) {
  define( 'RUN_CLIENT_TESTS', true );
}else{
  define( 'RUN_CLIENT_TESTS', false );
}

// If we want to run the client tests, load the test configuration and reset the storage backend (of the test environment)
if ( APPLICATION_ENV === BeeHub::ENVIRONMENT_TEST ) {
  require_once( dirname( dirname ( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'environment_building.php' );
  \BeeHub\tests\loadTestConfig();
  \BeeHub\tests\setUpStorageBackend();
}

// End of file
