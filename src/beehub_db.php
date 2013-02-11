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

/**
 * A MySQL exception
 * @package BeeHub
 */
class BeeHub_MySQL extends Exception {

}

/**
 * A deadlock occured: Try again.
 * @package BeeHub
 */
class BeeHub_Deadlock extends BeeHub_MySQL {

}

/**
 * Out of resources: maybe later.
 * @package BeeHub
 */
class BeeHub_Timeout extends BeeHub_MySQL {

}


/**
 * An (invisible) wrapper around class mysqli_stmt, which throws exceptions
 * on error.
 */
class BeeHub_Statement {
  /**
   * @var mysqli_stmt
   */
  public $statement;
  public $query;
  public $reflection;
  public $results_array = null;
  /**
   * @param $statement mysqli_stmt
   */
  public function __construct($statement, $query) {
    $this->query = $query;
    if ( $result = $statement->result_metadata() ) {
      $r = array_fill(0, $result->field_count, null);
      switch ($result->field_count) {
        case 0: break;
        case 1: $statement->bind_result( $r[0] ); break;
        case 2: $statement->bind_result( $r[0], $r[1] ); break;
        case 3: $statement->bind_result( $r[0], $r[1], $r[2] ); break;
        case 4: $statement->bind_result( $r[0], $r[1], $r[2], $r[3] ); break;
        case 5: $statement->bind_result( $r[0], $r[1], $r[2], $r[3], $r[4] ); break;
        case 6: $statement->bind_result( $r[0], $r[1], $r[2], $r[3], $r[4], $r[5] ); break;
        case 7: $statement->bind_result( $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6] ); break;
        case 8: $statement->bind_result( $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6], $r[7] ); break;
        case 9: $statement->bind_result( $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6], $r[7], $r[8] ); break;
        default: throw new DAV_Status(
          DAV::HTTP_INTERNAL_SERVER_ERROR,
          "Queries with more than 9 result values are not yet supported."
        );
      }
      $this->results_array = &$r;
    }
    $this->statement = $statement;
    $this->reflection = new ReflectionClass('mysqli_stmt');
  }
  public function fetch_row() {
    $result = $this->statement->fetch();
    if (false === $result)
      throw new BeeHub_MySQL( self::mysqli()->error, self::mysqli()->errno );
    return $result ? $this->results_array : null;
  }
  public function store_result() {
    $retval = $this->statement->store_result();
    if (false === $retval)
      throw new BeeHub_MySQL( self::mysqli()->error, self::mysqli()->errno );
    return $retval;
  }
  public function __call($method, $args) {
    $method = $this->reflection->getMethod($method);
    return $method->invokeArgs($this->statement, $args);
  }
  public function __get($name) { return $this->statement->$name; }
}


/**
 * Just a namespace.
 * @package BeeHub
 */
class BeeHub_DB {


  /**
   * @return mysqli
   * @throws DAV_Status
   */
  public static function mysqli() {
    static $mysqli;
    if ($mysqli === null) {
      $mysqli = new mysqli(
        BeeHub::$CONFIG['mysql']['host'],
        BeeHub::$CONFIG['mysql']['username'],
        BeeHub::$CONFIG['mysql']['password'],
        BeeHub::$CONFIG['mysql']['database']
      );
      if (!$mysqli) {
        $mysqli = null;
        throw new BeeHub_MySQL(mysqli_connect_error(), mysqli_connect_errno());
      }
    }
    return $mysqli;
  }

  public static function execute() {
    $args = func_get_args();
    $query = array_shift($args);
    static $prepared = array();
    if ( !@$prepared[$query] ) {
      if (! ($stmt = self::mysqli()->prepare($query) ) )
        throw new BeeHub_MySQL($query . "\n" . self::mysqli()->error, self::mysqli()->errno);
      $prepared[$query] = $stmt;
    } else
      $stmt = $prepared[$query];
    if (!empty($args)) {
      $ref = new ReflectionClass('mysqli_stmt');
      $method = $ref->getMethod('bind_param');
      $method->invokeArgs($stmt, $args);
    }
    if (!$stmt->execute()) {
      if (self::mysqli()->errno == 1213)
        throw new BeeHub_Deadlock(self::mysqli()->error);
      if (self::mysqli()->errno == 1205)
        throw new BeeHub_Timeout(self::mysqli()->error);
      throw new BeeHub_MySQL(self::mysqli()->error, self::mysqli()->errno);
    }
    $stmt->store_result();
    return new BeeHub_Statement($stmt, $query);
  }

  public static function escape_string($string) {
    return is_null($string) ? 'NULL' : '\'' . self::mysqli()->escape_string($string) . '\'';
  }

  public static function ETag($etag = null) {
    $etag = self::execute('INSERT INTO ETag VALUES()')->insert_id;
    if (!($etag % 100))
      self::execute('DELETE FROM ETag WHERE etag < ?', 'i', $etag);
    return '"' . trim(base64_encode(pack('H*', dechex($etag))), '=') . '"';
  }

  /**
   * @param string $query
   * @return mysqli_result
   * @throws Exception
   */
  public static function query($query) {
    if (!( $retval = self::mysqli()->query($query) )) {
      if (self::mysqli()->errno == 1213)
        throw new BeeHub_Deadlock(self::mysqli()->error);
      if (self::mysqli()->errno == 1205)
        throw new BeeHub_Timeout(self::mysqli()->error);
      throw new BeeHub_MySQL(self::mysqli()->error, self::mysqli()->errno);
    }
    return $retval;
  }

} // class BeeHub_DB

