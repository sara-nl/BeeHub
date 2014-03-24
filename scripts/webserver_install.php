<?php
/**
 * Checks the webserver configuration and installs several parts
 *
 * Copyright ©2014 SURFsara b.v., Amsterdam, The Netherlands
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
 */

namespace nl\surfsara\beehub\install;

\define( 'nl\surfsara\beehub\install\DEFAULT_SPONSOR_NAME', 'e-infra' );
\define( 'nl\surfsara\beehub\install\DEFAULT_SPONSOR_DISPLAYNAME', 'e-Infra' );
\define( 'nl\surfsara\beehub\install\DEFAULT_SPONSOR_DESCRIPTION', 'e-Infra supports the development and hosting of BeeHub. For now, all BeeHub users are sponsored by e-Infra' );
\define( 'nl\surfsara\beehub\install\ADMIN_GROUP_DISPLAYNAME', 'Administrators' );
\define( 'nl\surfsara\beehub\install\ADMIN_GROUP_DESCRIPTION', 'Administrators can manage BeeHub' );

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
  exit();
}

\ob_start();

print( "Checking PHP configuration:\n" );
$notGood = false;

// PHP should be version 5.3 or higher
$version = \explode( '.', \phpversion() );
print( 'PHP version should be >= 5.3 ...' );
if ( ( $version[0] < 5 ) || ( ( $version[0] == 5 ) && ( $version[1] < 3 ) ) ) {
  print( 'WRONG (actual value: ' . \phpversion() . "\n" );
  $notGood = true;
}else{
  print( "ok\n" );
}

print( "/system/js/server/principals.js should be writable by the webserver..." );
if ( \file_put_contents( \dirname( __DIR__ ) . \DIRECTORY_SEPARATOR . 'public' . \DIRECTORY_SEPARATOR . 'system' . \DIRECTORY_SEPARATOR . 'js' . \DIRECTORY_SEPARATOR . 'server' . \DIRECTORY_SEPARATOR . 'principals.js', 'some contents' ) === false ) {
  print( "WRONG\n" );
  $notGood = true;
}else{
  print( "ok\n" );
}

print( "The configured data directory should be writable by the webserver..." );
if ( empty( $config['environment']['datadir'] ) ) {
  $tempfile = false;
}else{
  $tempfile = \tempnam ( $config['environment']['datadir'], 'deleteMe_' );
}
if ( $tempfile === false ) {
  print( "WRONG\n" );
  $notGood = true;
}else{
  \unlink( $tempfile );
  print( "ok\n" );
}

// short_open_tags should only be true for PHP 5.3, as of 5.4 <?= is always enabled
if ( ( $version[0] == 5 ) && ( $version[1] < 4 ) ) {
  $notGood = !test_config( 'short_open_tag', true ) || $notGood;
}

// If we encountered an error, abort now!
if ( $notGood ) {
  \header( 'HTTP/1.1 500 Internal Server Error' );
  \ob_end_flush();
  print( "Your PHP configuration is not correct.\n" );
  exit();
}

// The configuration checks out, let's install stuff

// First initialise the datadir
$config = \BeeHub::config();
$datadir = new \DirectoryIterator( $config['environment']['datadir'] );
$hasChildren = false;
foreach ( $datadir as $child ) {
  if ( ! $child->isDot() ) {
    $hasChildren = true;
    break;
  }
}

if ( $hasChildren ) {
  print( "The data directory already has content. Skipping initialisation of data directory.\n" );
}else{
  print( "Initialising data directory..." );
  if ( ! (
    \mkdir( $config['environment']['datadir'] . 'home', 0770, true ) &&
    \mkdir( $config['environment']['datadir'] . 'system', 0770, true ) &&
    \mkdir( $config['environment']['datadir'] . \BeeHub::GROUPS_PATH, 0770, true ) &&
    \mkdir( $config['environment']['datadir'] . \BeeHub::SPONSORS_PATH, 0770, true ) &&
    \mkdir( $config['environment']['datadir'] . \BeeHub::USERS_PATH, 0770, true )
  ) ) {
    \header( 'HTTP/1.1 500 Internal Server Error' );
    \ob_end_flush();
    print( "\nUnable to create the system directories\n" );
    exit();
  }
  print( "ok\n" );
}

// Then import the database structure
$mysql = \BeeHub_DB::mysqli();
if ( $mysql->connect_errno ) {
  \header( 'HTTP/1.1 500 Internal Server Error' );
  \ob_end_flush();
  print( "\nFailed to connect to MySQL: (" . $mysql->connect_errno . ") " . $mysql->connect_error . "\n" );
  exit();
}

$result = $mysql->query( 'SHOW TABLES' );
if ( $result->num_rows > 0 ) {
  print( "MySQL database already contains tables. Skipping initialisation of database.\n" );
}else{
  print( "Creating database structure..." );
  if ( \BeeHub_DB::createDbTables() === false ) {
    \header( 'HTTP/1.1 500 Internal Server Error' );
    \ob_end_flush();
    print( "\nUnable to create database structure\n" );
    exit();
  }
  print( "ok\n" );

  // And for now; the e-infra sponsor
  $mysql->real_query( 'INSERT INTO `beehub_sponsors` ( `sponsor_name`, `displayname`, `description` ) VALUES ( \'' . DEFAULT_SPONSOR_NAME . '\', \'' . DEFAULT_SPONSOR_DISPLAYNAME . '\', \'' . DEFAULT_SPONSOR_DESCRIPTION . '\' );' );

  // Add the administrator group
  $config = \BeeHub::config();
  $mysql->real_query( 'INSERT INTO `beehub_groups` ( `group_name`, `displayname`, `description` ) VALUES ( \'' . \basename( $config['namespace']['admin_group'] ) . '\', \'' . ADMIN_GROUP_DISPLAYNAME . '\', \'' . ADMIN_GROUP_DESCRIPTION . '\' );' );

  // Add a real user
  $userStatement = $mysql->prepare( 'INSERT INTO `beehub_users` ( `user_name`, `displayname`, `email`, `password` ) VALUES ( ?, ?, ?, ? );' );
  $username = $_SERVER['PHP_AUTH_USER'];
  $email = $_POST['email'];
  $password = \crypt( $_SERVER['PHP_AUTH_PW'], '$6$rounds=5000$' . md5(time() . rand(0, 99999)) . '$');
  $userStatement->bind_param( 'ssss', $username, $username, $email, $password );
  $userStatement->execute();
  $userStatement->close();
  $sponsor = new \BeeHub_Sponsor( \BeeHub::SPONSORS_PATH . DEFAULT_SPONSOR_NAME );
  $sponsor->change_memberships( array( $username ), true, true );
  $adminGroup = new \BeeHub_Group( $config['namespace']['admin_group'] );
  $adminGroup->change_memberships( array( $username ), true, true, true );
  $userdir = $config['environment']['datadir'] . 'home' . \DIRECTORY_SEPARATOR . $username;
  \mkdir( $userdir, 0770 );
  \xattr_set( $userdir, \rawurlencode( \DAV::PROP_OWNER ), \BeeHub::USERS_PATH . \rawurlencode( $username ) );
  \xattr_set( $userdir, \rawurlencode( \BeeHub::PROP_SPONSOR ), \BeeHub::SPONSORS_PATH . \rawurlencode( DEFAULT_SPONSOR_NAME ) );
}

// Create principals.js with displaynames of all principals
\BeeHub_Principal::update_principals_json();

// Let 'them' know everything went well
print( "\nDone configuring webserver\n" );
\ob_end_flush();

/**
 * Checks whether a PHP configuration value is correct
 * 
 * @param   string  $key    The configuration item to check
 * @param   mixed   $value  The value it should have
 * @return  boolean         True if the configuration is correct, false otherwise
 */
function test_config( $key, $value ) {
  print( $key . ' should be ' . \strval( $value ) . '...' );
  if ( \ini_get( $key ) == $value ) {
    print( "ok\n" );
    return true;
  }else{
    print( 'WRONG (actual value: ' . \strval( \ini_get( $key ) ) . " )\n" );
    return false;
  }
}
