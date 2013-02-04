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
class BeeHub_Users extends BeeHub_Principal_Collection {

  public function method_GET($headers) {
    $view = new BeeHub_View('new_user.php');
    $view->parseView();
  }


  public function method_POST(&$headers) {
    //TODO: check juistheid POST formulier
    $user_name = $_POST['user_name'];
    $displayname = $_POST['displayname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $surfconext_id = $_POST['surfconext_id'];
    $x509 = $_POST['x509'];

    // Store in the database
    $statement = BeeHub::mysqli()->prepare("INSERT INTO `beehub_users` (`user_name`, `surfconext_id`) VALUES (?, ?)");
    $statement->bind_param('ss', $user_name, $surfconext_id);//, $displayname, $email, $password, $x509);
    if (!$statement->execute()) {
      throw new Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }

    $user = BeeHub_Registry::inst()->resource(BeeHub::$CONFIG['namespace']['users_path'] . $user_name);
    $user->user_set_internal(DAV::PROP_DISPLAYNAME, $displayname);
    $user->user_set_internal(BeeHub::PROP_EMAIL, $email);
    $user->user_set_internal(BeeHub::PROP_PASSWORD, $password);
    $user->user_set_internal(BeeHub::PROP_X509, $x509);
    $user->storeProperties();
  }


  public function report_principal_property_search($properties) {
    if ( 1 != count( $properties ) ||
         ! isset( $properties[DAV::PROP_DISPLAYNAME] ) ||
         1 != count( $properties[DAV::PROP_DISPLAYNAME] ) )
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST,
        'You\'re searching for a property which cannot be searched.'
      );
    $match = $properties[DAV::PROP_DISPLAYNAME][0];
    $match = str_replace(array('_', '%'), array('\\_', '\\%'), $match) . '%';
    $match = BeeHub::escape_string($match);
    $result = BeeHub::query("SELECT `user_name` FROM `beehub_users` WHERE `displayname` LIKE {$match};");
    $retval = array();
    while ($row = $result->fetch_row()) {
      $retval[] = BeeHub::$CONFIG['namespace']['users_path'] . rawurlencode($row[0]);
    }
    $result->free();
    return $retval;
  }


  protected function init_members() {
    $result = BeeHub::query('SELECT `user_name` FROM `beehub_users`;');
    $this->members = array();
    while ($row = $result->fetch_row()) {
      $this->members[] = rawurlencode($row[0]);
    }
    $result->free();
  }


} // class BeeHub_Users