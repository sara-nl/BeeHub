<?php
/**
 * Sets up an environment to emulate a webserver environment
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
}
reset_SERVER();


/**
 * Returns an array with the parsed configuration file
 *
 * @param   boolean  $parse  If set to true, the configuration file will be parsed, even if we have a parsed version in cache
 * @return  array            The configuration.
 * @see     parse_ini_file
 */
function getConfig( $parse = false ) {
  static $config = null;
  if ( $parse || \is_null( $config ) ) {
    $config = \parse_ini_file( \dirname( __FILE__ ) . \DIRECTORY_SEPARATOR . 'config.ini', true );
  }
  return $config;
}


// Check for configuration file
if ( !\file_exists( \dirname( __FILE__ ) . \DIRECTORY_SEPARATOR . 'config.ini' ) ) {
  print( "No configuration file exists. Please copy ' . \dirname( __FILE__ ) . \DIRECTORY_SEPARATOR . 'config_example.ini to ' . \dirname( __FILE__ ) . \DIRECTORY_SEPARATOR . 'config.ini and edit it to set the right configuration options\n" );
  die( 1 );
}

// Load dependencies
require_once( \dirname( \dirname( __FILE__ ) ) . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'autoload.php' );
require_once( \dirname( __FILE__ ) . \DIRECTORY_SEPARATOR . 'dbtests_data' . \DIRECTORY_SEPARATOR . 'db_testcase.php' );
require_once( \dirname( \dirname( __FILE__ ) ) . \DIRECTORY_SEPARATOR . 'webdav-php' . \DIRECTORY_SEPARATOR . 'tests' . \DIRECTORY_SEPARATOR . 'mocks' . \DIRECTORY_SEPARATOR . 'dav_cache.php' );
\spl_autoload_register('\spl_autoload');
require_once( \dirname( \dirname( __FILE__ ) ) . '/src/beehub_bootstrap.php' );
\DAV::$testMode = true;

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

// End of file