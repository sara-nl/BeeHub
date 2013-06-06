<?php
  /*
   * Available variables:
   * $groups     All members of this directory
   */
  require 'views/header.php';
?>

<!-- Tabs-->
<ul id="beehub-top-tabs" class="nav nav-tabs">
  <li class="active"><a href="#bh-groups-panel-mygroups" data-toggle="tab">My groups</a></li>
  <li><a href="#bh-groups-panel-join" data-toggle="tab">Join</a></li>
  <li><a href="#bh-groups-panel-create" data-toggle="tab">Create</a></li>
</ul>

<!-- Tab contents -->
<div class="tab-content">

	<!-- My Groups tab -->
	<br/>
  <div id="bh-groups-panel-mygroups" class="tab-pane fade in active">
  	<!--    List with my groups -->
    <div class="accordion" id="bh-groups-mygroups">
        <?php
      $i = 1;
      foreach ($groups as $group) :
           if ( $group->is_member() ) :
      ?>
        <div class="accordion-group">
          <div class="accordion-heading">
            <div class="accordion-toggle" data-toggle="collapse" data-parent="#bh-groups-mygroups" href="#bh-groups-mygroups-<?= $i ?>">
              <table width="100%"><tbody><tr>
                <th align="left"><?= DAV::xmlescape($group->prop_displayname()) ?></th>
                <td align="right">
                  <!--    View button (when not admin of the group) or manage button -->
                  <?php if ( $group->is_admin() ) : ?>
                  <a href="<?= $group->path ?>" class="btn btn-info">Manage</a>
                  <?php else : ?>
                  <a href="<?= $group->path ?>" class="btn">View</a>
                  <?php endif; ?>
                  <!--    Leave button -->
                  <button type="button" value="<?= $group->path ?>" class="btn btn-danger bh-gs-mygs-leave-button">Leave</button>
                </td>
              </tr></tbody></table>
            </div>
          </div>
          <div id="bh-groups-mygroups-<?= $i ?>" class="accordion-body collapse">

            <div class="accordion-inner">
              <?= DAV::xmlescape($group->prop(BeeHub::PROP_DESCRIPTION)) ?>
            </div>
          </div>
        </div>
     <?php
      $i = $i + 1;
         endif;
        endforeach;
     ?>
    </div>
  </div>
	<!--   End my groups tab -->

	<!-- Join tab -->
  <div id="bh-groups-panel-join" class="tab-pane fade">
<!-- 	  <br> -->
	  <div class="control-group">
<!-- 	    <label class="control-label" for="inputIcon">Filter by name:</label> -->
		    <div class="controls">
			    <div class="input-prepend">
 				    <span class="add-on" id="bh-groups-icon-filter"><i class="icon-filter"></i></span>
				    <input class="span3" id="bh-groups-filter-by-name" type="text" placeholder="Filter by name..." autocomplete="off" />
			    </div>
	    </div>
    </div>
  	<br>

		<!--    List with all groups -->
    <div class="accordion" id="bh-groups-join-groups">
        <?php
      $i = 1;
      foreach ($groups as $group) :
        if ( $group->is_member() || $group->is_invited() )
          continue;
        ?> 
        <div class="accordion-group">
          <div class="accordion-heading">
            <div class="accordion-toggle" data-toggle="collapse" data-parent="#bh-groups-join-groups" href="#bh-groups-join-groups-<?= $i ?>">
              <table width="100%"><tbody><tr>
                <th align="left"><?= DAV::xmlescape($group->prop_displayname()) ?></th>
                <td align="right">
                  <!--    Leave, Cancel request or Join button -->
                  <?php if ($group->is_requested()) : ?>
                    <a><button type="button" value="<?= $group->path ?>" class="btn btn-danger bh-gs-join-leave-button">Cancel request</button></a>
                  <?php else : ?>
                    <a><button type="button" value="<?= $group->path ?>" class="btn btn-success bh-gs-join-button">Join</button></a>
                  <?php endif; ?>
                </td>
              </tr></tbody></table>
            </div>
          </div>
          <div id="bh-groups-join-groups-<?= $i ?>" class="accordion-body collapse">
            <div class="accordion-inner">
              <?= DAV::xmlescape($group->prop(BeeHub::PROP_DESCRIPTION)) ?>
            </div>
          </div>
        </div>
     <?php
      $i = $i + 1;
        endforeach;
     ?>
    </div>
  </div>
	<!--   End join tab


	<!-- Create tab -->
	<br/>
	<div id="bh-groups-panel-create" class="tab-pane fade">
    <form id="bh-groups-create-group-form" class="form-horizontal" action="<?= BeeHub::$CONFIG['namespace']['groups_path'] ?>" method="post">
	    <div class="control-group">
		    <label class="control-label" for="bh-groups-group-name">Group name</label>
		    <div class="controls">
		    	<input type="text" id="bh-groups-group-name" name="group_name" required>
		    </div>
	    </div>
	    <div class="control-group">
		    <label class="control-label" for="bh-groups-group-display-name">Display name</label>
		    <div class="controls">
		    	<input type="text" id="bh-groups-group-display-name" name="displayname" required>
		    </div>
	    </div>
	      <div class="control-group">
		    <label class="control-label" for="bh-groups-group-description">Group description</label>
		    <div class="controls">
		    	<textarea class="input-xlarge" id="bh-groups-group-description" rows="5" name="description"></textarea>
		    </div>
	    </div>
	    <div class="control-group">
		    <div class="controls">
		    	<button  type="submit" class="btn">Create group</button>
		    </div>
	    </div>
    </form>
  </div>
	<!-- 	End create tab -->

</div>
<!-- End tab contents -->

<?php
  $footer='<script type="text/javascript" src="/system/js/groups-sponsors.js"></script>';
  require 'views/footer.php';
