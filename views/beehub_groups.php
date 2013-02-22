<?php
  /*
   * Available variables:
   * $groups     All members of this directory
   */
  // TODO custom settings to beehub.css or bootstrap.css
  // TODO netter maken, dit zorgt ervoor dat het er uitziet zoals ik
  // wil maar dit kan waarschijnlijk netter/anders
  $header = '
<style type="text/css">
  .accordion-group {
//   		 background-color: #E6E7E8 !important;
  }
  		
  .accordion-group:hover {
    background-color: #D1E2D3 !important;
  }

  .accordion-group-active {
    background-color: #E8F1E9 !important;
  }

  .accordion-group-active:hover {
    background-color: #D1E2D3 !important;
  }

  .admin{
    font-size: 12px !important;
    text-align: right;
  }

  .rightbutton{
    padding: 12px !important;
    text-align: right;
  }

  .custom-btn-primary {
    width: 130px !important;
  }
  		
</style>
  ';
  require 'views/header.php';
?>

<!-- Tabs-->
<ul id="beehub-top-tabs" class="nav nav-tabs">
  <li class="active"><a href="#panel-mygroups" data-toggle="tab">My groups</a></li>
  <li><a href="#panel-join" data-toggle="tab">Join</a></li>
  <li><a href="#panel-create" data-toggle="tab">Create</a></li>
</ul>

<!-- Tab contents -->
<div class="tab-content">

	<!-- My Groups tab -->
  <div id="panel-mygroups" class="tab-pane fade in active">
  	<!--    List with my groups -->
    <div class="accordion" id="mygroups">
        <?php
      $i = 1;
      foreach ($groups as $group) :
           if ( $group->is_member() ) :
      ?>
        <div class="accordion-group">
          <div class="accordion-heading">
            <div class="accordion-toggle" data-toggle="collapse" data-parent="#mygroups" href="#mygroups-<?= $i ?>">
              <table width="100%"><tbody><tr>
                <th align="left"><?= $group->prop_displayname() ?></th>
                <td align="right">
                  <!--    View button (when not admin of the group) or manage button -->
                  <?php if ( $group->is_admin() ) : ?>
                  <a href="<?= $group->path ?>" class="btn btn-info">Manage</a>
                  <?php else : ?>
                  <a href="<?= $group->path ?>" class="btn">View</a>
                  <?php endif; ?>
                  <!--    Leave button -->
                  <button type="button" value="<?= $group->path ?>" class="btn btn-danger mygroupsleavebutton">Leave</button> 
                </td>
              </tr></tbody></table>
            </div>
          </div>
          <div id="mygroups-<?= $i ?>" class="accordion-body collapse">

            <div class="accordion-inner">
              <?= $group->prop(BeeHub::PROP_DESCRIPTION) ?>
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
  <div id="panel-join" class="tab-pane fade">
	  <br>
	  <div class="control-group">
<!-- 	    <label class="control-label" for="inputIcon">Filter by name:</label> -->
		    <div class="controls">
			    <div class="input-prepend">
 				    <span class="add-on"><i class="icon-filter"></i></span>
				    <input class="span3" id="filterbyname" type="text" placeholder="Filter by name..."/>
			    </div>
	    </div>
    </div>
  	<br>
  	
		<!--    List with all groups -->
<!-- 		<h3>Groups</h3> -->
    <div class="accordion" id="joingroups">
        <?php
      $i = 1;
      foreach ($groups as $group) :
      ?>
        <div class="accordion-group">
          <div class="accordion-heading">
            <div class="accordion-toggle" data-toggle="collapse" data-parent="#joingroups" href="#joingroups-<?= $i ?>">
              <table width="100%"><tbody><tr>
                <th align="left"><?= $group->prop_displayname() ?></th>
                <td align="right">
                  <!--    Leave, Cancel request or Join button -->
                  <?php if ( $group->is_member() ) : ?>
                  <a><button type="button" value="<?= $group->path ?>" class="btn btn-danger joinleavebutton">Leave</button></a>
                  <?php elseif ($group->is_invited()) : ?>
                  <a><button type="button" value="<?= $group->path ?>" class="btn btn-success joinbutton">Accept invitation</button></a>
                  <a><button type="button" value="<?= $group->path ?>" class="btn btn-danger joinleavebutton">Deny invitation</button></a>
                   <?php elseif ($group->is_requested()) : ?>
                  <a><button type="button" value="<?= $group->path ?>" class="btn btn-danger joinleavebutton">Cancel request</button></a>
                  <?php else : ?>
                  <a><button type="button" value="<?= $group->path ?>" class="btn btn-success joinbutton">Join</button></a>
                  <?php endif; ?>
                </td>
              </tr></tbody></table>
            </div>
          </div>
          <div id="joingroups-<?= $i ?>" class="accordion-body collapse">

            <div class="accordion-inner">
              <?= $group->prop(BeeHub::PROP_DESCRIPTION) ?>
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
	<div id="panel-create" class="tab-pane fade">
		Create
  </div>
	<!-- 	End create tab -->

</div>
<!-- End tab contents -->

<?php
  $footer='<script type="text/javascript" src="/system/js/groups.js"></script>';
  require 'views/footer.php';
?>


