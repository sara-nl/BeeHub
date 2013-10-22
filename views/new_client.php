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
    <?php
    // first and last of $crumb are empty
    $crumb = explode("/", $this->path);
    $pathString = "<ul class=\"breadcrumb bh-dir-breadcrumb \">";
    // Root
    $pathString .= "<li><a href=\"/\">BeeHub root</a><span class=\"divider\">&raquo;</span></li>";
    $count = count($crumb);
    $start = 1;
    //Show maximal two directories
    if ($count > 4) {
			$pathString .= "<li><span class=\"divider\">.. /</span></li>";
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
	        $pathString .= "<li class=\"active\">" . DAV::xmlescape( $value ) . "</li>";
	      } else {
	        $pathString .= "<li><a href=\"" . DAV::xmlescape( $newpath ) . "\">" . DAV::xmlescape( $value ) . "</a><span class=\"divider\">/</span></li>";
	      }
	    }
    }
    $pathString .= "</ul>";
    ?>
  <h4><?= $pathString ?></h4>
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
<!--   CONTENT VIEW -->
	<!--  CONTENT: Up button -->
  <?php if (DAV::unslashify($this->collection()->path) != "") : ?>
    <button id="<?= DAV::xmlescape( DAV::unslashify($this->collection()->path) ) ?>"
            class="btn btn-small bh-dir-content-up">
      <i class="icon-chevron-up"></i> Up
    </button>
	<!--   No up possible -->
  <?php else: ?>
    <button id="<?= DAV::xmlescape( DAV::unslashify($this->collection()->path) ) ?>"
            class="btn btn-small bh-dir-content-up" disabled="disabled">
      <i class="icon-chevron-up"></i> Up
    </button>
  <?php endif; ?>
  
	<!--	CONTENT: Home button-->
  <button
    id="<?= DAV::xmlescape( preg_replace('@^' . BeeHub::USERS_PATH . '(.*)@', '/home/\1/', BeeHub_Auth::inst()->current_user()->path) ) ?>"
    class="btn btn-small bh-dir-content-gohome" data-toggle="tooltip"
    title="Go to home folder">
    <i class="icon-home"></i> Home
  </button>
  
	<!--	CONTENT: Upload button-->
  <input class="bh-dir-content-upload-hidden" type="file" name="files[]"
         multiple hidden>
         
	<!--   Hidden upload field, this is needed to show the upload button -->
  <button data-toggle="tooltip"
          title="Upload to current folder" class="btn btn-small bh-dir-content-upload">
    <i class="icon-upload"></i> Upload
  </button>
  
	<!-- CONTENT: New folder button-->
  <button data-toggle="tooltip"
          title="Create new folder in current folder" class="btn btn-small bh-dir-content-newfolder">
    <i class="icon-folder-close"></i> New
  </button>
  
	<!-- CONTENT: Copy button-->
  <button data-toggle="tooltip"
          title="Copy selected to other folder" class="btn btn-small bh-dir-content-copy"
          disabled="disabled">
    <i class="icon-hand-right"></i> Copy
  </button>
  
	<!-- CONTENT: Move button-->
  <button data-toggle="tooltip"
          title="Move selected to other folder" class="btn btn-small bh-dir-content-move"
          disabled="disabled">
    <i class="icon-move"></i> Move
  </button>
  
	<!-- CONTENT: Delete button-->
  <button data-toggle="tooltip"
          title="Delete selected" class="btn btn-small bh-dir-content-delete" disabled="disabled">
    <i class="icon-remove"></i> Delete
  </button>

<!-- 	ACL VIEW -->
	<!-- ACL: Add button-->
  <button data-toggle="tooltip" title="Add rule" class="btn btn-small bh-dir-acl-add hide" >
    <i class="icon-plus"></i> Add rule
  </button> 
</div>
  
<!-- End fixed buttons -->

<!-- Dialog, for dialog view -->
<div id="bh-dir-dialog" hidden></div>

<!-- Tree slide out, dynatree - tree view -->
<div id="bh-dir-tree" class="bh-dir-tree-slide">
  <ul> 
    <?php
/*    // Fill the tree nodes
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
*/      ?>
  </ul>
</div>
<!-- End tree slide out -->

<!-- Tree header -->
<div id="bh-dir-tree-header">
	<table>
		<tr>
			<td id="bh-dir-tree-cancel" hidden><i class="icon-remove" style="cursor: pointer"></i></td>
			<td class="bh-dir-tree-header" hidden>Browse</td>
		</tr>
	</table>
</div>

<!-- Arrow to show the tree -->
<a class="bh-dir-tree-slide-trigger" href="#"><i class="icon-folder-open"></i></a>

<!-- Tab contents -->
<div class="tab-content">
  <!-- Fixed divs don't use space -->
  <div class="bh-dir-allocate-space"></div>
  <!-- Contents tab -->
  <div id="bh-dir-panel-contents" class="tab-pane fade in active">
    <table id="bh-dir-content-table" class="table table-striped table-hover table-condensed">
      <thead class="bh-dir-content-table-header">
        <tr>
<!--         	Checkbox header -->
          <th class="bh-dir-small-column"><input type="checkbox" class="bh-dir-content-checkboxgroup"></th>
<!--           Rename icon header -->
          <th class="bh-dir-small-column"></th>
<!--           Name header -->
          <th>Name</th>
<!-- 					Hidden rename column -->
          <th hidden></th>
<!--           Size header -->
          <th>Size</th>
<!--           Type header -->
          <th>Type</th>
<!--           Modified header -->
          <th>Modified</th>
<!--           Owner header -->
          <th>Owner</th>
          <!-- share file, not yet implemented -->
          <!--  <th class="bh-dir-small-column"></th> -->
        </tr>
      </thead>
      <tbody>
        <?php
        // For all resources, fill table
        foreach ($this as $inode) :
          $member = DAV::$REGISTRY->resource($this->path . $inode);
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
<td class="bh-dir-small-column"><input type="checkbox" class="bh-dir-content-checkbox" name="<?= DAV::xmlescape( DAV::unslashify( $member->path ) ) ?>"
                                    value="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>"></td>
<!--             Rename icon -->
            <td class="bh-dir-small-column" data-toggle="tooltip" title="Rename"><i
                class="icon-edit bh-dir-content-edit" style="cursor: pointer"></i></td>
<!--                 Name -->
<!--                 Directory -->
            <?php if (substr($member->path, -1) === '/'): ?>
              <td class="bh-dir-content-name displayname" name="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>"><a
                  href="<?= DAV::xmlescape( DAV::unslashify( $member->path ) ) ?>"><b><?= DAV::xmlescape( $member->user_prop_displayname() ) ?>/</b>
                </a></td>
<!--                 File -->
            <?php else : ?>
              <td class="bh-dir-content-name displayname" name="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>"><a
                  href="<?= DAV::xmlescape( DAV::unslashify( $member->path ) ) ?>"><?= DAV::xmlescape( $member->user_prop_displayname() ) ?>
                </a></td>
            <?php endif; ?>
<!--             Hidden rename -->
            <td class="bh-dir-content-rename-td" hidden><input 
                class="bh-dir-content-rename-form"
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
                     class="icon-folder-close bh-dir-content-openselected"
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
							<!--  <td class="bh-dir-small-column" data-toggle="tooltip" -->
							<!--      title="Email read-only share link"><i class="icon-share"></i></td> -->
            <?php else : ?>
	            <!-- share file, not yet implemented -->         
							<!-- <td></td> -->
            <?php endif; ?>
          </tr>
          <?php
          DAV::$REGISTRY->forget($this->path . $inode);
        endforeach;
        ?>
      </tbody>
    </table>
  </div>
  <!-- End contents tab -->

  <!-- Acl tab -->
  <div id="bh-dir-panel-acl" class="tab-pane fade">
    <!-- <h4>ACL <?= DAV::xmlescape( $this->path ) ?></h4> -->
    <table id="bh-dir-acl-table" class="table table-striped table-hover table-condensed">
      <thead class="bh-dir-acl-table-header">
        <tr>
<!--           Principal -->
          <th>Principal</th>
<!--           Permissions -->
          <th>Permissions</th>
<!-- 					Hidden dropdown column -->
          <th hidden></th>
<!--           Comments -->
          <th>Comment</th>
<!--         	Move up -->
          <th class="bh-dir-small-column"></th>
<!--           Move down -->
          <th class="bh-dir-small-column"></th>
<!--           Delete row -->
          <th class="bh-dir-small-column"></th>
        </tr>
      </thead>
      <tbody class="bh-dir-acl-contents" name="<?= DAV::xmlescape( DAV::unslashify($member->path) ) ?>">
<!--       Niek -->
      <?php
      $acl = $this->user_prop_acl();
      $acl_length = count( $acl );
      for ( $key = 0; $key < $acl_length; $key++ ) :
        $ace = $acl[ $key ];
      
      if  ( $ace->protected  || $ace->inherited ) {
      	$class = "info";
      } else {
				$class = "";
			};
        ?>
      	<tr class="bh-dir-acl-row <?= $class ?>">
<!-- 					Principal -->
<?php
  // Determine how to show the principal
  switch ( $ace->principal ) {
    case 'DAV: owner':
      $displayname = '<em>Owner</em>';
      break;
    case DAVACL::PRINCIPAL_ALL:
      $displayname = '<em>Everybody</em>';
      break;
    case DAVACL::PRINCIPAL_AUTHENTICATED:
      $displayname = '<em>All BeeHub users</em>';
      break;
    case DAVACL::PRINCIPAL_UNAUTHENTICATED:
      $displayname = '<em>All unauthenticated users</em>';
      break;
    case DAVACL::PRINCIPAL_SELF:
      $displayname = '<em>This resource itself</em>';
      break;
    default:
      $principal = DAV::$REGISTRY->resource( $ace->principal );
      if ( $principal instanceof DAVACL_Principal ) {
        $displayname = DAV::xmlescape($principal->user_prop( DAV::PROP_DISPLAYNAME ));
      }else{
        $displayname = '<em>Unrecognized principal!</em>';
      }
    break;
  }
    $icon= '<i class="icon-user"></i><i class="icon-user"></i>';
    if ((strpos($ace->principal, BeeHub::USERS_PATH) !== false) || ($ace->principal == 'DAV: owner' )) {
  		$icon= '<i class="icon-user"></i>';
    }
?>
					<td class="bh-dir-acl-principal" name="<?= DAV::xmlescape($ace->principal) ?>" data-toggle="tooltip"
          title="<?= DAV::xmlescape($ace->principal)?>" ><b><?= ( $ace->invert ? 'Everybody except ' : '' ) . $displayname ?> </b>(<?= $icon?>)</td>
					
<?php 
	// make permissions string
	$tooltip="";
	$class="";
	$permissions=""; 
	if ( $ace->deny) {
		$permissions="deny ";
		$class="bh-dir-acl-deny";
		if ( ( count( $ace->privileges ) === 1 ) && in_array( DAVACL::PRIV_WRITE_ACL, $ace->privileges ) ) {
			$permissions .= "manage";
			$tooltip="deny change acl";
		} elseif ( ( count( $ace->privileges ) === 2 ) && in_array( DAVACL::PRIV_WRITE, $ace->privileges ) && in_array( DAVACL::PRIV_WRITE_ACL, $ace->privileges) ) {
			$permissions .= "write";
			$tooltip="deny write, change acl";
		} elseif ( ( count( $ace->privileges ) === 3 ) && in_array( DAVACL::PRIV_READ, $ace->privileges ) && in_array( DAVACL::PRIV_WRITE, $ace->privileges ) && in_array( DAVACL::PRIV_WRITE_ACL, $ace->privileges) ) {
			$permissions .= "read";
			$tooltip="deny read, write, change acl";
    } elseif ( in_array( DAVACL::PRIV_ALL, $ace->privileges ) ) {
			$permissions .= "read";
			$tooltip="deny read, write, change acl";
		} else {
			$permissions .= "unknown privilege (combination)";
			$tooltip="deny " . implode( '; ', $ace->privileges );
		}
	} else { 
		$permissions="allow ";
		$class="bh-dir-acl-allow";
		if ( ( count( $ace->privileges ) === 1 ) && in_array( DAVACL::PRIV_READ, $ace->privileges ) ) {
			$permissions .= "read";
			$tooltip="allow read";
		} elseif ( ( count( $ace->privileges ) === 2 ) && in_array( DAVACL::PRIV_WRITE, $ace->privileges ) && in_array( DAVACL::PRIV_READ, $ace->privileges) ) {
			$permissions .= "write";
			$tooltip="allow read, write";
		} elseif ( ( count( $ace->privileges ) === 3 ) && in_array( DAVACL::PRIV_WRITE_ACL, $ace->privileges ) && in_array( DAVACL::PRIV_WRITE, $ace->privileges ) && in_array( DAVACL::PRIV_READ, $ace->privileges ) ) {
			$permissions .= "manage";
			$tooltip="allow read, write, change acl";
    } elseif ( in_array( DAVACL::PRIV_ALL, $ace->privileges ) ) {
			$permissions .= "manage";
			$tooltip="allow read, write, change acl";
		} else {
			$permissions .= "unknown privilege (combination)";
			$tooltip="allow " . implode( '; ', $ace->privileges );
		}
	};

	$changePermissionsClass = "bh-dir-acl-change-permissions";
	$style= 'style="cursor: pointer"';
	if  ( $ace->protected  || $ace->inherited ) {
		$changePermissionsClass = "";
		$style= "";
	}
?>
	<td class="bh-dir-acl-permissions-select" hidden>
		<select class="bh-dir-acl-table-permissions">
      <option value="allow read" <?= ( $permissions === 'allow read' ) ? 'selected="selected"' : '' ?> >allow read (read)</option>
      <option value="allow write" <?= ( $permissions === 'allow write' ) ? 'selected="selected"' : '' ?> >allow write (read, write)</option>
      <option value="allow manage" <?= ( $permissions === 'allow manage' ) ? 'selected="selected"' : '' ?> >allow manage (read, write, change acl)</option>
      <option value="deny read" <?= ( $permissions === 'deny read' ) ? 'selected="selected"' : '' ?> >deny read (read, write, change acl)</option>
      <option value="deny write" <?= ( $permissions === 'deny write' ) ? 'selected="selected"' : '' ?> >deny write (write, change acl)</option>
      <option value="deny manage" <?= ( $permissions === 'deny manage' ) ? 'selected="selected"' : '' ?> >deny manage (change acl)</option>
  	</select>
  </td>
	<td class="bh-dir-acl-permissions <?= $changePermissionsClass ?> <?= $class?>" <?= $style ?> data-toggle="tooltip" title="<?= $tooltip?>">
    <span class="presentation"><?= $permissions ?></span>
    <?php if ( strpos( $permissions, 'unknown' ) !== false ) : ?>
      <span class="original" hidden="hidden"><?= implode( ' ', $ace->privileges ) ?></span>
    <?php endif; ?>
  </td>
<!-- 					Info -->
<?php
  $info = '';
  $message = '';
  $class='';
  if ( $ace->protected ) {
    $info = 'protected';
    $message = 'protected, no changes are possible';
    $class ='bh-dir-acl-protected';
  } elseif ( ! is_null( $ace->inherited ) ) {
    $info = 'inherited';
    $message = 'inherited from: <a href="' . $ace->inherited . '">' . $ace->inherited . '</a>';
    $class ='bh-dir-acl-inherited';
  }
?>
					<td class="bh-dir-acl-comment <?= $class  ?>" name="<?= $info  ?>" ><?= $message ?></td>
<!--       	When ace is not protected, inherited and previous ace exists and is not protected  -->
      	<?php if ( ! $ace->protected &&
      						( is_null( $ace->inherited ) ) &&
      						($key !== 0) &&
      						( ! $acl[$key-1]->protected ) )
      	 :?>
<!-- 					Move up -->
					<td class="bh-dir-acl-up"><i title="Move up" class="icon-chevron-up bh-dir-acl-icon-up" style="cursor: pointer"></i></td>
				<?php else : ?>
<!-- 					No move up possible -->
					<td class="bh-dir-acl-up"></td>
				<?php endif; ?>
				<!--       	When ace is not protected, inherited and next ace exists and is not inherited  -->
      	<?php if ( ! $ace->protected &&
      						( is_null( $ace->inherited ) ) &&
      						($key !== $acl_length - 1) &&
      						 is_null( $acl[$key+1]->inherited ) )
      	 :?>
<!-- 					Move down -->
					<td class="bh-dir-acl-down"><i title="Move down" class="icon-chevron-down bh-dir-acl-icon-down" style="cursor: pointer"></i></td>
				<?php else : ?>
<!-- 					No move down possible -->
					<td class="bh-dir-acl-down"></td>
				<?php endif; ?>
				<?php if ( $ace->protected || ! is_null( $ace->inherited ) ) :?>
<!--       	no delete possible -->
      	<td></td>
      	<?php else : ?>
<!--       		Delete icon -->
      		<td><i title="Delete" class="icon-remove bh-dir-acl-icon-remove" style="cursor: pointer"></i></td>
      	<?php endif; ?>
      	</tr>  
     <?php
        endfor;
     ?>  	
      </tbody>
    </table>
  </div>
  <!-- End Acl tab -->
</div>
<!-- End tab div -->

<!-- Mask input -->
<div id="bh-dir-mask" hidden></div>

<!-- Disable input -->
<div id="bh-dir-mask-transparant" hidden></div>

<!-- Loading mask -->
<div id="bh-dir-mask-loading" hidden></div>
<!--  <img src="/system/img/bh-dir-loading.gif" id="bh-dir-mask-loading" style="display:none" /> -->
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
