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

//BeeHub::handle_method_spoofing();
DAV::$REGISTRY = BeeHub_Registry::inst();
DAV::$LOCKPROVIDER = BeeHub_Lock_Provider::inst();
DAV::$ACLPROVIDER = BeeHub_ACL_Provider::inst();
if (isset($_SERVER['PHP_AUTH_USER'])) {
  BeeHub_ACL_Provider::inst()->CURRENT_USER_PRINCIPAL = DAV::parseURI(
    BeeHub::USERS_PATH . $_SERVER['PHP_AUTH_USER']
  );
}

#try {
#  if ( empty($_SERVER['PHP_AUTH_DIGEST']) ||
#       !( $data = BeeHub::http_digest_parse($_SERVER['PHP_AUTH_DIGEST']) ) ||
#       !( $principal = BeeHub_Registry::inst()->resource( DAV::parseURI(
#              BeeHub::USERS_PATH . rawurlencode( $data['username'] )
#        ) ) )
#     )
#    throw new DAV_Status( DAV::HTTP_UNAUTHORIZED );
  // generate the valid response
#  $A1 = md5(
#    $data['username'] . ':' . BeeHub::REALM . ':' . $principal->user_prop(BeeHub::PROP_PASSWD)
#  );
#  $A2 = md5(
#    $_SERVER['ORIGINAL_REQUEST_METHOD'] . ':' . $data['uri']
#  );
#  $valid_response = md5(
#    $A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] .
#    ':' . $data['qop'] . ':' . $A2
#  );
#  if ($data['response'] != $valid_response)
#    throw new DAV_Status( DAV::HTTP_UNAUTHORIZED );
#  if ('guest' != $data['username'])
#    BeeHub_ACL_Provider::inst()->CURRENT_USER_PRINCIPAL = $principal->path;
#}
#catch (DAV_Status $e) {
#  $e->output();
#  exit();
#}

//if ( !isset($_SERVER['PHP_AUTH_USER']) ||
//     ! (( $principal = BeeHub_Registry::inst()->resource(
//            DAV::parseURI( BeeHub::USERS_PATH . rawurlencode( $_SERVER['PHP_AUTH_USER'] ) )
//          ) )) ||
//     @$_SERVER['PHP_AUTH_PW'] != $principal->user_prop(BeeHub::PROP_PASSWD) ) {
//  $status = new DAV_Status(DAV::HTTP_UNAUTHORIZED);
//  $status->output();
//  exit();
//}
//BeeHub_ACL_Provider::inst()->CURRENT_USER_PRINCIPAL = $principal->path;


$request = DAV_Request::inst();
if ($request) {
  $request->handleRequest();
}
//DAV::debug('done!');
