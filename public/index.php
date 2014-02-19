<?php
/**
 * Entry point for the web server
 *
 * Copyright ©2014 SURFsara b.v., Amsterdam, The Netherlands
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
 *
 * @package BeeHub
 */

// Bootstrap the application
require_once '../src/beehub_bootstrap.php';

$config = BeeHub::config();
if ( @$config['install']['run_install'] === 'true' ) {
  require_once( dirname( __DIR__) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'webserver_install.php' );
  exit();
}

// IE 8 or older really don't work at all anymore. Prompt the user to upgrade
if ( ( $_SERVER['REQUEST_METHOD'] === 'GET' ) &&
     ( ( ( DAV::determine_client() & ~ DAV::CLIENT_IE ) & ( DAV::CLIENT_IE_OLD | DAV::CLIENT_IE8 ) ) > 0 )
   ) {
  BeeHub::htmlError( file_get_contents( dirname( dirname ( __FILE__ ) ) . '/views/error_old_browser.html', DAV::HTTP_BAD_REQUEST ) );
}

// If a GET request on the root doesn't have this server as a referer, redirect to the homepage:
if ( !isset($_GET['nosystem']) &&
     DAV::getPath() === '/' &&
     $_SERVER['REQUEST_METHOD'] === 'GET' &&
     ( ! isset( $_SERVER['HTTP_REFERER'] ) ||
       $_SERVER['SERVER_NAME'] !== parse_url(
         $_SERVER['HTTP_REFERER'], PHP_URL_HOST
       ) ) ) {
  DAV::redirect(
    DAV::HTTP_SEE_OTHER,
    BeeHub::SYSTEM_PATH
  );
  return;
}

// After bootstrapping, start authentication
if ( ( APPLICATION_ENV === BeeHub::ENVIRONMENT_TEST ) || ! empty( $_SERVER['HTTPS'] ) ) {
  BeeHub_Auth::inst()->handle_authentication( BeeHub_Auth::is_authentication_required() );
}

// And finally handle the request
$request = DAV_Request::inst();
if ( $request ) {
  $request->handleRequest();
}

// End of file
