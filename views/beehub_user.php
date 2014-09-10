<?php
$footer = '<script type="text/javascript" src="/system/js/user.js"></script>';
$header = '
<style type="text/css">
	.control-label-left {
    	text-align: left !important;
// 			color: #008741 !important;
	}
		
	#surfconext, #panel-verify {
    padding-left:25px !important;
  }
		
	.verification_code {
		width: 245px !important;
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
        <label class="control-label control-label-left" for="user_name">   <?= DAV::xmlescape($this->name) ?></label>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="displayname">Display name</label>
      <div class="controls">
        <input type="text" id="displayname" name="displayname" value="<?= DAV::xmlescape($this->user_prop(DAV::PROP_DISPLAYNAME)) ?>" required />
      </div>
    </div>
    <?php if ( !is_null( $unverified_address ) ) : ?>
      <div class="control-group warning">
    <?php else: ?>
    	<div class="control-group">
    <?php endif;?>
      <label class="control-label" for="email">E-mail address</label>
      <div class="controls">
        <input type="email" id="email" name="email" value="<?= DAV::xmlescape($this->user_prop(BeeHub::PROP_EMAIL)) ?>" required />
        <?php if ( !is_null( $unverified_address ) ) : ?>
        	<span class="help-inline">You've requested to change this to <?= DAV::xmlescape($unverified_address) ?>, but you haven't verified this address yet!</span>
        <?php endif; ?>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" >Default sponsor</label>
      <div class="controls">
      <?php
/*        <?php if ( DAV::determine_client() & DAV::CLIENT_IE ) : ?>
          <input type="hidden" id="sponsor" name="sponsor" value="<?= DAV::xmlescape( $this->user_prop( BeeHub::PROP_SPONSOR ) ) ?>" />
          <?= DAV::xmlescape( BeeHub::sponsor( $this->user_prop(BeeHub::PROP_SPONSOR) )->user_prop( DAV::PROP_DISPLAYNAME ) ) ?> (Sponsor can't be changed in Internet Explorer)
        <?php else: ?>
*/ ?>
          <select id="sponsor" name="sponsor" <?= ( DAV::determine_client() & DAV::CLIENT_IE ) ? 'disabled="disabled"' : '' ?> >
            <?php
            $registry = BeeHub_Registry::inst();
            foreach($this->user_prop(BeeHub::PROP_SPONSOR_MEMBERSHIP) as $sponsor_path) : ?>
              <option value="<?= DAV::xmlescape($sponsor_path) ?>" <?= ( $this->user_prop(BeeHub::PROP_SPONSOR) === $sponsor_path ) ? 'selected="selected"' : '' ?>>
                <?= DAV::xmlescape( BeeHub::sponsor($sponsor_path)->user_prop( DAV::PROP_DISPLAYNAME ) ) ?>
              </option>
              <?php
              $registry->forget($sponsor_path);
            endforeach; ?>
          </select>
          <?= ( DAV::determine_client() & DAV::CLIENT_IE ) ? '(Sponsor can\'t be changed in Internet Explorer)' : '' ?>
        <?php //endif; ?>
      </div>
    </div>
    <div class="control-group">
      <div class="controls">
        <button id="save_button" type="submit" class="btn">Save</button>
      </div>
    </div>
  </form>
</div>


<div id="panel-password" class="tab-pane fade">
  <br />
  <form class="form-horizontal" id="change-password" method="post">
    <input type="hidden" name="POST_auth_code" value="<?= DAV::xmlescape( BeeHub::getAuth()->getPostAuthCode() ) ?>" />
    <div class="control-group passwd">
      <label class="control-label" for="password">Old password</label>
      <div class="controls">
        <input type="password" id="password" name="password" required />
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

<div id="panel-surfconext" class="tab-pane fade">
	<div id="surfconext">
	  <?php if ( !is_null($this->user_prop( BeeHub::PROP_SURFCONEXT ) ) ) : ?>
	  <div id="surfconext_linked">
	    <h5>Your BeeHub account is currently linked to SURFconext account:</h5>
	    <table><tbody><tr>
        <th align="left"><?= DAV::xmlescape($this->user_prop(BeeHub::PROP_SURFCONEXT_DESCRIPTION)) ?></th>
        	<td width="10px"></td>
          <td align="right">
            <button type="button" class="btn btn-danger" id="unlink">Unlink</button> 
            <a type="button" href="/system/saml_connect.php" class="btn btn-success">Unlink and link another SURFconext account</a>
          </td>
        </tr></tbody>
      </table>
	    <br/><br/><br/>    
	  </div>
	  <?php else: ?>
	  <br/>
	    <h5>Your BeeHub account is not linked to a SURFconext account.</h5>
	    <p><a type="button" href="/system/saml_connect.php" class="btn btn-success">Link SURFconext account</a></p>
	  <?php endif; ?>
	</div>
</div>

<?php if ( !is_null( $unverified_address ) ) : ?>
  <div id="panel-verify" class="tab-pane fade <?= isset($_GET['verification_code']) ? 'in active' : '' ?>">
    <form id="verify_email" class="form-horizontal" method="post">
      <input type="hidden" name="POST_auth_code" value="<?= DAV::xmlescape( BeeHub::getAuth()->getPostAuthCode() ) ?>" />
      <h4>Verify email address: <?= $unverified_address ?></h4>
      <p>Please verify your e-mail address by entering the verification code you've recieved through an e-mail and for security reasons, enter your password.</p>
      <div class="control-group">
        <label class="control-label" for="verification_code">Verification code: </label>
        <div class="controls">
          <input type="text" class="verification_code" id="verification_code" name="verification_code" value="<?= DAV::xmlescape(@$_GET['verification_code']) ?>" required />
        </div>
      </div>
      <div class="control-group passwd">
      <label class="control-label" for="verify_password">Password:</label>
      <div class="controls">
        <input class="verification_code" type="password" id="verify_password" name="password" required />
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
