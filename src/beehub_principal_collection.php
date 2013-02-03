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
 * A class.
 * @package BeeHub
 *
 */
abstract class BeeHub_Principal_Collection
  extends BeeHub_Resource
  implements DAVACL_Principal_Collection, DAV_Collection {


public function __construct($path) {
  parent::__construct(DAV::slashify($path));
}


public function report_principal_match($input) {}


/**
 * @see DAVACL_Principal_Collection::report_principal_search_property_set()
 */
public function report_principal_search_property_set() {
  return array('DAV: displayname' => 'Name');
}


protected $members = null;
protected $current = 0;


abstract protected function init_members();


/**
 * @return mixed
 */
public function current() {
  if (null === $this->members)
    $this->init_members();
  return $this->members[$this->current];
}


/**
 * @return scalar
 */
public function key()     {
  return $this->current;
}


/**
 * @return void
 */
public function next()    {
  $this->current++;
}


/**
 * @return void
 */
public function rewind()  {
  $this->current = 0;
}


/**
 * @return boolean
 */
public function valid()   {
  if (null === $this->members)
    $this->init_members();
  return $this->current < count($this->members);
}


/**#@+
 * Must be implemented by all realizations of DAV_Collection.
 */
public function create_member( $name ) {
  throw new DAV_Status(DAV::HTTP_FORBIDDEN);
}


public function method_DELETE( $name ) {
  throw new DAV_Status(DAV::HTTP_FORBIDDEN);
}


public function method_MOVE( $name, $destination ) {
  throw new DAV_Status(DAV::HTTP_FORBIDDEN);
}


public function method_MKCOL( $name ) {
  throw new DAV_Status(DAV::HTTP_FORBIDDEN);
}
/**#@-*/


/**
 * We allow everybody to do everything with this object in the ACL, so we can
 * handle all privileges hard-coded without ACL's interfering.
 * @see BeeHub_Resource::user_prop_acl_internal()
 */
public function user_prop_acl_internal() {
  return array(new DAVACL_Element_ace('DAV: all', false, array('DAV: all'), false, true, null));
}


} // class BeeHub_Principal


