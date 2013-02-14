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
		<!--   	List with my groups -->
		<div class="accordion" id="membershipgroups">
  	<?php
    	$i = 1;
     	foreach ($groups as $group) :
//           if ( $group->is_member() ) :
    	?>
      	<div class="accordion-group">
        	<div class="accordion-heading">
          	<div class="accordion-toggle" data-toggle="collapse" data-parent="#membershipgroups" href="#membershipgroups-<?= $i ?>">
            	<table width="100%"><tbody><tr>
              	<th align="left"><?= $group->prop_displayname() ?></th>
              	<td align="right">
              		<!--   	View button (when not admin of the group) or manage button -->
                	<?php if ( $group->is_admin() ) : ?>
	                <a href="<?= $group->path ?>" class="btn btn-info">Manage</a>
	                <?php else : ?>
	                <a href="<?= $group->path ?>" class="btn">View</a>
	                <?php endif; ?>
	                <!--   	Leave button -->
                	<button type="button" name="leave" value="<?= $this->user_prop_current_user_principal() ?>" class="btn btn-danger">Leave</button>
	              </td>
	            </tr></tbody></table>
	          </div>
	        </div>
	        <div id="membershipgroups-<?= $i ?>" class="accordion-body collapse">

        		<div class="accordion-inner">
          		<?= $group->prop(BeeHub::PROP_DESCRIPTION) ?>
	      		</div>
	      	</div>
	      </div>
     <?php
     	$i = $i + 1;
//         endif;
      	endforeach;
     ?>        
  </div>
	<!--   En my groups tab -->

	<!-- Join tab -->
  <div id="panel-join" class="tab-pane fade">
		Join
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


