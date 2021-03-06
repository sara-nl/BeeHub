<?php
/*
 * Available variables:
 * $sponsor  The BeeHub_Sponsor instance representing the current sponsor
 * $members  A 2 dimensional array containing all members. Each member array
 *   contains 5 keys: user_name, displayname, admin, invited and requested.
 *   For example: $members[0]['user_name']
 */

$header = '<style type="text/css">
  /* style the axis elements */
  .axis path,
  .axis line {
    fill: none;
    stroke : black;
    shape-rendering: crispEdges;
  }

  .axis text {
    font-family: sans-serif;
    font-size: 11px;
  }
</style>';

require 'views/header.php';
?>

<div id="bs-gs-view">
 <h4 id="bh-gs-header"><?= DAV::xmlescape( $this->user_prop( DAV::PROP_DISPLAYNAME ) ) ?> (<?= DAV::xmlescape(basename($this->path)) ?>)</h4><br>
 <!-- Tabs-->
 <ul id="beehub-top-tabs" class="nav nav-tabs">
   <li class="active"><a href="#bh-gs-panel-members" data-toggle="tab">Members</a></li>
   <?php if ( $this->is_admin() ) : ?>
    <li><a href="#bh-gs-panel-edit" data-toggle="tab">Edit</a></li>
    <li><a href="#bh-gs-panel-usage" data-toggle="tab">Usage</a></li>
   <?php endif; ?>
 </ul>
 
 <!-- Tab contents -->
 <div class="tab-content">
 
  <!-- My Members tab -->
  <div id="bh-gs-panel-members" class="tab-pane fade in active">
   <?php if ( $this->is_member() ) : ?>
    <?php if ( $this->is_admin() ) : ?>
      <form id="bh-gs-invite-gs-form" class="form-horizontal">
        <div class="control-group">
          <div class="controls bh-gs-invite_members">
            <button  type="submit" class="btn btn-primary">Add user</button>
            <input type="text" id="bh-gs-invite-typeahead" data-provide="typeahead" placeholder="Type username..." autocomplete="off" required>
          </div>
        </div>
      </form>
   <?php endif; ?>
  
   <?php foreach ($members as $member) : ?>
     <div class="row-fluid" id="bh-gs-user-<?= DAV::xmlescape($member['user_name']) ?>">
       <div class="span12 well well-small"><table width="100%"><tbody><tr>
         <th align="left"><?= DAV::xmlescape($member['displayname']) ?> </th>
         <?php if ($this->is_admin()) : ?>
          <td align="right">
            <!-- Promote or demote? -->
            <?php if ( $member['is_admin'] ) : ?>
            <button type="button" value="<?= DAV::xmlescape($member['user_name']) ?>" class="btn btn-primary bh-gs-demote-gs">Demote to member</button>
            <?php else : ?>
            <button type="button" value="<?= DAV::xmlescape($member['user_name']) ?>" class="btn btn-primary bh-gs-promote-gs">Promote to admin</button>
            <?php endif; ?>
            <button type="button" value="<?= DAV::xmlescape($member['user_name']) ?>" class="btn btn-danger bh-gs-remove-gs">Remove member</button>
          </td>
         <?php endif; ?>
         </tr></tbody></table></div>
       </div>
     <?php endforeach ?>
   <?php endif ?>
  </div>
  <!--  End members tab -->
 
  <!-- Edit tab -->
  <div id="bh-gs-panel-edit" class="tab-pane">
    <form id="bh-gs-edit-form" class="form-horizontal" action="<?= DAV::xmlescape($this->path) ?>" method="post">
      <div class="control-group">
        <label class="control-label bh-gs-display-gs" for="bh-gs-display-name">Display name</label>
        <div class="controls">
          <input type="text" id="bh-gs-display-name" name="displayname" value="<?= DAV::xmlescape( $this->user_prop_displayname() ) ?>" required data-org-name="<?= DAV::xmlescape( $this->user_prop_displayname() ) ?>"/>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label bh-gs-display-gs" for="bh-gs-sponsor-description">Group description</label>
        <div class="controls">
          <textarea class="input-xlarge" id="bh-gs-sponsor-description" rows="5" name="description" data-org-name="<?= DAV::xmlescape( $this->user_prop(BeeHub::PROP_DESCRIPTION) ) ?>"><?= DAV::xmlescape( $this->user_prop(BeeHub::PROP_DESCRIPTION) ) ?></textarea>
        </div>
      </div>
      <div class="control-group">
        <div class="controls">
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </div>
    </form>
  </div>
  <!--  End edit tab -->
  
  <!-- Usage tab -->
  <br/>
  <div id="bh-gs-panel-usage" class="tab-pane">
    <div id="bh-dir-loading" hidden="hidden"></div>
  </div>
  <!--  End usage tab -->
  
  </div>
  <!-- End tab contents -->
</div>

<?php
$footer='<script type="text/javascript" src="/system/js/groupsponsor.js"></script>
         <script type="text/javascript" src="/system/js/gs-controller.js"></script>
         <script type="text/javascript" src="/system/js/gs-utils.js"></script>
         <script type="text/javascript" src="/system/js/groupsponsor-view.js"></script>
         <script type="text/javascript" src="/system/js/plugins/d3.min.js"></script>
';
require 'views/footer.php';
