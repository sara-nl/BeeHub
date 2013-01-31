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
class BeeHub_XFSResource extends BeeHub_Resource {


  /**
   * @var string the path of the resource on the local filesystem.
   */
  protected $localPath;


  /**
   * @var array Array of propery_name => property_value pairs.
   */
  protected $xattr_props;


  /**
   * @var array
   */
  protected $stat;


  public function __construct($path) {
    parent::__construct($path);
    $this->localPath = BeeHub::localPath($path);
    $this->stat = stat($this->localPath);
  }


  protected function init_props() {
    if (is_null($this->xattr_props)) {
      $this->xattr_props = array();
      $attributes = xattr_list($this->localPath);
      foreach ($attributes as $attribute)
        $this->xattr_props[rawurldecode($attribute)] =
                xattr_get($this->localPath, $attribute);
    }
  }


  /**
   * @see DAV_Resource::user_prop()
   */
  public function user_prop($propname) {
    $this->init_props();
    return @$this->xattr_props[$propname];
  }


  /**
   * @return Array a list of ACEs.
   * @see BeeHub_Resource::user_prop_acl_internal()
   */
  public function user_prop_acl_internal() {
    return DAVACL_Element_ace::json2aces($this->user_prop(DAV::PROP_ACL));
  }


  /**
   * @see DAV_Resource::user_prop_getetag()
   */
  public function user_prop_getetag() {
    return $this->user_prop(DAV::PROP_GETETAG);
  }


  /**
   * @see DAV_Resource::user_prop_getlastmodified()
   */
  public function user_prop_getlastmodified() {
    return $this->stat['mtime'];
  }


  /**
   * @see DAV_Resource::user_propname()
   */
  public function user_propname() {
    $this->init_props();
    $retval = array();
    foreach (array_keys($this->xattr_props) as $prop)
      if (!isset(DAV::$SUPPORTED_PROPERTIES[$prop]))
        $retval[$prop] = true;
    return $retval;
  }


  /**
   * @see DAV_Resource::user_set()
   */
  protected function user_set($propname, $value = null) {
    $this->assert(DAVACL::PRIV_WRITE);
    $this->init_props();
    if (is_null($value) && isset($this->xattr_props[$propname])) {
      unset($this->xattr_props[$propname]);
      $this->touched = true;
    } elseif (!is_null($value) && $value !== @$this->xattr_props[$propname]) {
      $this->xattr_props[$propname] = $value;
      $this->touched = true;
    }
  }


  /**
   * @see DAV_Resource::user_prop_displayname()
   */
  public function user_prop_displayname() {
    return $this->user_prop(DAV::PROP_DISPLAYNAME);
  }


  /**
   * @TODO rewrite to BeeHub::PROP_SPONSOR
   */
  protected function user_set_group($group) {
    $this->assert(DAVACL::PRIV_READ);
    if (!( $group = DAV::$REGISTRY->resource($group) ) ||
            !$group instanceof BeeHub_Group ||
            !$group->isVisible())
      throw new DAV_Status(
              DAV::HTTP_BAD_REQUEST,
              DAV::COND_RECOGNIZED_PRINCIPAL
      );
    if (!$group->user_prop_executable())
      throw new DAV_Status(
              DAV::HTTP_BAD_REQUEST,
              DAV::COND_ALLOWED_PRINCIPAL
      );
    if ($this->user_prop_owner() != $this->user_prop_current_user_principal() &&
            !BeeHub_ACL_Provider::inst()->wheel())
      throw DAV::forbidden( 'Only the owner can change the group of a resource.' );
    if (!in_array($group->path, $this->current_user_principals()))
      throw DAV::forbidden( "You're not a member of group {$group->path}" );
    return $this->user_set(DAV::PROP_GROUP, $group->path);
  }


  public function user_prop_owner() {
    return $this->user_prop(DAV::PROP_OWNER);
  }


  /**
   * @TODO check this implementation
   */
  protected function user_set_owner($owner) {
    $this->assert(DAVACL::PRIV_READ);
    $cups = BeeHub_Registry::inst()->resource($this->user_prop_current_user_principal());
    if ($this->user_prop_owner() != $this->user_prop_current_user_principal() and
            !BeeHub_ACL_Provider::inst()->wheel())
      throw DAV::forbidden( 'Only the resource owner can grant ownership.' );
    if (!( $owner = DAV::$REGISTRY->resource($owner) ) ||
            !$owner->isVisible() ||
            !$owner instanceof BeeHub_User)
      throw new DAV_Status(
              DAV::HTTP_BAD_REQUEST,
              DAV::COND_RECOGNIZED_PRINCIPAL
      );
    if (!in_array($this->user_prop_group(), $owner->current_user_principals()))
      throw DAV::forbidden( 'User ' . $owner->path . ' is not a member of group ' . $this->user_prop_group() . '.' );
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
    if (!$this->touched)
      return;
    foreach (xattr_list($this->localPath) as $attribute)
      if (!isset($this->xattr_props[rawurldecode($attribute)]))
        xattr_remove($this->localPath, $attribute);
    foreach ($this->xattr_props as $name => $value)
      xattr_set($this->localPath, rawurlencode($name), $value);
    $this->touched = false;
  }


  /**
   * @see DAVACL_Resource::method_ACL()
   */
  public function method_ACL($aces) {
    $this->assert(DAVACL::PRIV_WRITE_ACL);
    $this->user_set(DAV::PROP_ACL, $aces ? DAVACL_Element_ace::aces2json($aces) : null);
    $this->storeProperties();
  }


} // class BeeHub_XFSResource
