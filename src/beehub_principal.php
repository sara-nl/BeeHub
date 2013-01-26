<?php

/******************************************************************************
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
 ******************************************************************************/

/**
 * File documentation (who cares)
 * @package BeeHub
 */

/**
 * A class.
 * @package BeeHub
 *
 */
abstract class BeeHub_Principal extends BeeHub_File implements DAVACL_Principal {

  public function __construct($path) {
    $localPath = BeeHub::localPath($path);
    if (!file_exists($localPath)) {
      $result = touch($localPath);
      if (!$result)
        throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
      xattr_set($localPath, rawurlencode(DAV::PROP_GETETAG), BeeHub::ETag(0));
      xattr_set($localPath, rawurlencode(DAV::PROP_OWNER), BeeHub::$CONFIG['webdav_namespace']['wheel_path']);
    }
    parent::__construct($path);
  }

  public function user_prop_alternate_uri_set() {
    return array();
  }

  public function user_prop_principal_url() {
    return $this->path;
  }

  public function property_priv_read($properties) {
    $retval = array();
    try {
      $this->assert(DAVACL::PRIV_READ);
      foreach ($properties as $property)
        $retval[$property] = true;
    } catch (DAV_Status $e) {
      foreach ($properties as $property)
        $retval[$property] = false;
    }
    if (isset($retval[DAV::PROP_ACL]))
      try {
        $this->assert(DAVACL::PRIV_READ_ACL);
        $retval[DAV::PROP_ACL] = true;
      } catch (DAV_Status $e) {
        $retval[DAV::PROP_ACL] = false;
      }
    if (isset($retval[DAV::PROP_OWNER]))
      $retval[DAV::PROP_OWNER] = true;
    return $retval;
  }

}

// class BeeHub_Principal


