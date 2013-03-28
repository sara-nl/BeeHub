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
.inviteMembers {
		margin-left: 20px !important;
}
.displayGroup {
		width: 110px !important;
}
</style>';

require 'views/header.php';
?>
<h1>Group</h1>
<div class="row-fluid" id="beehub-group-display">
    <dl class="dl-horizontal">
      <dt class="displayGroup" >Name</dt>
      <dd id="groupNameValue"><?= DAV::xmlescape( $this->name) ?></dd>
      <dt class="displayGroup" >Display name</dt>
      <dd id="groupDisplayNameValue"><?= DAV::xmlescape( $this->user_prop( DAV::PROP_DISPLAYNAME ) ) ?></dd>
      <dt class="displayGroup">Description</dt>
      <dd id="groupDescriptionValue" style="white-space: pre-wrap;"><?= DAV::xmlescape( $this->user_prop(BeeHub::PROP_DESCRIPTION) ) ?></dd>
      <?php if ( $this->is_admin() ) : ?>
        <br/>
        <dt class="displayGroup"></dt>
        <dd class="btn" id="edit-group-button">Edit group</dd>
      <?php endif; ?>
    </dl>
</div>

<div class="row-fluid hide" id="beehub-group-edit">
  <div class="span12">  
  	<br/>  
    <form id="editGroupForm" class="form-horizontal" action="<?= DAV::xmlescape($this->path) ?>" method="post">
      <div class="control-group">
        <label class="control-label displayGroup" for="groupDisplayName">Display name</label>
        <div class="controls">
          <input type="text" id="groupDisplayName" name="displayname" value="<?= DAV::xmlescape( $this->user_prop_displayname() ) ?>" required />
        </div>
      </div>
      <div class="control-group">
        <label class="control-label displayGroup" for="groupDescription">Group description</label>
        <div class="controls">
          <textarea class="input-xlarge" id="groupDescription" rows="5" name="description"><?= DAV::xmlescape( $this->user_prop(BeeHub::PROP_DESCRIPTION) ) ?></textarea>
        </div>
      </div>
      <div class="control-group">
        <div class="controls">
          <button type="submit" class="btn btn-primary">Save</button>
          <button id="cancel-button" type="button" class="btn btn">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php if ( $this->is_member() ) : ?>
  <h2>Current members</h2>
  <br/>
  <form id="inviteGroupForm" class="form-horizontal">
  	<div class="control-group">
  		<div class="controls inviteMembers">
  			<button  type="submit" class="btn btn-primary">Invite user</button>   
  			<input type="text" id="inviteTypeahead" data-provide="typeahead" placeholder="Type username..." autocomplete="off" required> 
  		</div>
   </div>
  </form>
   
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
      <?php
    endif;
  endforeach;
endif;

$footer='<script type="text/javascript" src="/system/js/group.js"></script>';
require 'views/footer.php';
