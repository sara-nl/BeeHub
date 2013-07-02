<?php

$CONFIG = parse_ini_file(
  dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.ini', true
);

$mysqli = new mysqli(
        $CONFIG['mysql']['host'],
        $CONFIG['mysql']['username'],
        $CONFIG['mysql']['password'],
        $CONFIG['mysql']['database']
      );
$mongo = new Mongo();
$collection = $mongo->beehub->principals;

$name = $displayname = null;
foreach( array( 'user', 'group', 'sponsor' ) as $thing ) {
  $resultset = $mysqli->query(
    "SELECT   *
     FROM     `beehub_{$thing}s`",
    MYSQLI_STORE_RESULT
  );
  while ( $row = $resultset->fetch_assoc() ) {
    $principal = array( 'type' => $thing );
    foreach ( $row as $key => $value ) {
      if ( !is_null($value) ) {
        $principal[$key] = $value;
      }
    }
    
    //And fetch group and/or sponsor memberships
    switch ($thing) {
      case 'user':
        foreach( array('group', 'sponsor') as $subthing ) {
          $membershipset = $mysqli->query(
            "SELECT   `" . $subthing . "_name`
             FROM     `beehub_" . $subthing . "_members`
             WHERE    `user_name` = '" . $mysqli->escape_string( $principal[ 'user_name' ] ) . "'",
            MYSQLI_STORE_RESULT
          );
          $principal[ $subthing . 's' ] = array();
          while ( $membership = $membershipset->fetch_assoc() ) {
            unset( $membership['user_name'] );
            $principal[ $subthing . 's' ][] = $membership[$subthing . '_name'];
          }
        }
      break;
      case 'group':
      case 'sponsor':
        $membershipset = $mysqli->query(
          "SELECT   *
           FROM     `beehub_" . $thing . "_members`
           WHERE    `" . $thing . "_name` = '" . $mysqli->escape_string( $principal[ $thing . '_name' ] ) . "'",
          MYSQLI_STORE_RESULT
        );
        $principal[ 'members' ] = array();
        while ( $membership = $membershipset->fetch_assoc() ) {
          unset( $membership[$thing . '_name'] );
          $principal[ 'members' ][] = $membership;
        }
      break;
    }
    
    $collection->save( $principal );
  }
}

$mysqli->close();