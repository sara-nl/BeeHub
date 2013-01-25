<?php
/*
Available variables:

$directory  The beehub_directory object representing the current directory
$groups     All members of this directory
*/
$this->setTemplateVar('active', "groups");
$this->setTemplateVar('header', '<style type="text/css">
.groupname {
  padding: 0.5em;
  background: #ddd;
}
.groupdescription {
  padding: 0.5em;
  margin-bottom: 2em;
}
.actions {
  margin: 10px 0;
  text-align: right;
}
</style>');
?>
<h1>Groups</h1>
<?php foreach ($groups as $group) : ?>
  <div class="row-fluid groupname">
    <div class="span10"><h4><?= $group->prop(DAV::PROP_DISPLAYNAME) ?></h4></div>
    <div class="span2 actions"><?= ('is_admin?' == 'is_admin?' ? '<a href="' . $group->path . '">Admin</a> / ' : '') ?><a href="#">Unsubscribe</a></div>
  </div>
  <div class="row-fluid groupdescription">
    <div class="span9 offset1"><?= $group->prop(BeeHub::PROP_DESCRIPTION) ?></div>
  </div>
<?php endforeach; ?>