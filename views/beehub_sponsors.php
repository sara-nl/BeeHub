<?php
  /*
   * Available variables:
   * $sponsors     All members of this directory
   */
  require 'views/header.php';
?>

<!-- Tabs-->
<ul id="beehub-top-tabs" class="nav nav-tabs">
  <li class="active"><a href="#bh-sponsors-panel-mysponsors" data-toggle="tab">My sponsors</a></li>
  <li><a href="#bh-sponsors-panel-join" data-toggle="tab">Join</a></li>
</ul>

<!-- Tab contents -->
<div class="tab-content">

	<!-- My Sponsors tab -->
	<br/>
  <div id="bh-sponsors-panel-mysponsors" class="tab-pane fade in active">
  	<!--    List with my sponsors -->
    <div class="accordion" id="bh-sponsors-mysponsors">
        <?php
      $i = 1;
      foreach ($sponsors as $sponsor) :
           if ( $sponsor) :
//            if ( $sponsor->is_member() ) :
      ?>
        <div class="accordion-group">
          <div class="accordion-heading">
            <div class="accordion-toggle" data-toggle="collapse" data-parent="#bh-sponsors-mysponsors" href="#bh-sponsors-mysponsors-<?= $i ?>">
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
          <div id="bh-sponsors-mysponsors-<?= $i ?>" class="accordion-body collapse">

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
  <div id="bh-sponsors-panel-join" class="tab-pane fade">
<!-- 	  <br> -->
	  <div class="control-group">
<!-- 	    <label class="control-label" for="inputIcon">Filter by name:</label> -->
		    <div class="controls">
			    <div class="input-prepend">
 				    <span class="add-on" id="bh-sponsors-icon-filter"><i class="icon-filter"></i></span>
				    <input class="span3" id="bh-sponsors-filter-by-name" type="text" placeholder="Filter by name..." autocomplete="off" />
			    </div>
	    </div>
    </div>
  	<br>

		<!--    List with all sponsors -->
    <div class="accordion" id="bh-sponsors-join-sponsors">
        <?php
      $i = 1;
      foreach ($sponsors as $sponsor) :
//         if ( $sponsor->is_member() || $sponsor->is_invited() )
      	if ( $sponsor || $sponsor->is_invited() )
      		 
          continue;
        ?> 
        <div class="accordion-group">
          <div class="accordion-heading">
            <div class="accordion-toggle" data-toggle="collapse" data-parent="#bh-sponsors-join-sponsors" href="#bh-sponsors-join-sponsors-<?= $i ?>">
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
          <div id="bh-sponsors-join-sponsors-<?= $i ?>" class="accordion-body collapse">
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

