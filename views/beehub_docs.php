<?php

$header = '<style type="text/css">
.fieldname {
  text-align: right;
}
.inviteMembers {
		margin-left: 20px !important;
}
.displayGroup {
		width: 110px !important;
}
</style>';
$footer= '<script type="text/javascript" src="/system/js/docs.js"></script>';

require 'views/header.php';
?>
<div class="row-fluid">
<div class="span12">

<div class="tab-content">
  <div class="tab-pane active" id="pane-share">
    <h3>Share your data</h3>    
    <p>There are two ways to share your data:</p>
    <ul>
		  <li>
		 		<b>Create a group</b>
		 		<ul>
		 			<li>A directory with the groupname will be created</li>
		 			<li>By default all group members have all permissions to the contents of this directory</li>
		 			<li>Create or control a group <a href="https://beehub.nl/system/groups">here</a></li>
		 		</ul>
		 	</li>
      <li>
		 		<b>Change an ACL</b>
		 		<ul>
		 			<li>This can be done with the <a href="/?nosystem">BeeHub Web Interface</a></li>
		 			<li>See below for more information about the BeeHub Web Interface</li>
		 		</ul>
		 	</li>
		</ul>
		<br/>
		<h4>BeeHub Web Interface</h4>
		<p>With the <a title="BeeHub Web Interface" href="/?nosystem" target="_blank">BeeHub Web Interface</a>  
		it is possible to share your BeeHub data with others. At this moment the best browsers to use are firefox and chrome.</p>
		
		<p>The user interface contains three panels:</p>
		<ul> 
			<li>Directory Panel</li>
			<li>Content Panel</li>
			<li>Access Control List (ACL) Panel</li> 
		</ul>
		
		<p><img class="" title="BeeHub_Panels" src="/system/img/docs/BeeHub_Panels3-300x233.jpg" alt="" width="300" height="233" />
		&nbsp;</p>
		
		<p>When an entry in the directory panel is selected the contents of that directory will be shown in the content panel.
		When an entry in the directory panel or the content panel is selected the ACL panel will be enabled and the ACL will 
		be shown (when available).</p>

		<h5>ACL Explained</h5>
		<p>An <strong>access control list</strong> (<strong>ACL</strong>) is a list of permissions attached to an object  
		(your files or directories).  An ACL specifies which users or groups are granted access to objects.</p>
		
		<p>When an ACL is attached to an object the contents of the ACL will be read from top to bottom. After the first 
		match (grant or deny) the items below this match will not be read anymore. When no match is found access is denied 
		to the object the ACL belongs to.</p>
		
		<p>The BeeHub ACL consists of:</p>
		<ul>
			<li>Protected entries</li>
			<li>Normal entries</li>
			<li>Inherited entries</li>
		</ul>
		
		<p>An ACL starts with the <strong>protected entries. </strong>These are default system entries and can not be changed. 
		In the ACL panel the row is gray. An ACL ends with the <strong>inherited entries</strong>. These entries are inherited 
		from a parent directory of the object.  In the ACL panel the row is gray and in the column inherited the object from 
		which the ACL entry is inherited is shown as a link.</p>
		
		<p>An ACL entry consists of:</p>
		<ul>
			<li>principal</li>
			<li>privileges</li>
			<li>access</li>
			<li>inherited</li>
		</ul>
		
		<p>A <strong>principal</strong> is a user, a group or all (the world). <strong>Privileges </strong>are the 
		permissions (read, write, read ACL and write ACL). <strong>Access</strong> is grant or deny and inherited is
		empty or a link to a parent directory from which the ACL entry is inherited from.</p>
		
		<h5>An Example: grant access for a group to a file</h5>
		<p>Select the directory in the <em>directory panel</em></p>
		
		<p><img title="BeeHub_Select_Directory" src="/system/img/docs/BeeHub_Select_Directory.jpg" alt="" width="103" height="93" /></p>
		
		<p>Select the file in the <em>content panel</em></p>
		
		<p><img title="BeeHub_Select_File" src="/system/img/docs/BeeHub_Select_File-300x62.jpg" alt="" width="300" height="62" /></p>
		<br/>
		<p>Click "Add" in the <em>ACL Panel</em></p>
		
		<p><img title="BeeHub_Add_ACL_Item" src="/system/img/docs/BeeHub_Add_ACL_Item.jpg" alt="" width="178" height="120" /></p>
		
		<p>Select group in the Add ACL Rule window</p>
		
		<p>Search a group Principal.</p>
		
		<p>Select Grant at the access radio button.</p>
		
		<p>Select Read and Write at the privileges checkboxes and click the Ok button</p>
		
		<p><img title="BeeHub_Search_Principal" src="/system/img/docs/BeeHub_Search_Principal1-300x207.jpg" alt="" width="300" height="207" /></p>
		<br/>
		<p>In the <em>ACL panel</em> the ACL item is added. Click save to make this change definitive.</p>
		
		<p><img title="BeeHub_ACL_Save" src="/system/img/docs/BeeHub_ACL_Save2-300x110.jpg" alt="" width="300" height="110" /></p>
		
		<p>All members of the group can now access the file.</p>
  </div>
</div>

</div></div><!-- cell, row, and outer container-fluid -->
<?php
require 'views/footer.php';
