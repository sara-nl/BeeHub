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
class BeeHub_User extends BeeHub_Principal {


private static $statement_display_name = null;
private static $param_user_login = null;
private static $result_display_name = null;


public function __construct($path) {
  if (null === self::$statement_display_name) {
    self::$statement_display_name = BeeHub::mysqli()->prepare(
      'SELECT `display_name` FROM `bh_users` WHERE `user_login` = ?;'
    );
    self::$statement_display_name->bind_param('s', self::$param_user_login);
    self::$statement_display_name->bind_result(self::$result_display_name);
  }
  self::$param_user_login = basename($path);
  self::$statement_display_name->execute();
  self::$result_display_name = null;
  self::$statement_display_name->fetch();
  $this->display_name = self::$result_display_name;
  self::$statement_display_name->free_result();
  if (null === $this->display_name)
    throw new DAV_Status(DAV::HTTP_NOT_FOUND);
  parent::__construct($path);
}


public function user_prop_group_membership() {
  $esclogin = BeeHub::escape_string(basename($this->path));
  $query = <<<EOS
SELECT `g`.`slug`
FROM `bh_users` AS `u`
INNER JOIN `bh_bp_groups_members` AS `gm`
  ON `gm`.`user_id` = `u`.`ID`
INNER JOIN `bh_bp_groups` AS `g`
  ON `g`.`id` = `gm`.`group_id`
WHERE `u`.`user_login` = $esclogin;
EOS;
  $result = BeeHub::query($query);
  $retval = array();
  while (($row = $result->fetch_row()))
    $retval[] = BeeHub::$CONFIG['groups_path'] . rawurlencode($row[0]);
  $result->free();
  return $retval;
}


public function user_prop_group_member_set() {
  return array();
}


public function user_set_group_member_set($set) {
  throw new DAV_Status(DAV::HTTP_FORBIDDEN);
}


} // class BeeHub_User


