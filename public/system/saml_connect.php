<?php
require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'beehub.php');

// You have to be logged in through HTTP Basic authentication
if (empty($_SERVER['PHP_AUTH_PW'])) {
  BeeHub_ACL_Provider::inst()->unauthorized();
}
$auth = BeeHub_Auth::inst();
$auth->handle_authentication(true, true);

// And through simpleSAML too!
$simpleSaml = $auth->simpleSaml();
if (!$simpleSaml->isAuthenticated()) {
  $simpleSaml->login();
}

// Get some authentication info
$user = $auth->current_user();
$surfId = $simpleSaml->getAuthData("saml:sp:NameID");
$surfId = $surfId['Value'];

// Unlink potential other local account linked to this SURFconext ID
BeeHub_DB::execute('UPDATE `beehub_users` SET `surfconext_id`=null, `surfconext_description`=null WHERE `surfconext_id`=?', 's', $surfId);

// And connect it to the current user
$user->user_set(BeeHub::PROP_SURFCONEXT, $surfId);
$attributes = $simpleSaml->getAttributes();
// TODO: think about the default description
$surfconext_description = @$attributes['urn:mace:terena.org:attribute-def:schacHomeOrganization'][0];
if (empty($surfconext_description)) {
  $surfconext_description = 'Unknown, connected on ' . date('r');
}
$user->user_set(BeeHub::PROP_SURFCONEXT_DESCRIPTION, $surfconext_description);
$user->storeProperties();

// Redirect to the user's profile page
DAV::redirect(DAV::HTTP_SEE_OTHER, $user->path);