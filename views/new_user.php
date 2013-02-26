<?php require('views/header.php'); ?>
<?php if (BeeHub_Auth::inst()->simpleSaml()->isAuthenticated()) : ?>
  <h1>Login succeeded!</h1>
  <p>However, BeeHub didn't recognize this SURFconext account. If you have an existing BeeHub account and want to link your SURFconext account to this, <a href="/system/saml_connect.php">click here</a>. If you have not used BeeHub before, please give us some information about yourself:</p>
<?php else: ?>
  <p>Here you can create credentials for BeeHub. You need these credentials when you want to access BeeHub through a WebDAV client or mount it to your system.</p>
<?php endif; ?>
<form action="<?= BeeHub::$CONFIG['namespace']['users_path'] ?>" method="post">
  <div>User name: <input type="text" name="user_name" /></div>
  <div>Password: <input type="password" name="password" /></div>
  <div>Repeat password: <input type="password" name="password_confirmation" /></div>
  <div>Display name: <input type="text" name="displayname" value="<?= $display_name ?>" /></div>
  <div>E-mail address: <input type="text" name="email" value="<?= $email_address ?>" /></div>
  <?php if (BeeHub_Auth::inst()->simpleSaml()->isAuthenticated()) : ?>
    <div>Description of your SURFconext account: <input type="text" name="surfconext_description" value="<?= $surfconext_description ?>" /></div>
  <?php endif; ?>
  <div><input type="submit" value="Add" /></div>
</form>
<?php require('views/footer.php'); ?>
