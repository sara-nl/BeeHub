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
 * $Id: sd_resource.php 3365 2011-08-04 14:22:58Z pieterb $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package SD
 */

/**
 * Some class.
 * @package SD
 */
class SD_Resource extends DAVACL_Resource {

  
/**
 * @var string the path of the resource on the local filesystem.
 */
protected $localPath;
protected $protected_props;
protected $stat;
private $dead_props = null;
protected $touched = false;


public function __construct ($path) {
  parent::__construct($path);
  $this->localPath = SD::localPath($path);
  $this->stat = stat($this->localPath);
  $this->protected_props = array(
    DAV::PROP_GETLASTMODIFIED  => $this->stat['mtime'],
  );
}


/**
 * @param array $privileges
 * @throws DAV_Status FORBIDDEN
 */
public function assert($privileges) {
  if (SD_ACL_Provider::inst()->wheel())
    return;
  return parent::assert($privileges);
}


public function isVisible() {
  try {
    $this->assert(DAVACL::PRIV_READ);
  }
  catch(DAV_Status $e) {
    if (!( $collection = $this->collection() ))
      return false;
    try {
      $collection->assert(DAVACL::PRIV_WRITE);
    }
    catch(DAV_Status $f) {
      return false;
    }
  }
  return true;
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


//public function user_prop_creationdate() {
//  return $this->protected_props[DAV::PROP_CREATIONDATE];
//}


public function user_prop_acl_internal() {
  $parent = $this->collection();
  $parent_acl = $parent ? $parent->user_prop_acl_internal() : array();
  $retval = SD::json2aces( $this->user_prop(DAV::PROP_ACL) );
  while ( count($parent_acl) ) {
    if ( ! $parent_acl[0]->inherited )
      $parent_acl[0]->inherited = $parent->path;
    $retval[] = array_shift( $parent_acl );
  }
  return $retval;
}


public function user_prop_acl() {
  $protected = array(
    new DAVACL_Element_ace(
    	'DAV: owner', false, array('DAV: all'), false, true, null
    ),
//     new DAVACL_Element_ace(
//     	SD::WHEEL_PATH, false, array('DAV: all'), false, true, null
//     ),
  );
  if ( in_array( $this->path, array( '/' ) ) )
    $protected[] = new DAVACL_Element_ace(
    	'DAV: all', false, array('DAV: read', 'DAV: read-acl'), false, true, null
    );
  return array_merge(
    $protected,
    $this->user_prop_acl_internal()
  );
}


public function user_prop_getlastmodified() {
  return $this->protected_props[DAV::PROP_GETLASTMODIFIED];
}


public function user_prop_getetag() {
  return $this->user_prop(DAV::PROP_GETETAG);
}


private function init_dead_props() {
  if (is_null($this->dead_props)) {
    $this->dead_props = array();
    $attributes = xattr_list($this->localPath);
    foreach ($attributes as $attribute)
      $this->dead_props[rawurldecode($attribute)] =
        xattr_get($this->localPath, $attribute);
  }
}


public function user_prop($propname) {
  $this->init_dead_props();
  return @$this->dead_props[$propname];
}


/**
 * All available properties of the current resource.
 * This method must return an array with ALL property names as keys and a
 * boolean as value, indicating if the property should be returned in an
 * <allprop/> PROPFIND request.
 * @return array
 */
public function user_propname() {
  $this->init_dead_props();
  $retval = array();
  foreach ( array_keys( $this->dead_props ) as $prop )
    if (!isset(DAV::$SUPPORTED_PROPERTIES[$prop]))
      $retval[$prop] = true;
  return $retval;
}


/**
 * @param string $propname the name of the property to be set.
 * @param string $value an XML fragment, or null to unset the property.
 * @return void
 * @throws DAV_Status §9.2.1 specifically mentions the following statusses.
 * - 200 (OK): The property set or change succeeded. Note that if this appears 
 *   for one property, it appears for every property in the response, due to the 
 *   atomicity of PROPPATCH.
 * - 403 (Forbidden): The client, for reasons the server chooses not to 
 *   specify, cannot alter one of the properties.
 * - 403 (Forbidden): The client has attempted to set a protected property, such 
 *   as DAV:getetag. If returning this error, the server SHOULD use the 
 *   precondition code 'cannot-modify-protected-property' inside the response 
 *   body.
 * - 409 (Conflict): The client has provided a value whose semantics are not 
 *   appropriate for the property.
 * - 424 (Failed Dependency): The property change could not be made because of 
 *   another property change that failed.
 * - 507 (Insufficient Storage): The server did not have sufficient space to 
 *   record the property.
 */
protected function user_set($propname, $value = null) {
  $this->assert(DAVACL::PRIV_WRITE);
  $this->init_dead_props();
  if (is_null($value) && isset($this->dead_props[$propname])) {
    unset($this->dead_props[$propname]);
    $this->touched = true;
  } elseif (!is_null($value) && $value !== @$this->dead_props[$propname]) {
    $this->dead_props[$propname] = $value;
    $this->touched = true;
  }
}


public function user_prop_displayname() {
  return $this->user_prop(DAV::PROP_DISPLAYNAME);
}


public function user_prop_group() {
  return $this->user_prop(DAV::PROP_GROUP);
}


protected function user_set_group($group) {
  $this->assert(DAVACL::PRIV_READ);
  if ( !( $group = DAV::$REGISTRY->resource($group) ) ||
       ! $group instanceof SD_Group ||
       ! $group->isVisible() )
    throw new DAV_Status(
      DAV::HTTP_BAD_REQUEST,
      DAV::COND_RECOGNIZED_PRINCIPAL
    );
  if ( ! $group->user_prop_executable() )
     throw new DAV_Status(
      DAV::HTTP_BAD_REQUEST,
      DAV::COND_ALLOWED_PRINCIPAL
    );
  if ( $this->user_prop_owner() != $this->user_prop_current_user_principal() &&
       !SD_ACL_Provider::inst()->wheel() )
    throw new DAV_Status(
      DAV::forbidden(),
      'Only the owner can change the group of a resource.'
    );
  if ( !in_array( $group->path, $this->current_user_principals() ) )
    throw new DAV_Status(
      DAV::forbidden(),
      "You're not a member of group {$group->path}"
    );
  return $this->user_set(DAV::PROP_GROUP, $group->path);
}


public function user_prop_owner() {
  return $this->user_prop(DAV::PROP_OWNER);
}


protected function user_set_owner($owner) {
  $this->assert(DAVACL::PRIV_READ);
  $cups = SD_Registry::inst()->resource($this->user_prop_current_user_principal());
  if ( $this->user_prop_owner() != $this->user_prop_current_user_principal() and
       !SD_ACL_Provider::inst()->wheel() )
    throw new DAV_Status(
      DAV::forbidden(),
      'Only the resource owner can grant ownership.'
    );
  if ( !( $owner = DAV::$REGISTRY->resource($owner) ) ||
       ! $owner->isVisible() ||
       ! $owner instanceof SD_User )
    throw new DAV_Status(
      DAV::HTTP_BAD_REQUEST,
      DAV::COND_RECOGNIZED_PRINCIPAL
    );
  if (!in_array($this->user_prop_group(), $owner->current_user_principals()))
    throw new DAV_Status(
      DAV::forbidden(),
      'User ' . $owner->path . ' is not a member of group ' . $this->user_prop_group() . '.'
    );
  return $this->user_set(DAV::PROP_OWNER, $owner->path);
}


protected function user_set_displayname($value) {
  return $this->user_set(DAV::PROP_DISPLAYNAME, $value);
}


/**
 * Stores properties set earlier by set().
 * @return void
 * @throws DAV_Status in particular 507 (Insufficient Storage)
 */
public function storeProperties() {
  if ( !$this->touched ) return;
  foreach( xattr_list($this->localPath) as $attribute )
    if (!isset($this->dead_props[rawurldecode($attribute)]))
      xattr_remove($this->localPath, $attribute);
  foreach( $this->dead_props as $name => $value )
    xattr_set($this->localPath, rawurlencode($name), $value);
  $this->touched = false;
}


public function method_ACL($aces) {
  $this->assert(DAVACL::PRIV_WRITE_ACL);
  $this->user_set(DAV::PROP_ACL, $aces ? SD::aces2json($aces) : null);
  $this->storeProperties();
}


} // class SD_Resource


