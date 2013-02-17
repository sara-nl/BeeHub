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


  public function method_POST() {
    // TODO: translate group_name to ASCII and check for double group names
    $group_name = $_POST['group_name'];
    $displayname = $_POST['displayname'];
    $description = $_POST['description'];

    // Store in the database
    $statement = BeeHub_DB::execute(
      'INSERT INTO `beehub_groups` (`group_name`) VALUES (?)',
      's', $group_name
    );

    // Fetch the user and store extra properties
    $group = BeeHub_Registry::inst()->resource(BeeHub::$CONFIG['namespace']['groups_path'] . $group_name);
    $group->set_property(DAV::PROP_DISPLAYNAME, $displayname);
    $group->set_property(BeeHub::PROP_DESCRIPTION, $description);
    $group->storeProperties();

    // Add the current user as admin of the group
    $group->change_memberships(
      array( $this->user_prop_current_user_principal() ),
      true, true, true
    );
  }


  public function report_principal_property_search($properties) {
    if (1 != count($properties) ||
            !isset($properties[DAV::PROP_DISPLAYNAME]) ||
            1 != count($properties[DAV::PROP_DISPLAYNAME]))
      throw new DAV_Status(
              DAV::HTTP_BAD_REQUEST,
              'You\'re searching for a property which cannot be searched.'
      );
    $match = $properties[DAV::PROP_DISPLAYNAME][0];
    $match = str_replace(array('_', '%'), array('\\_', '\\%'), $match) . '%';
    $stmt = BeeHub_DB::execute('SELECT `group_name` FROM `beehub_groups` WHERE `displayname` LIKE ?', 's', $match);
    $retval = array();
    while ($row = $stmt->fetch_row()) {
      $retval[] = BeeHub::$CONFIG['namespace']['groups_path'] .
        rawurlencode($row[0]);
    }
    $stmt->free_result();
    return $retval;
  }


  protected function init_members() {
    $stmt = BeeHub_DB::execute('SELECT `group_name` FROM `beehub_groups` ORDER BY `displayname`');
    $this->members = array();
    while ($row = $stmt->fetch_row())
      $this->members[] = rawurlencode($row[0]);
    $stmt->free_result();
  }


} // class BeeHub_Groups
