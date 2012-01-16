<?php

/*·************************************************************************
 * Copyright ©2009 SARA Computing and Networking Services
 *                 Amsterdam, the Netherlands
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
 * $Id: global.php 170 2011-01-19 14:15:53Z kobasoft $
 **************************************************************************/

require_once(dirname(__FILE__) . '/REST/REST.php');
REST::handle_method_spoofing();
REST::setHTML( array('Topos', 'html_start'), array('Topos', 'html_end') );

#if (!isset($_SERVER['PHP_AUTH_USER'])) {
#  header('WWW-Authenticate: Basic realm="ToPoS 4.1"');
#  REST::fatal(
#    REST::HTTP_UNAUTHORIZED,
#    'Please login with your full name, using your e-mail address as the password.'
#  );
#}
#if (!preg_match('/^[\\w.\\-]+@(?:[\\w\\-]+\\.)+[a-z]{1,4}$/i', $_SERVER['PHP_AUTH_PW'])) {
#  header('WWW-Authenticate: Basic realm="ToPoS 4.1"');
#  REST::fatal(
#    REST::HTTP_UNAUTHORIZED,
#    $_SERVER['PHP_AUTH_PW'] . ' is not a valid e-mail address.'
#  );
#}

#require_once('rest.php');

#$debug = fopen(dirname(__FILE__) . '/debug.txt', 'a');
#fwrite($debug, "\n\n" . var_export($_SERVER, true));
#fclose($debug);

#$log = fopen(dirname(__FILE__) . '/log.txt', 'a');
#fwrite(
#  $log,
#  REST::http_date(time()) . "\t" .
#  addcslashes($_SERVER['PHP_AUTH_USER'], "\\\n\t") . "\t" .
#  addcslashes($_SERVER['PHP_AUTH_PW'], "\\\n\t") . "\t" .
#  $_SERVER['REQUEST_METHOD'] . "\t" . 
#  $_SERVER['SCRIPT_FILENAME'] . "\n"
#);
#fclose($log);

//session_name('aanwezigheidsbord');
//session_set_cookie_params( 0, dirname($_SERVER['SCRIPT_NAME']) );
//session_start();

date_default_timezone_set('Europe/Amsterdam');

function sara_exception_handler(Exception $e) {
  REST::fatal(
    REST::HTTP_INTERNAL_SERVER_ERROR,
    '<pre id="message">' . $e->getMessage() . "</pre>\n<pre>" . $e->getTraceAsString() . '</pre>'
  );
}
set_exception_handler('sara_exception_handler');

// Parse the PATH_INFO string, if present:
/**
 * @var string
 */
$TOPOS_POOL =  null;
/**
 * @var string
 */
$TOPOS_TOKEN = null;
if ( !empty($_SERVER['PATH_INFO']) &&
     preg_match( '/\\/([\\w\\-.]+)(?:\\/([\\da-fA-F\\-]+))?/',
                 $_SERVER['PATH_INFO'], $matches ) ) {
  $TOPOS_POOL =  @$matches[1];
  $TOPOS_TOKEN = @$matches[2];
}


/**
 * A MySQL exception
 * @package Topos
 */
class Topos_MySQL extends Exception {}


/**
 * Just a namespace.
 * @package Topos
 */
class Topos {


public static function sortable_date($timestamp) {
  return gmdate( 'Y-m-d\\TH:i:s\\Z', $timestamp );
}
  
  
/**
 * @var mysqli
 */
private static $MYSQLI = null;
/**
 * @return mysqli
 * @throws DAV_Status
 */
public static function mysqli() {
  if (self::$MYSQLI === null) {
    self::$MYSQLI = new mysqli(
      'localhost', 'topos', 'T49WpiQT', 'topos_4_1'
    );
    if ( !self::$MYSQLI )
      throw new Topos_MySQL(mysqli_connect_error());
  }
  return self::$MYSQLI;
}


public static function escape_string($string) {
  return is_null($string)
    ? 'NULL'
    : '\'' . self::mysqli()->escape_string($string) . '\'';
}


public static function poolId($poolName) {
  $escPoolName = self::escape_string($poolName);
  $result = self::query("SELECT getPoolId($escPoolName);");
  $row = $result->fetch_row();
  return $row[0];
}


/**
 * @param string $query
 * @return void
 * @throws Exception
 */
public static function real_query($query) {
  if ( ! self::mysqli()->real_query($query) )
    throw new Topos_MySQL( self::mysqli()->error, self::mysqli()->errno );
}


/**
 * @param string $query
 * @return mysqli_result
 * @throws Exception
 */
public static function query($query) {
  if ( !( $retval = self::mysqli()->query($query) ) )
    throw new Topos_MySQL( self::mysqli()->error, self::mysqli()->errno );
  return $retval;
}


public static function uuid() {
  $result = self::query('SELECT UUID();');
  $row = $result->fetch_row();
  return $row[0];
}


/**
 * @param $title string title in UTF-8
 */
public static function html_start($title) {
  $t_title = htmlspecialchars($title, ENT_COMPAT, "UTF-8");
  $t_index = REST::urlencode( dirname( $_SERVER['REQUEST_URI'] ) );
  if ($t_index != '/') $t_index .= '/';
  $t_stylesheet = self::urlbase() . 'style.css';
  $t_icon       = self::urlbase() . 'favicon.png';
  return DAV::xml_header() . <<<EOS
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
<head>
  <title>{$t_title}</title>
  <link rel="stylesheet" type="text/css" href="{$t_stylesheet}" />
  <link rel="index" rev="child" type="application/xhtml+xml" href="{$t_index}"/>
  <link rel="icon" type="image/png" href="{$t_icon}" />
</head><body>
<div id="div_header">
<div id="div_index"><a rel="index" rev="child" href="{$t_index}">index</a></div>
<h1>{$t_title}</h1>
</div>
EOS;
}


/**
 * Outputs HTML end-tags
 */
public static function html_end() {
  return '</body></html>';
}


/**
 * Shows a message screen to the user.
 * @param string $message HTML message
 * @param string $status HTTP status
 * @param string $redirect URL for automatic redirection
 * @param string $location Location of the created URL
 */
public static function show_message($message, $status, $location) {
  REST::header(array(
    'status' => $status,
    'Content-Type' => REST::best_xhtml_type() . '; charset=UTF-8',
    'Location' => REST::rel2url($location)
  ));
  echo REST::html_start('Redirect') . <<<EOS
<p>{$message}</p>
<script type="text/javascript"><![CDATA[
  setTimeout(
    'window.location.href = "{$location}";',
    1000
  );
]]></script>
EOS;
  echo REST::html_end();
  exit;
}


/**
 * Cache for urlbase().
 * @var string
 */
private static $URLBASE = null;
/**
 * Returns the base URI.
 * The base URI is 'protocol://server.name:port'
 * @return string
 */
public static function urlbase() {
  if ( is_null( self::$URLBASE ) ) {
    //DAV::debug('$_SERVER: ' . var_export($_SERVER, true));
    self::$URLBASE = REST::urlbase() . '/4.1/';
  }
  return self::$URLBASE;
}


} // class Topos



