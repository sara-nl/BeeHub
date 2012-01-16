<?php

/*·************************************************************************
 * Copyright ©2007-2011 Pieter van Beek <http://pieterjavanbeek.hyves.nl/>
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
 * $Id: dav_multistatus.php 170 2011-01-19 14:15:53Z kobasoft $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package DAV_Server
 */


/**
 * A status, returned by the user.
 * @package DAV_Server
 */
class DAV_Multistatus extends Exception {
  
  
/**
 * @var array <code>array( <path> => <status>, ... )</code>
 */
private $statuses = array();
/**
 * @return array <code>array( <path> => <status>, ... )</code>
 */
public function statuses() { return $this->statuses; }


/**
 * @param DAV_Multistatus $multistatus
 * @return DAV_Multistatus $this
 */
public function mergeWith( $multistatus ) {
  foreach ($multistatus->statuses() as $path => $status)
    $this->statuses[$path] = $status;
  return $this;
}


/**
 * @param string $path
 * @param DAV_Status $status
 * @return DAV_Multistatus $this
 * @see DAV_Status::__construct()
 */
public function addStatus($path, $status) {
  $this->statuses[$path] = $status;
  return $this;
}
  
  
//public $headers = array();
public function __construct()
{
  parent::__construct(REST::HTTP_MULTI_STATUS);
}


public function output() {
  // first, check if there's only one status, for the current resource:
  $paths = array_keys($this->statuses);
  if ( count($paths) == 1 and
       $paths[0] == DAV::$PATH ) {
    $this->statuses[$paths[0]]->output();
    return;
  }
  
  REST::header( array(
    'Content-Type' => 'application/xml; charset=UTF-8',
    'status' => '207'
  ));
  echo self::xml_header() . "<D:multistatus xmlns:D=\"DAV:\">\n";

  foreach ($this->statuses as $path => $props) {
    echo
      '<D:response><D:href>' .
      REST::urlencode( $path ) .
      "</D:href>\n";
    $message = null;
    if ($props instanceof Exception)
      $props = $props->__toString();
    $status = explode(' ', "$props", 2);
    if (count($status) == 2) $message = $status[1];
    $status = REST::status_code((int)($status[0]));
    echo "<D:status>HTTP/1.1 $status</D:status>\n";
    if ( !is_null($message) ) {
      $condition = preg_split( '@\\s+@', $message, 2 );
      $condition = $condition[0];
      $conditions = DAV::$CONDITIONS;
      if ( isset( $conditions[$condition] ) ) {
        echo "<D:error><D:$condition";
        if (strlen($message) > strlen($condition))
          echo '>' . substr($message, strlen($condition)) . "</D:$condition>";
        else
          echo '/>';
        echo "</D:error>\n";
        break;
      }
      echo "<D:responsedescription>$message</D:responsedescription>\n";
    }
    echo "</D:response>\n";
  }
  echo "</D:multistatus>\n";
}


public function __toString() {
  throw new DAV_Status(
    REST::HTTP_INTERNAL_SERVER_ERROR,
    'DAV_Multistatus cannot be converted to a string'
  );
}
  
  
} // class DAV_Status

