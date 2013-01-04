<?php
/*
$path     The path of the directory
$members  All members of this directory
*/
?>
<h1>Directory index</h1>
<?php if ( '/' != $path ) : ?>
  <p><a href="../">Up one level</a></p>
<?php endif; ?>
<ul>
  <?php foreach ($members as $member) : ?>
    <li><a href="<?= $path . $member ?>"><?= DAV::xmlescape(rawurldecode($member)) ?></a></li>
  <?php endforeach; ?>
</ul>
