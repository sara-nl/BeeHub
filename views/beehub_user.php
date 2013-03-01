<?php
$footer = '<script type="text/javascript" src="/system/js/user.js"></script>';
require 'views/header.php';
?>

<!-- Tabs-->
<ul id="beehub-top-tabs" class="nav nav-tabs">
  <li <?= !isset($_GET['verification_code']) ? 'class="active"' : '' ?>><a href="#panel-profile" data-toggle="tab">My profile</a></li>
  <li><a href="#panel-surfconext" data-toggle="tab">SURFconext</a></li>
  <?php if ( !is_null( $unverified_address ) ) : ?>
    <li <?= isset($_GET['verification_code']) ? 'class="active"' : '' ?>><a href="#panel-verify" data-toggle="tab">Verify e-mail address</a></li>
  <?php endif; ?>
</ul>

<!-- Tab contents -->
<div class="tab-content">

<div id="panel-profile" class="tab-pane fade <?= !isset($_GET['verification_code']) ? 'in active' : '' ?>">
  <form class="form-horizontal" action="<?= $this->path ?>" method="post">
    <div class="control-group">
      <label class="control-label" for="user_name">User name</label>
      <div class="controls">
        <?= htmlspecialchars($this->prop(DAV::PROP_DISPLAYNAME), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="displayname">Display name</label>
      <div class="controls">
        <input type="text" id="displayname" name="displayname" value="<?= htmlspecialchars($this->prop(DAV::PROP_DISPLAYNAME), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" required />
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="email">E-mail address</label>
      <div class="controls">
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($this->prop(BeeHub::PROP_EMAIL), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" required />
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="change_password">Change my password</label>
      <div class="controls">
        <input type="checkbox" id="change_password" name="change_password" value="true" />
      </div>
    </div>
    <div class="control-group passwd">
      <label class="control-label" for="password">New password</label>
      <div class="controls">
        <input type="password" id="password" name="password" />
      </div>
    </div>
    <div class="control-group passwd">
      <label class="control-label" for="password2">Repeat new password</label>
      <div class="controls">
        <input type="password" id="password2" name="password2" />
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="displayname">Default ponsor</label>
      <div class="controls">
        <div><?= htmlspecialchars(BeeHub::sponsor($this->user_prop(BeeHub::PROP_SPONSOR))->prop(DAV::PROP_DISPLAYNAME), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
      </div>
    </div>
    <div class="control-group">
      <div class="controls">
        <button id="save_button" type="submit" class="btn">Save</button>
      </div>
    </div>
  </form>
</div>

<div id="panel-surfconext" class="tab-pane fade">
  <?php if ( !is_null($this->prop( BeeHub::PROP_SURFCONEXT ) ) ) : ?>
    <p>Your BeeHub account is currently linked to a SURFconext account which you gave the following description:</p>
    <p><em><?= htmlspecialchars($this->user_prop(BeeHub::PROP_SURFCONEXT_DESCRIPTION), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></em></p>
    <p><button class="btn">Unlink SURFconext</button> <a href="/system/saml_connect.php" class="btn">Link a different SURFconext account</a></p>
  <?php else: ?>
    <p>Your BeeHub account is not linked to a SURFconext account.</p>
    <p><a href="/system/saml_connect.php" class="btn">Link SURFconext account</a></p>
  <?php endif; ?>
</div>

<?php if ( !is_null( $unverified_address ) ) : ?>
  <div id="panel-verify" class="tab-pane fade <?= isset($_GET['verification_code']) ? 'in active' : '' ?>">
    <form class="form-horizontal" method="post" action="<?= $this->path ?>">
      <p>I want to verify this e-mail address: <?= $unverified_address ?></p>
      <div class="control-group">
        <label class="control-label" for="verification_code">Verification code: </label>
        <div class="controls">
          <input type="text" id="verification_code" name="verification_code" value="<?= htmlspecialchars(@$_GET['verification_code'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" required />
        </div>
      </div>
      <div class="control-group">
        <div class="controls">
          <button type="submit" class="btn">Verify e-mail address</button>
        </div>
      </div>
    </form>
  </div>
<?php endif;

require 'views/footer.php'; ?>
