<?php
/*
 * Available variables:
 *
 * $this       The beehub_directory object representing the current directory
 */
require 'views/header.php';
?><div class="container-fluid">
<h1>Directory index</h1>
<?php if ( '/' != $this->path ) : ?>
<p><a href="../">Up one level</a></p>
<?php endif; ?>
<ul>
  <?php foreach ($this as $member) : $memberPath = $this->path . $member; ?>
  <li><a href="<?= $memberPath ?>"><?= DAV::xmlescape(rawurldecode($member)) ?></a></li>
  <?php endforeach; ?>
</ul>
</div>
<?php require 'views/footer.php'; ?>
