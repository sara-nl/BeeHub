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
 * A sponsor principal
 *
 * @TODO Checken of de properties in de juiste gevallen afschermd worden
 * @package BeeHub
 */
class BeeHub_Sponsor extends BeeHub_File {

  const RESOURCETYPE = '<sponsor xmlns="http://beehub.nl/" />';

  private static $statement_props = null;
  private static $param_sponsor = null;
  private static $result_sponsor_id = null;
  private static $result_display_name = null;
  private static $result_description = null;

  protected $id = null;
  private $admin = null;

  public function __construct($path) {
    $localPath = BeeHub::localPath($path);
    if (!file_exists($localPath)) {
      $result = touch($localPath);
      if (!$result)
        throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
      xattr_set($localPath, rawurlencode(DAV::PROP_GETETAG), BeeHub::ETag(0));
      xattr_set($localPath, rawurlencode(DAV::PROP_OWNER), BeeHub::$CONFIG['webdav_namespace']['wheel_path']);
    }
    parent::__construct($path);
  }

  /**
   * @return string an HTML file
   * @see DAV_Resource::method_GET()
   */
  public function method_GET($headers) {
    $query = <<<EOS
    SELECT `user_name`,
           `display_name`,
           `admin`,
           `accepted`
      FROM `beehub_users`
INNER JOIN `beehub_sponsor_members`
     USING (`user_id`)
     WHERE `beehub_sponsor_members`.`sponsor_id` = ?;
EOS;
    $statement = BeeHub::mysqli()->prepare($query);
    $sponsorId = $this->getId();
    $statement->bind_param('d', $sponsorId);
    $user_name = null;
    $displayname = null;
    $admin = null;
    $accepted = null;
    $statement->bind_result($user_name, $displayname, $admin, $accepted);
    $statement->execute();
    $members = array();
    while ($statement->fetch()) {
      $members[] = Array(
        'user_name' => $user_name,
        'displayname' => $displayname,
        'admin' => ($admin == 1),
        'accepted' => ($accepted == 1)
      );
    }
    $view = new BeeHub_View('sponsor.php');
    $view->setVar('sponsor', $this);
    $view->setVar('members', $members);
    return ((BeeHub::best_xhtml_type() != 'text/html') ? DAV::xml_header() : '' ) . $view->getParsedView();
  }

  public function method_HEAD() {
    // Test if the current user is admin of this sponsor, only those are allowed to HEAD and GET.
    if (!$this->isAdmin()) {
      throw new DAV_Status(
              DAV::HTTP_FORBIDDEN,
              DAV::COND_NEED_PRIVILEGES
      );
    }
    return array( 'Cache-Control' => 'no-cache' );
  }

  public function method_POST ( &$headers ) {
    //First add members, admins and requests
    foreach (array('add_requests', 'add_members', 'add_admins', 'delete_admins', 'delete_members', 'delete_requests') as $key) {
      if (isset($_POST[$key])) {
        $members = array();
        if (!is_array($_POST[$key])) {
          throw new DAV_Status(DAV::HTTP_BAD_REQUEST);
        }
        foreach ($_POST[$key] as $uri) {
          $members[] = DAV::parseURI($uri, false);
        }
        switch ($key) {
          case 'add_members':
            $this->change_memberships($members, true, false, true);
            break;
          case 'add_admins':
            $this->change_memberships($members, true, true, true, true);
            break;
          case 'add_requests':
            $this->change_memberships($members, false, false, false, false);
            break;
          case 'delete_admins':
            $this->change_memberships($members, true, false, true, false);
            break;
          case 'delete_members':
          case 'delete_requests':
            $this->delete_members($members);
            break;
          default: //Should/cloud never happen
            throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
          break;
        }
      }
    }
  }

  /**
   * Adds member requests or sets them to be an accepted member or an administrator
   *
   * @param   Array    $members           An array with paths to the principals to add
   * @param   Boolean  $newAccepted       The value the 'accepted' field should have if the membership had to be added to the database
   * @param   Boolean  $newAdmin          The value the 'admin' field should have if the membership had to be added to the database
   * @param   Boolean  $existingAccepted  Optionally; The value the 'accepted' field should have if the membership is already in the database. If ommited values will not be changed for existing memberships
   * @param   Boolean  $existingAdmin     Optionally; The value the 'admin' field should have if the membership is already in the database. If ommited values will not be changed for existing membership
   * @return  void
   */
  protected function change_memberships($members, $newAccepted, $newAdmin, $existingAccepted = null, $existingAdmin = null){
    $sponsorId = intval($this->getId());
    $newAccepted = ($newAccepted ? 1 : 0);
    $newAdmin = ($newAdmin ? 1 : 0);
    if (is_null($existingAccepted)) {
      $existingAccepted = "`accepted`";
    }else{
      $existingAccepted = ($existingAccepted ? 1 : 0);
    }
    if (is_null($existingAdmin)) {
      $existingAdmin = "`admin`";
    }else{
      $existingAdmin = ($existingAdmin ? 1 : 0);
    }
    if (count($members) > 0) {
      $idQueryParts = array();
      foreach ($members as $path) {
        $user = BeeHub_Registry::inst()->resource($path);
        $idQueryParts[] = "('" . $sponsorId . "', '" . intval($user->getId()) . "', " . $newAdmin . ", " . $newAccepted . ")";
        // TODO: sent the user an e-mail
      }
      $query = 'INSERT INTO `beehub_sponsor_members` (`sponsor_id`, `user_id`, `admin`, `accepted`) VALUES ' . implode(',', $idQueryParts) . ' ON DUPLICATE KEY UPDATE `admin`=' . $existingAdmin . ', `accepted`=' . $existingAccepted;
      BeeHub::mysqli()->query($query);
    }
  }

  /**
   * Delete memberships
   *
   * @param   Array    $members           An array with paths to the principals to add
   * @return  void
   */
  protected function delete_members($members) {
    $sponsorId = intval($this->getId());
    if (count($members) > 0) {
      $idQueryParts = array();
      foreach ($members as $path) {
        $user = BeeHub_Registry::inst()->resource($path);
        $idQueryParts[] = "(`sponsor_id`='" . $sponsorId . "' AND `user_id`='" . intval($user->getId()) . "')";
      }
      BeeHub::mysqli()->query('DELETE FROM `beehub_sponsor_members` WHERE ' . implode(' OR ', $idQueryParts));
    }
  }

  protected $sql_props = null;
  protected function init_props() {
    if (is_null($this->sql_props)) {
      parent::init_props();
      $this->protected_props[BeeHub::PROP_NAME] = basename($this->path);

      if (null === self::$statement_props) {
        self::$statement_props = BeeHub::mysqli()->prepare(
                'SELECT
                  `sponsor_id`,
                  `display_name`,
                  `description`
                 FROM `beehub_sponsors`
                 WHERE `sponsorname` = ?;'
        );
        self::$statement_props->bind_param('s', self::$param_sponsor);
        self::$statement_props->bind_result(
                self::$result_sponsor_id, self::$result_display_name, self::$result_description
        );
      }
      self::$param_sponsor = $this->prop(BeeHub::PROP_NAME);
      self::$statement_props->execute();
      self::$result_sponsor_id = null;
      self::$result_display_name = null;
      self::$result_description = null;
      self::$statement_props->fetch();
      $this->id = self::$result_sponsor_id;
      $this->sql_props[DAV::PROP_DISPLAYNAME] = self::$result_display_name;
      $this->sql_props[BeeHub::PROP_DESCRIPTION] = self::$result_description;
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
    if (isset($this->sql_props[DAV::PROP_DISPLAYNAME])) {
      $displayname = $this->sql_props[DAV::PROP_DISPLAYNAME];
      unset($this->sql_props[DAV::PROP_DISPLAYNAME]);
    } else {
      $displayname = '';
    }
    if (isset($this->sql_props[BeeHub::PROP_DESCRIPTION])) {
      $description = $this->sql_props[BeeHub::PROP_DESCRIPTION];
      unset($this->sql_props[BeeHub::PROP_DESCRIPTION]);
    } else {
      $description = null;
    }

    // Write all data to database
    $updateStatement = BeeHub::mysqli()->prepare('UPDATE `beehub_sponsors` SET `display_name`=?, `description`=? WHERE `sponsor_id`=?');
    $id = $this->getId();
    $updateStatement->bind_param('ssd', $displayname, $description, $id);
    $updateStatement->execute();

    // Store all other properties
    parent::storeProperties();

    // And set the database properties again
    $this->sql_props[DAV::PROP_DISPLAYNAME] = $displayname;
    if (!is_null($description)) {
      $this->sql_props[BeeHub::PROP_DESCRIPTION] = $description;
    }
  }

  public function user_prop($propname) {
    switch ($propname) {
      case DAV::PROP_GROUP_MEMBER_SET:
        $retval = $this->user_prop_group_member_set();
        return $retval ? new DAV_Element_href( $retval ) : '';
      default:
        return parent::user_prop($propname);
      break;
    }
  }

  protected function user_set($propname, $value = null) {
    $this->assert(DAVACL::PRIV_WRITE);
    $this->init_props();
    switch ($propname) {
      case DAV::PROP_GROUP_MEMBER_SET:
        if (is_null($value)) {
          $value = array();
        }
        $set = DAVACL::parse_hrefs($value)->URIs;
        foreach ($set as &$uri) {
          $uri = DAV::parseURI($uri, false);
        }
        $this->user_set_group_member_set($set);
        $this->touched = true;
        break;
      default:
        return parent::user_set($propname, $value);
      break;
    }
  }

  public function user_set_group_member_set($set) {
  }

  public function user_prop_group_member_set() {
  }

  // We allow everybody to do everything with this object in the ACL, so we can handle all privileges hard-coded without ACL's interfering
  public function user_prop_acl() {
    return array(new DAVACL_Element_ace('DAV: all', false, array('DAV: all'), false, true, null));
  }

  /**
   * Gets the (database) ID of the user
   *
   * @return  int  The (database) ID of this user
   */
  public function getId() {
    $this->init_props();
    return $this->id;
  }

  /**
   * Determines whether the currently logged in user is an administrator of this sponsor or not.
   *
   * @return  boolean  True if the currently logged in user is an administrator of this sponsor, false otherwise
   */
  public function isAdmin() {
    if (is_null($this->admin)) {
      $result = null;
      $userId = BeeHub_Registry::inst()->resource(BeeHub::current_user())->getId();
      $sponsorId = $this->getId();
      $statement = BeeHub::mysqli()->prepare('SELECT `user_id` FROM `beehub_sponsor_members` WHERE `sponsor_id`=? AND `user_id`=? AND `admin`=1');
      $statement->bind_param('dd', $sponsorId, $userId);
      $statement->bind_result($result);
      $statement->execute();
      $response = $statement->fetch();
      if (is_null($response) || !($result > 0)) {
        $this->admin = false;
      } else {
        $this->admin = true;
      }
    }
    return $this->admin;
  }

  // These methods are only available for a limited range of users!
  public function method_PROPPATCH($propname, $value = null) {
    if (!$this->isAdmin()) {
      throw new DAV_Status(
              DAV::HTTP_FORBIDDEN,
              DAV::COND_NEED_PRIVILEGES
      );
    }
    return parent::method_PROPPATCH($propname, $value);
  }

  // All these methods are forbidden:
  public function method_ACL($aces) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

  public function method_COPY($path) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

  public function method_COPY_external($destination, $overwrite) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

  public function method_PUT($stream) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

  public function method_PUT_range($stream, $start, $end, $total) {
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  }

} // class BeeHub_Sponsor