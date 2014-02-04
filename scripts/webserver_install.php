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
  print( "Your PHP configuration is not correct.\n" );
  exit();
}

// The configuration checks out, let's install stuff

// First import the database structure
$mysql = \BeeHub_DB::mysqli();

$result = $mysql->query( 'SHOW TABLES' );
if ( $result->num_rows > 0 ) {
  print( "MySQL database already contains tables. Skipping initialisation of database.\n" );
}else{
  print( "Creating database structure..." );
  $query = '';
  $filePointer = \fopen( \dirname( __DIR__ ) . \DIRECTORY_SEPARATOR . 'db' . \DIRECTORY_SEPARATOR . 'db_structure.sql', 'r' );
  while ( ( $line = \fgets( $filePointer ) ) !== false ) {
    if ( \substr( $line, 0, 2 ) === '--' ) {
      continue;
    }
    $query .= ' ' . \trim( $line );
    if ( \substr( $query, -1 ) === ';' ) {
      if ( $mysql->real_query( $query ) === false ) {
        \header( 'HTTP/1.1 500 Internal Server Error' );
        print( "\nUnable to create database structure\n" );
        exit();
      }
      $query = '';
    }
  }
  \fclose( $filePointer );
  print( "ok\n" );

  // Then add the administrator user
  $config = \BeeHub::config();
  $wheelStatement = $mysql->prepare( 'INSERT INTO `beehub_users` ( `user_name`, `displayname`, `email` ) VALUES ( ?, \'Administrator\', ? );' );
  $wheel = \basename( $config['namespace']['wheel_path'] );
  $email = $config['email']['sender_address'];
  $wheelStatement->bind_param( 'ss', $wheel, $email );
  $wheelStatement->execute();
  $wheelStatement->close();

  // And for now; the e-infra sponsor
  $mysql->real_query( 'INSERT INTO `beehub_sponsors` ( `sponsor_name`, `displayname`, `description` ) VALUES ( \'' . DEFAULT_SPONSOR_NAME . '\', \'' . DEFAULT_SPONSOR_DISPLAYNAME . '\', \'' . DEFAULT_SPONSOR_DESCRIPTION . '\' );' );
}

// Create principals.js with displaynames of all principals
\BeeHub_Principal::update_principals_json();

// Then initialise the datadir
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
  if (
    \mkdir( $config['environment']['datadir'] . 'home', 0770, true ) &&
    \mkdir( $config['environment']['datadir'] . 'system' . \DIRECTORY_SEPARATOR . 'groups', 0770, true ) &&
    \mkdir( $config['environment']['datadir'] . 'system' . \DIRECTORY_SEPARATOR . 'sponsors', 0770, true ) &&
    \mkdir( $config['environment']['datadir'] . 'system' . \DIRECTORY_SEPARATOR . 'users', 0770, true )
  ){
    $prop = rawurlencode( \DAV::PROP_OWNER );
    $value = $config['namespace']['wheel_path'];
    \xattr_set( $config['environment']['datadir'], $prop, $value );
    \xattr_set( $config['environment']['datadir'] . 'home', $prop, $value );
    \xattr_set( $config['environment']['datadir'] . 'system', $prop, $value );
    \xattr_set( $config['environment']['datadir'] . 'system' . \DIRECTORY_SEPARATOR . 'groups', $prop, $value );
    \xattr_set( $config['environment']['datadir'] . 'system' . \DIRECTORY_SEPARATOR . 'sponsors', $prop, $value );
    \xattr_set( $config['environment']['datadir'] . 'system' . \DIRECTORY_SEPARATOR . 'users', $prop, $value );
  }else{
    \header( 'HTTP/1.1 500 Internal Server Error' );
    print( "\nUnable to create the system directories\n" );
    exit();
  }
  print( "ok\n" );
}


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
    print( 'WRONG (actual value: ' . \strval( \ini_get( $key ) ) . "\n" );
    return false;
  }
}