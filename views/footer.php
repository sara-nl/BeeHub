<?php
/**
 * Template parameters:
 * $footer - optional, some XHTML to put at the end of the document.
 */
?>
    </div>
    <?php if ( RUN_CLIENT_TESTS ) : ?>
      </div> <!-- End qunit-fixture -->
      <script src="/system/tests/resources/qunit.js"></script>
      <script src="/system/tests/resources/mock.js"></script>
    <?php endif; ?>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script type="text/javascript" src="/system/js/jquery-ui.js"></script>
    <script type="text/javascript" src="/system/bootstrap/js/bootstrap.js"></script>
    <script type="text/javascript" src="/system/js/webdavlib.js"></script>
    <script type="text/javascript" src="/system/js/beehub.js"></script>
    <script type="text/javascript" src="/system/js/server/principals.js"></script>
    <script type="text/javascript">
      nl.sara.beehub.show_notifications(<?= json_encode( BeeHub::notifications( BeeHub_Auth::inst() ) ) ?>);
      <?= ( ( intval(@$_GET['show_notifications']) === 1 ) ? '$("#notification_button").dropdown("toggle");' : '' ) ?>
    </script>
    <?= isset($footer) ? $footer : '' ?>
  </body>
</html>
