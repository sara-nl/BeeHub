<?php
// Some bootstrapping
defined('APPLICATION_ENV') || define(
  'APPLICATION_ENV',
  ( getenv('APPLICATION_ENV') ? strtolower(getenv('APPLICATION_ENV')) : 'production' )
);
defined('ENT_HTML5') || define('ENT_HTML5', 0);
require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'beehub_bootstrap.php');

// HTTPS is required for this page!
if ( empty( $_SERVER['HTTPS'] ) ) {
  header( 'location: ' . BeeHub::urlbase(true) . $_SERVER['REQUEST_URI'] );
  die();
}

BeeHub_Auth::inst()->handle_authentication(false);

// If you are logged in, you don't need this page, so let's redirect you to the homepage
if ( BeeHub_Auth::inst()->is_authenticated() ) {
  header( 'location: ' . BeeHub::urlbase(true) . '/system/' );
  die();
}

header('Content-Type: text/html; charset="UTF-8"');

// A GET requests just gives you the forms
if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
  require('views/password_reset_form.php');
}elseif ( $_SERVER['REQUEST_METHOD'] === 'POST' ) { // POST requests will either send you a reset code or, if a code is given, it will reset your password
  
  //First try to get the username
  $username = null;
  if ( isset( $_POST['username'] ) && !empty( $_POST['username'] ) ) {
    $username = $_POST['username'];
  }elseif ( isset( $_POST['email'] ) && !empty( $_POST['email'] ) ) {
    $statement_props = BeeHub_DB::execute(
        'SELECT `user_name`
         FROM `beehub_users`
         WHERE `email` = ?', 's', $_POST['email']
    );
    $row = $statement_props->fetch_row();
    if ( !is_null($row) ) {
      $username = $row[0];
    }
  }
  
  // Then find the actual user
  $user = null;
  try {
    $user = BeeHub::user( $username );
  }catch (DAV_Status $exception) {
    // We don't care yet whether we found a valid user, because that will be checked depending on what the user is actually trying to do.
  }

  // Check whether we need to send a reset code or need to check it
  if ( isset( $_POST['reset_code'] ) && !empty( $_POST['reset_code']) ) {
    if ( !is_null( $user ) &&
         ( isset( $_POST['new_password'] ) && !empty( $_POST['new_password'] ) ) &&
         ( isset( $_POST['new_password2'] ) && ( $_POST['new_password'] === $_POST['new_password2'] ) ) && 
         $user->check_password_reset_code($_POST['reset_code'] ) ) {
      $user->set_password( $_POST['new_password'] );
      require('views/password_reset_done.php');
    }else{
      BeeHub::htmlError('<p>The form was not correctly filled out.</p>', DAV::HTTP_BAD_REQUEST);
    }
  }else{ // Send a new reset code
    if ( !is_null($user) ) {
      $reset_code = $user->create_password_reset_code();
      $reset_link = BeeHub::urlbase(true) . '/system/password_reset.php?reset_code=' . $reset_code . '&username=' . $username;
      $message =
  'Dear ' . $user->prop( DAV::PROP_DISPLAYNAME ) . ',

  A password reset was requested for your BeeHub account. You can confirm this action by following this link:

  ' . $reset_link . '

  If this link doesn\'t work, you can go to BeeHub and choose \'I forgot my password\' in the login menu. Here, on the \'Enter reset code\' tab you can fill out the following details:

  Username: ' . $username . '
  Reset code: ' . $reset_code . '

  Note that you\'re reset code is only valid for 1 hours.

  If this was a mistake, or you do not want to reset the password for your BeeHub account, you don\'t have to do anything.

  Best regards,

  BeeHub';
        BeeHub::email($user->prop( DAV::PROP_DISPLAYNAME ) . ' <' . $user->prop( BeeHub::PROP_EMAIL ) . '>',
                      'Password reset for BeeHub',
                      $message);
    }
    require('views/password_reset_code_sent.php');
  }
}
