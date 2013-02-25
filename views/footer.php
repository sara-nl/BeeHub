<?php
/**
 * Template parameters:
 * $footer - optional, some XHTML to put at the end of the document.
 */
if (! @$CONFINED_BOOTSTRAP) echo '</div>';
?>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script type="text/javascript" src="/system/js/jquery-ui.js"></script>
    <script type="text/javascript" src="/system/bootstrap/js/bootstrap.js"></script>
    <script type="text/javascript" src="/system/js/webdavlib.js"></script>
    <script type="text/javascript" src="/system/js/beehub.js"></script>
    <script type="text/javascript" src="/system/js/server/principals.js"></script>
    <script type="text/javascript">
      nl.sara.beehub.show_notifications(<?= json_encode(BeeHub::notifications()) ?>);
    </script>
    <?= isset($footer) ? $footer : '' ?>
  </body>
</html>
