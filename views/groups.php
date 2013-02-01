<?php
/*
Available variables:

$directory  The beehub_directory object representing the current directory
$groups     All members of this directory
*/
$this->setTemplateVar('active', "groups");
$this->setTemplateVar('header', '<style type="text/css">
.customaccordion {
	border: 0px solid #E5E5E5 !important;
	border-radius: 0px 0px 0px 0px !important;
	border-style:dotted !important;
	border-top-width:1px !important;
	margin-bottom: 0px !important;
	padding-top:5px;
	padding-bottom:5px;
}

#membership {
	padding-left:50px;
}
		
a:hover {
	text-decoration: none !important; 
}
		
.customaccordion:hover {
	background-color: #F3F3F4 !important;	
	border-style:dotted !important;
}
</style>');
?>

<div class="bootstrap">
  <h3>Group memberships</h3>
  <div class="accordion" id="membership">
  	<?php 
  		$i=1;
  		foreach ($groups as $group) : ?>
  		
	  		<div class="accordion-group customaccordion">
				<div class="accordion-heading">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="#membership" href=#accordion<?= $i ?>>
						<?= $group->prop(DAV::PROP_DISPLAYNAME) ?>
					</a>
				</div>
				<div id="accordion<?= $i ?>" class="accordion-body collapse">
				
				<div class="accordion-inner">

					<?= $group->prop(BeeHub::PROP_DESCRIPTION) ?>
					<form method="post">
				  
									<form id="membership_form" method="post">
				  <p>The following users requested for you to sponsor them:</p>
				  <table>
				    <thead>
				      <tr>
				        <th>user_name</th>
				        <th>Display name</th>
				        <th>Accept?</th>
				        <th>Delete?</th>
				      </tr>
				    </thead>
				    <tbody>
				                <tr class="member_row" id="/users/evert">
				            <td>evert</td>
				            <td>evert</td>
				            <td><a href="#" class="accept_link">Accept</a></td>
				            <td><a href="#" class="remove_link">Delete</a></td>
				          </tr>
				            </tbody>
				  </table>
				
				  <p>The following users are member:</p>
				  <table>
				    <thead>
				      <tr>
				        <th>user_name</th>
				        <th>Display name</th>
				        <th>Admin?</th>
				        <th>Delete?</th>
				      </tr>
				    </thead>
				    <tbody id="current_members">
				                <tr class="member_row" id="/users/laura">
				            <td>laura</td>
				            <td>laura</td>
				            <td>jep <a href="#" class="demote_link">demote</a></td>
				            <td><a href="#" class="remove_link">Delete</a></td>
				          </tr>
				                  <tr class="member_row" id="/users/niek">
				            <td>niek</td>
				            <td>Niek bosch</td>
				            <td>jep <a href="#" class="demote_link">demote</a></td>
				            <td><a href="#" class="remove_link">Delete</a></td>
				          </tr>
				                  <tr class="member_row" id="/users/pieterb">
				            <td>pieterb</td>
				            <td>Pipo</td>
				            <td>jep <a href="#" class="demote_link">demote</a></td>
				            <td><a href="#" class="remove_link">Delete</a></td>
				          </tr>
				            </tbody>
				  </table>
				</div>	
			</div>
		</div>
		<?php $i = $i+1;?>
 	<?php endforeach; ?>
  </div>
</div>
				<script>
   $(window).on('hide', function () {
		alert("ja");
	})
</script>
