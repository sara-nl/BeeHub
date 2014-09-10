<?php
require 'views/header.php';
$user = BeeHub_Auth::inst()->current_user();
$attributes = $simpleSaml->getAttributes();
?>
<h3>Link SURFconext account</h3>
<p>
  <div>You are about to link your SURFconext account to the BeeHub account with user name '<em><?= $user->name ?></em>' and display name '<em><?= $user->prop(DAV::PROP_DISPLAYNAME) ?></em>'. To help you determine if you're logged in to the right SURFconext account, here is the information you've shared with BeeHub through SURFconext:</div>
  <div>Display name: <em><?= !empty($attributes['urn:mace:dir:attribute-def:displayName']) ? $attributes['urn:mace:dir:attribute-def:displayName'][0] : 'not provided' ?></em></div>
  <div>E-mail address: <em><?= !empty($attributes['urn:mace:dir:attribute-def:mail']) ? $attributes['urn:mace:dir:attribute-def:mail'][0] : 'not provided' ?></em></div>
  <div>Organization: <em><?= !empty($attributes['urn:mace:terena.org:attribute-def:schacHomeOrganization']) ? $attributes['urn:mace:terena.org:attribute-def:schacHomeOrganization'][0] : 'not provided' ?></em></div>
</p>
<p>For security reasons, please provide your password.</p>
<form class="form-horizontal" method="post">
  <input type="hidden" name="POST_auth_code" value="<?= DAV::xmlescape( BeeHub::getAuth()->getPostAuthCode() ) ?>" />
  <div class="control-group">
    <label class="control-label" for="password">BeeHub password:</label>
    <div class="controls">
      <input type="password" id="password" name="password" required="required" />
    </div>
  </div>
  <div class="control-group">
    <div class="controls">
      <button type="submit" class="btn">Link SURFconext</button>
    </div>
  </div>
</form>
<?php
require 'views/footer.php';
?>
