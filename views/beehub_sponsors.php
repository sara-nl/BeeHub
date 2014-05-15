<?php
  /*
   * Available variables:
   * $sponsors     All members of this directory
   */
  require 'views/header.php';
?>

<!-- Tabs-->
<ul id="beehub-top-tabs" class="nav nav-tabs">
  <li class="active"><a href="#bh-gss-panel-mygss" data-toggle="tab">My sponsors</a></li>
  <li><a href="#bh-gss-panel-join" data-toggle="tab">Join</a></li>
  <?php if ( DAV::$ACLPROVIDER->wheel() ): ?>
    <li><a href="#bh-gss-panel-create" data-toggle="tab">Create</a></li>
  <?php endif; ?>
</ul>

<!-- Tab contents -->
<div class="tab-content">

	<!-- My Sponsors tab -->
	<br/>
  <div id="bh-gss-panel-mygss" class="tab-pane fade in active">
  	<!--    List with my sponsors -->
    <div class="accordion" id="bh-gss-mygss">
        <?php
      $i = 1;
      foreach ($sponsors as $sponsor) :
           if ( $sponsor->is_member() ) :
      ?>
        <div class="accordion-group">
          <div class="accordion-heading">
            <div class="accordion-toggle" data-toggle="collapse" data-parent="#bh-gss-mygss" href="#bh-gss-mygss-<?= $i ?>">
              <table width="100%"><tbody><tr>
                <th align="left"><?= DAV::xmlescape($sponsor->user_prop_displayname()) ?></th>
                <td align="right">
                  <!--    View button (when not admin of the sponsor) or manage button -->
                  <?php if ( $sponsor->is_admin() ) : ?>
                  <a href="<?= $sponsor->path ?>" class="btn btn-info">Manage</a>
                  <?php else : ?>
                  <a href="<?= $sponsor->path ?>" class="btn">View</a>
                  <?php endif; ?>
                  <!--    Leave button -->
                  <button type="button" value="<?= $sponsor->path ?>" class="btn btn-danger bh-gss-mygss-leave-button">Leave</button>
                </td>
              </tr></tbody></table>
            </div>
          </div>
          <div id="bh-gss-mygss-<?= $i ?>" class="accordion-body collapse">

            <div class="accordion-inner">
              <?= DAV::xmlescape($sponsor->user_prop(BeeHub::PROP_DESCRIPTION)) ?>
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
	<!--   End my sponsors tab -->

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

		<!--    List with all sponsors -->
    <div class="accordion" id="bh-gss-join-gss">
        <?php
      $i = 1;
      foreach ($sponsors as $sponsor) :
        if ( $sponsor->is_member())      		 
          continue;
        ?> 
        <div class="accordion-group">
          <div class="accordion-heading">
            <div class="accordion-toggle" data-toggle="collapse" data-parent="#bh-gss-join-gss" href="#bh-gss-join-gss-<?= $i ?>">
              <table width="100%"><tbody><tr>
                <th align="left"><?= DAV::xmlescape($sponsor->user_prop_displayname()) ?></th>
                <td align="right">
                  <!--    Leave, Cancel request or Join button -->
                  <?php if ($sponsor->is_requested()) : ?>
                    <a><button type="button" value="<?= $sponsor->path ?>" class="btn btn-danger bh-gss-join-leave-button">Cancel request</button></a>
                  <?php else : ?>
                    <a><button type="button" value="<?= $sponsor->path ?>" class="btn btn-success bh-gss-join-button">Join</button></a>
                  <?php endif; ?>
                </td>
              </tr></tbody></table>
            </div>
          </div>
          <div id="bh-gss-join-gss-<?= $i ?>" class="accordion-body collapse">
            <div class="accordion-inner">
              <?= DAV::xmlescape($sponsor->user_prop(BeeHub::PROP_DESCRIPTION)) ?>
            </div>
          </div>
        </div>
     <?php
      $i = $i + 1;
        endforeach;
     ?>
    </div>
  </div>
	<!--   End join tab -->
	
 <!-- Create tab -->
 <br/>
 <div id="bh-gss-panel-create" class="tab-pane fade">
    <form id="bh-gss-create-form" class="form-horizontal" action="<?= BeeHub::SPONSORS_PATH ?>" method="post">
     <div class="control-group">
      <label class="control-label" for="bh-gss-name">Sponsor name</label>
      <div class="controls">
       <input type="text" id="bh-gss-name" name="sponsor_name" required>
      </div>
     </div>
     <div class="control-group">
      <label class="control-label" for="bh-gss-display-name">Display name</label>
      <div class="controls">
       <input type="text" id="bh-gss-display-name" name="displayname" required>
      </div>
     </div>
       <div class="control-group">
      <label class="control-label" for="bh-gss-description">Sponsor description</label>
      <div class="controls">
       <textarea class="input-xlarge" id="bh-gss-description" rows="5" name="description"></textarea>
      </div>
     </div>
     <div class="control-group">
      <div class="controls">
       <button  type="submit" class="btn">Create sponsor</button>
      </div>
     </div>
    </form>
  </div>
 <!--  End create tab -->
 
</div>
<!-- End tab contents -->

<?php
  $footer .='
           <script type="text/javascript" src="/system/js/gss.js"></script>
           <script type="text/javascript" src="/system/js/gss-controller.js"></script>
           <script type="text/javascript" src="/system/js/gss-view.js"></script>
  ';
  require 'views/footer.php';

