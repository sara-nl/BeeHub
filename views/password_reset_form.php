<?php
require 'views/header.php';
?>

<!-- Tabs-->
<ul id="beehub-top-tabs" class="nav nav-tabs">
  <li <?= !isset($_GET['reset_code']) ? 'class="active"' : '' ?>><a href="#panel-request-code" data-toggle="tab">Request reset code</a></li>
  <li <?= isset($_GET['reset_code']) ? 'class="active"' : '' ?>><a href="#panel-enter-code" data-toggle="tab">Enter reset code</a></li>
</ul>

<!-- Tab contents -->
<div class="tab-content">
  <div id="panel-request-code" class="tab-pane fade <?= !isset($_GET['reset_code']) ? 'in active' : '' ?>">
    <p class="long_text">If you&#39;ve forgotten your password, you can request a reset code here. This code will allow you to choose a new password. After completing this form, an e-mail will be sent to the e-mail address attached to your account.</p>
    <br />
    <form class="form-horizontal" method="post">
      <input type="hidden" name="POST_auth_code" value="<?= DAV::xmlescape( BeeHub::getAuth()->getPostAuthCode() ) ?>" />
      <div class="control-group">
        <label class="control-label" for="username">Username</label>
        <div class="controls">
          <input type="text" id="username" name="username" />
        </div>
      </div>
      <div class="control-group">
        <div class="controls">OR</div>
      </div>
      <div class="control-group">
        <label class="control-label" for="email">E-mail address</label>
        <div class="controls">
          <input type="text" id="email" name="email" />
        </div>
      </div>
      <div class="control-group">
        <div class="controls">
          <button type="submit" class="btn">Request reset code</button>
        </div>
      </div>
    </form>
  </div>

  <div id="panel-enter-code" class="tab-pane fade <?= isset($_GET['reset_code']) ? 'in active' : '' ?>">
    <p class="long_text">Please fill out the reset code you received in your e-mail and choose a new password.</p>
    <br />
    <form class="form-horizontal" method="post">
      <input type="hidden" name="POST_auth_code" value="<?= DAV::xmlescape( BeeHub::getAuth()->getPostAuthCode() ) ?>" />
      <div class="control-group">
        <label class="control-label" for="username">Username</label>
        <div class="controls">
          <input type="text" id="username" name="username" value="<?= @$_GET['username'] ?>" required />
        </div>
      </div>
      <div class="control-group passwd">
        <label class="control-label" for="reset_code">Reset code</label>
        <div class="controls">
          <input type="text" id="reset_code" name="reset_code" value="<?= @$_GET['reset_code'] ?>" required />
        </div>
      </div>
      <div class="control-group passwd">
        <label class="control-label" for="new_password">New password</label>
        <div class="controls">
          <input type="password" id="new_password" name="new_password" required />
        </div>
      </div>
      <div class="control-group passwd">
        <label class="control-label" for="new_password2">Repeat new password</label>
        <div class="controls">
          <input type="password" id="new_password2" name="new_password2" required />
        </div>
      </div>
      <div class="control-group">
        <div class="controls">
          <button id="save_button" type="submit" class="btn">Save</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php
require 'views/footer.php';
