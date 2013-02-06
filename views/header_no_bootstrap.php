<?php
if ( false !== strpos( $this->user_prop_getcontenttype(), 'xml' ) )
  echo DAV::xml_header();
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>BeeHub</title>
    <link rel="stylesheet" href="/system/jquery/css/surfsara/jquery-ui-1.10.0.custom.min.css" />
    <link rel="stylesheet" href="/system/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/system/bootstrap/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="/system/css/beehub.css"/>
    <?= isset($header) ? $header : '' ?>
  </head>
  <body>
    <div class="bootstrap">
      <?php require 'views/navbar.php' ?>
      <a href="http://www.surfsara.nl/"><img src="/system/img/surfsara.png" class="surfsara-logo visible-desktop" /></a>
    </div>
