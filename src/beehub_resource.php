<?php
/**
 * Contains the BeeHub_Resource class
 *
 * Copyright ©2007-2013 SURFsara b.v., Amsterdam, The Netherlands
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
 * @package BeeHub
 */

/**
 * All BeeHub (webDAV) resources inherit from this class
 * @package BeeHub
 */
abstract class BeeHub_Resource extends DAVACL_Resource {


  /**
   * @var  boolean  True if one of the properties are changed
   */
  protected $touched = false;


  /**
   * @var  array  Array of propery_name => property_value pairs.
   */
  protected $stored_props = null;


  /**
   * Loads all the properties from persistent storage
   *
   * This function loads all properties from persistent storage. They can then
   * be modified and should later be stored into persistence with
   * DAV_Resource::storeProperties()
   *
   * @return  void
   */
  abstract protected function init_props();



  /**
   * Checks whether the current user has certain privileges.
   *
   * @param   array       $privileges  The privileges needed
   * @return  void                     This function only returns if the current user has the privileges
   * @throws  DAV_Status               FORBIDDEN  When the user doesn't have the privileges
   */
  public function assert($privileges) {
    if (BeeHub_ACL_Provider::inst()->wheel())
      return;
    return parent::assert($privileges);
  }


  /**
   * Sets a (webDAV) property
   *
   * @param   string  $name   The name of the property
   * @param   mixed   $value  The value of the property
   * @return  void
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


  /**
   * Checks whether this resource is visible to the current user
   *
   * @return  boolean  True if this resource is visible, false otherwise
   */
  public function isVisible() {
    try {
      $this->assert(DAVACL::PRIV_READ);
    } catch (DAV_Status $e) {
      return false;
      #if (!( $collection = $this->collection() ))
      #  return false;
      #try {
      #  $collection->assert(DAVACL::PRIV_READ);
      #} catch (DAV_Status $f) {
      #  return false;
      #}
    }
    return true;
  }


  /**
   * Gets a (webDAV) property
   *
   * @param   string  $name  The name of the property
   * @return  mixed          The value of the property
   */
  public function user_prop($name) {
    $this->init_props();
    return @$this->stored_props[$name];
  }


  /**
   * Returns an array with all sponsors of the current user
   *
   * @return  array  An array of principals (either paths or properties), indexed by their own value
   */
  final public function current_user_sponsors() {
    $retval = array();
    $user = $this->user_prop_current_user_principal();
    if (null === $user)
      return $retval;
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
   * Determines which properties are readable
   *
   * @param   array  $properties  An array with the properties to check
   * @return  array               An array of (property => isReadable) pairs.
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
   * Gets all not inherited and not protected ACEs of an ACL
   *
   * @return  array  A list of ACEs
   */
  abstract public function user_prop_acl_internal();


  /**
   * Gets a complete ACL (including inherited and protected ACEs)
   *
   * @return  array  A list of ACEs
   */
  public function user_prop_acl() {
    $protected = array(
      new DAVACL_Element_ace(
        'DAV: owner', false, array('DAV: all'), false, true, null
      ),
    );
    if ( ('/' === $this->path) ||
         ('/home/' === $this->path) ){
      $protected[] = new DAVACL_Element_ace(
        'DAV: all', false, array( 'DAV: read' ), false, true, null
      );
    }else{
      $protected[] = new DAVACL_Element_ace(
        'DAV: all', false, array('DAV: unbind'), false, true, null
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
   * Gets the (path of the) owner of this resource
   *
   * @return  string  The path to the owner
   */
  public function user_prop_owner() {
    $retval = $this->user_prop(DAV::PROP_OWNER);
    return $retval ? $retval : BeeHub::$CONFIG['namespace']['wheel_path'];
  }


  /**
   * Gets DAV_Element_href with the path of the sponsor of this resource
   *
   * @return  DAV_Element_href  The path to the sponsor
   */
  final public function prop_sponsor() {
    $retval = $this->user_prop_sponsor();
    return $retval ? new DAV_Element_href($retval) : '';
  }


  /**
   * Set the sponsor of this resource
   * @param   string  $sponsor  The path to the new sponsor in an XML piece (<D:href></D:href>)
   * @return  void
   */
  final public function set_sponsor($sponsor) {
    $sponsor = DAVACL::parse_hrefs($sponsor);
    if (1 !== count($sponsor->URIs))
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST,
        'Illegal value for property sponsor.'
      );
    $this->user_set_sponsor(DAV::parseURI($sponsor->URIs[0]));
  }


  /**
   * Gets the (path of the) sponsor of this resource
   *
   * @return  string  The path to the sponsor
   */
  public function user_prop_sponsor() {
    return $this->user_prop(BeeHub::PROP_SPONSOR);
  }


  /**
   * Sets the sponsor of this resource
   *
   * @param   string  $sponsor  The path to the new sponsor
   * @return  void
   */
  protected function user_set_sponsor($sponsor) {
    throw new DAV_Status( DAV::HTTP_FORBIDDEN );
  }


  /**
   * Gets the getcontenttype property
   *
   * @return  string  The content-type
   */
  public function user_prop_getcontenttype() {
    return BeeHub::best_xhtml_type() . '; charset="utf-8"';
  }


  /**
   * Include a view (mostly an HTML page) to present to the user
   *
   * @param   string  $view_name   Optionally; The name of the view. If omitted, the name of the current class is used.
   * @param   array   $parameters  Optionally; Variables which should be available in the view. This should be an array with variable names as array keys.
   * @return  void
   */
  public function include_view() {
    if ( ( func_num_args() > 1 ) && !is_null( func_get_arg(1) ) ) {
      foreach ( func_get_arg(1) as $param_BeEhUb_MaGiC => $value_BeEhUb_MaGiC ) {
        $$param_BeEhUb_MaGiC = $value_BeEhUb_MaGiC;
      }
      unset($param_BeEhUb_MaGiC, $value_BeEhUb_MaGiC);
    }

    if ( ( func_num_args() > 0 ) && !is_null( func_get_arg(0) ) ) {
      require( 'views/' . func_get_arg(0) . '.php' );
    }else{
      require( 'views/' . strtolower( get_class( $this ) ) . '.php' );
    }
  }


} // class BeeHub_Resource

// End of file
