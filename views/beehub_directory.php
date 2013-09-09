<?php
/*
  Available variables:

  $this       The beehub_directory object representing the current directory
  $members    All members of this directory
 */

// used for acl
$aclAllowed = $this->property_priv_read(array(DAV::PROP_ACL));
$aclAllowed = $aclAllowed[DAV::PROP_ACL];
require 'views/header.php';

/**
 * Create tree for tree view (dynatree plugin)
 * 
 * @param   string  $path        A slashified path to a directory
 * @param   string  $oldpath     The path to the collection containing $oldmembers (is one of the children of the current path)
 * @param   string  $oldmembers  The members of the subcollection which is currently selected
 * @return  array                An array with the following keys: title, id, expand, isFolder, children, isLazy. These are used as parameters for dynatree (see dynatree documentation for more information).
 */
function createTree($path, $oldpath = null, $oldmembers = null) {
  $dir = BeeHub_Registry::inst()->resource($path);
  
  $members = array();
  foreach ($dir as $member) { 	
   if ('/' !== substr($member, -1) ||
            ( '/' === $path && $member === 'system/' ))
      continue;
    $tmp = array(
        'title' => rawurldecode(DAV::unslashify($member)),
        'id' => $path . $member,
        'expand' => ( $oldpath === $path . $member ? true : false ),
    		'isFolder' => true
    );
        if ( $tmp['expand'] === true ) {
          $tmp['children'] = $oldmembers;
        } else {
          $tmp['isLazy'] = true;
        };
    $members[] = $tmp;
  }
  if ('/' === $path)
    return $members;
  return createTree(
          DAV::slashify(dirname($path)), $path, $members
  );
}
$tree = createTree(DAV::slashify(dirname($this->path)));
?>

<!-- Bread crumb -->
<div class="bh-dir-fixed-path">
  <h4>
    <?php
    // first and last of $crumb are empty
    $crumb = explode("/", $this->path);
    print "<ul class=\"breadcrumb bh-dir-breadcrumb \">";
    // Root
    print "<li><a href=\"/\">BeeHub root</a><span class=\"divider\">&raquo;</span></li>";
    $count = count($crumb);
    $start = 1;
    //Show maximal two directories
    if ($count > 4) {
			print "<li><span class=\"divider\">.. /</span></li>";
			$start = $count - 3;
		};
    $last = $count - 2;
    $newpath = '';
    for ($x=1; $x<=$count-2; $x++) {
      $value = urldecode($crumb[$x]);
      $newpath .= '/' . $value; // We extend the path for each intermediate directory, but...
      // ...show only the two last directories
      if ($x >= $start) {
				// Last directory is current directory, no link
	      if ($x === $last) {
	        print "<li class=\"active\">" . DAV::xmlescape( $value ) . "</li>";
	      } else {
	        print "<li><a href=\"" . DAV::xmlescape( $newpath ) . "\">" . DAV::xmlescape( $value ) . "</a><span class=\"divider\">/</span></li>";
	      }
	    }
    }
    print "</ul>";
    ?>
  </h4>
</div>
<!-- End div class fixed path -->

<!-- Tabs - Content and ACL tab -->
<div class="bh-dir-fixed-tabs">
  <ul id="bh-dir-tabs" class="nav nav-tabs">
    <li class="active"><a href="#bh-dir-panel-contents" data-toggle="tab">Contents</a>
    </li>
    <li><a href="#bh-dir-panel-acl" data-toggle="tab">ACL</a></li>
  </ul>
</div>
<!-- End class fixed tabs -->

<!-- Fixed buttons at the top -->
<div class="bh-dir-fixed-buttons">
	<!--  Up button -->
	<!-- 	Up -->
  <?php if (DAV::unslashify($this->collection()->path) != "") : ?>
    <button id="<?= DAV::xmlescape( DAV::unslashify($this->collection()->path) ) ?>"
            class="btn btn-small bh-dir-up">
      <i class="icon-chevron-up"></i> Up
    </button>
	<!--   No up possible -->
  <?php else: ?>
    <button id="<?= DAV::xmlescape( DAV::unslashify($this->collection()->path) ) ?>"
            class="btn btn-small bh-dir-up" disabled="disabled">
      <i class="icon-chevron-up"></i> Up
    </button>
  <?php endif; ?>
  
	<!--   Home -->
  <button
    id="<?= DAV::xmlescape( preg_replace('@^' . BeeHub::USERS_PATH . '(.*)@', '/home/\1/', BeeHub_Auth::inst()->current_user()->path) ) ?>"
    class="btn btn-small bh-dir-gohome" data-toggle="tooltip"
    title="Go to home folder">
    <i class="icon-home"></i> Home
  </button>
  
	<!--   Upload -->
  <input id="bh-dir-upload-hidden" type="file" name="files[]"
         hidden="true" multiple>
	<!--   Hidden upload field, this is needed to show the upload button -->
  <button id="bh-dir-upload" data-toggle="tooltip"
          title="Upload to current folder" class="btn btn-small">
    <i class="icon-upload"></i> Upload
  </button>
  
	<!--   New folder -->
  <button id="bh-dir-newfolder" data-toggle="tooltip"
          title="Create new folder in current folder" class="btn btn-small">
    <i class="icon-folder-close"></i> New
  </button>
  
	<!--   Copy -->
  <button id="bh-dir-copy" data-toggle="tooltip"
          title="Copy selected to other folder" class="btn btn-small"
          disabled="disabled">
    <i class="icon-hand-right"></i> Copy
  </button>
  
	<!--   Move -->
  <button id="bh-dir-move" data-toggle="tooltip"
          title="Move selected to other folder" class="btn btn-small"
          disabled="disabled">
    <i class="icon-move"></i> Move
  </button>
  
	<!--   Delete -->
  <button id="bh-dir-delete" data-toggle="tooltip"
          title="Delete selected" class="btn btn-small" disabled="disabled">
    <i class="icon-remove"></i> Delete
  </button>
</div>
<!-- End fixed buttons -->

<!-- Dialog, for dialog view -->
<div id="bh-dir-dialog" hidden="true"></div>

<!-- Tree slide out, dynatree - tree view -->
<div id="bh-dir-tree" class="bh-dir-tree-slide">
  <ul>
    <?php
    // Fill the tree nodes
    $registry = BeeHub_Registry::inst();
    foreach ($this as $inode) :
      $member = $registry->resource($this->path . $inode);
      if (DAV::unslashify($member->path) === '/system') {
        continue;
      }
      if (substr($member->path, -1) === '/'):
        ?>
        <li id="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>" class="folder"><?= DAV::xmlescape( $member->user_prop_displayname() ) ?>
          <ul>
            <li></li>
          </ul> <?php
        endif;
        $registry->forget($this->path . $inode);
      endforeach;
      ?>
  </ul>
</div>
<!-- End tree slide out -->

<!-- Tree header -->
<div id="bh-dir-tree-header">
	<table>
		<tr>
			<td id="bh-dir-tree-cancel" hidden=true><i class="icon-remove" style="cursor: pointer"></i></td>
			<td class="bh-dir-tree-header" hidden=true>Browse</td>
		</tr>
	</table>
</div>

<!-- Arrow to show the tree -->
<a class="bh-dir-tree-slide-trigger" href="#"><i class="icon-chevron-left"></i> </a>

<!-- Tab contents -->
<div
  class="tab-content">
  <!-- Fixed divs don't use space -->
  <div class="bh-dir-allocate-space"></div>
  <!-- Contents tab -->
  <div id="bh-dir-panel-contents" class="tab-pane fade in active">
    <table id="bh-dir-content-table" class="table table-striped">
      <thead class="bh-dir-table-header">
        <tr>
<!--         	Checkbox header -->
          <th width="10px"><input type="checkbox" class="bh-dir-checkboxgroup"></th>
<!--           Rename icon header -->
          <th width="10px"></th>
<!--           Name header -->
          <th>Name</th>
<!-- 					Hidden rename column -->
          <th hidden="true"></th>
<!--           Size header -->
          <th>Size</th>
<!--           Type header -->
          <th>Type</th>
<!--           Modified header -->
          <th>Modified</th>
<!--           Owner header -->
          <th>Owner</th>
          <!-- share file, not yet implemented -->
          <!--  <th width="10px"></th> -->
        </tr>
      </thead>
      <tbody>
        <?php
        // For all resources, fill table
        foreach ($this as $inode) :
          $member = $registry->resource($this->path . $inode);
          if (DAV::unslashify($member->path) === '/system') {
            continue;
          }
          $owner = BeeHub_Registry::inst()->resource(
                  #$member->prop('DAV: owner')->URIs[0]
                  $member->user_prop_owner()
          );
          ?>
          <tr id="<?= DAV::xmlescape( DAV::unslashify($member->path) ) ?>">
<!--             Select checkbox -->
<td width="10px"><input type="checkbox" class="bh-dir-checkbox" name="<?= DAV::xmlescape( DAV::unslashify( $member->path ) ) ?>"
                                    value=""<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>"></td>
<!--             Rename icon -->
            <td width="10px" data-toggle="tooltip" title="Rename"><i
                class="icon-edit bh-dir-edit" style="cursor: pointer"></i></td>
<!--                 Name -->
<!--                 Directory -->
            <?php if (substr($member->path, -1) === '/'): ?>
              <td class="bh-dir-name displayname" name="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>"><a
                  href="<?= DAV::xmlescape( DAV::unslashify( $member->path ) ) ?>"><b><?= DAV::xmlescape( $member->user_prop_displayname() ) ?>/</b>
                </a></td>
<!--                 File -->
            <?php else : ?>
              <td class="bh-dir-name displayname" name="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>"><a
                  href="<?= DAV::xmlescape( DAV::unslashify( $member->path ) ) ?>"><?= DAV::xmlescape( $member->user_prop_displayname() ) ?>
                </a></td>
            <?php endif; ?>
<!--             Hidden rename -->
            <td class="bh-dir-rename-td" hidden="true"><input
                class="bh-dir-rename-form"
                name="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>"
                value="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>"></td>
              <?php
              // 					  Calculate size
              $size = $member->user_prop_getcontentlength();
              if ($size !== '' && $size != 0) {
                $unit = null;
                $units = array('B', 'KB', 'MB', 'GB', 'TB');
                for ($i = 0, $c = count($units); $i < $c; $i++) {
                  if ($size > 1024) {
                    $size = $size / 1024;
                  } else {
                    $unit = $units[$i];
                    break;
                  }
                }
                $showsize = round($size, 2) . ' ' . $unit;
              } else {
                $showsize = '';
              }
              ?>
<!--             Size -->
            <td class="contentlength" name="<?= DAV::xmlescape( $member->user_prop_getcontentlength() ) ?>"><?= DAV::xmlescape( $showsize ) ?></td>
            <?php if (substr($member->path, -1) === '/'): ?>
              <td class="type" name="collection"><i name="<?= DAV::xmlescape( DAV::unslashify($member->path) ) ?>"
                     class="icon-folder-close bh-dir-openselected"
                     style="cursor: pointer"></i></td> 
              <?php else : ?>
              <td class="type" name="<?= DAV::xmlescape( $member->user_prop_getcontenttype() ) ?>"><?= DAV::xmlescape( $member->user_prop_getcontenttype() ) ?></td>
            <?php endif; ?> 
<!--             Date, has to be the same as shown with javascript -->
            <td class="lastmodified" name="<?= DAV::xmlescape( date( 'r', $member->user_prop_getlastmodified() ) ) ?>"><?= DAV::xmlescape( date('j-n-Y G:i', $member->user_prop_getlastmodified() ) ) ?>
            </td>
<!--             Owner -->
            <td class="owner" name="<?= DAV::xmlescape( $owner->path ) ?>"><?= DAV::xmlescape( $owner->user_prop_displayname() ) ?></td>
            <?php if (substr($member->path, -1) !== '/'): ?>
            	<!-- share file, not yet implemented -->         
							<!--  <td width="10px" data-toggle="tooltip" -->
							<!--      title="Email read-only share link"><i class="icon-share"></i></td> -->
            <?php else : ?>
	            <!-- share file, not yet implemented -->         
							<!-- <td></td> -->
            <?php endif; ?>
          </tr>
          <?php
          $registry->forget($this->path . $inode);
        endforeach;
        ?>
      </tbody>
    </table>
  </div>
  <!-- End contents tab -->

  <!-- Acl tab -->
  <div id="bh-dir-panel-acl" class="tab-pane fade">
    <!-- <h4>ACL <?= DAV::xmlescape( $this->path ) ?></h4> -->
    <table id="bh-dir-acl-table" class="table table-striped">
      <thead>
        <tr>
          <th>Principal</th>
          <th>Privileges</th>
          <th>Access</th>
          <th>Inherited</th>
        </tr>
      </thead>
      <tbody id="bh-dir-aclcontent">
      </tbody>
    </table>
  </div>
  <!-- End Acl tab -->

</div>
<!-- End tab div -->

<!-- Mask input -->
<div id="bh-dir-all" hidden=true></div>

<?php
$footer = '
	<script type="text/javascript">
  	// For Dynatree
 	  var treecontents = ' . json_encode($tree) . ';
    //   create acl variable
    var aclxml = ' .
        json_encode(
                DAV::xml_header() .
                '<D:acl xmlns:D="DAV:">' .
                ($aclAllowed ? $this->prop(DAV::PROP_ACL) : '') .
                '</D:acl>'
        )
        . ';
 		
 		if (window.DOMParser) {
	 		parser = new DOMParser();
	 		var aclxmldocument = parser.parseFromString(aclxml,"text/xml");
		} else { // IE
	 		var aclxmldocument = new ActiveXObject("Microsoft.XMLDOM");
	 		aclxmldocument.async = false;
	 		aclxmldocument.loadXML(aclxml);
		}
 	</script>
 	
 	<script type="text/javascript" src="/system/js/directory.js"></script>
 		
 	<script type="text/javascript" src="/system/js/directory_controller.js"></script>
 		
 	<script type="text/javascript" src="/system/js/directory_view.js"></script>
 		
 	<script type="text/javascript" src="/system/js/directory_view_content.js"></script>
 		
 	<script type="text/javascript" src="/system/js/directory_view_tree.js"></script>
 	<link href="/system/js/plugins/dynatree/src/skin/ui.dynatree.css" rel="stylesheet" type="text/css" />
 	<script type="text/javascript" src="/system/js/plugins/dynatree/jquery/jquery.cookie.js"></script>
 	<script type="text/javascript" src="/system/js/plugins/dynatree/src/jquery.dynatree.js"></script>
 		
 	<script type="text/javascript" src="/system/js/directory_view_dialog.js"></script>
 		
 	<script type="text/javascript" src="/system/js/directory_view_acl.js"></script>
 		
 	<script type="text/javascript" src="/system/js/directory_resource.js"></script>

  <script type="text/javascript" src="/system/js/plugins/tablesorter/js/jquery.tablesorter.js"></script>
 	<script type="text/javascript" src="/system/js/plugins/tablesorter/js/jquery.tablesorter.widgets.js"></script>
';
require 'views/footer.php';

// End of file