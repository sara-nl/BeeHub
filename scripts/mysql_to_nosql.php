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
$db = $mongo->beehub;

$name = $displayname = null;
foreach( array( 'user', 'group', 'sponsor' ) as $thing ) {
  $collection = $db->__get($thing . 's');
  $resultset = $mysqli->query(
    "SELECT   *
     FROM     `beehub_{$thing}s`",
    MYSQLI_STORE_RESULT
  );

  while ( $row = $resultset->fetch_assoc() ) {
    $principal = array();
    foreach ( $row as $key => $value ) {
      if ( !is_null($value) ) {
        if ( $key === $thing . '_name' ) {
          $key = 'name';
        }
        if ( ( $thing === 'user' ) && ( $key === 'sponsor_name' ) ) {
          $key = 'default_sponsor';
        }
        $principal[$key] = $value;
      }
    }
    
    //And fetch group and/or sponsor memberships
    switch ($thing) {
      case 'user':
        foreach( array('group', 'sponsor') as $subthing ) {
          if ( $subthing === 'group' ) {
            $membershipset = $mysqli->query(
              "SELECT   `group_name`
               FROM     `beehub_group_members`
               WHERE    `user_name` = '" . $mysqli->escape_string( $principal[ 'name' ] ) . "'
               AND      `is_invited` = 1
               AND      `is_requested` = 1",
              MYSQLI_STORE_RESULT
            );
          }else{
            $membershipset = $mysqli->query(
              "SELECT   `sponsor_name`
               FROM     `beehub_sponsor_members`
               WHERE    `user_name` = '" . $mysqli->escape_string( $principal[ 'name' ] ) . "'
               AND      `is_accepted` = 1",
              MYSQLI_STORE_RESULT
            );
          }
          $principal[ $subthing . 's' ] = array();
          while ( $membership = $membershipset->fetch_assoc() ) {
            $principal[ $subthing . 's' ][] = $membership[$subthing . '_name'];
          }
        }
      break;
      case 'group':
      case 'sponsor':
        $membershipset = $mysqli->query(
          "SELECT   *
           FROM     `beehub_" . $thing . "_members`
           WHERE    `" . $thing . "_name` = '" . $mysqli->escape_string( $principal[ 'name' ] ) . "'",
          MYSQLI_STORE_RESULT
        );
        $admins = array();
        $members = array();
        $admin_accepted_memberships = array();
        $user_accepted_memberships = array();
        while ( $membership = $membershipset->fetch_assoc() ) {
          if ( $membership['is_admin'] === '1' ) {
            $admins[] = $membership['user_name'];
          } elseif ( ( ( @$membership['is_invited'] === '1' ) && ( @$membership['is_requested'] === '1' ) ) || ( @$membership['is_accepted'] === '1' ) ) {
            $members[] = $membership['user_name'];
          } elseif ( ( @$membership['is_invited'] === '1' ) && ( @$membership['is_requested'] === '0' ) ) {
            $admin_accepted_memberships[] = $membership['user_name'];
          } elseif ( ( ( @$membership['is_invited'] === '0' ) && ( @$membership['is_requested'] === '1' ) ) || ( @$membership['is_accepted'] === '0' ) ) {
            $user_accepted_memberships[] = $membership['user_name'];
          } else {
            trigger_error( 'Unknown type of membership: ' . var_dump( $membership ) );
          }
        }
        if ( count($admins) > 0 ) {
          $principal['admins'] = $admins;
        }
        if ( count($members) > 0 ) {
          $principal['members'] = $members;
        }
        if ( count($admin_accepted_memberships) > 0 ) {
          $principal['admin_accepted_memberships'] = $admin_accepted_memberships;
        }
        if ( count($user_accepted_memberships) > 0 ) {
          $principal['user_accepted_memberships'] = $user_accepted_memberships;
        }
      break;
    }
    
    $collection->save( $principal );
  }
}

$result = $mysqli->query("SHOW TABLE STATUS LIKE 'ETag'");
$row = $result->fetch_array();
$nextId = $row['Auto_increment'];
$result->free();

$systemCollection = $db->beehub_system;
$systemCollection->remove( array( 'name' => 'etag' ) );
$systemCollection->save( array(
    'name' => 'etag',
    'counter' => intval( $nextId ) )
);

$mysqli->close();