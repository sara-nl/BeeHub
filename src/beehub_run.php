<?php

/*·************************************************************************
 * Copyright ©2007-2012 SARA b.v., Amsterdam, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at <http://www.apache.org/licenses/LICENSE-2.0>
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package BeeHub
 */

// Perform some bootstrapping
require_once dirname(__FILE__) . '/beehub.php';
BeeHub::handle_method_spoofing();

// If a GET request on the root doesn't have this server as a referer, redirect to the homepage:
// TODO: This can also be done in the apache configuration...
if ( DAV::$PATH === '/' &&
     $_SERVER['REQUEST_METHOD'] === 'GET' &&
     ( ! isset( $_SERVER['HTTP_REFERER'] ) ||
       $_SERVER['SERVER_NAME'] !== parse_url(
         $_SERVER['HTTP_REFERER'], PHP_URL_HOST
       ) ) ) {
  DAV::redirect(
    DAV::HTTP_SEE_OTHER,
    BeeHub::$CONFIG['namespace']['system_path']
  );
  exit;
}

DAV::$REGISTRY = BeeHub_Registry::inst();
DAV::$LOCKPROVIDER = BeeHub_Lock_Provider::inst();
DAV::$ACLPROVIDER = BeeHub_ACL_Provider::inst();

$request = DAV_Request::inst();

// Start authentication
/* You don't need to authenticate when:
 * - Accessing over regular HTTP (as opposed to HTTPS)
 * - An OPTIONS request never requires authentication
 * - GET (or HEAD) or POST on the users collection (required to create a new user)
 * - GET (or HEAD) on the system collection (required to read the 'homepage')
 *
 * Note that the if-statements below check the inverse of these rules (because, if evaluated to true, it will start the authentication process)
 */
$path = DAV::unslashify(DAV::$PATH);
$noRequireAuth = (
  (
    $path === DAV::unslashify( BeeHub::$CONFIG['namespace']['users_path'] ) &&
    in_array( $_SERVER['REQUEST_METHOD'], array('GET', 'HEAD') )
  ) ||
  (
    $path === DAV::unslashify( BeeHub::$CONFIG['namespace']['system_path'] ) &&
    in_array($_SERVER['REQUEST_METHOD'], array('GET', 'HEAD') )
  )
);
$requireAuth = !$noRequireAuth;

if ( !empty( $_SERVER['HTTPS'] ) &&
     $_SERVER['REQUEST_METHOD'] !== 'OPTIONS' ) {
  require_once(BeeHub::$CONFIG['environment']['simplesamlphp_autoloader']);
  $as = new SimpleSAML_Auth_Simple('default-sp');

  if (isset($_GET['logout']) && $as->isAuthenticated()) {
    $as->logout();
  }
  if ('conext' === @$_GET['login'] && !$as->isAuthenticated()) {
    $as->login(array('saml:idp'=>'https://engine.surfconext.nl/authentication/idp/metadata'));
  }

  if ( $as->isAuthenticated() ) {
    // @TODO: Retrieve and store the correct user (name) when authenticated through SimpleSamlPHP
    $CONEXT = true;
  } else { // If we are not authenticated through SimpleSamlPHP, require HTTP basic authentication
    if ( isset($_SERVER['PHP_AUTH_PW'])) { // The user already sent username and password: check them!
      $stmt = BeeHub_DB::execute(
        'SELECT `password`
         FROM `beehub_users`
         WHERE `user_name` = ?',
        's', $_SERVER['PHP_AUTH_USER']
      );
      if ( !( $row = $stmt->fetch_row() ) ||
           $row[0] != crypt($_SERVER['PHP_AUTH_PW'], $row[0]) ) {
        // If authentication fails, respond accordingly
        if ($requireAuth) {
          $stmt->free_result();
          // User could not be authenticated with supplied credentials, but we
          // require authentication, so we ask again!
          BeeHub_ACL_Provider::inst()->unauthorized();
          exit;
        }
      } else { // Authentication succeeded: store credentials!
        BeeHub_ACL_Provider::inst()->CURRENT_USER_PRINCIPAL =
          BeeHub::$CONFIG['namespace']['users_path'] .
          rawurlencode( $_SERVER['PHP_AUTH_USER'] );
      }
      $stmt->free_result();
    } elseif ( $requireAuth || 'passwd' === @$_GET['login'] ) {
      // If the user didn't send any credentials, but we require authentication, ask for it!
      BeeHub_ACL_Provider::inst()->unauthorized();
      exit;
    }
  }
}

// Clean up, just because it's nice to do so
unset($path, $requireAuth, $as, $statement, $storedPassword);

// After bootstrapping and authentication is done, handle the request
$request->handleRequest();
