<?php require('views/header.php'); ?>
<form action="<?= BeeHub::$CONFIG['namespace']['users_path'] ?>" method="post">
  <div>User name: <input type="text" name="user_name" /></div>
  <div>Display name: <input type="text" name="displayname" /></div>
  <div>E-mail address: <input type="text" name="email" /></div>
  <div><input type="submit" value="Add" /></div>
</form>
<?php require('views/footer.php'); ?>
