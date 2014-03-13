#!/usr/bin/php
<?php
/**
 * Install SimpleSamlPHP
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
 * @subpackage  scripts
 */

if ( ( $argc > 1 ) && ( ( $argv[1] === "-h" ) || ( $argv[1] === "--help" ) ) ) {
  print( "Install SimpleSamlPHP\n" );
  print( "Usage: " . $argv[0] . "\n" );
  exit( 0 );
}

$path = dirname( __DIR__ ) . DIRECTORY_SEPARATOR;

// Load config
$beehubConfig = parse_ini_file( $path . 'config.ini', true );

# Link to simplesamlphp
if ( substr( $beehubConfig['environment']['simplesamlphp'], -1 ) !== DIRECTORY_SEPARATOR ) {
  $beehubConfig['environment']['simplesamlphp'] .= DIRECTORY_SEPARATOR;
}
@unlink( 'public' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'simplesaml' );
if ( false === symlink( $beehubConfig['environment']['simplesamlphp'] . 'www', $path . 'public' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'simplesaml' ) ) {
  print( "Unable to link simpleSAMLphp to BeeHub. Please check that this location exists:\n" );
  print( $beehubConfig['environment']['simplesamlphp'] . "www\n" );
  print( "And that this script is able to create a link here:\n" );
  print( $path . 'public' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . "simplesaml\n" );
  print( "After fixing the problem, please rerun this script:\n" );
  print( $argv[0] . "\n" );
  exit( 1 );
}

exit( 0 );
