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
  public function user_set($name, $value = null) {
    $this->init_props();
    if (is_null($value)) {
      if (array_key_exists($name, $this->stored_props)) {
        unset($this->stored_props[$name]);
        $this->touched = true;
      }
    } else {
      if ($value !== @$this->stored_props[$name]) {
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
   * @return array of principals (either paths or properties),
   *         indexed by their own value.
   */
  final public function current_user_sponsors() {
    $retval = array();
    $user = $this->user_prop_current_user_principal();
    if (null === $user)
      return $retval;
    $statement =
    $retval = array(DAVACL::PRINCIPAL_ALL => DAVACL::PRINCIPAL_ALL);
    if ( $current_user_principal = $this->user_prop_current_user_principal() ) {
      $retval = array_merge($retval, self::current_user_principals_recursive($current_user_principal));
      $retval[DAVACL::PRINCIPAL_AUTHENTICATED] = DAVACL::PRINCIPAL_AUTHENTICATED;
    }
    else {
      $retval[DAVACL::PRINCIPAL_UNAUTHENTICATED] = DAVACL::PRINCIPAL_UNAUTHENTICATED;
    }
    return $retval;
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
    if ( ('/' === $this->path) ||
         ('/home/' === $this->path) ){
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


  public function user_prop_owner() {
    $retval = $this->user_prop(DAV::PROP_OWNER);
    return $retval ? $retval : BeeHub::$CONFIG['namespace']['wheel_path'];
  }


  /**
   * @return DAV_Element_href
   */
  final public function prop_sponsor() {
    $retval = $this->user_prop_sponsor();
    return $retval ? new DAV_Element_href($retval) : '';
  }


  final public function set_sponsor($sponsor) {
    $sponsor = DAVACL::parse_hrefs($sponsor);
    if (1 != count($sponsor->URIs))
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST,
        'Illegal value for property sponsor.'
      );
    $this->user_set_sponsor(DAV::parseURI($sponsor->URIs[0]));
  }


  /**
   * @return string path
   */
  public function user_prop_sponsor() {
    return $this->user_prop(BeeHub::PROP_SPONSOR);
  }


  /**
   * @param string $sponsor path
   */
  protected function user_set_sponsor($sponsor) {
    throw new DAV_Status( DAV::HTTP_FORBIDDEN );
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

