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
<div class="row-fluid" id="user-<?= rawurlencode($member['user_name']) ?>">
  <div class="span12 well well-small"><table width="100%"><tbody><tr>
    <th align="left"><?= DAV::xmlescape($member['displayname']) ?></th>
    <?php if ($this->is_admin()) : ?>
    <td align="right">
      <!-- Promote or demote? -->
      <?php if ( $member['is_admin'] ) : ?>
      <button type="button" value="<?= DAV::xmlescape($member['user_name']) ?>" class="btn btn-primary">Demote to member</button>
      <?php else : ?>
      <button type="button" value="<?= DAV::xmlescape($member['user_name']) ?>" class="btn btn-primary">Promote to admin</button>
      <?php endif; ?>
      <button type="button" value="<?= DAV::xmlescape($member['user_name']) ?>" class="btn btn-danger">Remove member</button>
    </td>
    <?php endif; ?>
  </tr></tbody></table></div>
</div>
<?php   endif;
      endforeach; ?>
<?php 

/*    <td><?= DAV::xmlescape($member['user_name']) ?></td>
    <td><?= DAV::xmlescape($member['displayname']) ?></td>
    <td><?= ($member['is_admin'] ? 'jep <a href="#" class="demote_link">demote</a>' : 'nope <a href="#" class="promote_link">promote</a>') ?></td>
    <td><a href="#" class="remove_link">Delete</a></td>
  </tr>
  <table>
    <thead>
      <tr>
        <th>user_name</th>
        <th>Display name</th>
        <th>Admin?</th>
        <th>Delete?</th>
      </tr>
    </thead>
    <tbody id="current_members">
      <?php foreach ($members as $member) :
        if ($member['is_invited']) : ?>
          <tr class="member_row" id="<?= BeeHub::$CONFIG['namespace']['users_path'] . rawurlencode($member['user_name']) ?>">
            <td><?= DAV::xmlescape($member['user_name']) ?></td>
            <td><?= DAV::xmlescape($member['displayname']) ?></td>
            <td><?= ($member['is_admin'] ? 'jep <a href="#" class="demote_link">demote</a>' : 'nope <a href="#" class="promote_link">promote</a>') ?></td>
            <td><a href="#" class="remove_link">Delete</a></td>
          </tr>
        <?php endif;
      endforeach; ?>
    </tbody>
  </table>
  <input type="submit" value="Store" />
</form>
<?php */
  $footer='<script type="text/javascript" src="/system/js/group.js"></script>';
  require 'views/footer.php';
