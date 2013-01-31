<?php
/*
 * Available variables:
 * $sponsor  The BeeHub_Sponsor instance representing the current sponsor
 * $members  A 2 dimensional array containing all members. Each member array contains 5 keys: user_name, displayname, admin, invited and requested. For example: $members[0]['user_name']
 */
$this->setTemplateVar('active', "groups");
$this->setTemplateVar('header', '<style type="text/css">
.fieldname {
  text-align: right;
}
</style>');
$this->setTemplateVar('footer', '<script type="text/javascript" src="/system/js/group.js"></script>
<script type="text/javascript" src="/system/js/webdavlib.js"></script>');
?><div class="bootstrap">
<h1>Profile</h1>
<form method="post">
  <div class="row-fluid">
    <div class="span2 fieldname">Group name</div>
    <div class="span10 fieldvalue"><?= htmlspecialchars($group->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname">Display name</div>
    <div class="span10 fieldvalue"><input type="text" name="displayname" value="<?= htmlspecialchars($group->prop(DAV::PROP_DISPLAYNAME), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
  </div>
  <div class="row-fluid">
    <div class="span2 fieldname">Description</div>
    <div class="span10 fieldvalue"><input type="text" name="description" value="<?= htmlspecialchars($group->prop(BeeHub::PROP_DESCRIPTION), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" /></div>
  </div>
  <button class="btn">Save</button>
</form>
</div>

<table>
  <thead>
    <tr>
      <th>user_name</th>
      <th>Display name</th>
      <th>Admin?</th>
      <th>Invited?</th>
      <th>Requested?</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($members as $member) : ?>
      <tr>
        <td><?= $member['user_name'] ?></td>
        <td><?= $member['displayname'] ?></td>
        <td><?= ($member['admin'] ? 'jep' : 'nope') ?></td>
        <td><?= ($member['invited'] ? 'jep' : 'nope') ?></td>
        <td><?= ($member['requested'] ? 'jep' : 'nope') ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
