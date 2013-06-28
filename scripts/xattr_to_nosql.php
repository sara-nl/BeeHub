<?php

$CONFIG = parse_ini_file(
  dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.ini', true
);

$db = new Mongo();
$collection = $db->beehub->files;

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
print(substr( $file, strlen( $CONFIG['environment']['datadir'] ) ) . "\n" );
    $attributes = xattr_list($file);
    $stored_props = array();
    foreach ($attributes as $attribute) {
      $stored_props[rawurldecode($attribute)] = xattr_get($file, $attribute);
    }
    $document = array(
        'path' => substr( $file, strlen( $CONFIG['environment']['datadir'] ) ),
        'props' => $stored_props );
    $collection->save( $document );
  }
}

traverse(new DirectoryIterator( $CONFIG['environment']['datadir'] ));