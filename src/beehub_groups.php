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
 * Some class.
 * @package BeeHub
 */
class BeeHub_Groups extends BeeHub_Principal_Collection {

  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET() {
    $this->assert(DAVACL::PRIV_READ);
    $view = new BeeHub_View('groups.php');
    $view->setVar('directory', $this);
    $groups = array();
    foreach ($this as $group) {
      $groups[strtolower($group)] = DAV::$REGISTRY->resource($this->path . $group);
    }
    ksort($groups, SORT_STRING);
    $view->setVar('groups', $groups);
    return ((BeeHub::best_xhtml_type() != 'text/html') ? DAV::xml_header() : '' ) . $view->getParsedView();
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
    $match = BeeHub::escape_string($match);
    $result = BeeHub::query("SELECT `groupname` FROM `beehub_groups` WHERE `display_name` LIKE {$match};");
    $retval = array();
    while ($row = $result->fetch_row()) {
      $retval[] = BeeHub::$CONFIG['webdav_namespace']['groups_path'] . rawurlencode($row[0]);
    }
    $result->free();
    return $retval;
  }

  protected function init_members() {
    $result = BeeHub::query('SELECT `groupname` FROM `beehub_groups`;');
    $this->members = array();
    while ($row = $result->fetch_row()) {
      $this->members[] = rawurlencode($row[0]);
    }
    $result->free();
  }

}

// class BeeHub_Groups