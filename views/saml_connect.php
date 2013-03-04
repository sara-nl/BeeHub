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
<p>Please provide a description for this SURFconext account so you can recognize it easily later on.</p>
<form class="form-horizontal" method="post">
  <div class="control-group">
    <label class="control-label" for="surfconext_description">SURFconext description</label>
    <div class="controls">
      <input type="text" id="surfconext_description" name="surfconext_description" value="<?= DAV::xmlescape($surfconext_description) ?>" required />
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
