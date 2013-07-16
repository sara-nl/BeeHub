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


// Set the include path, so BeeHub_* classes are automatically loaded
set_include_path(
  realpath( dirname( dirname(__FILE__) ) ) . PATH_SEPARATOR .
  dirname(__FILE__) . PATH_SEPARATOR .
  get_include_path()
);
require_once dirname(dirname(__FILE__)) . '/webdav-php/lib/dav.php';


// Set a default exception handler, so we always output nice errors if an exception is uncaught
function beehub_exception_handler($e) {
  if (! $e instanceof DAV_Status) {
    $e = new DAV_Status(
      DAV::HTTP_INTERNAL_SERVER_ERROR,
      "$e"
    );
  }
  $e->output();
}
set_exception_handler('beehub_exception_handler');


/**
 * Just a namespace.
 * @package BeeHub
 */
class BeeHub {


  const PROP_EMAIL                  = 'http://beehub.nl/ email';
  const PROP_SURFCONEXT             = 'http://beehub.nl/ surfconext';
  const PROP_SURFCONEXT_DESCRIPTION = 'http://beehub.nl/ surfconext-description';
  const PROP_X509                   = 'http://beehub.nl/ x509';
  const PROP_DESCRIPTION            = 'http://beehub.nl/ description';
  const PROP_SPONSOR                = 'http://beehub.nl/ sponsor';
  const PROP_SPONSOR_MEMBERSHIP     = 'http://beehub.nl/ sponsor-membership';


  public static $USER_PROPS = array(
    self::PROP_EMAIL                  => true,
    self::PROP_SURFCONEXT             => true,
    self::PROP_SURFCONEXT_DESCRIPTION => true,
    self::PROP_X509                   => true,
    self::PROP_SPONSOR                => true,
    self::PROP_SPONSOR_MEMBERSHIP     => true,
  );
  public static $GROUP_PROPS = array(
    self::PROP_DESCRIPTION     => true,
  );
  public static $SPONSOR_PROPS = array(
    self::PROP_DESCRIPTION     => true,
  );
  // For the next values: check if you also need to change them in /system/js/beehub.js
  public static $FORBIDDEN_GROUP_NAMES = array(
    'home',
    'system',
  );
  const SYSTEM_PATH     = "/system/";
  const USERS_PATH      = "/system/users/";
  const GROUPS_PATH     = "/system/groups/";
  const SPONSORS_PATH   = "/system/sponsors/";
  const JAVASCRIPT_PATH = "/system/js/server/";


  /**#@+
   * These constants define the different environments the code can run in.
   *
   * The global constant APPLICATION_ENV can be compared to one of these
   * constants to check whether the application is running in the respective
   * environment. This reduces the chance of developers making up their own
   * environment values without in stead of using one of the existing ones.
   */
  const ENVIRONMENT_DEVELOPMENT = 'development';
  const ENVIRONMENT_PRODUCTION  = 'production';
  /**#@-*/

  public static $CONFIG;


  /**
   * Returns the base URI.
   * The base URI is 'protocol://server.name:port'
   * @return string
   */
  public static function urlbase($https = null) {
    static $URLBASE = array();
    if ( !@$URLBASE[$https] ) {
      if ( true === $https || ! empty($_SERVER['HTTPS']) && null === $https )
        $tmp = 'https://';
      else
        $tmp = 'http://';
      $tmp .= $_SERVER['SERVER_NAME'];
      $server_port = intval($_SERVER['SERVER_PORT'], 10);
      if ( !empty($_SERVER['HTTPS']) && $server_port !== 443 or
            empty($_SERVER['HTTPS']) && $server_port !== 80 ) {
        if ( true === $https && empty($_SERVER['HTTPS']) )
          $server_port += 443 - 80;
        elseif ( false === $https && ! empty($_SERVER['HTTPS']) )
          $server_port -= 443 - 80;
        $tmp .= ":{$server_port}";
      }
      $URLBASE[$https] = $tmp;
    }
    return $URLBASE[$https];
  }


  /**
   * A better escapeshellarg.
   * The default PHP version seems not to work for UTF-8 strings...
   * @return string
   * @param string $arg
   */
  public static function escapeshellarg($arg) {
    return "'" . str_replace("'", "'\\''", $arg) . "'";
  }


  /**
   * Shows a decent HTML page with an error for the end-user
   * @param   type  $message  The message to show. Could contain HTML.
   * @param   type  $status   The HTTP status code to return
   * @return  void
   */
  public static function htmlError($message, $status = DAV::HTTP_OK) {
    DAV::header( array( 'status' => $status ) );
    require( 'views/html_error.php' );
    exit;
  }


  public static function localPath($path) {
    return DAV::unslashify(self::$CONFIG['environment']['datadir'] . rawurldecode($path));
  }


  public static function best_xhtml_type() {
    return 'text/html';
    // The rest of the function will be skipped. This is because ExtJS doesn't support X(HT)ML, so we always need to send it as 'text/html'
    return ( false === strstr(@$_SERVER['HTTP_USER_AGENT'], 'MSIE') &&
            false === strstr(@$_SERVER['HTTP_USER_AGENT'], 'Microsoft') ) ?
            'application/xhtml+xml' : 'text/html';
  }


  public static function handle_method_spoofing() {
    $_SERVER['ORIGINAL_REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' and
            isset($_GET['_method'])) {
      $http_method = strtoupper($_GET['_method']);
      unset($_GET['_method']);
      if ($http_method === 'GET' &&
              strstr(@$_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false) {
        $_GET = $_POST;
        $_POST = array();
      }
      $_SERVER['QUERY_STRING'] = http_build_query($_GET);
      $_SERVER['REQUEST_URI'] =
              substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
      if ($_SERVER['QUERY_STRING'] !== '')
        $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
      $_SERVER['REQUEST_METHOD'] = $http_method;
    }
  }


  /**
   * @param $name string the path or name of the resource
   * @return BeeHub_User
   */
  public static function user($name) {
    if ($name[0] !== '/')
      $name = BeeHub::USERS_PATH .
        rawurlencode($name);
    $retval = BeeHub_Registry::inst()->resource( $name );
    if ( !$retval || !( $retval instanceof BeeHub_User ) ) throw new DAV_Status(
      DAV::HTTP_FORBIDDEN, DAV::COND_RECOGNIZED_PRINCIPAL
    );
    return $retval;
  }


  /**
   * @param $name string the path or name of the resource
   * @return BeeHub_Group
   */
  public static function group($name) {
    if ($name[0] !== '/')
      $name = BeeHub::GROUPS_PATH .
        rawurlencode($name);
    $retval = BeeHub_Registry::inst()->resource( $name );
    if ( !$retval || !( $retval instanceof BeeHub_Group ) ) throw new DAV_Status(
      DAV::HTTP_FORBIDDEN, DAV::COND_RECOGNIZED_PRINCIPAL
    );
    return $retval;
  }


  /**
   * @param $name string the path or name of the resource
   * @return BeeHub_Sponsor
   */
  public static function sponsor($name) {
    if ($name[0] !== '/')
      $name = BeeHub::SPONSORS_PATH .
        rawurlencode($name);
    $retval = BeeHub_Registry::inst()->resource( $name );
    if ( !$retval || !( $retval instanceof BeeHub_Sponsor ) ) throw new DAV_Status(
      DAV::HTTP_FORBIDDEN, DAV::COND_RECOGNIZED_PRINCIPAL
    );
    return $retval;
  }


  /**
   * Checks for notifications for the current user
   *
   * Notifications are associative arrays with two keys: type and data. The type
   * describes what type the notification is. For example 'group_invitation. The
   * lay-out of the notification can then be determined client side. The data
   * differs per type and its format is dictated by the client side scripts
   * handling the type. This can for example be an array with the group name and
   * display name of the group you are invited for.
   *
   * @return  array  An array with notifications.
   */
  public static function notifications() {
    $notifications = array();
    $auth = BeeHub_Auth::inst();
    if ($auth->is_authenticated()) {
      $user = $auth->current_user();

      // Fetch all group invitations
      $groupsCollection = BeeHub::getNoSQL()->groups;
      $invitationsResultSet = $groupsCollection->find( array( 'admin_accepted_memberships' => $user->name ), array( 'name' => true, 'displayname' => true ) );
      foreach ( $invitationsResultSet as $row ) {
        $notifications[] = array(
            'type' => 'group_invitation',
            'data' => array(
                'group'       => BeeHub::GROUPS_PATH . rawurlencode( $row['name'] ),
                'displayname' => $row['displayname']
            )
        );
      }

      // Fetch all group membership requests
      $groupRequestsResultSet = $groupsCollection->find( array( 'user_accepted_memberships' => array( '$exists' => true ), 'admins' => $user->name ), array( 'name' => true, 'displayname' => true, 'user_accepted_memberships' => true ) );
      foreach ( $groupRequestsResultSet as $group ) {
        foreach ( $group['user_accepted_memberships'] as $user_name ) {
          $user = BeeHub_Registry::inst()->resource( BeeHub::USERS_PATH . $user_name );
          $notifications[] = array(
              'type' => 'group_request',
              'data' => array(
                  'group'             => BeeHub::GROUPS_PATH . rawurlencode( $group['name'] ),
                  'group_displayname' => $group['displayname'],
                  'user'              => $user->path,
                  'user_displayname'  => $user->user_prop_displayname(),
                  'user_email'        => $user->user_prop( BeeHub::PROP_EMAIL )
              )
          );
        }
      }

      // If the user doesn't have a sponsor, he can't do anything.
      if ( count( $user->prop( BeeHub::PROP_SPONSOR_MEMBERSHIP ) ) === 0 ) {
        $notifications[] = array( 'type'=>'no_sponsor', 'data'=>array() );
      }else{
        // Fetch all sponsor membership requests
        $sponsorsCollection = BeeHub::getNoSQL()->sponsors;
        $sponsorRequestsResultSet = $sponsorsCollection->find( array( 'user_accepted_memberships' => array( '$exists' => true ), 'admins' => $user->name ), array( 'name' => true, 'displayname' => true, 'user_accepted_memberships' => true ) );
        foreach ( $sponsorRequestsResultSet as $sponsor ) {
          foreach ( $sponsor['user_accepted_memberships'] as $user_name ) {
            $user = BeeHub_Registry::inst()->resource( BeeHub::USERS_PATH . $user_name );
            $notifications[] = array(
                'type' => 'sponsor_request',
                'data' => array(
                    'sponsor'             => BeeHub::SPONSORS_PATH . rawurlencode( $sponsor['name'] ),
                    'sponsor_displayname' => $sponsor['displayname'],
                    'user'                => $user->path,
                    'user_displayname'    => $user->user_prop_displayname(),
                    'user_email'          => $user->user_prop( BeeHub::PROP_EMAIL )
                )
            );
          }
        }
      } // end else for if ( count( $user->prop( BeeHub::PROP_SPONSOR_MEMBERSHIP ) ) === 0 )
    } // end if ($auth->is_authenticated())
    return $notifications;
  }


  /**
   * Send an e-mail
   * @param   string|array  $recipients  The recipient or an array of recepients
   * @param   type          $subject     The subject of the message
   * @param   type          $message     The message body
   * @return  void
   */
  public static function email($recipients, $subject, $message) {
    if (is_array($recipients)) {
      $recipients = implode(',', $recipients);
    }
    mail($recipients, $subject, $message, 'From: ' . BeeHub::$CONFIG['email']['sender_name'] . ' <' . BeeHub::$CONFIG['email']['sender_address'] . '>', '-f ' . BeeHub::$CONFIG['email']['sender_address']);
  }
  
  
  /**
   * Return the noSQL database
   * @todo    Move database name to the configuration file
   * @return  MongoDB  The noSQL database
   */
  public static function getNoSQL() {
    static $client;
    if ( ! ( $client instanceof MongoClient ) ) {
      $client = new MongoClient();
    }
    return $client->beehub;
  }


} // class BeeHub

BeeHub::$CONFIG = parse_ini_file(
  dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.ini',
  true
);
// We need SimpleSamlPHP
require_once(BeeHub::$CONFIG['environment']['simplesamlphp_autoloader']);

DAV::$PROTECTED_PROPERTIES[ DAV::PROP_GROUP_MEMBER_SET ] = true;
DAV::$ACL_PROPERTIES[BeeHub::PROP_SPONSOR] =
DAV::$SUPPORTED_PROPERTIES[BeeHub::PROP_SPONSOR] = 'sponsor';
