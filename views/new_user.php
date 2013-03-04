<?php require('views/header.php'); ?>
<?php if (BeeHub_Auth::inst()->simpleSaml()->isAuthenticated()) : ?>
  <h4>SURFconext login succeeded!</h4>
  <p>However, BeeHub didn't recognize this SURFconext account.</p>
  <p>If you have an existing BeeHub account and want to link your SURFconext account to this, <a href="/system/saml_connect.php">click here</a>. </p>
  <br/>
  <h4>If you have not used BeeHub before, please give us some information about yourself:</h4>
  <?php else: ?>
  <h3>Create BeeHub account</h3>
<?php endif; ?>
<br/>
</form>
    <form id="createuserform" class="form-horizontal" action="<?= BeeHub::$CONFIG['namespace']['users_path'] ?>" method="post">
	    <div class="control-group">
		    <label class="control-label" for="username">User name</label>
		    <div class="controls">
		    	<input type="text" id="username" name="user_name" pattern="^[a-zA-Z0-9]{1}[a-zA-Z0-9_\\-\\.]{0,255}$" required/>
		    </div>
	    </div>
	    <div class="control-group">
		    <label class="control-label" for="user_displayname">Display name</label>
		    <div class="controls">
		    	<input type="text" id="user_displayname" name="displayname" value="<?= $display_name ?>" required/>
		    </div>
	    </div>
	    <div class="control-group">
		    <label class="control-label" for="user_email">E-mail address</label>
		    <div class="controls">
		    	<input type="email" id="user_displayName" name="email" value="<?= $email_address ?>" required/>
		    </div>
	    </div>
	  <?php if (BeeHub_Auth::inst()->simpleSaml()->isAuthenticated()) : ?> 
	      <div class="control-group">
		    	<label class="control-label" for="surfconext_description">SURFconext description</label>
		    	<div class="controls">
		    		<input type="text" id="surfconext_description" name="surfconext_description" value="<?= $surfconext_description ?>"/>
		    	</div>
	    	</div>
		<?php endif; ?> 
	    <div class="control-group">
		    <label class="control-label" for="username_password">Password</label>
		    <div class="controls">
		      <input type="password" id="username_password" name="password" required/>
		    </div>
	    </div>
	   	<div class="control-group">
		    <label class="control-label" for="username_password_confirmation">Repeat password</label>
		    <div class="controls">
		      <input type="password" id="username_password_confirmation" name="password_confirmation" required/>
		    </div>
	    </div>
	    <div class="control-group">
		    <div class="controls">
		    	<button  type="submit" class="btn">Create</button>
		    </div>
	    </div>
    </form>
 
<?php 
	$footer='<script type="text/javascript" src="/system/js/users.js"></script>';
	require('views/footer.php'); 
?>
