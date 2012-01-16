<?php

/*·************************************************************************
 * Copyright ©2007-2011 Pieter van Beek, Almere, The Netherlands
 * 		    <http://purl.org/net/6086052759deb18f4c0c9fb2c3d3e83e>
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
 * $Id: sd_principal.php 3349 2011-07-28 13:04:24Z pieterb $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package SD
 */

/**
 * A class.
 * @package SD
 *
 */
abstract class SD_Principal extends SD_File implements DAVACL_Principal {

  
public function __construct($path) {
  $localPath = SD::localPath($path);
  if (!file_exists($localPath)) {
    $result = touch($localPath);
    if ( !$result )
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    xattr_set( $localPath, rawurlencode(DAV::PROP_GETETAG), SD::ETag(0) );
    xattr_set( $localPath, rawurlencode(DAV::PROP_OWNER  ), SD::WHEEL_PATH );
  }
  parent::__construct($path);
}


protected $display_name = null;
public function user_prop_displayname() {
  return $this->display_name;
}


public function user_set_displayname() {
  throw new DAV_Status(
    DAV::HTTP_FORBIDDEN,
    DAV::COND_CANNOT_MODIFY_PROTECTED_PROPERTY
  );
}


public function user_prop_alternate_uri_set() { return array(); }


public function user_prop_principal_url() {
  return $this->path;
}


public function property_priv_read($properties) {
  $retval = array();
  try {
    $this->assert(DAVACL::PRIV_READ);
    foreach ($properties as $property)
      $retval[$property] = true;
  }
  catch (DAV_Status $e) {
    foreach ($properties as $property)
      $retval[$property] = false;
  }
  if (isset($retval[DAV::PROP_ACL]))
    try {
      $this->assert(DAVACL::PRIV_READ_ACL);
      $retval[DAV::PROP_ACL] = true;
    }
    catch (DAV_Status $e) {
      $retval[DAV::PROP_ACL] = false;
    }
  if (isset($retval[DAV::PROP_OWNER]))
    $retval[DAV::PROP_OWNER] = true;
  return $retval;
}


} // class SD_Principal


