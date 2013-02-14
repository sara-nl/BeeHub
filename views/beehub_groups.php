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
//   #joingroups, #membershiprequests {
//     padding-left:30px;
//   }

//   .custom-accordion {
//     border: 0px solid #E5E5E5 !important;
//     border-style:dotted !important;
//     border-top-width:1px !important;
//     margin-bottom: 0px !important;
//   }

//   .custom-accordion-other-background{
//     background-color: #E6E7E8 !important;
//   }

//   .custom-accordion:hover {
//     background-color: #D1E2D3 !important;
//     border-style:dotted !important;
//   }

//   .custom-accordion-inner {
//     border-top: 1px solid #E5E5E5 !important;
//   }

//   .custom-accordion-active {
//     background-color: #E8F1E9 !important;
//     border: 1px solid #B9D3BA !important;
//   }

//   .custom-accordion-active:hover {
//     background-color: #E8F1E9 !important;
//     border-style:solid !important;
//   }

//   .admin{
//     font-size: 12px !important;
//     text-align: right;
//   }

//   .rightbutton{
//     padding: 12px !important;
//     text-align: right;
//   }

//   .custom-btn-primary {
//     width: 130px !important;
//   }

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
      My groups
  </div>
	<!--   En my groups tab -->

	<!-- Join tab -->
  <div id="panel-join" class="tab-pane fade">
		Join
  </div>
	<!--   End join tab


	<!-- Create tab -->
	<div id="panel-join" class="tab-pane fade">
		Create
  </div>
	<!-- 	End create tab -->

</div>
<!-- End tab contents -->

<?php
  $footer='<script type="text/javascript" src="/system/js/groups.js"></script>';
  require 'views/footer.php';
?>


