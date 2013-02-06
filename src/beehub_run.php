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

require_once dirname(__FILE__) . '/beehub.php';

BeeHub::handle_method_spoofing();
DAV::$REGISTRY = BeeHub_Registry::inst();
DAV::$LOCKPROVIDER = BeeHub_Lock_Provider::inst();
DAV::$ACLPROVIDER = BeeHub_ACL_Provider::inst();
if (isset($_SERVER['PHP_AUTH_USER'])) {
  # TODO Waarom wordt hier DAV::parseURI aangeroepen? --pieterb
  BeeHub_ACL_Provider::inst()->CURRENT_USER_PRINCIPAL = DAV::parseURI(
    BeeHub::$CONFIG['namespace']['users_path'] . $_SERVER['PHP_AUTH_USER']
  );
}

$request = DAV_Request::inst();
if ($request) {
  $request->handleRequest();
}
//DAV::debug('done!');
