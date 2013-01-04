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
$this->setTemplateVar('footer', <<<EOS
<script type="text/javascript">
  $(function (){
    $('#change_password').change(function() {
      if ($(this).attr('checked') == 'checked') {
        $('div.passwd').show("blind");
      }else{
        $('div.passwd').hide("blind");
      }
    });
  });
</script>
EOS
);
?>
<h1>Profile</h1>
<form method="post">
  <input type="hidden" name="request_key" value="<?= $requestKey ?>" />
  <!--div class="row-fluid">
    <div class="span2 fieldname">Username</div>
    <div class="span10 fieldvalue"><input type="text" name="username" value="<?= htmlspecialchars($attributes['username'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
  </div-->
  <div class="row-fluid">
    <div class="span2 fieldname">Display name</div>
    <div class="span10 fieldvalue"><input type="text" name="displayname" value="<?= htmlspecialchars($attributes['displayname'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname">E-mail address</div>
    <div class="span10 fieldvalue"><input type="text" name="email" value="<?= htmlspecialchars($attributes['email'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname"><label class="checkbox" for="change_password">Change my password</label></div>
    <div class="span10 fieldvalue"><input type="checkbox" id="change_password" name="change_password" value="true" /></div>
  </div>
  <div class="row-fluid passwd">
    <div class="span2 fieldname">New password</div>
    <div class="span10 fieldvalue"><input type="password" name="password1" value="<?= htmlspecialchars($attributes['email'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
  </div>
  <div class="row-fluid passwd">
    <div class="span2 fieldname">Repeat new password</div>
    <div class="span10 fieldvalue"><input type="password" name="password2" value="<?= htmlspecialchars($attributes['email'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname">X509 certificate DN</div>
    <div class="span10 fieldvalue"><?= htmlspecialchars($attributes['x509'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname">Sponsor</div>
    <div class="span10 fieldvalue"><?= htmlspecialchars($attributes['account'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
  </div>
  <button class="btn">Save</button>
</form>
