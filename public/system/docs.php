<?php
defined('APPLICATION_ENV') || define(
  'APPLICATION_ENV',
  ( getenv('APPLICATION_ENV') ? strtolower(getenv('APPLICATION_ENV')) : 'production' )
);
defined('ENT_HTML5') || define('ENT_HTML5', 0);
require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'beehub.php');

BeeHub_Auth::inst()->handle_authentication(false);

header('Content-Type: text/html; charset="UTF-8"');
require('views/beehub_docs.php');
