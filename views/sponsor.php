<?php
/*
 * Available variables:
 * $sponsor  The BeeHub_Sponsor instance representing the current sponsor
 * $members  A 2 dimensional array containing all members. Each member array contains 4 keys: username, displayname, admin, accepted. For example: $members[0]['username']
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
  <button class="btn">Save</button>
</form>
</div>

<table>
  <thead>
    <tr>
      <th>Username</th>
      <th>Display name</th>
      <th>Admin?</th>
      <th>Accepted?</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($members as $member) : ?>
      <tr>
        <td><?= $member['username'] ?></td>
        <td><?= $member['displayname'] ?></td>
        <td><?= ($member['admin'] ? 'jep' : 'nope') ?></td>
        <td><?= ($member['accepted'] ? 'jep' : 'nope') ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>