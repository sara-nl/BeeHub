<?php require('views/header.php'); ?>
<?php if (BeeHub_Auth::inst()->simpleSaml()->isAuthenticated()) : ?>
  <h1>Login succeeded!</h1>
  <p>However, BeeHub didn't recognize this SURFconext account. If you have an existing BeeHub account and want to link your SURFconext account to this, <a href="/system/saml_connect.php">click here</a>. If you have not used BeeHub before, please give us some information about yourself:</p>
<?php else: ?>
  <h3>Create Beehub account</h3>
<?php endif; ?>
<br/>
</form>
    <form id="createUserForm" class="form-horizontal" action="<?= BeeHub::$CONFIG['namespace']['groups_path'] ?><?= BeeHub::$CONFIG['namespace']['users_path'] ?>" method="post">
	    <div class="control-group">
		    <label class="control-label" for="username">User name</label>
		    <div class="controls">
		    	<input type="text" id="username" name="user_name" required/>
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
		    	<input type="email" id="user_displayName" name="displayname" value="<?= $email_address ?>" required/>
		    </div>
	    </div>
	  <?php if (BeeHub_Auth::inst()->simpleSaml()->isAuthenticated()) : ?> 
	      <div class="control-group">
		    	<label class="control-label" for="surfconext_description">SURFconext description</label>
		    	<div class="controls">
		    		<input type="text" id="surfconext_description" name="surfconext_description" value="<?= $surfconext_description ?>" required/>
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
<?php require('views/footer.php'); ?>
