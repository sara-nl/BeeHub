<?php

$CONFIG = parse_ini_file(
  dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.ini', true
);
chdir($CONFIG['environment']['datadir']);

$OWNERS = array();
$SPONSORS = array();

function traverse($iterator) {
  global $OWNERS, $SPONSORS;
  $retval = array();
  $path = substr($iterator->getPath() . '/', 1);
  foreach($iterator as $fileinfo) {
    if ( $fileinfo->isDot() ||
         false !== strpos( $fileinfo->getBasename(), '#' ) ) {
      continue;
    } elseif ( $fileinfo->isDir() ) {
      $tmp = traverse(new DirectoryIterator($fileinfo->getPathname()));
      foreach ($tmp as $owner => $array) {
        foreach ($array as $sponsor => $size) {
          @$retval["$owner"]["$sponsor"] += $size;
        }
      }
    } elseif ( $fileinfo->isFile() ) {
      $size = $fileinfo->getSize();
      $owner = xattr_get(
        $fileinfo->getPathname(),
        rawurlencode('DAV: owner')
      );
      $sponsor = xattr_get(
        $fileinfo->getPathname(),
        rawurlencode('http://beehub.nl/ sponsor')
      );
      if (!is_null($owner)) {
        @$retval["$owner"]["$sponsor"]   += $size;
        @$retval["$owner"]['*']          += $size;
        @$SPONSORS["$sponsor"]["$owner"] += $size;
      }
    }
  }
  foreach ($retval as $owner => $array) {
    foreach ($array as $sponsor => $size) {
      $OWNERS["$owner"]["$sponsor"]["$path"] = $size;
    }
  }
  return $retval;
}

$r = traverse(new DirectoryIterator( '.' ));
var_export($r);

var_export($SPONSORS);
var_export($OWNERS);
