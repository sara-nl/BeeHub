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
class BeeHub_Groups extends BeeHub_Principal_Collection {


  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET() {
    $groups = array();
    foreach ($this as $group_name)
      $groups[$group_name] = DAV::$REGISTRY->resource( $this->path . $group_name );
    $this->include_view(null, array('groups' => $groups));
  }


  public function method_POST( &$headers ) {
    $displayname = $_POST['displayname'];
    $description = $_POST['description'];
    $group_name = $_POST['group_name'];
    $user_sponsor = BeeHub::getAuth()->current_user()->user_prop( BeeHub::PROP_SPONSOR );
    // If you don't have a (default) sponsor, you're not allowed to add a group
    if (empty($user_sponsor)) {
      throw DAV::forbidden("Only users with a sponsor are allowed to create groups");
    }
    // Group name must be one of the following characters a-zA-Z0-9_-., starting with an alphanumeric character and must be between 1 and 255 characters long and can't be one of the forbidden names
    if (empty($displayname) ||
        in_array( strtolower($group_name), BeeHub::$FORBIDDEN_GROUP_NAMES ) ||
        !preg_match('/^[a-zA-Z0-9]{1}[a-zA-Z0-9_\-\.]{0,254}$/D', $group_name)) {
      throw new DAV_Status( DAV::HTTP_BAD_REQUEST, 'Group name has the wrong format. The name can be a maximum of 255 characters long and should start with an alphanumeric character, followed by alphanumeric characters or one of the following: _-.' );
    }

    // Check if the group name doesn't exist
    $collection = BeeHub::getNoSQL()->groups;
    $result = $collection->findOne( array( 'name' => $group_name ), array( 'name' => true) );
    if ( !is_null( $result ) ) { // Duplicate key: bad request!
      throw new DAV_Status(DAV::HTTP_CONFLICT, "Group name already exists, please choose a different group name!");
    }

    $groupdir = DAV::unslashify(BeeHub::$CONFIG['environment']['datadir']) . DIRECTORY_SEPARATOR . $group_name;
    // Check for existing groupdir
    if (file_exists($groupdir)) {
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Store in the database
    $collection->insert( array( 'name' => $group_name ) );

    // Fetch the group and store extra properties
    $group = DAV::$REGISTRY->resource( BeeHub::GROUPS_PATH . $group_name );
    $group->user_set(DAV::PROP_DISPLAYNAME, $displayname);
    if (!empty($description)) {
      $group->user_set(BeeHub::PROP_DESCRIPTION, $description);
    }
    $group->storeProperties();

    // Add the current user as admin of the group
    $group->change_memberships( basename( $this->user_prop_current_user_principal() ), BeeHub_Group::USER_ACCEPT );
    $group->change_memberships( basename( $this->user_prop_current_user_principal() ), BeeHub_Group::ADMIN_ACCEPT );
    $group->change_memberships( basename( $this->user_prop_current_user_principal() ), BeeHub_Group::SET_ADMIN );

    // And create a group directory
    if (!mkdir($groupdir)) {
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }
    // And create the directory in the database
    $document = array( 'path' => $group_name, 'collection' => true );
    $filesCollection = BeeHub::getNoSQL()->files;
    $filesCollection->save( $document );

    $groupdir_resource = DAV::$REGISTRY->resource( '/' . $group_name );
    $groupdir_resource->user_set( BeeHub::PROP_SPONSOR, $user_sponsor );
    $groupdir_resource->user_set( DAV::PROP_ACL, '[["' . BeeHub::GROUPS_PATH . rawurlencode($group->name) . '",false,["DAV: read", "DAV: write"],false]]' );
    $groupdir_resource->storeProperties();

    // Group created, redirect to the group page
    DAV::redirect(
      DAV::HTTP_SEE_OTHER,
      BeeHub::GROUPS_PATH . rawurlencode($group->name)
    );
  }


  public function report_principal_property_search($properties) {
    if ( 1 !== count( $properties ) ||
         ! isset( $properties[DAV::PROP_DISPLAYNAME] ) ||
         1 !== count( $properties[DAV::PROP_DISPLAYNAME] ) ) {
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST,
        'You\'re searching for a property which cannot be searched.'
      );
    }
    $match = '^' . preg_quote( $properties[DAV::PROP_DISPLAYNAME][0] ) . '.*$';
    $collection = BeeHub::getNoSQL()->groups;
    $resultSet = $collection->find( array( 'displayname' => array( '$regex' => $match, '$options' => 'i' ) ), array( 'name' => true ) );
    $retval = array();
    foreach ( $resultSet as $row ) {
      $retval[] = BeeHub::GROUPS_PATH . rawurlencode( $row['name'] );
    }
    return $retval;
  }


  protected function init_members() {
    $collection = BeeHub::getNoSQL()->groups;
    $this->members = $collection->find( array(), array( 'name' ) )->sort( array( 'displayname' => 1 ) );
  }


} // class BeeHub_Groups
