<?php

$CONFIG = parse_ini_file(
  dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.ini', true
);

//Create a mongoDB 
$db = new MongoClient();
$collection = $db->beehub->files;

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
  $retval = array();
  $path = substr($iterator->getPath() . '/', 1);
  foreach($iterator as $fileinfo) {
    $file = $fileinfo->getPathname();
    if ( $fileinfo->isDot() ||
         false !== strpos( $fileinfo->getBasename(), '#' ) ) {
      continue;
    } elseif ( $fileinfo->isDir() ) {
      traverse( new DirectoryIterator( $file ) );
    }
    $attributes = xattr_list($file);
    $stored_props = array();
    foreach ($attributes as $attribute) {
      // Property names are already stored url encoded in extended attributes, but we just need it a few characters to be encoded.
      // This url encodes only the characters needed to create valid mongoDB keys. You can just run rawurldecode to decode it.
      $encodedKey = str_replace(
          array( '%'  , '$'  , '.'   ),
          array( '%25', '%24', '%2E' ),
          rawurldecode( $attribute )
      );
      $stored_props[ $encodedKey ] = xattr_get($file, $attribute);
    }
    $document = array(
        'path' => substr( $file, strlen( $CONFIG['environment']['datadir'] ) ),
        'props' => $stored_props );
    $collection->save( $document );
  }
}

// Traverse over the data directory so we get all files
traverse(new DirectoryIterator( $CONFIG['environment']['datadir'] ));