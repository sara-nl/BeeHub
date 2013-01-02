<?php
/*
$path     The d_th of the directory
$members  All members of this directory
*/
?><!DOCTYPE html  PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-us">
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <title>Directory index</title>
    <!--script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
    <script type="text/javascript" src="/webdav.js"></script-->
  </head>
  <body>
    <div id="inhoud">
    <h1>Directory index</h1>
    <?php if ( '/' != $path ) : ?>
      <p><a href="../">Up one level</a></p>
    <?php endif; ?>
    <ul>
      <?php foreach ($members as $member) : ?>
        <li><a href="<?= $path . $member ?>"><?= DAV::xmlescape(rawurldecode($member)) ?></a></li>
      <?php endforeach; ?>
    </ul>
    </div>
    </body>
</html>
