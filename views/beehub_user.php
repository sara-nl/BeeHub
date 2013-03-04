<?php
$footer = '<script type="text/javascript" src="/system/js/user.js"></script>';
$header = '
<style type="text/css">
	.control-label-left {
    	text-align: left !important;
// 			color: #008741 !important;
	}
</style>
  ';
require 'views/header.php';
?>

<!-- Tabs-->
<ul id="beehub-top-tabs" class="nav nav-tabs">
  <li <?= !isset($_GET['verification_code']) ? 'class="active"' : '' ?>><a href="#panel-profile" data-toggle="tab">My profile</a></li>
  <li><a href="#panel-password" data-toggle="tab">Change password</a></li>
  <li><a href="#panel-surfconext" data-toggle="tab">SURFconext</a></li>
  <?php if ( !is_null( $unverified_address ) ) : ?>
    <li <?= isset($_GET['verification_code']) ? 'class="active"' : '' ?>><a href="#panel-verify" data-toggle="tab">Verify e-mail address</a></li>
  <?php endif; ?>
</ul>

<!-- Tab contents -->
<div class="tab-content">
<div id="panel-profile" class="tab-pane fade <?= !isset($_GET['verification_code']) ? 'in active' : '' ?>">
   <br />
  <form id="myprofile_form" class="form-horizontal">
    <div class="control-group">
      <label class="control-label">User name</label>
      <div class="controls">
        <label class="control-label control-label-left" for="user_name">   <?= htmlspecialchars($this->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></label>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="displayname">Display name</label>
      <div class="controls">
        <input type="text" id="displayname" name="displayname" value="<?= htmlspecialchars($this->prop(DAV::PROP_DISPLAYNAME), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" required />
      </div>
    </div>
    <?php if ( !is_null( $unverified_address ) ) : ?>
      <div class="control-group warning">
    <?php else: ?>
    	<div class="control-group">
    <?php endif;?>
      <label class="control-label" for="email">E-mail address</label>
      <div class="controls">
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($this->prop(BeeHub::PROP_EMAIL), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" required />
        <?php if ( !is_null( $unverified_address ) ) : ?>
        	<span class="help-inline">You've requested to change this to <?= htmlspecialchars($unverified_address, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>, but you haven't verified this address yet!</span>   
        <?php endif; ?>
      </div>
    </div>
<!--     <div class="control-group"> -->
<!--       <label class="control-label" >Default sponsor</label> -->
<!--       <div class="controls"> -->
<!--         <select name="sponsor"> -->
<!--           <option value=""><?= htmlspecialchars(BeeHub::sponsor($this->user_prop(BeeHub::PROP_SPONSOR))->prop(DAV::PROP_DISPLAYNAME), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></option> -->
<!--         </select> -->
<!--       </div> -->
<!--     </div> -->
    <div class="control-group">
      <div class="controls">
        <button id="save_button" type="submit" class="btn">Save</button>
      </div>
    </div>
  </form>
</div>


<div id="panel-password" class="tab-pane fade">
  <br />
  <div class="form-horizontal">
    <div class="control-group passwd">
      <label class="control-label" for="password">Old password</label>
      <div class="controls">
        <input type="password" id="old_password" name="old_password" />
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
      <div class="controls">
        <button id="save_button" type="submit" class="btn">Save</button>
      </div>
    </div>
  </div>
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
    <div class="form-horizontal">
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
    </div>
  </div>
<?php endif;

require 'views/footer.php'; ?>
