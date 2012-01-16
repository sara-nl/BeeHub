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
 * $Id: dav_element_lockdiscovery.php 171 2011-01-24 21:01:13Z kobasoft $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package DAV_Server
 */

/**
 * @package DAV_Server
 */
class DAV_Element_lockdiscovery {
// prop
// `-lockdiscovery
//   `-activelock*
//     |-lockroot
//     | `-href
//     |-lockscope
//     | `-exclusive|shared
//     |-locktype
//     | `-write
//     |-depth
//     |-locktoken?
//     | `-href
//     |-owner?
//     `-timeout? (Second-1*DIGIT|infinite)

  
/**
 * @var array array of DAV_Element_activelock
 */
public $activelocks = array();


/**
 * @return string an XML element
 */
public function toXML() {
  $xml = '';
  foreach ($this->activelocks as $activelock) {
    $xml .=
      "<D:activelock><D:locktype><D:write/></D:locktype>\n" .
      '<D:lockroot><D:href>' . DAV::urlencode( $activelock['lockroot'] ) .
      "</D:href></D:lockroot>\n" .
      '<D:lockscope>' .
      ( $activelock['lockscope'] == DAV::LOCKSCOPE_EXCLUSIVE ?
        '<D:exclusive/>' : '<D:shared/>' ) .
      "</D:lockscope>\n" .
      "<D:depth>{$activelock['depth']}</D:depth>\n";
    if (!$activelock['hide'])
      $xml .= "<D:locktoken><D:href>{$activelock['token']}</D:href></D:locktoken>\n";
    if ( !is_null( $activelock['owner'] ) )
      $xml .= "<D:owner>{$activelock['owner']}</D:owner>\n";
    if ( is_null( $activelock['timeout'] ) )
      $xml .= "<D:timeout>Infinite</D:timeout>\n";
    else
      $xml .= '<D:timeout>Second-' .
        ( $activelock['timeout'] > 0 ? $activelock['timeout'] : 0 ) .
        "</D:timeout>\n";
    $xml .= "</D:activelock>";
  }
  return $xml;
}
  
    
} // class DAV_Element_lockdiscovery


