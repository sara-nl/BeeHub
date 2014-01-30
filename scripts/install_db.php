#!/usr/bin/php
<?php
/**
 * Installs the BeeHub tables in mySQL
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
 * @subpackage  scripts
 */

if ( ( $argc > 1 ) && ( ( $argv[1] === "-h" ) || ( $argv[1] === "--help" ) ) ) {
  print( "Installs the BeeHub tables in mySQL\n\n" );
  print( "Usage: " . $argv[0] . "\n" );
  exit( 0 );
}

$path = dirname( __DIR__ ) . DIRECTORY_SEPARATOR;

// Load config and try to connect to mySQL
$config = parse_ini_file( $path . 'config.ini', true );
$mysql = new mysqli( $config['mysql']['host'], $config['mysql']['username'], $config['mysql']['password'], $config['mysql']['database'] );
if ( $mysql->connect_error ) {
  print( "Could not connect to the database! Please check your configuration in " . $path . "config.ini\n" );
  $resetDB = 'n';
}else{
  $resetDB = null;
}

// Ask if the user really wants to do this (unless we already established we can not open the database connection)
while ( is_null( $resetDB ) ) {
  print( "This will remove the BeeHub tables and install new tables. YOU LOSE ALL INFORMATION IN THE DATABASE! Are you sure you want to continue (y/n)?\n" );
  $resetDB = strtolower( fread( STDIN, 1 ) );
}
if ( $resetDB === "n" ) {
  print( 'If mySQL is not configured yet or you want to reset the database run ' . $argv[0] . ' again after specifying the mySQL credentials in ' . $path . "config.ini\n" );
  exit( 0 );
}

// First import the database structure
$query = '';
$filePointer = fopen( 'db' . DIRECTORY_SEPARATOR . 'db_structure.sql', 'r' );
while ( ( $line = fgets( $filePointer ) ) !== false ) {
  if ( substr( $line, 0, 2 ) === '--' ) {
    continue;
  }
  $query .= ' ' . trim( $line );
  if ( substr( $query, -1 ) === ';' ) {
    if ( $mysql->real_query( $query ) === false ) {
      print( "Unable to perform query:\n" );
      print( $query . "\n" );
      print( "Please try to fix the problem and run this script again to install a clean database: " . $argv[0] . "\n" );
      exit( 1 );
    }
    $query = '';
  }
}
fclose( $filePointer );

// Then add the administrator user
$wheelStatement = $mysql->prepare( 'INSERT INTO `beehub_users` ( `user_name`, `displayname`, `email` ) VALUES ( ?, \'Administrator\', ? );' ); 
$wheel = basename( $config['namespace']['wheel_path'] );
$email = $config['email']['sender_address'];
$wheelStatement->bind_param( 'ss', $wheel, $email );
$wheelStatement->execute();
$wheelStatement->close();

// And for now; the e-infra sponsor
$mysql->real_query( 'INSERT INTO `beehub_sponsors` ( `sponsor_name`, `displayname`, `description` ) VALUES ( \'e-infra\', \'e-Infra\', \'e-Infra supports the development and hosting of BeeHub. For now, all BeeHub users are sponsored by e-Infra\' );' );

$mysql->close();
exit( 0 );
