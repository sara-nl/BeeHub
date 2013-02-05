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
DAV::$REGISTRY = BeeHub_Registry::inst();
DAV::$LOCKPROVIDER = BeeHub_Lock_Provider::inst();
DAV::$ACLPROVIDER = BeeHub_ACL_Provider::inst();

// Start authentication
/* You don't need to authenticate when:
 * - Accessing over regular HTTP (as opposed to HTTPS), unless you're in a development environment
 * - An OPTIONS request never requires authentication
 * - GET (or HEAD) or POST on the users collection (required to create a new user)
 * - GET (or HEAD) on the homepage collection (required to read the 'homepage')
 *
 * Note that the if-statements below check the inverse of these rules (because, if evaluated to true, it will start the authentication process)
 */
$requireAuth = (
        (DAV::unslashify($_SERVER['REQUEST_URI']) != DAV::unslashify(BeeHub::$CONFIG['namespace']['users_path']) || !in_array($_SERVER['REQUEST_METHOD'], array('GET', 'HEAD', 'POST'))) &&
        (DAV::unslashify($_SERVER['REQUEST_URI']) != DAV::unslashify(BeeHub::$CONFIG['namespace']['homepage']) || !in_array($_SERVER['REQUEST_METHOD'], array('GET', 'HEAD'))));
if (
        (!empty($_SERVER['HTTPS']) || (APPLICATION_ENV == BeeHub::ENVIRONMENT_DEVELOPMENT)) &&
        ($_SERVER['REQUEST_METHOD'] != 'OPTIONS')) {
  require_once(BeeHub::$CONFIG['environment']['simplesamlphp_autoloader']);
  $as = new SimpleSAML_Auth_Simple('default-sp');
  if ($as->isAuthenticated()) {
    // @TODO: Retrieve and store the correct user (name) when authenticated through SimpleSamlPHP
  }else{ // If we are not authenticated through SimpleSamlPHP, require HTTP basic authentication
    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) { // The user already sent username and password: check them!
      $statement = BeeHub::mysqli()->prepare('SELECT `password` FROM `beehub_users` WHERE `user_name`=?');
      $statement->bind_param('s', $_SERVER['PHP_AUTH_USER']);
      $storedPassword = null;
      $statement->bind_result($storedPassword);
      if (!$statement->execute()) {
        throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
      }
      if (!$statement->fetch() || ($storedPassword != crypt($_SERVER['PHP_AUTH_PW'], $storedPassword))) { // If authentication fails, respond accordingly
        if ($requireAuth) { // User could not be authenticated with supplied credentials, but we require authentication, so we ask again!
          DAV::header(array(
              'status' => DAV::HTTP_UNAUTHORIZED,
              'WWW-Authenticate' => 'Basic realm="' . BeeHub::$CONFIG['authentication']['realm'] . '"')
                  );
          die();
        }
      }else{ // Authentication succeeded: store credentials!
        # TODO Waarom wordt hier DAV::parseURI aangeroepen? --pieterb
        BeeHub_ACL_Provider::inst()->CURRENT_USER_PRINCIPAL = DAV::parseURI(
          BeeHub::$CONFIG['namespace']['users_path'] . $_SERVER['PHP_AUTH_USER']
        );
      }
      $statement->free_result();
    }elseif ($requireAuth) { // If the user didn't send any credentials, but we require authentication, ask for it!
      DAV::header(array(
          'status' => DAV::HTTP_UNAUTHORIZED,
          'WWW-Authenticate' => 'Basic realm="' . BeeHub::$CONFIG['authentication']['realm'] . '"')
              );
      die();
    }
  }
}

// After bootstrapping and authentication is done, handle the request
$request = DAV_Request::inst();
if ($request) {
  $request->handleRequest();
}
//DAV::debug('done!');
