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
 * The system directory (/system/) is a virtual collection
 * @package BeeHub
 */

/**
 * Interface to the system folder.
 * @package BeeHub
 */
class BeeHub_System_Collection extends BeeHub_Resource implements DAV_Collection {


  private $members = array();
  private $current_key = 0;


  public function __construct($path) {
    parent::__construct($path);

    $this->members[] = 'groups';
    $this->members[] = 'sponsors';
    $this->members[] = 'users';
  }


  protected function init_props() {
    if (is_null($this->stored_props)) {
      $this->stored_props = array();
    }
  }


  public function create_member($name) {
    throw DAV::forbidden();
  }


  public function user_prop_acl_internal() {
    return array(
      new DAVACL_Element_ace(
        DAVACL::PRINCIPAL_ALL, false, array(
          DAVACL::PRIV_READ
        ), false, true
      )
    );
  }


  public function method_GET() {
    $this->include_view();
  }


  public function method_DELETE($name) {
    throw DAV::forbidden();
  }


  public function method_MKCOL($name) {
    throw DAV::forbidden();
  }


  public function method_MOVE($member, $destination) {
    throw DAV::forbidden();
  }


  public function current() {
    return $this->members[$this->current_key];
  }


  public function key() {
    return $this->current_key;
  }


  public function next() {
    $this->current_key++;
  }


  public function rewind() {
    $this->current_key = 0;
  }


  public function valid() {
    return isset($this->members[$this->current_key]);
  }


} // class BeeHub_System_Collection
