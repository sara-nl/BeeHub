<?php
/**
 * Contains the BeeHub class
 *
 * Copyright ©2007-2013 SURFsara b.v., Amsterdam, The Netherlands
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

/**
 * This class contains several general (static) functions and is more like a namespace than a real class
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
   * environment values instead of using one of the existing ones.
   */
  const ENVIRONMENT_DEVELOPMENT = 'development';
  const ENVIRONMENT_PRODUCTION  = 'production';
  const ENVIRONMENT_TEST        = 'test';
  /**#@-*/


  /**
   * Returns the base URI.
   * The base URI is 'protocol://server.name:port'
   * @return string
   */
  public static function urlbase($https = null) {
    $cache = DAV_Cache::inst( 'BeeHub' );
    $URLBASE = $cache->get( 'urlbase' );
    if ( is_null( $URLBASE ) ) {
      $URLBASE = array();
    }
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
      $cache->set( 'urlbase', $URLBASE );
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
    require( 'views/' . 'html_error.php' );
    exit;
  }


  /**
   * Determine the local path in the storage backend
   *
   * @param   string  $path  The path in the webDAV namespace
   * @return  string         The location where the data is stored in the storage backend
   */
  public static function localPath($path) {
    $path = rawurldecode( $path );
    if ( substr( $path, 0, 1 ) === '/' ) {
      $path = substr( $path, 1 );
    }
    return DAV::unslashify( self::$CONFIG['environment']['datadir'] . $path );
  }


  /**
   * Returns the best content-type value for (X)HTML output
   *
   * For now this should always return text/html
   *
   * @return  string  The best content-type value
   */
  public static function best_xhtml_type() {
    return 'text/html';
    // The rest of the function will be skipped. This is because ExtJS doesn't support X(HT)ML, so we always need to send it as 'text/html'
    return ( false === strstr(@$_SERVER['HTTP_USER_AGENT'], 'MSIE') &&
            false === strstr(@$_SERVER['HTTP_USER_AGENT'], 'Microsoft') ) ?
            'application/xhtml+xml' : 'text/html';
  }


  /**
   * Handles method spoofing
   *
   * Some browsers (read: Internet Explorer) don't support all webDAV request
   * methods. For these browsers, the build-in client will use a (non-standard)
   * form of method spoofing which this server can handle.
   *
   * The HTTP POST method is used along with a (GET) query string containing one
   * variable '_method' describing the method that should have been used. For
   * example: POST /path/to/resource?_method=ACL
   *
   * This function puts the original request method in
   * $_SERVER['ORIGINAL_REQUEST_METHOD'], removes the _method key from $_GET,
   * rebuilds $_SERVER['QUERY_STRING'] to exclude _method, also rebuilds
   * $_SERVER['REQUEST_URI'] to do the same and of course sets
   * $_SERVER['REQUEST_METHOD'] to the spoofed method. Also, it copies $_POST to
   * $_GET if the spoofed method is GET (and $_POST is cleared).
   *
   * @return void
   */
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
    $retval = DAV::$REGISTRY->resource( $name );
    if ( !$retval || !( $retval instanceof BeeHub_User ) ) throw new DAV_Status(
      DAV::HTTP_FORBIDDEN, DAV::COND_RECOGNIZED_PRINCIPAL
    );
    return $retval;
  }


  /**
   * Convert a path to a BeeHub_Group
   *
   * @param   string        $name  The path or name of the resource
   * @return  BeeHub_Group
   * @throws  DAV_Status           DAV::HTTP_FORBIDDEN when the path provided doesn't point to a group
   */
  public static function group($name) {
    if ($name[0] !== '/')
      $name = BeeHub::GROUPS_PATH .
        rawurlencode($name);
    $retval = DAV::$REGISTRY->resource( $name );
    if ( !$retval || !( $retval instanceof BeeHub_Group ) ) throw new DAV_Status(
      DAV::HTTP_FORBIDDEN, DAV::COND_RECOGNIZED_PRINCIPAL
    );
    return $retval;
  }


  /**
   * Convert a path to a BeeHub_Sponsor
   *
   * @param   string          $name  The path or name of the resource
   * @return  BeeHub_Sponsor
   * @throws  DAV_Status             DAV::HTTP_FORBIDDEN when the path provided doesn't point to a sponsor
   */
  public static function sponsor($name) {
    if ($name[0] !== '/')
      $name = BeeHub::SPONSORS_PATH .
        rawurlencode($name);
    $retval = DAV::$REGISTRY->resource( $name );
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
   * @param   BeeHub_Auth  $auth  An instance of the authentication class that can be used to determine the current user
   * @return  array               An array with notifications.
   */
  public static function notifications( BeeHub_Auth $auth ) {
    $notifications = array();
    if ($auth->is_authenticated()) {
      $currentUser = $auth->current_user();

      // Fetch all group invitations
      $groupsCollection = BeeHub::getNoSQL()->groups;
      $invitationsResultSet = $groupsCollection->find( array( 'admin_accepted_memberships' => $currentUser->name ), array( 'name' => true, 'displayname' => true ) );
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
      $groupRequestsResultSet = $groupsCollection->find( array( 'user_accepted_memberships' => array( '$exists' => true ), 'admins' => $currentUser->name ), array( 'name' => true, 'displayname' => true, 'user_accepted_memberships' => true ) );
      foreach ( $groupRequestsResultSet as $group ) {
        foreach ( $group['user_accepted_memberships'] as $user_name ) {
          $user = DAV::$REGISTRY->resource( BeeHub::USERS_PATH . $user_name );
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
      if ( count( $currentUser->prop( BeeHub::PROP_SPONSOR_MEMBERSHIP ) ) === 0 ) {
        $notifications[] = array( 'type'=>'no_sponsor', 'data'=>array() );
      }else{
        // Fetch all sponsor membership requests
        $sponsorsCollection = BeeHub::getNoSQL()->sponsors;
        $sponsorRequestsResultSet = $sponsorsCollection->find( array( 'user_accepted_memberships' => array( '$exists' => true ), 'admins' => $currentUser->name ), array( 'name' => true, 'displayname' => true, 'user_accepted_memberships' => true ) );
        foreach ( $sponsorRequestsResultSet as $sponsor ) {
          foreach ( $sponsor['user_accepted_memberships'] as $user_name ) {
            $user = DAV::$REGISTRY->resource( BeeHub::USERS_PATH . $user_name );
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
  
  
  private static $emailer = null;


  public static function setEmailer( BeeHub_Emailer $emailer ) {
    self::$emailer = $emailer;
  }


  /**
   * Send an e-mail
   * @param   string|array  $recipients  The recipient or an array of recepients
   * @param   type          $subject     The subject of the message
   * @param   type          $message     The message body
   * @return  void
   */
  public static function email($recipients, $subject, $message) {
    if ( is_null( self::$emailer ) ) {
      self::$emailer = new BeeHub_Emailer();
    }
    self::$emailer->email($recipients, $subject, $message);
  }
  

  /**
   * @var  BeeHub_Auth  The instance that handles the authentication
   */
  private static $auth = null;


  /**
   * Get the instance that handles the authentication
   *
   * @return  BeeHub_Auth  The instance that handles the authentication
   */
  public static function getAuth() {
    if ( is_null( self::$auth ) ) {
      self::$auth = BeeHub_Auth::inst();
    }
    return self::$auth;
  }


  /**
   * Set the instance that should handle the authentication
   *
   * @param   BeeHub_Auth  $auth  The instance that should handle the authentication
   * @return  void
   */
  public static function setAuth( BeeHub_Auth $auth ) {
    self::$auth = $auth;
  }


  /**
   * This should be made private in the future. See the comments at the bottom of this source file
   * @var  array  Contains the configuration options once they are parsed from file
   */
  public static $CONFIG = null;


  public static function loadConfig( $path = null ) {
    if ( is_null( $path ) ) {
      $path = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'config.ini';
    }
    BeeHub::$CONFIG = parse_ini_file( $path, true );
    if ( substr( BeeHub::$CONFIG['environment']['simplesamlphp'], -1 ) !== DIRECTORY_SEPARATOR ) {
      BeeHub::$CONFIG['environment']['simplesamlphp'] .= DIRECTORY_SEPARATOR;
    }
    if ( substr( BeeHub::$CONFIG['environment']['datadir'], -1 ) !== DIRECTORY_SEPARATOR ) {
      BeeHub::$CONFIG['environment']['datadir'] .= DIRECTORY_SEPARATOR;
    }
  }


  /**
   * Returns the configuration file parsed into an array
   * @return  array  An array with the configuration options
   */
  public static function config() {
    if ( is_null( self::$CONFIG ) ) {
      self::loadConfig();
    }
    return self::$CONFIG;
  }
  
  
  /**
   * Changes one particular configuration field
   * 
   * @param   string  $section  The section containing the field
   * @param   string  $field    The field to change
   * @param   mixed   $value    The new value for the field
   * @return  void
   */
  public static function changeConfigField( $section, $field, $value ) {
    $section = strval( $section );
    $field = strval( $field );
    if ( !isset( self::$CONFIG[$section] ) || !is_array( self::$CONFIG[$section] ) ) {
      self::$CONFIG[$section] = array();
    }
    self::$CONFIG[$section][$field] = $value;
  }
  
  
  /**
   * @var  MongoClient  The mongoDB client
   */
  private static $mongo = null;
  
  
  /**
   * Return the noSQL database
   * @todo    Move database name to the configuration file
   * @return  MongoDB  The noSQL database
   */
  public static function getNoSQL() {
    $config = BeeHub::config();
    if ( ! isset( $config['mongo'] ) || ! isset( $config['mongo']['database'] ) ) {
      throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR );
    }

    if ( ! ( self::$mongo instanceof MongoClient ) ) {
      if ( ! empty( $config['mongo']['host'] ) ) {
        $server = 'mongodb://';
        if ( ! empty( $config['mongo']['user'] ) && ! empty( $config['mongo']['password'] ) ) {
          $server .= $config['mongo']['user'] . ':' . $config['mongo']['password'];
        }
        $server .= $config['mongo']['host'];
        if ( ! empty( $config['mongo']['port'] ) ) {
          $server .= ':' . $config['mongo']['port'];
        }
        self::$mongo = new MongoClient( $server );
      }else{
        self::$mongo = new MongoClient();
      }
    }

    return self::$mongo->selectDB( $config['mongo']['database'] );
  }
  
  
  /**
   * Closes the current mongo connection so a new connection will be made
   */
  public static function forceMongoReconnect() {
    if ( self::$mongo instanceof MongoClient ) {
      self::$mongo->close();
    }
    self::$mongo = null;
  }

  
  /**
   * Returns a new, unique, unused ETag
   * @return  String      The new ETag
   * @throws  DAV_Status  When a database error occurs
   */
  public static function ETag() {
    $result = BeeHub::getNoSQL()->command( array(
      'findAndModify' => "beehub_system",
      'query'         => array( 'name' => 'etag' ),
      'update'        => array( '$inc' => array( 'counter' => 1 ) ),
      'new'           => true
    ) );
    
    if ( $result['ok'] != 1 ) {
trigger_error(print_r($result, true));
      throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR );
    }
    $etag = $result['value']['counter'];
    return '"' . trim( base64_encode( pack( 'H*', dechex( $etag ) ) ), '=' ) . '"';
  }


  /**
   * The default exception handler, so we always output nice errors if an exception is uncaught
   * @param   exception  $exception  The (uncaught) exception
   * @return  void
   */
  public static function exception_handler( $exception ) {
    if ( ! $exception instanceof DAV_Status ) {
      $exception = new DAV_Status(
        DAV::HTTP_INTERNAL_SERVER_ERROR,
        strval( $exception )
      );
    }
    $exception->output();
  }

} // class BeeHub

// When PHP 5.4 is more widely adapted, all calls to BeeHub::$CONFIG['some_key']
// should be replaced by BeeHub::config()['some_key']. But as PHP 5.3.3 and
// earlier don't support this, let's not make things more complicated and make
// BeeHub::$CONFIG public and call BeeHub::loadConfig() here so it is always filled.
BeeHub::loadConfig();

// End of file
