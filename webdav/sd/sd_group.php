<?php

/*·************************************************************************
 * Copyright ©2007-2011 Pieter van Beek, Almere, The Netherlands
 * 		    <http://purl.org/net/6086052759deb18f4c0c9fb2c3d3e83e>
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
 *
 * $Id: sd_group.php 3349 2011-07-28 13:04:24Z pieterb $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package SD
 */

/**
 * A class.
 * @package SD
 */
class SD_Group extends SD_Principal {


private static $statement_display_name = null;
private static $param_slug = null;
private static $result_name = null;


public function __construct($path) {
  if (null === self::$statement_display_name) {
    self::$statement_display_name = SD::mysqli()->prepare(
    	'SELECT `name` FROM `bh_bp_groups` WHERE `slug` = ?;'
    );
    self::$statement_display_name->bind_param('s', self::$param_slug);
    self::$statement_display_name->bind_result(self::$result_name);
  }
  self::$param_slug = basename($path);
  self::$statement_display_name->execute();
  self::$result_name = null;
  self::$statement_display_name->fetch();
  $this->display_name = self::$result_name;
  self::$statement_display_name->free_result();
  if (null === $this->display_name)
    throw new DAV_Status(DAV::HTTP_NOT_FOUND);
  parent::__construct($path);
}


public function user_prop_group_membership() {
  return array();
}


public function user_set_group_member_set($set) {
  throw new DAV_Status(DAV::HTTP_FORBIDDEN);
}


public function user_prop_group_member_set() {
  $escslug = SD::escape_string(basename($this->path));
  $query = <<<EOS
SELECT `u`.`user_login`
FROM `bh_bp_groups` AS `g`
INNER JOIN `bh_bp_groups_members` AS `gm`
  ON `g`.`id` = `gm`.`group_id`
INNER JOIN `bh_users` AS `u`
  ON `gm`.`user_id` = `u`.`ID`
WHERE `g`.`slug` = $escslug;
EOS;
  $result = SD::query($query);
  $retval = array();
  while (($row = $result->fetch_row()))
    $retval[] = SD::USERS_PATH . rawurlencode($row[0]);
  $result->free();
  return $retval;
}


//public function user_set_group_member_set($set) {}


} // class SD_Group


