<?php require('views/header.php'); ?>
<p>
  Here you can create credentials for BeeHub. You need these credentials when you want to access BeeHub through a WebDAV client or mount it to your system.
</p>
<?php if (BeeHub_Auth::inst()->surfconext()) : ?>
  <p>Already have an account? Then BeeHub didn't recognize this SURFconext ID. To connect this SURFconext ID to your existing account, log in using username/password and attach this SURFconext ID to your account on your profile page.</p>
<?php endif; ?>
<form action="<?= BeeHub::$CONFIG['namespace']['users_path'] ?>" method="post">
  <div>User name: <input type="text" name="user_name" /></div>
  <div>Password: <input type="password" name="password" /> (needed for accessing BeeHub through a mount or WebDav client)</div>
  <div>Repeat password: <input type="password" name="password_confirmation" /></div>
  <div>Display name: <input type="text" name="displayname" value="<?= $display_name ?>" /></div>
  <div>E-mail address: <input type="text" name="email" value="<?= $email_address ?>" /></div>
  <div><input type="submit" value="Add" /></div>
</form>
<?php require('views/footer.php'); ?>
