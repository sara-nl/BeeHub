<?php
$this->setTemplateVar('active', "profile");
$this->setTemplateVar('header', '<style type="text/css">
.fieldname {
  text-align: right;
}
div.passwd {
  display: none;
}
</style>');
?>
<div class="bootstrap">
  <form action="<?= BeeHub::$CONFIG['webdav_namespace']['users_path'] ?>" method="post">
    <div>User name: <input type="text" name="user_name" /></div>
    <div>displayname: <input type="text" name="displayname" /></div>
    <div>email: <input type="text" name="email" /></div>
    <div>password: <input type="text" name="password" /></div>
    <div>surfconext_id: <input type="text" name="surfconext_id" /></div>
    <div>x509: <input type="text" name="x509" /></div>
    <div><input type="submit" value="Add" /></div>
  </form>
</div>