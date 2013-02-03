<?php
/**
 * Template parameters:
 * $footer - optional, some XHTML to put at the end of the document.
 */
?>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.0.min.js"></script>
    <script type="text/javascript" src="/system/js/jquery-ui.js"></script>
    <script type="text/javascript" src="/system/bootstrap/js/bootstrap.js"></script>
    <script type="text/javascript" src="/system/js/beehub.js"></script>
    <?= isset($footer) ? $footer : '' ?>
  </body>
</html>
