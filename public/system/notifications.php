<?php
defined('APPLICATION_ENV') || define(
  'APPLICATION_ENV',
  ( getenv('APPLICATION_ENV') ? strtolower(getenv('APPLICATION_ENV')) : 'production' )
);
defined('ENT_HTML5') || define('ENT_HTML5', 0);
require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'beehub_bootstrap.php');

$auth = BeeHub_Auth::inst();

$auth->handle_authentication(false);
header('Content-type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$notifications = BeeHub::notifications( BeeHub_Auth::inst() );

print(json_encode($notifications));
