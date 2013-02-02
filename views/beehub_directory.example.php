<?php
/*
 * Available variables:
 *
 * $this       The beehub_directory object representing the current directory
 */
$active = 'files';
include 'views/header_bootstrap.php';
?><div class="container-fluid">
<h1>Directory index</h1>
<?php if ( '/' != $directory->path ) : ?>
<p><a href="../">Up one level</a></p>
<?php endif; ?>
<ul>
  <?php foreach ($this as $member) : $memberPath = $this->path . $member; ?>
  <li><a href="<?= $memberPath ?>"><?= DAV::xmlescape(rawurldecode($member)) ?></a></li>
  <?php endforeach; ?>
</ul>
</div>
<?php include 'views/footer_bootstrap.php'; ?>
