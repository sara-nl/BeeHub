<?php
#set_include_path( dirname(dirname(__FILE__)) . ':' . get_include_path() );
require_once ( dirname(__FILE__) . '/sd.php' );
require_once ( dirname(__FILE__) . '/../simplesamlphp/lib/_autoload.php' );

class SSPAuth {
  private $ssp;

  public function __construct() {
    $this->ssp = new SimpleSAML_Auth_Simple('beehub');
  }

  public function login() {
    if ($this->isLoggedIn())
      return;

    $this->ssp->requireAuth();
    $attr = $this->ssp->getAttributes();

    $_SESSION['userAttr'] = $attr;
  }

  private function isLoggedIn() {
    return isset( $_SESSION['userAttr'] );
  }

  public function logout($url = NULL) {
    unset( $_SESSION['userAttr'] );
    if ($url !== NULL)
      header("Location: $url");
  }
} // class SSPAuth

function generatePassword() {
  $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789.!@#$%&*';
  $nchars = strlen($chars);
  $retval = '';
  while (8 > strlen($retval))
    $retval .= $chars[mt_rand(0, $nchars - 1)];
  return $retval;
}

session_start();
$sspAuth = new SSPAuth();
$sspAuth->login();

#echo get_include_path();
#exit;
DAV::$REGISTRY = SD_Registry::inst();
DAV::$LOCKPROVIDER = SD_Lock_Provider::inst();
DAV::$ACLPROVIDER = SD_ACL_Provider::inst();
DAV::$ACLPROVIDER->CURRENT_USER_PRINCIPAL = SD::USERS_PATH . 'admin';

$users = DAV::$REGISTRY->resource(SD::USERS_PATH);

#$institute = DAV::$REGISTRY->resource(
#  SD::USERS_PATH . rawurlencode(
#    $_SESSION['userAttr']["urn:mace:terena.org:attribute-def:schacHomeOrganization"][0]
#  )
#);
#if (!$institute) {
#  $users->method_MKCOL( rawurlencode( $_SESSION['userAttr']["urn:mace:terena.org:attribute-def:schacHomeOrganization"][0] ) . '/' );
#  $institute = DAV::$REGISTRY->resource(
#    SD::USERS_PATH . rawurlencode(
#      $_SESSION['userAttr']["urn:mace:terena.org:attribute-def:schacHomeOrganization"][0]
#    )
#  );
#}

$user = DAV::$REGISTRY->resource(
  SD::USERS_PATH . rawurlencode(
    $_SESSION['userAttr']["urn:mace:dir:attribute-def:uid"][0]
  ) . '@' . rawurlencode(
    $_SESSION['userAttr']["urn:mace:terena.org:attribute-def:schacHomeOrganization"][0]
  )
);
if (!$user) {
  $userbasename = 
    rawurlencode(
      $_SESSION['userAttr']["urn:mace:dir:attribute-def:uid"][0] . '@' . 
      $_SESSION['userAttr']["urn:mace:terena.org:attribute-def:schacHomeOrganization"][0]
    );
  $users->create_member($userbasename);
  $userpath = SD::USERS_PATH . $userbasename;
  $user = DAV::$REGISTRY->resource( $userpath );
  $user->method_PROPPATCH( DAV::PROP_DISPLAYNAME, DAV::xmlescape($_SESSION['userAttr']["urn:mace:dir:attribute-def:cn"][0]) );
  $user->method_PROPPATCH( SD::SARANS . ' conextAttributes', DAV::xmlescape( json_encode( $_SESSION['userAttr'] ) ) );
  $user->method_PROPPATCH( DAV::PROP_GROUP, new DAV_Element_href( SD::GROUPS_PATH . 'biggrid' ) );
  $password = generatePassword();
  $user->method_PROPPATCH( SD::PROP_PASSWD, DAV::xmlescape($password) );
  $user->method_PROPPATCH( SD::SARANS . ' email', DAV::xmlescape( $_SESSION['userAttr']["urn:mace:dir:attribute-def:mail"][0] ) );
  $user->storeProperties();
  $root = DAV::$REGISTRY->resource('/');
  $root->method_MKCOL($userbasename);
  $home = DAV::$REGISTRY->resource( '/' . $userbasename );
  $home->method_ACL( array( new DAVACL_Element_ace( $userpath, false, array( DAVACL::PRIV_ALL ), false ) ) );
  exec( '/home/pieterb/webdav/passwd/update_passwords' );
  $message = <<<EOS
Dear {$_SESSION['userAttr']["urn:mace:dir:attribute-def:cn"][0]},

These are your credentials for {$_SERVER['SERVER_NAME']}:
username: {$username}
password: {$password}

A home-directory has been created for you:
<http://{$_SERVER['SERVER_NAME']}/{$username}/>

Now that you have an account, please continue reading here:
<http://wiki.biggrid.nl/wiki/index.php/BeeHub/Step2

Good luck!
EOS;
  mail(
    $_SESSION['userAttr']["urn:mace:dir:attribute-def:mail"][0],
    'Password for BeeHub',
    $message,
    "From: BeeHub <accounts@beehub.nl>\nBcc: Pieter van Beek <pieterb@sara.nl>" //[, string $additional_parameters ]
  );
  DAV::redirect( DAV::HTTP_TEMPORARY_REDIRECT, 'http://wiki.biggrid.nl/wiki/index.php/BeeHub/You_did_it%21' );
  exit;
}

DAV::redirect( DAV::HTTP_TEMPORARY_REDIRECT, 'http://wiki.biggrid.nl/wiki/index.php/BeeHub/Step2' );
