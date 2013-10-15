<?php
if ( ( substr( $_SERVER['REMOTE_ADDR'], 0, strlen( BeeHub::$CONFIG['environment']['trusted_lan'] ) ) !== BeeHub::$CONFIG['environment']['trusted_lan'] ) ||
     ( ( $_GET['client'] !== 'new_one' ) &&
       ( ( $_COOKIE['client'] === 'old_one' ) ||
         ( $_GET['client'] === 'old_one' )
       )
     )
   ) {
  setcookie ( 'client', 'old_one', 0, '/' );
  require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'old_client.php' );
}else{
  setcookie ( 'client', 'new_one', 0, '/' );
  require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'new_client.php' );
}

// End of file
