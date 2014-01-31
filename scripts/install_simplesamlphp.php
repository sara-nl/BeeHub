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

// Load config and try to connect to mySQL
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

// Then load and change the simpleSAMLphp configuration
$configFile = $beehubConfig['environment']['simplesamlphp'] . 'config' . DIRECTORY_SEPARATOR . 'authsources.php';
require_once( $configFile );

$config['BeeHub'] = array(
  'saml:SP',
  'entityID' => NULL,
  'idp' => 'https://engine.surfconext.nl/authentication/idp/metadata',
  'discoURL' => NULL,
  'name' => array(
    'en' => 'BeeHub',
    'nl' => 'BeeHub'
  ),
  'description' => array(
    'en' => 'BeeHub is a data storage service enabling you to store and easily share data with others.',
    'nl' => 'BeeHub is een dienst om makkelijk je data op te slaan en te delen met anderen.'
  ),
  'OrganizationName' => array(
    'en' => 'SURFsara',
    'nl' => 'SURFsara'
  ),
  'OrganizationURL' => array(
    'en' => 'http://www.surfsara.nl/',
    'nl' => 'http://www.surfsara.nl/nl'
  ),
  'url' => array(
    'en' => 'http://beehub.nl/',
    'nl' => 'http://beehub.nl/'
  ),
  'attributes' => array(
    'urn:mace:dir:attribute-def:mail',
    'urn:mace:dir:attribute-def:displayName',
    'urn:mace:terena.org:attribute-def:schacHomeOrganization',
    'urn:mace:dir:attribute-def:eduPersonAffiliation'
  ),
  'attributes.required' => array(
    'urn:mace:dir:attribute-def:mail',
    'urn:mace:dir:attribute-def:displayName',
    'urn:mace:terena.org:attribute-def:schacHomeOrganization',
    'urn:mace:dir:attribute-def:eduPersonAffiliation'
  ),
  'acs.Bindings' => array (
    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
  ),
  'NameIDPolicy' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent'
);

file_put_contents( $configFile, '<?php' . "\n" . '$config = ' . var_export( $config, true ) . ';' );

// And load and add the SURFconext metadata
$metadataFile = $beehubConfig['environment']['simplesamlphp'] . 'metadata' . DIRECTORY_SEPARATOR . 'saml20-idp-remote.php';
$metadata = array (
  'name' => array(
    'en' => 'SURFconext',
  ),
  'SingleSignOnService' => 'https://engine.surfconext.nl/authentication/idp/single-sign-on',
  'certFingerprint'     => array('a36aac83b9a552b3dc724bfc0d7bba6283af5f8e')
);

file_put_contents( $metadataFile, "\n" . '$metadata[\'https://engine.surfconext.nl/authentication/idp/metadata\'] = ' . var_export( $metadata, true ) . ';', FILE_APPEND );

exit( 0 );
