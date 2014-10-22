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

if ( isset( $_GET['POST_auth_code'] ) ) {
  print( \BeeHub::getAuth()->getPostAuthCode() );
  exit();
}elseif ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
  exit();
}

\ob_start();

print( "Checking PHP configuration:\n" );
$notGood = false;

// PHP should be version 5.4 or higher
$version = \explode( '.', \phpversion() );
print( 'PHP version should be > 5.4 ...' );
if ( ( $version[0] < 5 ) || ( ( $version[0] == 5 ) && ( $version[1] < 4 ) ) ) {
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

$config = \BeeHub::config();
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

// If we encountered an error, abort now!
if ( $notGood ) {
  \header( 'HTTP/1.1 500 Internal Server Error' );
  \ob_end_flush();
  print( "Your PHP configuration is not correct.\n" );
  exit();
}

try {
  $db = \BeeHub::getNoSQL();
}catch ( DAV_Status $exception ) {
  \header( 'HTTP/1.1 500 Internal Server Error' );
  \ob_end_flush();
  print( "\nFailed to connect to MongoDB\n" );
  exit();
}

$collections = $db->listCollections();
if ( \count( $collections ) > 0 ) {
  \header( 'HTTP/1.1 500 Internal Server Error' );
  \ob_end_flush();
  print( "MongoDB database already contains collections. Cannot initialise the database.\n" );
  exit();
}

$datadir = new \DirectoryIterator( $config['environment']['datadir'] );
$hasChildren = false;
foreach ( $datadir as $child ) {
  if ( ! $child->isDot() ) {
    $hasChildren = true;
    break;
  }
}
if ( $hasChildren ) {
  \header( 'HTTP/1.1 500 Internal Server Error' );
  \ob_end_flush();
  print( "The data directory already has content. Cannot initialise the data directory.\n" );
  exit();
}

// The configuration checks out, let's install stuff

// Import the database structure
print( "Creating database structure..." );

// Create users collection
// TODO: Check input!
$username = $_SERVER['PHP_AUTH_USER'];
$userEmail = $_POST['email'];
$usersCollection = $db->createCollection( 'users' );
$usersCollection->ensureIndex( array( 'name' => 1 ), array( 'unique' => 1 ) );
$usersCollection->insert(
  array(
    'name' => $username,
    'displayname' => 'Administrator',
    'email' => $userEmail,
    'password' => \crypt( $_SERVER['PHP_AUTH_PW'], '$6$rounds=5000$' . md5(time() . rand(0, 99999)) . '$'),
    'default_sponsor' => DEFAULT_SPONSOR_NAME
  )
);

// Create groups collection
$groupsCollection = $db->createCollection( 'groups' );
$groupsCollection->ensureIndex( array( 'name' => 1 ), array( 'unique' => 1 ) );
$groupsCollection->insert(
  array(
    'name' => \basename( $config['namespace']['admin_group'] ),
    'displayname' => ADMIN_GROUP_DISPLAYNAME,
    'description' => ADMIN_GROUP_DESCRIPTION
  )
);
$group = new \BeeHub_Group( $config['namespace']['admin_group'] );
$group->change_memberships( array( $username ), \BeeHub_Group::USER_ACCEPT );
$group->change_memberships( array( $username ), \BeeHub_Group::ADMIN_ACCEPT );
$group->change_memberships( array( $username ), \BeeHub_Group::SET_ADMIN );

// Create sponsors collection
$sponsorsCollection = $db->createCollection( 'sponsors' );
$sponsorsCollection->ensureIndex( array( 'name' => 1 ), array( 'unique' => 1 ) );
$sponsorsCollection->insert(
  array(
    'name' => DEFAULT_SPONSOR_NAME,
    'displayname' => DEFAULT_SPONSOR_DISPLAYNAME,
    'description' => DEFAULT_SPONSOR_DESCRIPTION
  )
);
$sponsor = new \BeeHub_Sponsor( \BeeHub::SPONSORS_PATH . DEFAULT_SPONSOR_NAME );
$sponsor->change_memberships( array( $username ), \BeeHub_Sponsor::ADMIN_ACCEPT );
$sponsor->change_memberships( array( $username ), \BeeHub_Sponsor::SET_ADMIN );

// Create the beehub_system collection
$systemCollection = $db->createCollection( 'beehub_system' );
$systemCollection->insert(
  array(
    'name' => 'etag',
    'counter' => 0
  )
);

// Create the files collection
$filesCollection = $db->createCollection( 'files' );
$filesCollection->ensureIndex( array( 'props.http://beehub%2Enl/ sponsor' => 1 ) );
$filesCollection->ensureIndex( array( 'props.DAV: owner' => 1 ) );
$filesCollection->ensureIndex( array( 'path' => 1 ), array( 'unique' => 1 ) );

// Done creating the database structure
print( "ok\n" );

// First initialise the datadir
print( "Initialising data directory..." );
$userdir = 'home' . \DIRECTORY_SEPARATOR . $username;
if (
  \mkdir( $config['environment']['datadir'] . 'system', 0770, true ) &&
  \mkdir( $config['environment']['datadir'] . 'home', 0770, true ) &&
  \mkdir( $config['environment']['datadir'] . $userdir, 0770 ) &&
  \mkdir( $config['environment']['datadir'] . \basename( $config['namespace']['admin_group'] ), 0770 ) &&
  \mkdir( $config['environment']['datadir'] . \BeeHub::GROUPS_PATH, 0770, true ) &&
  \mkdir( $config['environment']['datadir'] . \BeeHub::SPONSORS_PATH, 0770, true ) &&
  \mkdir( $config['environment']['datadir'] . \BeeHub::USERS_PATH, 0770, true )
){
  $sysDirs = array( 'system', 'home', \BeeHub::GROUPS_PATH, \BeeHub::SPONSORS_PATH, \BeeHub::USERS_PATH );
  foreach ( $sysDirs as $sysDir ) {
    $sysDir = \DAV::unslashify( $sysDir );
    if ( substr( $sysDir, 0, 1) === '/' ) {
      $sysDir = substr( $sysDir, 1 );
    }
    $fileDocument = array(
      'path' => $sysDir,
      'collection' => true,
      'props' => array()
    );
    $filesCollection->insert( $fileDocument );
  }

  // Add the user's home directory with different properties
  $fileDocument = array(
    'path' => \DAV::unslashify( $userdir ),
    'collection' => true,
    'props' => array(
      \DAV::PROP_OWNER => $username
    )
  );
  if ( substr( $fileDocument['path'], 0, 1) === '/' ) {
    $fileDocument['path'] = substr( $fileDocument['path'], 1 );
  }
  $encodedKey = str_replace(
    array( '%'  , '$'  , '.'   ),
    array( '%25', '%24', '%2E' ),
    \BeeHub::PROP_SPONSOR
  );
  $fileDocument['props'][ $encodedKey ] = DEFAULT_SPONSOR_NAME;
  $filesCollection->insert( $fileDocument );

  // Add the group directory with different properties
  $fileDocument = array(
    'path' => \DAV::unslashify( \basename( $config['namespace']['admin_group'] ) ),
    'collection' => true,
    'props' => array(
      \DAV::PROP_ACL => '[["' . $config['namespace']['admin_group'] . '",false,["DAV: read", "DAV: write"],false]]'
    )
  );
  if ( substr( $fileDocument['path'], 0, 1) === '/' ) {
    $fileDocument['path'] = substr( $fileDocument['path'], 1 );
  }
  $encodedKey = str_replace(
    array( '%'  , '$'  , '.'   ),
    array( '%25', '%24', '%2E' ),
    \BeeHub::PROP_SPONSOR
  );
  $fileDocument['props'][ $encodedKey ] = DEFAULT_SPONSOR_NAME;
  $filesCollection->insert( $fileDocument );
}else{
  \header( 'HTTP/1.1 500 Internal Server Error' );
  \ob_end_flush();
  print( "\nUnable to create the system directories\n" );
  exit();
}
print( "ok\n" );

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
