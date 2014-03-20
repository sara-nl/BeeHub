<?php
  /*
   * Available variables:
   * $groups     All members of this directory
   */
  require 'views/header.php';
?>

<!-- Tabs-->
<ul id="beehub-top-tabs" class="nav nav-tabs">
  <li class="active"><a href="#bh-gs-panel-mygs" data-toggle="tab">My groups</a></li>
  <li><a href="#bh-gs-panel-join" data-toggle="tab">Join</a></li>
  <li><a href="#bh-gs-panel-create" data-toggle="tab">Create</a></li>
</ul>

<!-- Tab contents -->
<div class="tab-content">

	<!-- My Groups tab -->
	<br/>
  <div id="bh-gs-panel-mygs" class="tab-pane fade in active">
  	<!--    List with my groups -->
    <div class="accordion" id="bh-gs-mygs">
        <?php
      $i = 1;
      foreach ($groups as $group) :
           if ( $group->is_member() ) :
      ?>
        <div class="accordion-group">
          <div class="accordion-heading">
            <div class="accordion-toggle" data-toggle="collapse" data-parent="#bh-gs-mygs" href="#bh-gs-mygs-<?= $i ?>">
              <table width="100%"><tbody><tr>
                <th align="left"><?= DAV::xmlescape($group->user_prop_displayname()) ?></th>
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
          <div id="bh-gs-mygs-<?= $i ?>" class="accordion-body collapse">

            <div class="accordion-inner">
              <?= DAV::xmlescape($group->user_prop(BeeHub::PROP_DESCRIPTION)) ?>
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
  <div id="bh-gs-panel-join" class="tab-pane fade">
<!-- 	  <br> -->
	  <div class="control-group">
<!-- 	    <label class="control-label" for="inputIcon">Filter by name:</label> -->
		    <div class="controls">
			    <div class="input-prepend">
 				    <span class="add-on" id="bh-gs-icon-filter"><i class="icon-filter"></i></span>
				    <input class="span3" id="bh-gs-filter-by-name" type="text" placeholder="Filter by name..." autocomplete="off" />
			    </div>
	    </div>
    </div>
  	<br>

		<!--    List with all groups -->
    <div class="accordion" id="bh-gs-join-gs">
        <?php
      $i = 1;
      foreach ($groups as $group) :
        if ( $group->is_member() )
          continue;
        ?> 
        <div class="accordion-group">
          <div class="accordion-heading">
            <div class="accordion-toggle" data-toggle="collapse" data-parent="#bh-gs-join-gs" href="#bh-gs-join-gs-<?= $i ?>">
              <table width="100%"><tbody><tr>
                <th align="left"><?= DAV::xmlescape($group->user_prop_displayname()) ?></th>
                <td align="right">
                  <!--    Leave, Cancel request or Join button -->
                  <?php if ($group->is_requested()) : ?>
                    <a><button type="button" value="<?= $group->path ?>" class="btn btn-danger bh-gs-join-leave-button">Cancel request</button></a>
                  <?php elseif ( $group->is_invited() ) : ?>
                    <a><button type="button" value="<?= $group->path ?>" class="btn btn-success bh-gs-join-button">Accept invitation</button></a>
                  <?php else : ?>
                    <a><button type="button" value="<?= $group->path ?>" class="btn btn-success bh-gs-join-button">Join</button></a>
                  <?php endif; ?>
                </td>
              </tr></tbody></table>
            </div>
          </div>
          <div id="bh-gs-join-gs-<?= $i ?>" class="accordion-body collapse">
            <div class="accordion-inner">
              <?= DAV::xmlescape($group->user_prop(BeeHub::PROP_DESCRIPTION)) ?>
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
	<div id="bh-gs-panel-create" class="tab-pane fade">
    <form id="bh-gs-create-form" class="form-horizontal" action="<?= BeeHub::GROUPS_PATH ?>" method="post">
	    <div class="control-group">
		    <label class="control-label" for="bh-gs-name">Group name</label>
		    <div class="controls">
		    	<input type="text" id="bh-gs-name" name="group_name" required>
		    </div>
	    </div>
	    <div class="control-group">
		    <label class="control-label" for="bh-gs-display-name">Display name</label>
		    <div class="controls">
		    	<input type="text" id="bh-gs-display-name" name="displayname" required>
		    </div>
	    </div>
	      <div class="control-group">
		    <label class="control-label" for="bh-gs-description">Group description</label>
		    <div class="controls">
		    	<textarea class="input-xlarge" id="bh-gs-description" rows="5" name="description"></textarea>
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
