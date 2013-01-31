<?php
/*
 * Available variables:
 * $sponsor  The BeeHub_Sponsor instance representing the current sponsor
 * $members  A 2 dimensional array containing all members. Each member array contains 4 keys: user_name, displayname, admin, accepted. For example: $members[0]['user_name']
 */
$this->setTemplateVar('active', "sponsor");
$this->setTemplateVar('header', '<style type="text/css">
.fieldname {
  text-align: right;
}
</style>');
$this->setTemplateVar('footer', '<script type="text/javascript" src="/system/js/sponsor.js"></script>
<script type="text/javascript" src="/system/js/webdavlib.js"></script>');
?><div class="bootstrap">
<h1>Sponsor</h1>
<form method="post">
  <div class="row-fluid">
    <div class="span2 fieldname">Group name</div>
    <div class="span10 fieldvalue"><?= htmlspecialchars($sponsor->prop(BeeHub::PROP_NAME), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname">Display name</div>
    <div class="span10 fieldvalue"><input type="text" name="displayname" value="<?= htmlspecialchars($sponsor->prop(DAV::PROP_DISPLAYNAME), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname">Description</div>
    <div class="span10 fieldvalue"><input type="text" name="description" value="<?= htmlspecialchars($sponsor->prop(BeeHub::PROP_DESCRIPTION), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
  </div>
  <button id="save_sponsor_buton" class="btn">Save</button>
</form>
</div>

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
        if (!$member['accepted']) : ?>
          <tr class="member_row" id="<?= BeeHub::$CONFIG['webdav_namespaces']['users_path'] . $member['user_name'] ?>">
            <td><?= $member['user_name'] ?></td>
            <td><?= $member['displayname'] ?></td>
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
        <th>Display name</th>
        <th>Admin?</th>
        <th>Delete?</th>
      </tr>
    </thead>
    <tbody id="current_members">
      <?php foreach ($members as $member) :
        if ($member['accepted']) : ?>
          <tr class="member_row" id="<?= $member['path'] ?>">
            <td><?= $member['displayname'] ?></td>
            <td><?= ($member['admin'] ? 'jep <a href="#" class="demote_link">demote</a>' : 'nope <a href="#" class="promote_link">promote</a>') ?></td>
            <td><a href="#" class="remove_link">Delete</a></td>
          </tr>
        <?php endif;
      endforeach; ?>
    </tbody>
  </table>
  <input type="submit" value="Store" />
</form>
