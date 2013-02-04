<?php

/* ·************************************************************************
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
 * ************************************************************************ */

/**
 * File documentation (who cares)
 * @package BeeHub
 */

/**
 * A class.
 * @package BeeHub
 *
 */
abstract class BeeHub_Principal extends BeeHub_Resource implements DAVACL_Principal {

  public $name;
  public $sql_props = null;

  protected function user_set($name, $value = null) {
    $this->set_property($name, $value);
  }

  /**
   * Sets a property on the current data model
   * @param   string  $name   The name of the property
   * @param   type    $value  The value of the property. Set to null to remove the property.
   * @return  void
   */
  public function set_property($name, $value = null) {
    $this->init_props();
    $this->sql_props[$name] = $value;
    $this->touched = true;
  }

  public function __construct($path) {
    parent::__construct($path);
    $this->name = rawurldecode(basename($path));
  }

  public function user_prop_alternate_uri_set() {
    return array();
  }

  public function user_prop_principal_url() {
    return $this->path;
  }

  /**
   * @see DAV_Resource::user_prop()
   */
  public function user_prop($propname) {
    $this->init_props();
    return DAV::xmlescape(@$this->sql_props[$propname]);
  }

  public function user_prop_displayname() {
    $this->init_props();
    return $this->sql_props[DAV::PROP_DISPLAYNAME];
  }

  public function user_prop_acl_internal() {
    return array();
  }

  // These methods are only available for a limited range of users!
  public function method_PROPPATCH($propname, $value = null) {
    if (!$this->is_admin()) {
      throw new DAV_Status(
              DAV::HTTP_FORBIDDEN,
              DAV::COND_NEED_PRIVILEGES
      );
    }
    return parent::method_PROPPATCH($propname, $value);
  }

  protected function user_set_displayname($displayname) {
    $this->set_property(DAV::PROP_DISPLAYNAME, $displayname);
  }

  public function user_prop_owner() {
    return BeeHub::$CONFIG['webdav_namespace']['wheel_path'];
  }

  public function user_prop_group_membership() {
    return array();
  }

  abstract protected function init_props();

  /**
   * @return bool is the current user allowed to administer $this?
   */
  abstract public function is_admin();
}

// class BeeHub_Principal


