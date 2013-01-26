<?php
/*
Available variables:

$directory  The beehub_directory object representing the current directory
$members    All members of this directory
*/
$this->setTemplateVar('active', 'files');
?>
<h1>Directory index</h1>
<?php if ( '/' != $directory->path ) : ?>
  <p><a href="../">Up one level</a></p>
<?php endif; ?>
<ul>
  <?php foreach ($members as $member) : $memberPath = $member->path; ?>
    <li><a href="<?= $memberPath ?>"><?= DAV::xmlescape(rawurldecode(basename($memberPath))) ?></a></li>
  <?php endforeach; ?>
</ul>
