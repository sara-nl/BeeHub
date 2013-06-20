<?php
defined('APPLICATION_ENV') || define(
  'APPLICATION_ENV',
  ( getenv('APPLICATION_ENV') ? strtolower(getenv('APPLICATION_ENV')) : 'production' )
);
defined('ENT_HTML5') || define('ENT_HTML5', 0);
require_once( dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'beehub.php' );

if ( empty( $_SERVER['HTTPS'] ) ) {
  header( 'location: ' . BeeHub::urlbase(true) . $_SERVER['REQUEST_URI'] );
  die();
}

// You have to be logged in through HTTP Basic authentication
if (empty($_SERVER['PHP_AUTH_PW'])) {
  BeeHub_Auth::inst()->unauthorized();
  die();
}
$auth = BeeHub_Auth::inst();
$auth->handle_authentication(true, true);

// And through simpleSAML too!
$simpleSaml = $auth->simpleSaml();
if (!$simpleSaml->isAuthenticated()) {
  $simpleSaml->login();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  require_once('views' . DIRECTORY_SEPARATOR . 'saml_connect.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  throw new DAV_Status(DAV::HTTP_METHOD_NOT_ALLOWED);
}

// Get some authentication info
$user = $auth->current_user();
$surfId = $simpleSaml->getAuthData("saml:sp:NameID");
$surfId = $surfId['Value'];

// You need to supply the users current password (as stored in the local database)
if ( !$user->check_password($_POST['password']) ) {
  throw DAV::forbidden();
}

// Unlink potential other local account linked to this SURFconext ID
BeeHub_DB::execute('UPDATE `beehub_users` SET `surfconext_id`=null, `surfconext_description`=null WHERE `surfconext_id`=?', 's', $surfId);

// And connect it to the current user
$user->user_set(BeeHub::PROP_SURFCONEXT, $surfId);
$attributes = $simpleSaml->getAttributes();
$surfconext_description = @$attributes['urn:mace:terena.org:attribute-def:schacHomeOrganization'][0];
if (empty($surfconext_description)) {
  $surfconext_description = 'Unknown account';
}
$user->user_set(BeeHub::PROP_SURFCONEXT_DESCRIPTION, $surfconext_description);
$user->storeProperties();

// Redirect to the user's profile page
DAV::redirect(DAV::HTTP_SEE_OTHER, $user->path);
