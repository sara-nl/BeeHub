<?php
// Prepare the environment: where is the configuration file and are we in development or production mode?
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? strtolower(getenv('APPLICATION_ENV')) : 'production'));

// Then start the application
require_once '../src/beehub_run.php';
