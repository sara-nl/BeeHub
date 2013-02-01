<?php
/*
 * Available variables:
 *
 * $directory  The beehub_directory object representing the current directory
 * $sponsors   All members of this directory
 */
$active = "sponsors";
$header = '<style type="text/css">
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
</style>';
include 'views/header_bootstrap.php';
?><div class="container-fluid">
  <h1>Sponsors</h1>
  <?php foreach ($sponsors as $sponsor) : ?>
  <div class="row-fluid groupname">
    <div class="span10">
      <h4><?= htmlspecialchars($sponsor->prop(DAV::PROP_DISPLAYNAME), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h4>
    </div>
    <div class="span2 actions">
      <?= ('is_admin?' == 'is_admin?' ? '<a href="' . $sponsor->path . '">Admin</a> / ' : '') ?><a href="#">Unsubscribe</a>
    </div>
  </div>
  <div class="row-fluid groupdescription">
    <div class="span9 offset1"><?= htmlspecialchars($sponsor->prop(BeeHub::PROP_DESCRIPTION), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php include 'views/footer_bootstrap.php'; ?>
