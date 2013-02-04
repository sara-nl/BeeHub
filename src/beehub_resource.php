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
abstract class BeeHub_Resource extends DAVACL_Resource {


  /**
   * @var boolean
   */
  protected $touched = false;


  /**
   * @var array Array of propery_name => property_value pairs.
   */
  protected $stored_props = null;


  abstract protected function init_props();


  public function method_HEAD() {
    $this->assert(DAVACL::PRIV_READ);
    return array();
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


  /**
   * @param $name string
   * @param $value mixed
   */
  protected function user_set($name, $value = null) {
    $this->init_props();
    if (is_null($value)) {
      if (array_key_exists($name, $this->stored_props)) {
        unset($this->stored_props[$name]);
        $this->touched = true;
      }
    } else {
      if ($value !== $this->stored_props[$name]) {
        $this->stored_props[$name] = $value;
        $this->touched = true;
      }
    }
  }


  public function isVisible() {
    try {
      $this->assert(DAVACL::PRIV_READ);
    } catch (DAV_Status $e) {
      if (!( $collection = $this->collection() ))
        return false;
      try {
        $collection->assert(DAVACL::PRIV_READ);
      } catch (DAV_Status $f) {
        return false;
      }
    }
    return true;
  }


  /**
   * @param $name string
   * @param $value mixed
   */
  public function user_prop($name) {
    $this->init_props();
    return @$this->stored_props[$name];
  }


  /**
   * @see DAV_Resource::property_priv_read()
   */
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
    foreach( array(
      DAV::PROP_OWNER,
      DAV::PROP_RESOURCETYPE,
      DAV::PROP_DISPLAYNAME
    ) as $prop )
      if (array_key_exists($prop, $retval))
        $retval[$prop] = true;
    return $retval;
  }


  /**
   * @return Array a list of ACEs.
   */
  abstract public function user_prop_acl_internal();


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
    $parent = $this->collection();
    $inherited = $parent ? $parent->user_prop_acl() : array();
    while ( count($inherited) && $inherited[0]->protected )
      array_shift($inherited);
    foreach( $inherited as &$ace )
      if ( ! $ace->inherited )
        $ace->inherited = $parent->path;
    return array_merge(
      $protected, $this->user_prop_acl_internal(), $inherited
    );
  }


  /**
   * Should be overriden by BeeHub_File
   */
  public function user_prop_getcontenttype() {
    //return 'httpd/unix-directory';
    // Hmm, this was commented out, but why? I think XHTML is perfect for now.
    // [PieterB]
    return BeeHub::best_xhtml_type() . '; charset="utf-8"';
  }


  public function include_view(
    $view_name_BeEhUb_MaGiC = null,
    $parameters_BeEhUb_MaGiC = null
  ) {
    if (is_null($view_name_BeEhUb_MaGiC))
      $view_name_BeEhUb_MaGiC = strtolower(get_class($this));
    if (is_null($parameters_BeEhUb_MaGiC))
      $parameters_BeEhUb_MaGiC = array();
    foreach ( $parameters_BeEhUb_MaGiC as $param_BeEhUb_MaGiC => $value_BeEhUb_MaGiC ) {
      $$param_BeEhUb_MaGiC = $value_BeEhUb_MaGiC;
    }
    set_include_path(
      realpath( dirname(__FILE__) . '/..' ) .
      PATH_SEPARATOR . get_include_path()
    );
    require( 'views/' . $view_name_BeEhUb_MaGiC . '.php' );
  }


} // class BeeHub_Resource

