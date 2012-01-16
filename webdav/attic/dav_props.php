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
 * $Id: dav_props.php 170 2011-01-19 14:15:53Z kobasoft $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package DAV_Server
 */

/**
 * Helper class representing an array of WebDAV properties.
 * @package DAV_Server
 */
class DAV_Props {
  
    
/**
 * Array of properties.
 * @var array
 */
private $properties = array();


/**
 * Sets a property.
 * @param string $property MUST be "<namespaceURI> <localName>"
 * @param string $xmlvalue an XML fragment
 * @return DAV_Props $this
 */
public function setProperty( $property, $xmlvalue = null ) {
  if (is_null($xmlvalue))
    unset( $this->properties[ $property ] );
  else
    $this->properties[ $property ] = $xmlvalue;
  return $this;
}


/**
 * Gets a property.
 * @param string $property MUST be "<namespaceURI> <localName>"
 * @return string an xml fragment
 */
public function getProperty( $property ) {
  return isset( $this->properties[ $property ] )
    ? $this->properties[ $property ]
    : null;
}


/**
 * Array of statuses.
 * @var array
 */
private $status = array();


/**
 * Sets a status for a property.
 * @param string $property MUST be "<namespaceURI> <localName>"
 * @param string $status a valid status string, like '200 Ok' or
 *        '403 Go away!'
 * @return DAV_Props $this
 */
public function setStatus($property, $status) {
  if (substr($status, 0, 3) === '200') {
    if ( isset( $this->status[$property] ) )
      unset( $this->status[$property] );
  } else {
    $this->status[$property] = $status;
  }
  return $this;
}


/**
 * Serializes this object to XML.
 * @param bool $hideValues
 * @return string XML
 */
public function toXML($hideValues = false) {
  // Create an array $status with only the statuses for properties that are
  // actually set. ($this->status might contain some pollution)
  $status = array( '200' => array() );
  foreach ( array_keys( $this->properties ) as $p )
    if ( isset( $this->status[$p] ) )
      $status[$this->status[$p]][] = $p;
    else
      $status['200'][] = $p;
      
  // Start generating some XML:
  $xml = '';
  // Each defined status gets its own <D:propstat> element:
  foreach ($status as $s => $props) {
    $xml .= '<D:propstat><D:prop';
    // Build an array of namespaces that are used by the properties within
    // this propstat section:
    $namespaces = array( 'DAV:' => 'D' );
    foreach ($props as $prop) {
      list($namespaceURI, $localName) = explode(' ', $prop, 2);
      // Sanity check. This should never happen, but I can't throw an exception
      // here because the caller is probably already sending output to the
      // client. And we don't want malformed XML!
      if (is_null($localName)) {
        trigger_error(
          debug_backtrace(),
          E_USER_WARNING
        );
        continue;
      }
      if (!isset($namespaces[$namespaceURI])) {
        $namespaces[$namespaceURI] = $ns = 'ns' . count($namespaces);
        $xml .= ' xmlns:' . $ns . '="' . $namespaceURI . '"';
      }
    }
    $xml .= ">\n";
    // Right! Now let's output the properties themselves.
    foreach ($props as $prop) {
      list($namespaceURI, $localName) = explode(' ', $prop);
      // Sanity check. This should never happen, but I can't throw an exception
      // here because the caller is probably already sending output to the
      // client. And we don't want malformed XML!
      if (is_null($localName)) continue;
      $xml .= ( $hideValues ||
                "{$this->properties[$prop]}" === '' )
        ? (
            '<' . $namespaces[$namespaceURI] . ':' . $localName . "/>\n"
          )
        : (
            '<' . $namespaces[$namespaceURI] . ':' . $localName . '>' .
            $this->properties[$prop] .
            '</' . $namespaces[$namespaceURI] . ':' . $localName . ">\n"
          );
    }
    // And give the status itself!
    list( $status_code, $message ) = explode(' ', $s, 2);
    $status_code = (int)($status_code);
    $xml .=
      "</D:prop>\n<D:status>HTTP/1.1 " .
      REST::status_code($status_code) .
      "</D:status>\n";
    if (!is_null($message))
      $xml .= "<D:responsedescription>$message</D:responsedescription>\n";
    $xml .= "</D:propstat>\n";
  }
  return $xml;
}


/**
 * Outputs a multistatus body.
 * @param array $info an array of (<path> => DAV_Props) pairs.
 * @param bool $hideValues Hide values of properties
 */
private static function props($info, $hideValues = false)
{
  REST::header( array(
    'Content-Type' => 'application/xml; charset=UTF-8',
    'Accept-Ranges' => 'bytes',
    'status' => '207'
  ));
  echo DAV::xml_header() . "<D:multistatus xmlns:D=\"DAV:\">\n";

  foreach ($info as $path => $props) {
    echo
      '<D:response><D:href>' .
      REST::urlencode( $path ) .
      "</D:href>\n";
    if ($props instanceof DAV_Props)
      echo $props->toXML( $hideValues );
    else {
      if ($props instanceof Exception)
        $props = $props->__toString();
      $message = null;
      $status = explode(' ', "$props", 2);
      if (count($status) == 2)
        $message = $status[1];
      $status = (int)($status[0]);
      echo "<D:propstat><D:prop/>\n<D:status>HTTP/1.1 " .
        REST::status_code($status) .
        "</D:status>\n";
      if (!is_null($message))
        echo "<D:responsedescription>$message</D:responsedescription>\n";
      echo "</D:propstat>\n";
    }
    echo "</D:response>\n";
  }
  echo "</D:multistatus>\n";
}
  
    
} // class DAV_Props

