<?php
  /*
   * Available variables:
   * $sponsors     All members of this directory
   */
  require 'views/header.php';
?>

<!-- Tabs-->
<ul id="beehub-top-tabs" class="nav nav-tabs">
  <li class="active"><a href="#bh-gs-panel-mygs" data-toggle="tab">My sponsors</a></li>
  <li><a href="#bh-gs-panel-join" data-toggle="tab">Join</a></li>
</ul>

<!-- Tab contents -->
<div class="tab-content">

	<!-- My Sponsors tab -->
	<br/>
  <div id="bh-gs-panel-mygs" class="tab-pane fade in active">
  	<!--    List with my sponsors -->
    <div class="accordion" id="bh-gs-mygs">
        <?php
      $i = 1;
      foreach ($sponsors as $sponsor) :
           if ( $sponsor->is_member() ) :
      ?>
        <div class="accordion-group">
          <div class="accordion-heading">
            <div class="accordion-toggle" data-toggle="collapse" data-parent="#bh-gs-mygs" href="#bh-gs-mygs-<?= $i ?>">
              <table width="100%"><tbody><tr>
                <th align="left"><?= DAV::xmlescape($sponsor->prop_displayname()) ?></th>
                <td align="right">
                  <!--    View button (when not admin of the sponsor) or manage button -->
                  <?php if ( $sponsor->is_admin() ) : ?>
                  <a href="<?= $sponsor->path ?>" class="btn btn-info">Manage</a>
                  <?php else : ?>
                  <a href="<?= $sponsor->path ?>" class="btn">View</a>
                  <?php endif; ?>
                  <!--    Leave button -->
                  <button type="button" value="<?= $sponsor->path ?>" class="btn btn-danger bh-gs-mygs-leave-button">Leave</button>
                </td>
              </tr></tbody></table>
            </div>
          </div>
          <div id="bh-gs-mygs-<?= $i ?>" class="accordion-body collapse">

            <div class="accordion-inner">
              <?= DAV::xmlescape($sponsor->prop(BeeHub::PROP_DESCRIPTION)) ?>
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

		<!--    List with all sponsors -->
    <div class="accordion" id="bh-gs-join-gs">
        <?php
      $i = 1;
      foreach ($sponsors as $sponsor) :
        if ( $sponsor->is_member())      		 
          continue;
        ?> 
        <div class="accordion-group">
          <div class="accordion-heading">
            <div class="accordion-toggle" data-toggle="collapse" data-parent="#bh-gs-join-gs" href="#bh-gs-join-gs-<?= $i ?>">
              <table width="100%"><tbody><tr>
                <th align="left"><?= DAV::xmlescape($sponsor->prop_displayname()) ?></th>
                <td align="right">
                  <!--    Leave, Cancel request or Join button -->
                  <?php if ($sponsor->is_requested()) : ?>
                    <a><button type="button" value="<?= $sponsor->path ?>" class="btn btn-danger bh-gs-join-leave-button">Cancel request</button></a>
                  <?php else : ?>
                    <a><button type="button" value="<?= $sponsor->path ?>" class="btn btn-success bh-gs-join-button">Join</button></a>
                  <?php endif; ?>
                </td>
              </tr></tbody></table>
            </div>
          </div>
          <div id="bh-gs-join-gs-<?= $i ?>" class="accordion-body collapse">
            <div class="accordion-inner">
              <?= DAV::xmlescape($sponsor->prop(BeeHub::PROP_DESCRIPTION)) ?>
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

</div>
<!-- End tab contents -->

<?php
  $footer='<script type="text/javascript" src="/system/js/groups-sponsors.js"></script>';
  require 'views/footer.php';

