<?php

if ( !isset( $_SERVER['PHP_AUTH_USER'] ) )
  $_SERVER['PHP_AUTH_USER'] = 'laura@sara.nl';

#if ( 'beehub.nl' == $_SERVER['SERVER_NAME'] )
  require_once 'sd_run.php';
#else
#  require_once 'devel/sd_run.php';
