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
      $this->assert( BeeHub::PRIV_READ_CONTENT );
      return true;
    } catch (DAV_Status $e) {}
    try {
      $this->assert( DAVACL::PRIV_READ_ACL );
      return true;
    } catch (DAV_Status $e) {}
    try {
      $this->assert( DAVACL::PRIV_WRITE_ACL );
      return true;
    } catch (DAV_Status $e) {}
    try {
      $this->assert( DAVACL::PRIV_WRITE_CONTENT );
      return true;
    } catch (DAV_Status $e) {}
    return false;
  }


  /**
   * Get a property in XML format
   *
   * @see     DAV_Resource::prop()
   * @see     DAVACL_Resource::prop()
   * @param   string  $propname  The name of the property to be returned, eg. "mynamespace: myprop"
   * @return  string             XML or NULL if the property is not defined.
   */
  public function prop( $propname ) {
    if ( $method = @BeeHub::$BEEHUB_PROPERTIES[ $propname ] ) {
      return call_user_func( array( $this, 'prop_' . $method ) );
    }
    return parent::prop( $propname );
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
   * Determines which properties are readable
   *
   * @param   array  $properties  An array with the properties to check
   * @return  array               An array of (property => isReadable) pairs.
   */
  public function property_priv_read($properties) {
    $retval = array();
    // Most properties can be read if the file contents can be read
    try {
      $this->assert( BeeHub::PRIV_READ_CONTENT );
      foreach ($properties as $property)
        $retval[$property] = true;
    } catch (DAV_Status $e) {
      foreach ($properties as $property)
        $retval[$property] = false;
    }

    // The 'DAV: acl' property has its own privilege to determine if it is readable
    if ( isset( $retval[DAV::PROP_ACL] ) ) {
      try {
        $this->assert(DAVACL::PRIV_READ_ACL);
        $retval[DAV::PROP_ACL] = true;
      } catch (DAV_Status $e) {
        $retval[DAV::PROP_ACL] = false;
      }
    }
    
    // And the 'DAV: current-user-privilege-set' property also has its own privilege
    if ( isset( $retval[DAV::PROP_CURRENT_USER_PRIVILEGE_SET] ) ) {
      try {
        $this->assert( DAVACL::PRIV_READ_CURRENT_USER_PRIVILEGE_SET );
        $retval[DAV::PROP_CURRENT_USER_PRIVILEGE_SET] = true;
      } catch( DAV_Status $e ) {
        $retval[DAV::PROP_CURRENT_USER_PRIVILEGE_SET] = false;
      }
    }

    // These properties are always readable
    foreach( array(
      DAV::PROP_OWNER,
      DAV::PROP_RESOURCETYPE,
      DAV::PROP_DISPLAYNAME
    ) as $prop ) {
      if ( array_key_exists( $prop, $retval ) ) {
        $retval[$prop] = true;
      }
    }

    return $retval;
  }


  /**
  * By default, properties are writeble if the current user has PRIV_WRITE_CONTENT
   *
  * @param   array  $properties  The properties to check for writability
  * @return  array               An array of (property => isWritable) pairs
  */
  public function property_priv_write($properties) {
    try {
      $this->assert( DAVACL::PRIV_WRITE_CONTENT );
      $allow = true;
    }
    catch( DAV_Status $e ) {
      $allow = false;
    }

    $retval = array();
    foreach ($properties as $prop){
      $retval[$prop] = $allow;
    }
    return $retval;
  }


  /**
   * Return all the resource specific response headers for the HEAD request
   *
   * This implementation will also assert that you have read-content privileges,
   * because else you are not allowed to perform HEAD or GET operations.
   *
   * @see DAV_Resource::method_HEAD()
   */
  public function method_HEAD() {
    $this->assert( BeeHub::PRIV_READ_CONTENT );
    return parent::method_HEAD();
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
    $protected = array();
    if ( ! is_null( $this->user_prop_owner() ) ) {
      $protected[0] = new DAVACL_Element_ace(
        'DAV: owner', false, array('DAV: all'), false, true, null
      );
    }
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

    $inherited = $this->getInheritedAces();

    return array_merge(
      $protected,
      $this->user_prop_acl_internal(),
      $inherited
    );
  }


  /**
   * Gets the (path of the) owner of this resource
   *
   * @return  string  The path to the owner
   */
  public function user_prop_owner() {
    $retval = $this->user_prop(DAV::PROP_OWNER);
    return $retval ? $retval : null;
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
 * Check that we have the correct privileges to set the ACL
 *
 * @param   array  $aces  The ACL in the form of an array of ACEs
 * @return  mixed         No idea what this method returns
 */
public function set_acl( $aces ) {
  $this->assert( DAVACL::PRIV_WRITE_ACL );
  return parent::set_acl( $aces );
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
    $this->user_set_sponsor( $sponsor->URIs[0] );
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
   * Returns the http://beehub.nl/ sponsor-membership property as a DAV_Element_href instance
   *
   * @return  DAV_Element_href
   */
  final public function prop_sponsor_membership() {
    $retval = $this->user_prop_sponsor_membership();
    return $retval ? new DAV_Element_href( $retval ) : '';
  }


  /**
   * Gets the http://beehub.nl/ sponsor-membership property in a PHP native format (instead of XML for webDAV)
   *
   * @return  array  Strings with the paths to the sponsors or null when this property is not set on this resource
   */
  public function user_prop_sponsor_membership() {
    return null;
  }


  /**
   * Returns the DAV: inherited-acl-set property as an array of URIs
   *
   * @return array an array of URIs
   * @see DAVACL_Resource::user_prop_inherited_acl_set()
   */
  public function user_prop_inherited_acl_set() {
    $inherited = $this->getInheritedAces();
    $urls = array();
    foreach( $inherited as $ace ) {
      $urls[] = $ace->inherited;
    }
    return array_unique( $urls );
  }


  /**
   * Get all ACE's that this resource inherits from its parents
   *
   * @return  array  A list of ACEs
   */
  private function getInheritedAces() {
    $parent = $this->collection();
    $inherited = $parent ? $parent->user_prop_acl() : array();
    while ( count($inherited) && $inherited[0]->protected ) {
      array_shift($inherited);
    }
    foreach( $inherited as &$ace ) {
      if ( ! $ace->inherited ) {
        $ace->inherited = $parent->path;
      }
    }
    return $inherited;
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
