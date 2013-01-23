<?php

/* * ***********************************************************************
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
 * A sponsor principal
 *
 * There are a few properties defined which are stored in the database instead
 * of as xfs attribute.
 * BeeHub::PROP_SPONSOR_ID
 *
 * @TODO Hoe zorg ik dat <resourcetype> goed gevuld wordt?
 * @TODO Checken of de properties in de juiste gevallen afschermd worden
 * @package BeeHub
 */
class BeeHub_Sponsor extends BeeHub_Principal {

  private static $statement_props = null;
  private static $param_sponsor = null;
  private static $result_sponsor_id = null;
  private static $result_display_name = null;

  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET() {
    // We won't sent sponsor data over regular HTTP, so we require HTTPS!
    if ((APPLICATION_ENV != BeeHub::ENVIRONMENT_DEVELOPMENT) && empty($_SERVER['HTTPS'])) {
      header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    }
    $view = new BeeHub_View('sponsor.php');
    $view->setVar('sponsor', $this);
    return ((BeeHub::best_xhtml_type() != 'text/html') ? DAV::xml_header() : '' ) . $view->getParsedView();
  }

  public function method_HEAD() {
    $this->assert(DAVACL::PRIV_READ);
    return array(
        'Content-Type' => BeeHub::best_xhtml_type() . '; charset="utf-8"',
        'Cache-Control' => 'no-cache'
    );
  }

  public function method_PROPPATCH($propname, $value) {
    // We won't allow user data to be manipulated over regular HTTP, so we require HTTPS!
    if ((APPLICATION_ENV != BeeHub::ENVIRONMENT_DEVELOPMENT) && empty($_SERVER['HTTPS'])) {
      throw new DAV_Status(DAV::HTTP_FORBIDDEN);
    }
    return parent::method_PROPPATCH($propname, $value);
  }

  protected function init_props() {
    if (is_null($this->writable_props)) {
      parent::init_props();
      $this->protected_props[BeeHub::PROP_SPONSORNAME] = basename($this->path);

      if (null === self::$statement_props) {
        self::$statement_props = BeeHub::mysqli()->prepare(
                'SELECT
                  `sponsor_id`,
                  `display_name`
                 FROM `beehub_sponsors`
                 WHERE `sponsorname` = ?;'
        );
        self::$statement_props->bind_param('s', self::$param_sponsor);
        self::$statement_props->bind_result(
                self::$result_sponsor_id, self::$result_display_name
        );
      }
      self::$param_sponsor = $this->prop(BeeHub::PROP_SPONSORNAME);
      self::$statement_props->execute();
      self::$result_sponsor_id = null;
      self::$result_display_name = null;
      self::$statement_props->fetch();
      $this->protected_props[BeeHub::PROP_SPONSOR_ID] = self::$result_sponsor_id;
      $this->writable_props[DAV::PROP_DISPLAYNAME] = self::$result_display_name;
      self::$statement_props->free_result();
    }
  }

  /**
   * Stores properties set earlier by set().
   * @return void
   * @throws DAV_Status in particular 507 (Insufficient Storage)
   */
  public function storeProperties() {
    if (!$this->touched) {
      return;
    }

    // Are database properties set? If so, get the value and unset them
    if (isset($this->writable_props[DAV::PROP_DISPLAYNAME])) {
      $displayname = $this->writable_props[DAV::PROP_DISPLAYNAME];
      unset($this->writable_props[DAV::PROP_DISPLAYNAME]);
    } else {
      $displayname = '';
    }

    // Write all data to database
    $updateStatement = BeeHub::mysqli()->prepare('UPDATE `beehub_sponsors` SET `display_name`=? WHERE `sponsor_id`=?');
    $id = $this->prop(BeeHub::PROP_SPONSOR_ID);
    $updateStatement->bind_param('sd', $displayname, $id);
    $updateStatement->execute();

    // Store all other properties
    parent::storeProperties();

    // And set the database properties again
    $this->writable_props[DAV::PROP_DISPLAYNAME] = $displayname;
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
      $retval[] = BeeHub::$CONFIG['webdav_namespace']['groups_path'] . rawurlencode($row[0]);
    $result->free();
    return $retval;
  }

  public function user_prop_group_member_set() {
    return array();
  }

  public function user_set_group_member_set($set) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

}

// class BeeHub_Sponsor