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
      $groups[$group_name] = BeeHub_Registry::inst()->resource( $this->path . $group_name );
    $this->include_view(null, array('groups' => $groups));
  }


  public function method_POST() {
    $displayname = $_POST['displayname'];
    $description = $_POST['description'];
    $group_name = $_POST['group_name'];
    $user_sponsor = BeeHub_Auth::inst()->current_user()->user_prop( BeeHub::PROP_SPONSOR );
    // If you don't have a (default) sponsor, you're not allowed to add a group
    if (empty($user_sponsor)) {
      throw DAV::forbidden("Only users with a sponsor are allowed to create groups");
    }
    // Group name must be one of the following characters a-zA-Z0-9_-., starting with an alphanumeric character and must be between 1 and 255 characters long and can't be one of the forbidden names
    if (empty($displayname) ||
        in_array( strtolower($group_name), BeeHub::$FORBIDDEN_GROUP_NAMES ) ||
        !preg_match('/^[a-zA-Z0-9]{1}[a-zA-Z0-9_\-\.]{0,254}$/D', $group_name)) {
      throw new DAV_Status( DAV::HTTP_BAD_REQUEST, 'Group name has the wrong format. The name can be a maximum of 255 characters long and should start with an alphanumeric character, followed by alphanumeric character, followed by alphanumeric characters or one of the following: _-.' );
    }
    $groupdir = DAV::unslashify(BeeHub::$CONFIG['environment']['datadir']) . DIRECTORY_SEPARATOR . $group_name;

    // Store in the database
    try {
      $statement = BeeHub_DB::execute(
        'INSERT INTO `beehub_groups` (`group_name`) VALUES (?)',
        's', $group_name
      );
    }catch (Exception $exception) {
      if ($exception->getCode() === 1062) { // Duplicate key: bad request!
        throw new DAV_Status( DAV::HTTP_CONFLICT, 'Group name already in use. Please choose another group name!' );
      }else{
        throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
      }
    }

    // Check for existing groupdir
    if ( file_exists( $groupdir ) ) {
      BeeHub_DB::execute( 'DELETE FROM `beehub_groups` WHERE `group_name`=?', 's', $group_name );
      throw new DAV_Status( DAV::HTTP_INTERNAL_SERVER_ERROR );
    }

    // Fetch the user and store extra properties
    $group = BeeHub_Registry::inst()->resource(BeeHub::GROUPS_PATH . $group_name);
    $group->user_set(DAV::PROP_DISPLAYNAME, $displayname);
    if (!empty($description)) {
      $group->user_set(BeeHub::PROP_DESCRIPTION, $description);
    }
    $group->storeProperties();

    // Add the current user as admin of the group
    $group->change_memberships(
      array( basename( $this->user_prop_current_user_principal() ) ),
      true, true, true
    );

    // And create a group directory
    if (!mkdir($groupdir)) {
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }
    xattr_set( $groupdir, rawurlencode('DAV: owner'), BeeHub::$CONFIG['namespace']['wheel_path'] );
    xattr_set( $groupdir, rawurlencode('DAV: acl'), '[["' . BeeHub::GROUPS_PATH . rawurlencode($group->name) . '",false,["DAV: read", "DAV: write"],false]]' );
    xattr_set( $groupdir, rawurlencode(BeeHub::PROP_SPONSOR), $user_sponsor );

    // Group created, redirect to the group page
    DAV::redirect(
      DAV::HTTP_SEE_OTHER,
      BeeHub::GROUPS_PATH . rawurlencode($group->name)
    );
  }


  public function report_principal_property_search($properties) {
    if ( 1 !== count( $properties ) ||
         ! isset( $properties[DAV::PROP_DISPLAYNAME] ) ||
         1 !== count( $properties[DAV::PROP_DISPLAYNAME] ) )
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST,
        'You\'re searching for a property which cannot be searched.'
      );
    $match = $properties[DAV::PROP_DISPLAYNAME][0];
    $match = str_replace(array('_', '%'), array('\\_', '\\%'), $match) . '%';
    $stmt = BeeHub_DB::execute(
      'SELECT `group_name`
       FROM `beehub_groups`
       WHERE `displayname` LIKE ?', 's', $match
    );
    $retval = array();
    while ($row = $stmt->fetch_row()) {
      $retval[] = BeeHub::GROUPS_PATH .
        rawurlencode($row[0]);
    }
    $stmt->free_result();
    return $retval;
  }


  protected function init_members() {
    $stmt = BeeHub_DB::execute('SELECT `group_name` FROM `beehub_groups` ORDER BY `displayname`');
    $this->members = array();
    while ($row = $stmt->fetch_row()) {
      $this->members[] = rawurlencode($row[0]);
    }
    $stmt->free_result();
  }


} // class BeeHub_Groups
