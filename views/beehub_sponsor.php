<?php

/*
 * Available variables:
 * $this  The BeeHub_Sponsor instance representing the current sponsor
 * $members  A 2 dimensional array containing all members. Each member array
 * contains 4 keys: user_name, displayname, is_admin, is_accepted. For example:
 * $members[0]['user_name']
 */


$active = "sponsors";
$header = '<style type="text/css">
.fieldname {
  text-align: right;
}
</style>';
$footer = '<script type="text/javascript" src="/system/js/sponsor.js"></script>
<script type="text/javascript" src="/system/js/webdavlib.js"></script>';
require 'views/header_bootstrap.php';

?>
<div class="container-fluid">
  <h1>Sponsor</h1>
  <form method="post">
    <div class="row-fluid">
      <div class="span2 fieldname">Group name</div>
      <div class="span10 fieldvalue"><?= htmlspecialchars($this->prop(BeeHub::PROP_NAME), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
    </div>
    <div class="row-fluid">
      <div class="span2 fieldname">Display name</div>
      <div class="span10 fieldvalue"><input type="text" name="displayname" value="<?= htmlspecialchars($this->prop(DAV::PROP_DISPLAYNAME), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
    </div>
    <div class="row-fluid">
      <div class="span2 fieldname">Description</div>
      <div class="span10 fieldvalue"><input type="text" name="description" value="<?= htmlspecialchars($this->prop(BeeHub::PROP_DESCRIPTION), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
    </div>
    <button id="save_sponsor_buton" class="btn">Save</button>
  </form>

  <form id="membership_form" method="post">
    <h2>Requests</h2>
    <p>The following users requested for you to sponsor them:</p>
    <table>
      <thead>
        <tr>
          <th>user_name</th>
          <th>Display name</th>
          <th>Accept?</th>
          <th>Delete?</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($members as $member) :
          if (!$member['is_accepted']) : ?>
            <tr class="member_row" id="<?= BeeHub::$CONFIG['webdav_namespace']['users_path'] . rawurlencode($member['user_name']) ?>">
              <td><?= htmlspecialchars($member['user_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($member['displayname'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
              <td><a href="#" class="accept_link">Accept</a></td>
              <td><a href="#" class="remove_link">Delete</a></td>
            </tr>
          <?php endif;
        endforeach; ?>
      </tbody>
    </table>

    <h2>Current members</h2>
    <p>The following users are member:</p>
    <table>
      <thead>
        <tr>
          <th>user_name</th>
          <th>Display name</th>
          <th>is_admin?</th>
          <th>Delete?</th>
        </tr>
      </thead>
      <tbody id="current_members">
        <?php foreach ($members as $member) :
          if ($member['is_accepted']) : ?>
            <tr class="member_row" id="<?= BeeHub::$CONFIG['webdav_namespace']['users_path'] . rawurlencode($member['user_name']) ?>">
              <td><?= htmlspecialchars($member['user_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($member['displayname'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
              <td><?= ($member['is_admin'] ? 'jep <a href="#" class="demote_link">demote</a>' : 'nope <a href="#" class="promote_link">promote</a>') ?></td>
              <td><a href="#" class="remove_link">Delete</a></td>
            </tr>
          <?php endif;
        endforeach; ?>
      </tbody>
    </table>
    <input type="submit" value="Store" />
  </form>
</div>
<?php require 'views/footer_bootstrap.php'; ?>
