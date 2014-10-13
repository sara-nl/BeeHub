#!/usr/bin/env php
<?php
// Prepare the configuration and database connection
require_once '../src/beehub_bootstrap.php';

$CONFIG = BeeHub::config();

$mysqli = new mysqli(
        $CONFIG['mysql']['host'],
        $CONFIG['mysql']['username'],
        $CONFIG['mysql']['password'],
        $CONFIG['mysql']['database']
      );
$mysqli->set_charset( 'utf8' );
mb_internal_encoding( 'UTF-8' );
$db = BeeHub::getNoSQL();

// We need to do almost the same for all users, groups and sponsors ('things')
$name = $displayname = null;
foreach( array( 'user', 'group', 'sponsor' ) as $thing ) {
  $collection = $db->selectCollection( $thing . 's' );
  $collection->remove();
  
  // Select all 'things'
  $resultset = $mysqli->query(
    "SELECT   *
     FROM     `beehub_{$thing}s`",
    MYSQLI_STORE_RESULT
  );

  // We need to add each thing individualy
  while ( $row = $resultset->fetch_assoc() ) {
    $principal = array();
    // Let's filter out NULL values and rename some keys for a better fit
    foreach ( $row as $key => $value ) {
      if ( !is_null($value) ) {
        if ( $key === $thing . '_name' ) {
          $key = 'name';
        }
        if ( ( $thing === 'user' ) && ( $key === 'sponsor_name' ) ) {
          $key = 'default_sponsor';
          if ( substr( $value, 0, 1 ) === '/' ) { // This has changed from full path to just sponsor name. Now we make sure it is just sponsor name!
            $value = rawurldecode( basename( $value ) );
          }
        }
        $principal[$key] = mb_convert_encoding( $value, 'UTF-8' );
      }
    }
    
    // Convert verification and password_reset expirations. And while we're at it, check for (and unset) expired codes.
    if ( isset( $principal['verification_expiration'] ) ) {
      $principal['verification_expiration'] = strtotime( $principal['verification_expiration'] );
      if ( $principal['verification_expiration'] <= time() ) {
        unset( $principal['verification_code'], $principal['verification_expiration'] );
      }
    }
    if ( isset( $principal['password_reset_expiration'] ) ) {
      $principal['password_reset_expiration'] = strtotime( $principal['password_reset_expiration'] );
      if ( $principal['password_reset_expiration'] <= time() ) {
        unset( $principal['password_reset_code'], $principal['password_reset_expiration'] );
      }
    }
    
    // And fetch group and/or sponsor memberships
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
            $principal[ $subthing . 's' ][] = mb_convert_encoding( $membership[$subthing . '_name'], 'UTF-8' );
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
        
        // Determine the type of membership, as they will now get stored in separate arrays
        while ( $membership = $membershipset->fetch_assoc() ) {
          if ( $membership['is_admin'] === '1' ) {
            $admins[] = mb_convert_encoding( $membership['user_name'], 'UTF-8' );
          } elseif ( ( ( @$membership['is_invited'] === '1' ) && ( @$membership['is_requested'] === '1' ) ) || ( @$membership['is_accepted'] === '1' ) ) {
            $members[] = mb_convert_encoding( $membership['user_name'], 'UTF-8' );
          } elseif ( ( @$membership['is_invited'] === '1' ) && ( @$membership['is_requested'] === '0' ) ) {
            $admin_accepted_memberships[] = mb_convert_encoding( $membership['user_name'], 'UTF-8' );
          } elseif ( ( ( @$membership['is_invited'] === '0' ) && ( @$membership['is_requested'] === '1' ) ) || ( @$membership['is_accepted'] === '0' ) ) {
            $user_accepted_memberships[] = mb_convert_encoding( $membership['user_name'], 'UTF-8' );
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

// And we need to find the next ETag value, which is the next 'auto increment' value of that table
$result = $mysqli->query("SHOW TABLE STATUS LIKE 'ETag'");
$row = $result->fetch_array();
$nextId = $row['Auto_increment'];
$result->free();

$systemCollection = $db->selectCollection( 'beehub_system' );
$systemCollection->remove( array( 'name' => 'etag' ) );
$systemCollection->save( array(
    'name' => 'etag',
    'counter' => intval( $nextId ) )
);

$mysqli->close();
