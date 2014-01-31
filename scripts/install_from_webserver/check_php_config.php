#!/usr/bin/php
<?php

print( "Checking PHP configuration:\n" );
$allGood = true;

function test_config( $key, $value ) {
  print( $key . ' should be ' . strval( $value ) . '...' );
  if ( ini_get( $key ) == $value ) {
    print( "ok\n" );
    return true;
  }else{
    print( 'WRONG (actual value: ' . strval( ini_get( $key ) ) . "\n" );
    return false;
  }
}

// Only check short_open_tag if we work with PHP versions before 5.4
$version = explode( '.', phpversion() );
if ( ( $version[0] < 5 ) || ( ( $version[0] == 5 ) && ( $version[1] < 4 ) ) ) {
  $allGood = test_config( 'short_open_tag', true ) || allGood;
}

if ( $allGood ) {
  exit( 0 );
}else{
  fwrite( STDERR, "Your PHP configuration is not correct.\n" );
  exit( 1 );
}
