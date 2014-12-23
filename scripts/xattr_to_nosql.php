#!/usr/bin/env php
<?php

require_once( dirname( __DIR__ ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'beehub_bootstrap.php' );
mb_internal_encoding( 'UTF-8' );

$CONFIG = BeeHub::config();

//Create a mongoDB 
$db = BeeHub::getNoSQL();
$collection = $db->files;
$collection->remove();
$collection->ensureIndex( array( 'props.http://beehub%2Enl/ sponsor' => 1 ) );
$collection->ensureIndex( array( 'props.DAV: owner' => 1 ) );
$collection->ensureIndex( array( 'depth' => 1 ) );
$collection->ensureIndex( array( 'path' => 1 ), array( 'unique' => 1 ) );
$locksCollection = $db->locks;
$locksCollection->ensureIndex( array( 'path' => 1 ), array( 'unique' => 1 ) );

/**
 * Traverse over the files and subdirectories
 * 
 * @global  MongoCollection    $collection  The MongoDB collection
 * @global  Array              $CONFIG      The configuration parameters
 * @param   DirectoryIterator  $iterator    The DirectoryIterator to iterate over
 * @return  void
 */
function traverse($iterator) {
  global $collection, $CONFIG;
  foreach($iterator as $fileinfo) {
    $file = $fileinfo->getPathname();
    if ( $fileinfo->isDot() ) {
      continue;
    } elseif ( $fileinfo->isDir() ) {
      traverse( new DirectoryIterator( $file ) );
    }
    $attributes = xattr_list($file);
    $stored_props = array();
    if ( ! $fileinfo->isDir() ) {
      $encodedKey = str_replace(
        array( '%'  , '$'  , '.'   ),
        array( '%25', '%24', '%2E' ),
        DAV::PROP_GETCONTENTLENGTH
      );
      $stored_props[ $encodedKey ] = $fileinfo->getSize();
    }
    foreach ( $attributes as $attribute ) {
      $decodedKey = rawurldecode( $attribute );
      $value = xattr_get( $file, $attribute );
      
      // Transform the value of the owner and sponsor properties (but only if necessary)
      if ( ( ( $decodedKey === 'DAV: owner' ) ||
             ( $decodedKey === 'http://beehub.nl/ sponsor' ) ) && 
           ( substr( $value, 0, 1 ) === '/' ) ) {
        $value = rawurldecode( basename( $value ) );
      }
      
      // Property names are already stored url encoded in extended attributes, but we just need it a few characters to be encoded.
      // This url encodes only the characters needed to create valid mongoDB keys. You can just run rawurldecode to decode it.
      $encodedKey = str_replace(
          array( '%'  , '$'  , '.'   ),
          array( '%25', '%24', '%2E' ),
          $decodedKey
      );
      $stored_props[ $encodedKey ] = mb_convert_encoding( $value, 'UTF-8' );
    }
    $unslashifiedPath = \DAV::unslashify( substr( $file, strlen( $CONFIG['environment']['datadir'] ) ) );
    if ( substr( $unslashifiedPath, 0, 1 ) === '/' ) {
      $unslashifiedPath = substr( $unslashifiedPath, 1 );
    }
    if ( $unslashifiedPath === '' ) {
      $depth = 0;
    }else{
      $depth = substr_count( $unslashifiedPath, '/' ) + 1;
    }
    $document = array(
        'path' => mb_convert_encoding( $unslashifiedPath, 'UTF-8' ),
        'depth' => $depth,
        'props' => $stored_props );
    if ( $fileinfo->isDir() ) {
      $document['collection'] = true;
    }
    $collection->save( $document );
  }
}

// Traverse over the data directory so we get all files
traverse( new DirectoryIterator( $CONFIG['environment']['datadir'] ) );
