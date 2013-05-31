<?php
/*
 Available variables:

$this       The beehub_directory object representing the current directory
$members    All members of this directory
*/

// used for acl
$aclAllowed = $this->property_priv_read(array(DAV::PROP_ACL));
$aclAllowed = $aclAllowed[DAV::PROP_ACL];
$header = '<style type="text/css">
// Background color path
.breadcrumb {
  background-color: white !important;
}	

// Do not show close button on dialog
.no-close .ui-dialog-titlebar-close {
	display: none;
}
}
</style>';
require 'views/header.php';
?>
<!-- Tabs-->
<ul id="beehub-directory-tabs" class="nav nav-tabs">
  <li class="active"><a href="#beehub-directory-panel-contents" data-toggle="tab">Contents</a></li>
  <li><a href="#beehub-directory-panel-acl" data-toggle="tab">ACL</a></li>
</ul>

<!-- Tab contents -->
<div class="tab-content">

	<!-- Contents tab -->
<div id="beehub-directory-panel-contents" class="tab-pane fade in active"> <h4>
		<?php 
			$crumb = explode("/", $this->path);
			print "<ul class='breadcrumb'>";
			print "<li><a href='/'>BeeHub root</a><span class='divider'>&raquo;</span></li>";
			$last = count($crumb)-2;
			$i=0;
			$newpath='';
			foreach($crumb as $value) {
				// first and last value are empty
				if ($value!=='') {
						$newpath .= '/'.$value;
					if ($i===$last) {
						print "<li class='active'>$value</li>";
					} else {
						print "<li><a href='". $newpath ."'>$value</a><span class='divider'>&raquo;</span></li>";
					}
				}
				$i++;
			}
			print "</ul>";
		?></h4>
		<?php if (DAV::unslashify($this->collection()->path) != "") :?>
		  <button id="<?= DAV::unslashify($this->collection()->path) ?>" class="btn btn-small beehub-directory-goup"><i class="icon-chevron-up" ></i>  Up</button>
		<?php else:?>
			<button id="<?= DAV::unslashify($this->collection()->path) ?>" class="btn btn-small beehub-directory-goup" disabled="disabled"><i class="icon-chevron-up" ></i>  Up</button>
		<?php endif;?>
		<button id="<?= preg_replace( '@^/system/users/(.*)@', '/home/\1/', BeeHub_Auth::inst()->current_user()->path)?>" class="btn btn-small beehub-directory-gohome" data-toggle="tooltip" title="Go to home folder"><i class="icon-home"></i> Home</button>
		<input id="beehub-directory-upload-hidden" type="file" name="files[]" hidden='true' multiple>
		<button id="beehub-directory-upload" data-toggle="tooltip" title="Upload to current folder" class="btn btn-small" ><i class="icon-upload" ></i> Upload</button>
		<button id="beehub-directory-newfolder" data-toggle="tooltip" title="Create new folder in current folder" class="btn btn-small"><i class="icon-folder-close" ></i> New</button>
		<button id="beehub-directory-copy" data-toggle="tooltip" title="Copy selected to other folder" class="btn btn-small" disabled="disabled"><i class="icon-hand-right" ></i> Copy</button>
		<button id="beehub-directory-move" data-toggle="tooltip" title="Move selected to other folder" class="btn btn-small" disabled="disabled"><i class="icon-move" ></i> Move</button>
		<button id="beehub-directory-delete" data-toggle="tooltip" title="Delete selected" class="btn btn-small" disabled="disabled"><i class="icon-remove" ></i> Delete</button>
		<br/><br/>
		<table id="beehub-directory-content-table" class="table table-striped">
			<thead>
				<tr>
					<th></th>
					<th><input type="checkbox"></th>
					<th>Name</th>
					<th>Size</th>
					<th>Type</th>
					<th>Modified</th>
					<th>Owner</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			  <?php
			      foreach ($members as $member) :
			      if (DAV::unslashify($member->path) === '/system') {
			      	continue;
			      }
			      $owner = BeeHub_Registry::inst()->resource(
			      		#$member->prop('DAV: owner')->URIs[0]
			      		$member->user_prop_owner()
			      );
			   ?>
	
				<tr>
					<td width="10px" data-toggle="tooltip" title="Rename file"><i class="icon-edit beehub-directory-edit" ></i></td>
					<td width="10px"><input type="checkbox"></td>
					<?php if (substr($member->path,-1) === '/'):?>
							<td class="beehub-directory-name"><a href=<?= DAV::unslashify($member->path)?>><b><?= $member->user_prop_displayname() ?>/</b></a></td>
					<?php else :?>
							<td class="beehub-directory-name"><a href=<?= DAV::unslashify($member->path) ?>><?= $member->user_prop_displayname() ?></a></td>
					<?php endif;?>
					<td class="beehub-directory-rename-td"  hidden='true'><input class="beehub-directory-rename-form" name=<?= $member->user_prop_displayname()  ?> value=<?= $member->user_prop_displayname() ?>></input></td>
					<?php 
// 					  Calculate size
						$size = $member->user_prop_getcontentlength();
						if ($size !== '' && $size != 0) {
							$unit = null;
							$units = array('B', 'KB', 'MB', 'GB', 'TB');
							for($i = 0, $c = count($units); $i < $c; $i++) {
								if ($size > 1024) {
									$size = $size / 1024;
								} else {
									$unit = $units[$i];
									break;
								}
							}
							$showsize = round($size, 0).' '.$unit;
						} else {
							$showsize='';
						}
					?>
					<td><?= $showsize?></td>
					<?php if (substr($member->path,-1) === '/'):?>
							<td><i name=<?=DAV::unslashify($member->path)?> class="icon-folder-close beehub-directory-openselected" style="cursor: pointer">></i></td>
					<?php else :?>
							<td><?= $member->user_prop_getcontenttype()?></td>
					<?php endif;?>
					<td><?= date('Y-m-d H:i:s',$member->user_prop_getlastmodified())?></td>
					<td><?= $owner->user_prop_displayname()?></td>	
					<?php if (substr($member->path,-1) !== '/'):?>
						<td width="10px" data-toggle="tooltip" title="Email read-only share link"><i class="icon-share" ></i></td>
					<?php else :?>
							<td></td>
					<?php endif;?>
				</tr>
				<?php
		        endforeach;
		     ?>
			</tbody>
		</table>
	</div> <!-- End contents tab -->
	
		<!-- Acl tab -->
  <div id="beehub-directory-panel-acl" class="tab-pane fade">
    <h4>ACL <?= $this->path?></h4>
  	<table id="beehub-directory-acl-table" class="table table-striped">
			<thead>
				<tr>
					<th>Principal</th>
					<th>Privileges</th>
					<th>Access</th>
					<th>Inherited</th>
				</tr>
			</thead>
			<tbody id="beehub-directory-aclcontent">
			</tbody>
		</table>
  </div> <!-- End Acl tab -->
  
</div><!-- End tab div -->

<!-- Dialogs -->
<!-- Upload -->
<div id="beehub-directory-upload-dialog" title="Upload" hidden='true'></div>
<?php

$footer= '
	<script type="text/javascript">
 		//   create acl variable
    var aclxml = ' .
    json_encode(
    		DAV::xml_header() .
    		'<D:acl xmlns:D="DAV:">' .
    		($aclAllowed ? $this->prop( DAV::PROP_ACL ) : '') .
    		'</D:acl>'
    )
    . ';
    if (window.DOMParser) {
      parser = new DOMParser();
      var aclxmldocument = parser.parseFromString(aclxml,"text/xml");
    }else{ // IE
      var aclxmldocument = new ActiveXObject("Microsoft.XMLDOM");
      aclxmldocument.async = false;
      aclxmldocument.loadXML(aclxml);
    }
  </script>
  <script type="text/javascript" src="/system/js/directory.js"></script>
';
require 'views/footer.php';
