<?php
if ( isset($this) && ( false !== strpos( $this->user_prop_getcontenttype(), 'xml' ) ) )
  echo DAV::xml_header();
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>BeeHub</title>
    <link rel="stylesheet" href="/system/css/jquery-ui.css" />
    <link rel="stylesheet" href="/system/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/system/bootstrap/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="/system/css/beehub.css"/>
    <?= isset($header) ? $header : '' ?>
  </head><?php

if ( @$CONFINED_BOOTSTRAP ) {

  ?><body>
    <div class="bootstrap">
      <?php require 'views/navbar.php' ?>
    </div><?php

} else {

  ?><body class="bootstrap">
    <?php require 'views/navbar.php' ?>
    <div class="container-fluid"><?php

}

?>