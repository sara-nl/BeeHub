<?php
  /*
   * Available variables:
   * $groups     All members of this directory
   */
  require 'views/header.php';
?>

<div id="bs-gs-view">
 <!-- Tabs-->
 <ul id="beehub-top-tabs" class="nav nav-tabs">
   <li class="active"><a href="#bh-gss-panel-mygss" data-toggle="tab">My groups</a></li>
   <li><a href="#bh-gss-panel-join" data-toggle="tab">Join</a></li>
   <li><a href="#bh-gss-panel-create" data-toggle="tab">Create</a></li>
 </ul>
 
 <!-- Tab contents -->
 <div class="tab-content">
 
 	<!-- My Groups tab -->
 	<br/>
   <div id="bh-gss-panel-mygss" class="tab-pane fade in active">
   	<!--    List with my groups -->
     <div class="accordion" id="bh-gss-mygss">
         <?php
       $i = 1;
       foreach ($groups as $group) :
            if ( $group->is_member() ) :
       ?>
         <div class="accordion-group">
           <div class="accordion-heading">
             <div class="accordion-toggle" data-toggle="collapse" data-parent="#bh-gss-mygss" href="#bh-gss-mygss-<?= $i ?>">
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
                   <button type="button" value="<?= $group->path ?>" class="btn btn-danger bh-gss-mygss-leave-button">Leave</button>
                 </td>
               </tr></tbody></table>
             </div>
           </div>
           <div id="bh-gss-mygss-<?= $i ?>" class="accordion-body collapse">
 
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
   <div id="bh-gss-panel-join" class="tab-pane fade">
 <!-- 	  <br> -->
 	  <div class="control-group">
 <!-- 	    <label class="control-label" for="inputIcon">Filter by name:</label> -->
 		    <div class="controls">
 			    <div class="input-prepend">
  				    <span class="add-on" id="bh-gss-icon-filter"><i class="icon-filter"></i></span>
 				    <input class="span3" id="bh-gss-filter-by-name" type="text" placeholder="Filter by name..." autocomplete="off" />
 			    </div>
 	    </div>
     </div>
   	<br>
 
 		<!--    List with all groups -->
     <div class="accordion" id="bh-gss-join-gss">
         <?php
       $i = 1;
       foreach ($groups as $group) :
         if ( $group->is_member() )
           continue;
         ?> 
         <div class="accordion-group">
           <div class="accordion-heading">
             <div class="accordion-toggle" data-toggle="collapse" data-parent="#bh-gss-join-gss" href="#bh-gss-join-gss-<?= $i ?>">
               <table width="100%"><tbody><tr>
                 <th align="left"><?= DAV::xmlescape($group->user_prop_displayname()) ?></th>
                 <td align="right">
                   <!--    Leave, Cancel request or Join button -->
                   <?php if ($group->is_requested()) : ?>
                     <a><button type="button" value="<?= $group->path ?>" class="btn btn-danger bh-gss-join-leave-button">Cancel request</button></a>
                   <?php elseif ( $group->is_invited() ) : ?>
                     <a><button type="button" value="<?= $group->path ?>" class="btn btn-success bh-gss-join-button">Accept invitation</button></a>
                   <?php else : ?>
                     <a><button type="button" value="<?= $group->path ?>" class="btn btn-success bh-gss-join-button">Join</button></a>
                   <?php endif; ?>
                 </td>
               </tr></tbody></table>
             </div>
           </div>
           <div id="bh-gss-join-gss-<?= $i ?>" class="accordion-body collapse">
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
 	<div id="bh-gss-panel-create" class="tab-pane fade">
     <form id="bh-gss-create-form" class="form-horizontal" action="<?= BeeHub::GROUPS_PATH ?>" method="post">
      <input type="hidden" name="POST_auth_code" value="<?= DAV::xmlescape( BeeHub::getAuth()->getPostAuthCode() ) ?>" />
 	    <div class="control-group">
 		    <label class="control-label" for="bh-gss-name">Group name</label>
 		    <div class="controls">
 		    	<input type="text" id="bh-gss-name" name="group_name" required>
 		    </div>
 	    </div>
 	    <div class="control-group">
 		    <label class="control-label" for="bh-gss-display-name">Display name</label>
 		    <div class="controls">
 		    	<input type="text" id="bh-gss-display-name" name="displayname" required>
 		    </div>
 	    </div>
 	      <div class="control-group">
 		    <label class="control-label" for="bh-gss-description">Group description</label>
 		    <div class="controls">
 		    	<textarea class="input-xlarge" id="bh-gss-description" rows="5" name="description"></textarea>
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
</div>

<?php
  $footer = '
    <script type="text/javascript" src="/system/js/groupssponsors.js"></script>
    <script type="text/javascript" src="/system/js/gs-controller.js"></script>
    <script type="text/javascript" src="/system/js/gs-utils.js"></script>
    <script type="text/javascript" src="/system/js/groupssponsors-view.js"></script>
  ';
  require 'views/footer.php';
