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
 * Some class.
 * @package BeeHub
 */
class BeeHub_Resource extends DAVACL_Resource {


  public function isVisible() {
    try {
      $this->assert(DAVACL::PRIV_READ);
    } catch (DAV_Status $e) {
      if (!( $collection = $this->collection() ))
        return false;
      try {
        $collection->assert(DAVACL::PRIV_WRITE);
      } catch (DAV_Status $f) {
        return false;
      }
    }
    return true;
  }


  /**
   * @param array $privileges
   * @throws DAV_Status FORBIDDEN
   */
  public function assert($privileges) {
    if (BeeHub_ACL_Provider::inst()->wheel())
      return;
    return parent::assert($privileges);
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


  /**
   * Returns a list of all ACEs, including all inherited ACEs, with the proper
   * inheritance info.
   * @return Array a list of ACEs.
   */
  public function user_prop_acl_internal() {
    $parent = $this->collection();
    $parent_acl = $parent ? $parent->user_prop_acl_internal() : array();
    $retval = DAVACL_Element_ace::json2aces($this->user_prop(DAV::PROP_ACL));
    while (count($parent_acl)) {
      if (!$parent_acl[0]->inherited)
        $parent_acl[0]->inherited = $parent->path;
      $retval[] = array_shift($parent_acl);
    }
    return $retval;
  }


  public function user_prop_acl() {
    $protected = array(
      new DAVACL_Element_ace(
        'DAV: owner', false, array('DAV: all'), false, true, null
      ),
    );
    if ('/' === $this->path) {
      $protected[] = new DAVACL_Element_ace(
        'DAV: all', false, array('DAV: read', 'DAV: read-acl'), false, true, null
      );
    }
    return array_merge(
      $protected, $this->user_prop_acl_internal()
    );
  }



} // class BeeHub_Resource

