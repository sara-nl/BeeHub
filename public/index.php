<?php
// Prepare the environment: where is the configuration file and are we in development or production mode? Different values are defined in BeeHub::ENVIRONMENT_* constants

// TODO: determine how to get the system to run the client tests
define( 'RUN_CLIENT_TESTS', true );

defined('APPLICATION_ENV') || define(
  'APPLICATION_ENV',
  ( getenv('APPLICATION_ENV') ? strtolower(getenv('APPLICATION_ENV')) : 'production' )
);
defined('ENT_HTML5') || define('ENT_HTML5', 0);

// Then start the application
require_once '../src/beehub_run.php';

