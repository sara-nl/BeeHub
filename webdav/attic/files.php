<?php

//if ($_SERVER['HTTP_X_LITMUS'] == 'props: 9 (move)') {
//  trigger_error(var_export($_SERVER, true), E_USER_NOTICE);
//  system('ls -laR /home/pieter/tmp/dav/litmus/ > /home/pieter/tmp/dav/debug.txt');
//}
require_once 'include/DAV_Server/DAV_Server.php';


/**
 * Just a namespace
 * @package Topos
 */
class My {
  

public static $BASE_DIR = '/home/pieter/tmp/dav';


/**
 * @var mysqli
 */
private static $MYSQLI = null;
/**
 * @return mysqli
 */
public static function mysqli() {
  if (self::$MYSQLI === null) {
    self::$MYSQLI = new mysqli(
      'localhost', 'topos', 'T49WpiQT', 'topos_new'
    );
    if ( !self::$MYSQLI )
      DAV::fatal('500 ' . mysqli_connect_error());
  }
  return self::$MYSQLI;
}


public static function escape_string($string) {
  return is_null($string)
    ? 'NULL'
    : '\'' . self::mysqli()->escape_string($string) . '\'';
}


/**
 * @param string $query
 * @return void
 * @throws Exception
 */
public static function real_query($query) {
  if (! self::mysqli()->real_query($query))
    throw new Exception(self::mysqli()->error, self::mysqli()->errno);
}


/**
 * @param string $query
 * @return mysqli_result
 * @throws Exception
 */
public static function query($query) {
  if ( !( $retval = self::mysqli()->query($query) ) )
    throw new Exception(self::mysqli()->error, self::mysqli()->errno);
  return $retval;
}


private static $in_transaction = false;
/**
 * @return bool
 */
public static function in_transaction() { return self::$in_transaction; }
public static function begin() {
  self::$in_transaction = true;
  self::real_query('BEGIN;');
}
public static function commit() {
  self::$in_transaction = false;
  self::mysqli()->commit();
}
public static function rollback() {
  self::$in_transaction = false;
  self::mysqli()->rollback();
}
  
public static function uuid() {
  $result = self::mysqli()->query('SELECT UUID()');
  $row = $result->fetch_row();
  $row = "urn:uuid:$row[0]";
  return $row;
}


/**
 * Fabricates a new Unique Database Object ID.
 * @throws object PeopleException E_MYSQL_ERROR
 * @return int a new unique ID.
 */
public static function uid() {
  self::mysqli()->real_query('INSERT INTO `IDSequence` () VALUES ()');
  $insertid = self::mysqli()->insert_id;
  self::mysqli()->real_query(
    "DELETE FROM `IDSequence` WHERE id < $insertid"
  );
  return $insertid;
}


} // class My


class My_Common {

  
public $path;
public $stat_size = null;
public $stat_mtime = null;
public $stat_ctime = null;
public $file_id;
public $file_etag;
public $file_contenttype;

public function __construct($path) {
  $this->path = $path;
  $escpath = My::escape_string($path);
  if (($stat = @lstat(My::$BASE_DIR . $path))) {
    $this->stat_size = $stat['size'];
    $this->stat_mtime = $stat['mtime'];
    $this->stat_ctime = $stat['ctime'];
  }
  $result = My::query(<<<EOS
SELECT `file_id`, `file_etag`, `file_contenttype`
FROM `File`
WHERE `file_path` = $escpath
EOS
  );
  if (!($row = $result->fetch_row()))
    throw new Exception();
  $this->file_id = $row[0];
  $this->file_etag = $row[1];
  $this->file_contenttype = $row[2];
}


public function method_COPY( $path, $overwrite, &$file_id) {
  $retval = false;
  if ( $overwrite and
       ( $resource = DAV_Server::inst()->resource($path) ) ) {
    if ($resource instanceof DAV_Collection)
      $resource->method_DELETE_recursive( $path );
    else
      $resource->method_DELETE();
    $retval = true;
  }
  $escdest = My::escape_string($path);
  $escetag = My::escape_string($this->file_etag);
  $esccontenttype = My::escape_string($this->file_contenttype);
  try {
    My::real_query(<<<EOS
INSERT INTO `File` (`file_path`, `file_etag`, `file_contenttype` )
VALUES ( $escdest, $escetag, $esccontenttype );
EOS
    );
  }
  catch (Exception $e) {
    throw new DAV_Status(412);
  }
  $file_id = My::mysqli()->insert_id;
  $result = My::query(<<<EOS
SELECT `property_namespace`, `property_name`, `property_value`
FROM `FileProperty`
WHERE `file_id` = {$this->file_id};
EOS
  );
  while ($row = $result->fetch_row()) {
    $escnamespace = My::escape_string($row[0]);
    $escname = My::escape_string($row[1]);
    $escvalue = My::escape_string($row[2]);
    My::real_query(<<<EOS
INSERT INTO `FileProperty` (
  `property_namespace`, `property_name`, `property_value`, `file_id`
)
VALUES ($escnamespace, $escname, $escvalue, $file_id)
EOS
    );
  }
  return $retval;
}


/**
 * All properties on a resource.
 * @param array $props an array of requested (live) properties that MUST be
 * included. By default, you're not required to return live properties
 * outside the DAV: namespace, especially if they're computationally
 * intensive.
 * @param DAV_Props $davprops
 * @return DAV_Props A DAV_Props object with all the properties.
 * Alternatively a status string may be returned. Sec.9.1.1 specifically
 * mentions '403 Forbidden'.
 */
public function method_PROPFIND( $props, $davprops ) {
  $result = My::query(<<<EOS
SELECT `property_namespace`, `property_name`, `property_value`
FROM `FileProperty`
WHERE `file_id` = {$this->file_id}
EOS
  );
  while ($row = $result->fetch_row())
    $davprops->setProperty("$row[0] $row[1]", $row[2]);
}


/**
 * @param array $props <pre>array(
 *   "<namespaceURI> <localName>" => value,
 *   ...
 * )</pre>. If the value is null, then the property should be unset.
 * @return array An array of properties that failed, if any. <code>array(
 *   "<namespaceURI> <localName>" => "<status> <string>",
 *   ...
 * )</code>
 * 
 * Sec.9.2.1 mentions the following status codes:
 * - 200 Ok
 * - 403 Forbidden - the client cannot alter the property
 * - 403 Forbidden - the client tried to set a protected property, such as
 *   DAV:getetag
 * - 409 Conflict - inappropriate value syntax/semantics
 * - 424 Failed Dependency
 * - 507 Insufficient Storage
 * Alternatively, just a status string may be returned.
 */
public function method_PROPPATCH( $props ) {
  $retval = array();
  My::begin();
  try {
    foreach ($props as $key => $value) {
      list($namespace, $name) = explode(' ', $key);
      if ($namespace == 'DAV:') {
        $retval[$key] = '403';
        throw new Exception();
      }
      $escnamespace = My::escape_string($namespace);
      $escname = My::escape_string($name);
      if ($name === null)
        throw new DAV_Status(
          400, 'Bad property name'
        );
      My::real_query(<<<EOS
DELETE FROM `FileProperty`
WHERE `file_id` = {$this->file_id}
  AND `property_namespace` = $escnamespace
  AND `property_name` = $escname
EOS
      );
      if ($value !== null) {
        $escvalue = My::escape_string($value);
        My::real_query(<<<EOS
INSERT INTO `FileProperty`  (
  `file_id`, `property_namespace`, `property_name`, `property_value`
)
VALUES (
  {$this->file_id}, $escnamespace, $escname, $escvalue
)
EOS
        );
      }
    }
    My::commit();
  }
  catch (DAV_Status $e) {
    My::rollback();
    throw $e;
  }
  catch(Exception $e) {
    My::rollback();
  }
  return $retval;
}


public function prop_getlastmodified() {
  return $this->stat_mtime;
}


public function prop_creationdate() {
  return $this->stat_ctime;
}


public function prop_getcontentlength() {
  return $this->stat_size;
}


public function prop_getcontenttype() {
  return $this->file_contenttype;
}


public function prop_getetag() {
  return $this->file_etag;
}


public function assert_lock() {
  $submittedtokens = DAV_Server::inst()->submittedTokens();
  $escpath = My::escape_string($this->path);
  $result = My::query(<<<EOS
SELECT `lock_id`, `lock_scope`, `FileLock`.`file_id`
FROM `FileLock` LEFT JOIN `File` USING (`file_id`)
WHERE `File`.`file_id` = {$this->file_id}
   OR ( $escpath LIKE CONCAT(`File`.`file_path`, '%')
        AND `lock_depth` = 'infinity' )
EOS
  );
  $hassharedlocks = array();
  $havesharedlock = array();
  while ($row = $result->fetch_row()) {
    if ($row[1] == 'shared') {
      $hassharedlocks[$row[2]] = true;
      if ( isset( $submittedtokens[$row[0]] ) )
        $havesharedlock[$row[2]] = true;
    } else {
      if ( !isset( $submittedtokens[$row[0]] ) )
        return false;
    }
  }
  foreach ( array_keys( $hassharedlocks ) as $file_id )
    if ( ! @$havesharedlock[$file_id] )
      return false;
  return true;
}


}


class My_Dir extends DAV_Collection {
  

/**
 * @var My_Common
 */
private $common;


public function __construct($path) {
  $this->common = new My_Common($path);
}


/**
 * Handle the COPY request.
 * @param string $path the destination
 * @param bool $overwrite
 * @return bool true if the destination was overwritten, false if it was newly
 * created
 * @throws DAV_Status Sec.9.8.5 mentions the following status codes:
 * - 403 Forbidden - also applicable if source and destination are equal,
 *   but this case is automatically handled for you.
 * - 409 Conflict - one or more intermediate collections are missing at the
 *   destination.
 * - 412 Precondition Failed - also applicable if the Overwrite: header was
 *   set to 'F' and the destination resource was mapped.
 * - 423 Locked - The destination (or members therein) are locked
 * - 507 Insufficient Storage
 */
public function method_COPY( $path, $overwrite ) {
  $file_id = 0;
  $retval = $this->common->method_COPY($path, $overwrite, $file_id);
  if ( !mkdir( My::$BASE_DIR . $path ) ) {
    My::real_query("DELETE FROM `File` WHERE `file_id` = $file_id");
    throw new DAV_Status(409, 'File copy failed. Directory missing?');
  }
  return $retval;
}


public function method_DELETE() {
  if (!$this->common->assert_lock())
    throw new DAV_Status(423);
  $escmask = My::escape_string($this->common->path . '_%');
  $result = My::query(<<<EOS
SELECT COUNT(*)
FROM `File`
WHERE `file_path` LIKE $escmask;
EOS
  );
  $row = $result->fetch_row();
  if ($row[0]) throw new DAV_Status(424);
  if (!rmdir(My::$BASE_DIR . $this->common->path))
    throw new DAV_Status(
      500, 'Cannot remove directory'
    );
  My::real_query(
    "DELETE FROM `File` WHERE `file_id` = {$this->common->file_id};"
  );
}


/**
 * @param string $member
 * @return void
 * @throws DAV_Status Sec.9.3.1 mentions the following status codes:
 * - 201 Created
 * - 403 Forbidden - the server doesn't allow the creation of collections at
 *   the given location
 * - 403 Forbidden - the parent collection cannot accept members
 * - 409 Conflict - intermediate collections are missing
 * - 415 Unsupported Media Type - the client sent a request body that the
 *   server didn't understand
 * - 507 Insufficient Storage
 */
public function method_MKCOL( $member ) {
  if (strstr($member, '%') !== false)
    throw new DAV_Status(400, 'Character "%" not allowed in collection names.');
  $path = $this->common->path . $member;
  $escpath = My::escape_string( $path );
  try {
    My::real_query(<<<EOS
INSERT INTO `File` (`file_path`, `file_etag`, `file_contenttype`)
VALUES ($escpath, CONCAT('"', UUID(), '"'), 'text/html');
EOS
    );
  }
  catch (Exception $e) {
    throw new DAV_Status(412);
  }
  $file_id = My::mysqli()->insert_id;
  if (!mkdir(My::$BASE_DIR . $path)) {
    My::real_query("DELETE FROM `File` WHERE `file_id` = $file_id");
    throw new DAV_Status(507);
  }
}


/**
 * All properties on a resource.
 * @param array $props an array of requested (live) properties that MUST be
 * included. By default, you're not required to return live properties
 * outside the DAV: namespace, especially if they're computationally
 * intensive.
 * @return DAV_Props A DAV_Props object with all the properties.
 * Alternatively a status string may be returned. Sec.9.1.1 specifically
 * mentions '403 Forbidden'.
 */
public function method_PROPFIND( $props ) {
  $retval = parent::method_PROPFIND($props);
  $this->common->method_PROPFIND($props, $retval);
  return $retval;
}


/**
 * @param array $props <pre>array(
 *   "<namespaceURI> <localName>" => value,
 *   ...
 * )</pre>. If the value is null, then the property should be unset.
 * @return array An array of properties that failed, if any. <code>array(
 *   "<namespaceURI> <localName>" => "<status> <string>",
 *   ...
 * )</code>
 * 
 * Sec.9.2.1 mentions the following status codes:
 * - 200 Ok
 * - 403 Forbidden - the client cannot alter the property
 * - 403 Forbidden - the client tried to set a protected property, such as
 *   DAV:getetag
 * - 409 Conflict - inappropriate value syntax/semantics
 * - 424 Failed Dependency
 * - 507 Insufficient Storage
 * Alternatively, just a status string may be returned.
 */
public function method_PROPPATCH( $props ) {
  return $this->common->method_PROPPATCH($props);
}


/**
 * Handle a PUT request.
 * @param array &$headers Headers you want to submit in the HTTP response.
 * @param string $member
 * @return void
 * @throws DAV_Status
 */
public function method_PUT( &$headers, $member ) {
  $path = $this->common->path . $member;
  $escpath = My::escape_string( $path );
  $esccontenttype = My::escape_string(
    isset($_SERVER['CONTENT_TYPE']) ?
      $_SERVER['CONTENT_TYPE'] : 'application/octet-stream'
  );
  My::real_query(<<<EOS
INSERT INTO `File` (`file_path`, `file_etag`, `file_contenttype`)
VALUES ( $escpath, CONCAT('"', UUID(), '"'), $esccontenttype );
EOS
  );
  $file_id = My::mysqli()->insert_id;
  $in  = fopen('php://input', 'r');
  $out = fopen(My::$BASE_DIR . $path, 'w');
  while ( is_string( $block = fread( $in, 4096 ) ) &&
          $block !== '' )
    try {
      if ( fwrite( $out, $block ) === false )
        throw new Exception();
    }
    catch(Exception $e) {
      My::real_query("DELETE FROM `File` WHERE `file_id` = $file_id");
      @fclose($in);
      @fclose($out);
      throw new DAV_Status(507);
    }
  @fclose($in);
  @fclose($out);
}


public function prop_creationdate() {
  return $this->common->prop_creationdate();
}


public function prop_getcontentlength() {
  return $this->common->prop_getcontentlength();
}


public function prop_getcontenttype() {
  return $this->common->prop_getcontenttype();
}


public function prop_getetag() {
  return $this->common->prop_getetag();
}


public function prop_getlastmodified() {
  return $this->common->prop_getlastmodified();
}


public function user_members() {
  $retval = array();
  if ( !( $files = @scandir(My::$BASE_DIR . $this->common->path) ) )
    return $retval;
  foreach ($files as $file)
    if (!preg_match('/^\\.{1,2}$/', $file))
      $retval[] = is_dir(My::$BASE_DIR . $this->common->path . $file)
        ? "$file/" : $file;
  return $retval;
}


}


class My_File extends DAV_Resource {
  

/**
 * @var My_Common
 */
private $common;


public function __construct($path) {
  $this->common = new My_Common($path);
}


/**
 * Handle the COPY request.
 * @param string $path the destination
 * @param bool $overwrite
 * @return bool true if the destination was overwritten, false if it was newly
 * created
 * @throws DAV_Status Sec.9.8.5 mentions the following status codes:
 * - 403 Forbidden - also applicable if source and destination are equal,
 *   but this case is automatically handled for you.
 * - 409 Conflict - one or more intermediate collections are missing at the
 *   destination.
 * - 412 Precondition Failed - also applicable if the Overwrite: header was
 *   set to 'F' and the destination resource was mapped.
 * - 423 Locked - The destination (or members therein) are locked
 * - 507 Insufficient Storage
 */
public function method_COPY( $path, $overwrite ) {
  $file_id = null;
  $retval = $this->common->method_COPY( $path, $overwrite, $file_id );
  if ( !copy( My::$BASE_DIR . $this->common->path,
              My::$BASE_DIR . $path ) ) {
    system('ls -laR /home/pieter/tmp/dav/litmus/ > /home/pieter/tmp/dav/debug.txt');
    My::real_query("DELETE FROM `File` WHERE `file_id` = $file_id");
    throw new DAV_Status(
      409, 'Copy failed: ' . My::$BASE_DIR . $this->common->path .
           ' => ' . My::$BASE_DIR . $path );
  }
  return $retval;
}


public function method_DELETE() {
  if (!$this->common->assert_lock())
    throw new DAV_Status(423);
  if (!unlink(My::$BASE_DIR . $this->common->path))
    throw new DAV_Status(
      500, 'Cannot remove file'
    );
  My::real_query(
    "DELETE FROM `File` WHERE `file_id` = {$this->common->file_id};"
  );
}


public function method_GET(&$headers) {
  $retval = fopen(My::$BASE_DIR . $this->common->path, 'r');
  if (!$retval) return null;
  return $retval;
}
  

/**
 * All properties on a resource.
 * @param array $props an array of requested (live) properties that MUST be
 * included. By default, you're not required to return live properties
 * outside the DAV: namespace, especially if they're computationally
 * intensive.
 * @return DAV_Props A DAV_Props object with all the properties.
 * Alternatively a status string may be returned. Sec.9.1.1 specifically
 * mentions '403 Forbidden'.
 */
public function method_PROPFIND( $props ) {
  $retval = parent::method_PROPFIND($props);
  $this->common->method_PROPFIND($props, $retval);
  return $retval;
}


/**
 * @param array $props <pre>array(
 *   "<namespaceURI> <localName>" => value,
 *   ...
 * )</pre>. If the value is null, then the property should be unset.
 * @return array An array of properties that failed, if any. <code>array(
 *   "<namespaceURI> <localName>" => "<status> <string>",
 *   ...
 * )</code>
 * 
 * Sec.9.2.1 mentions the following status codes:
 * - 200 Ok
 * - 403 Forbidden - the client cannot alter the property
 * - 403 Forbidden - the client tried to set a protected property, such as
 *   DAV:getetag
 * - 409 Conflict - inappropriate value syntax/semantics
 * - 424 Failed Dependency
 * - 507 Insufficient Storage
 * Alternatively, just a status string may be returned.
 */
public function method_PROPPATCH( $props ) {
  return $this->common->method_PROPPATCH($props);
}


/**
 * Handle a PUT request.
 * @param array $headers Headers you want to submit in the HTTP response.
 * @return void
 */
public function method_PUT( &$headers ) {
  return parent::method_PUT($headers);
}


public function prop_creationdate() {
  return $this->common->prop_creationdate();
}


public function prop_getcontentlength() {
  return $this->common->prop_getcontentlength();
}


public function prop_getcontenttype() {
  return $this->common->prop_getcontenttype();
}


public function prop_getetag() {
  return $this->common->prop_getetag();
}


public function prop_getlastmodified() {
  return $this->common->prop_getlastmodified();
}


} // class My_File


function resourceFactory($path) {
  if ($path == '/') $path = '';
  
  try {
    if (is_dir(My::$BASE_DIR . $path))
      return new My_Dir( DAV::slashify( $path ) );
    return new My_File($path);
  }
  catch (Exception $e) {}
  return NULL;
}


DAV::$PARANOID = false;
$locked = false;
if ( !in_array( $_SERVER['REQUEST_METHOD'],
                array( 'GET', 'HEAD', 'OPTIONS', 'PROPFIND' ) ) ) {
  My::real_query(<<<EOS
LOCK TABLES `File` WRITE, `FileLock` WRITE, `FileProperty` WRITE;
EOS
  );
  $locked = true;
}
DAV_Server::inst()->serveRequest('resourceFactory');
if ($locked)
  My::real_query('UNLOCK TABLES;');


?>