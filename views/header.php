<?php
if ( isset($this) && ( false !== strpos( $this->user_prop_getcontenttype(), 'xml' ) ) )
  echo DAV::xml_header();
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>BeeHub</title>
    <?php if ( RUN_CLIENT_TESTS ) : ?>
      <link rel="stylesheet" href="/system/tests/resources/qunit.css" />
    <?php else: ?>
      <link rel="stylesheet" href="/system/css/jquery-ui.css" />
      <link rel="stylesheet" href="/system/bootstrap/css/bootstrap.min.css" />
      <link rel="stylesheet" href="/system/bootstrap/css/bootstrap-responsive.min.css" />
      <link rel="stylesheet" href="/system/css/beehub.css"/>
      <link rel="shortcut icon" href="https://www.surfsara.nl/sites/all/themes/st_sara/favicon.ico" type="image/x-icon" />
    <?php endif; ?>
    <?= isset($header) ? $header : '' ?>
  </head><body class="bootstrap">
    <?php if ( RUN_CLIENT_TESTS ) : ?>
      <div id="qunit"></div>
      <div id="qunit-fixture">
    <?php endif; ?>
    <?php require( 'views/navbar.php' ); ?>
    <div class="container-fluid">
