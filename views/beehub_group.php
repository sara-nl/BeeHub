<?php
/*
 * Available variables:
 * $sponsor  The BeeHub_Sponsor instance representing the current sponsor
 * $members  A 2 dimensional array containing all members. Each member array
 *   contains 5 keys: user_name, displayname, admin, invited and requested.
 *   For example: $members[0]['user_name']
 */

$active = "groups";
$header = '<style type="text/css">
.fieldname {
  text-align: right;
}
</style>';

require 'views/header.php';
?>
<h1>Group</h1>
<div class="row-fluid" id="beehub-group-display">
  <div class="span11">
    <dl class="dl-horizontal">
      <dt>Display name</dt>
      <dd id="groupDisplayNameValue"><?= $this->prop(DAV::PROP_DISPLAYNAME) ?></dd>
      <dt>Description</dt>
      <dd id="groupDescriptionValue" style="white-space: pre-wrap;"><?= $this->prop(BeeHub::PROP_DESCRIPTION) ?></dd>
    </dl>
  </div>
  <div class="span1">
    <?php if ($this->is_admin()) : ?>
    <a class="btn btn-primary" id="edit-group-button">Edit</a>
    <?php endif ?>
  </div>
</div>
<div class="row-fluid hide" id="beehub-group-edit">
  <div class="span12">    
    <form id="editGroupForm" class="form-horizontal" action="<?= DAV::xmlescape($this->path) ?>" method="post">
      <div class="control-group">
        <label class="control-label" for="groupDisplayName">Display name</label>
        <div class="controls">
          <input type="text" id="groupDisplayName" name="displayname" value="<?= $this->prop(DAV::PROP_DISPLAYNAME) ?>" required />
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="groupDescription">Group description</label>
        <div class="controls">
          <textarea class="input-xlarge" id="groupDescription" rows="5" name="description"><?= $this->prop(BeeHub::PROP_DESCRIPTION) ?></textarea>
        </div>
      </div>
      <div class="control-group">
        <div class="controls">
          <button type="submit" class="btn">Save</button>
          <button id="cancel-button" type="button" class="btn">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<h2>Current members</h2>
<?php foreach ($members as $member) :
        if ($member['is_invited']) : ?>
<div class="row-fluid" id="user-<?= DAV::xmlescape($member['user_name']) ?>">
  <div class="span12 well well-small"><table width="100%"><tbody><tr>
    <th align="left"><?= DAV::xmlescape($member['displayname']) ?> </th>
    <?php if ($this->is_admin()) : ?>
    <td align="right">
      <!-- Promote or demote? -->
      <?php if ( $member['is_admin'] ) : ?>
      <button type="button" value="<?= DAV::xmlescape($member['user_name']) ?>" class="btn btn-primary demote_link">Demote to member</button>
      <?php else : ?>
      <button type="button" value="<?= DAV::xmlescape($member['user_name']) ?>" class="btn btn-primary promote_link">Promote to admin</button>
      <?php endif; ?>
      <button type="button" value="<?= DAV::xmlescape($member['user_name']) ?>" class="btn btn-danger remove_link">Remove member</button>
    </td>
    <?php endif; ?>
  </tr></tbody></table></div>
</div>
<?php   endif;
      endforeach; ?>
<?php 
  $footer='<script type="text/javascript" src="/system/js/group.js"></script>';
  require 'views/footer.php';
