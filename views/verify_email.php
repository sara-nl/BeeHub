<?php require('views/header_bootstrap.php'); ?>
<div class="bootstrap">
  <form action="<?= BeeHub::$CONFIG['webdav_namespace']['users_path'] ?>" method="post">
    <div>User name: <input type="text" name="user_name" value="<?= htmlspecialchars($_GET['user_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
    <div>Verification code: <input type="text" name="verification_code" value="<?= htmlspecialchars($_GET['verification_code'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
    <?php if ($setPassword) : ?>
      <div>Password: <input type="password" name="password" /></div>
      <div>X.509 client certificate DN: <input type="x509" name="text" /></div>
    <?php endif; ?>
    <div><input type="submit" value="Verify e-mail address" /></div>
  </form>
</div>
<?php require('views/footer_bootstrap.php'); ?>