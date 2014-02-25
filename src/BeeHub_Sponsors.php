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
 * Collection of sponsors
 * @package BeeHub
 */
class BeeHub_Sponsors extends BeeHub_Principal_Collection {


  /**
   * @see BeeHub_Principal_Collection::report_principal_search_property_set()
   */
  public function report_principal_search_property_set() {
    return array();
  }


  /**
   * @see DAVACL_Principal_Collection::report_principal_property_search()
   */
  public function report_principal_property_search ($input) {
    throw new DAV_Status(
      DAV::HTTP_BAD_REQUEST,
      'You\'re searching for a property which cannot be searched.'
    );
  }


  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET() {
    $sponsors = array();
    foreach ($this as $sponsor)
      $sponsors[] = DAV::$REGISTRY->resource( $this->path . $sponsor );
    $this->include_view(null, array('sponsors' => $sponsors));
  }


  protected function init_members() {
    $stmt = BeeHub_DB::execute(
      'SELECT `sponsor_name`
       FROM `beehub_sponsors`
       ORDER BY `displayname`'
    );
    $this->members = array();
    while ($row = $stmt->fetch_row()) {
      $this->members[] = rawurlencode($row[0]);
    }
    $stmt->free_result();
  }


}// class BeeHub_Sponsors
