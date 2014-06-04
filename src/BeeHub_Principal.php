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
 * @TODO: How to delete a principal?
 */
abstract class BeeHub_Principal extends BeeHub_Resource implements DAVACL_Principal {

  public $name;


  public function __construct($path) {
    parent::__construct($path);
    $this->name = rawurldecode(basename($path));
    $this->init_props();
  }


  public function method_HEAD() {
    $retval = parent::method_HEAD();
    $retval['Cache-Control'] = 'no-cache';
    return $retval;
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
    return @$this->stored_props[$propname];
  }

  public function user_prop_displayname() {
    return $this->user_prop(DAV::PROP_DISPLAYNAME);
  }


  protected function user_set_displayname($displayname) {
    $this->user_set(DAV::PROP_DISPLAYNAME, $displayname);
  }

  public function user_prop_owner() {
    return null;
  }

  public function user_prop_group_membership() {
    return array();
  }


  public function user_prop_group_member_set() {
    return array();
  }

  public function user_set_group_member_set($set) {
  }


  public function user_prop_sponsor_membership() {
    return array();
  }


  /**
  * The user has write privileges on all properties if he is the administrator of this principal
  * @param array $properties
  * @return array an array of (property => isWritable) pairs.
  */
  public function property_priv_write($properties) {
    $allow = $this->is_admin();
    $retval = array();
    foreach ($properties as $prop) $retval[$prop] = $allow;
    return $retval;
  }


  /**
  * This method renews file .../js/principals.js
  * @TODO make sure that .../js/principals.js is overwritable by a `rename`; consider not writing it to a location inside the document root for security reasons
  */
  public static function update_principals_json() {
    $json = array();

    foreach( array( 'users', 'groups', 'sponsors' ) as $thing ) {
      $collection = BeeHub::getNoSQL()->selectCollection($thing);
      $resultSet = $collection->find( array(), array( 'name' => true, 'displayname' => true) );
      $things = array();
      foreach ( $resultSet as $row ) {
        $things[ $row['name'] ] = $row['displayname'];
      }
      $json[ $thing ] = $things;
    }

    $local_js_path = dirname( dirname( __FILE__ ) ) . '/public' .
      BeeHub::JAVASCRIPT_PATH;
    $filename = tempnam($local_js_path, 'tmp_principals');
    file_put_contents(
      $filename, 
      'nl.sara.beehub.principals = ' . json_encode($json) . ';'
    );
    rename( $filename, $local_js_path . DIRECTORY_SEPARATOR . 'principals.js' );
    chmod( $local_js_path . 'principals.js', 0664 );
  }

  /**
   * @return bool is the current user allowed to administer $this?
   */
  abstract public function is_admin();
}

// class BeeHub_Principal


