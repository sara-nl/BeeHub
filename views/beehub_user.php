<?php
$active = "profile";
$header = '<style type="text/css">
.fieldname {
  text-align: right;
}
div.passwd {
  display: none;
}
</style>';
$footer = '<script type="text/javascript" src="/system/js/user.js"></script>';
require 'views/header.php';

?>
<h1>Profile</h1>
<form method="post">
  <div class="row-fluid">
    <div class="span2 fieldname">Username</div>
    <div class="span10 fieldvalue"><?= htmlspecialchars($this->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname">Display name</div>
    <div class="span10 fieldvalue"><input type="text" name="displayname" value="<?= htmlspecialchars($this->prop(DAV::PROP_DISPLAYNAME), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname">E-mail address</div>
    <div class="span10 fieldvalue"><input type="email" name="email" value="<?= htmlspecialchars($this->prop(BeeHub::PROP_EMAIL), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname"><label class="checkbox" for="change_password">Change my password</label></div>
    <div class="span10 fieldvalue"><input type="checkbox" id="change_password" name="change_password" value="true" /></div>
  </div>
  <div class="row-fluid passwd">
    <div class="span2 fieldname">New password</div>
    <div class="span10 fieldvalue"><input type="password" name="password1" /></div>
  </div>
  <div class="row-fluid passwd">
    <div class="span2 fieldname">Repeat new password</div>
    <div class="span10 fieldvalue"><input type="password" name="password2" /></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname">X509 certificate DN</div>
    <div class="span10 fieldvalue"><?= htmlspecialchars($this->prop(BeeHub::PROP_X509), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname">Sponsor</div>
    <div class="span10 fieldvalue"><?= htmlspecialchars('n.a.', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
  </div>
  <button class="btn">Save</button>
</form>

<form method="post">
  <div>Verification code: <input type="text" name="verification_code" value="<?= htmlspecialchars(@$_GET['verification_code'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
  <div><input type="submit" value="Verify e-mail address" /></div>
</form>
<?php require 'views/footer.php'; ?>
