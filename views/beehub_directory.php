<?php
/**
 * Creates the HTML document for the client
 *
 * Copyright ©2007-2013 SURFsara b.v., Amsterdam, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at <http://www.apache.org/licenses/LICENSE-2.0>
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package     BeeHub
 * @subpackage  views
 */

declare( encoding = 'UTF-8' );

/*
  Available variables:

  $this       The beehub_resource object representing the current resource
*/

// Load the default page header
$header = '<link href="/system/css/dynatree/ui.dynatree.css" rel="stylesheet" type="text/css" />';
require 'views/header.php';
?>

<!-- Bread crumb -->
<div class="bh-dir-fixed-path">
  <ul class="breadcrumb bh-dir-breadcrumb">
    <?php
    if ( $this->path === '/' ) {
      print( '<li>BeeHub root</li>' );
    }else{
      print( '<li><a href="/">BeeHub root</a><span class="divider">&raquo;</span></li>' );
      // Create an array with all directories in the path
      $crumb = explode( "/", trim( $this->path, '/' ) );
      $count = count( $crumb );

      // Determine where to start showing directories; Show max three directories (2 parent directories)
      $start = 0;
      if ( $count > 3 ) {
        print( '<li><span class="divider">.. /</span></li>' );
        $start = $count - 3;
      }

      // Then print the parent directories
      $newpath = '/';
      for ( $counter = 0 ; $counter < $count ; $counter++ ) {
        $value = urldecode( $crumb[ $counter ] );
        $newpath .= $value . '/'; // We extend the path for each intermediate directory, but...
        // ...show only the two last directories
        if ( $counter >= $start ) {
          // Last directory is current directory, no link
          if ( $counter === ( $count - 1 ) ) {
            print( '<li class="active">' . DAV::xmlescape( $value ) . '</li>' );
          } else {
            print( '<li><a href="' . DAV::xmlescape( $newpath ) . '">' . DAV::xmlescape( $value ) . '</a><span class="divider">/</span></li>' );
          }
        }
      }
    }
    ?>
  </ul>
</div>
<!-- End div class fixed path -->

<!-- Tabs - Content and ACL tab -->
<div class="bh-dir-fixed-tabs">
  <ul id="bh-dir-tabs" class="nav nav-tabs">
    <li class="active"><a href="#bh-dir-panel-contents" data-toggle="tab">Contents</a>
    </li>
    <li><a href="#bh-dir-panel-acl" data-toggle="tab">Share</a></li>
  </ul>
</div>
<!-- End class fixed tabs -->

<!-- Fixed buttons at the top -->
<div class="bh-dir-fixed-buttons">
  <!--   CONTENT VIEW -->
  <!--  CONTENT: Up button -->
  <button <?= $this->path !== '/' ? 'id="' . DAV::xmlescape( DAV::unslashify( $this->collection()->path ) ) . '"' : '' ?> class="btn btn-small bh-dir-content-up" <?= ( $this->path === "/" ) ? 'disabled="disabled"' : '' ?> >
    <i class="icon-chevron-up"></i> Up
  </button>

  <!--	CONTENT: Home button-->
  <button
    id="<?= DAV::xmlescape( preg_replace('@^' . BeeHub::USERS_PATH . '(.*)@', '/home/\1/', BeeHub_Auth::inst()->current_user()->path) ) ?>"
    class="btn btn-small bh-dir-content-gohome" data-toggle="tooltip"
    title="Go to home folder">
    <i class="icon-home"></i> Home
  </button>

  <!--	CONTENT: Upload button-->
  <input class="bh-dir-content-upload-hidden" type="file" name="files[]" multiple="multiple" hidden="hidden" />

  <!--   Hidden upload field, this is needed to show the upload button -->
  <button data-toggle="tooltip" title="Upload to current folder" class="btn btn-small bh-dir-content-upload">
    <i class="icon-upload"></i> Upload
  </button>

  <?php
  try {
    $this->assert( DAVACL::PRIV_WRITE );
    $mkcolButtonDisabled = false;
  } catch ( DAV_Status $e) {
    $mkcolButtonDisabled = true;
  }
  ?>
  <!-- CONTENT: New folder button-->
  <button data-toggle="tooltip" title="Create new folder in current folder" class="btn btn-small bh-dir-content-newfolder" <?= $mkcolButtonDisabled ? 'disabled="disabled"' : '' ?>>
    <i class="icon-folder-close"></i> New
  </button>

  <!-- CONTENT: Copy button-->
  <button data-toggle="tooltip" title="Copy selected to other folder" class="btn btn-small bh-dir-content-copy" disabled="disabled">
    <i class="icon-hand-right"></i> Copy
  </button>

  <!-- CONTENT: Move button-->
  <button data-toggle="tooltip" title="Move selected to other folder" class="btn btn-small bh-dir-content-move" disabled="disabled">
    <i class="icon-move"></i> Move
  </button>

  <!-- CONTENT: Delete button-->
  <button data-toggle="tooltip" title="Delete selected" class="btn btn-small bh-dir-content-delete" disabled="disabled">
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
<div id="bh-dir-dialog" hidden="hidden"></div>

<!-- Tree header -->
<div id="bh-dir-tree-header">
  <table>
    <tr>
      <td id="bh-dir-tree-cancel" hidden="hidden"><i class="icon-remove" style="cursor: pointer"></i></td>
      <td class="bh-dir-tree-header" <?= $_COOKIE['beehub-showtree'] === 'false' ? 'hidden="hidden"' : '' ?> >Browse</td>
    </tr>
  </table>
</div>

<!-- Arrow to show the tree -->
<a class="bh-dir-tree-slide-trigger <?= $_COOKIE['beehub-showtree'] !== 'false' ? 'active' : '' ?> " href="#">
  <i class="icon-folder-<?= $_COOKIE['beehub-showtree'] !== 'false' ? 'open' : 'close' ?>"></i>
</a>

<!-- Tree slide out, dynatree - tree view -->
<div id="bh-dir-tree" class="bh-dir-tree-slide" <?= $_COOKIE['beehub-showtree'] === 'false' ? 'hidden="hidden"' : '' ?>>
  <ul class="dynatree-container">
    <?php
    // Fill the tree nodes
    if ( isset( $_COOKIE[ 'tree_state' ] ) ) {
      $treeState = json_decode( $_COOKIE[ 'tree_state' ] );
    }else{
      $treeState = array();
    }

    $pathArray = explode( '/', trim( $this->path, '/' ) );
    $parentPath = '/';
    foreach ( $pathArray as $parent ) {
      $parentPath .= $parent . '/';
      $treeState[ $parentPath ] = true;
    }

    printTree( '/', $treeState, $this->path );

    function printTree( $path, $treeState, $selectedPath ) {
      $registry = BeeHub_Registry::inst();
      $resource = $registry->resource( $path );
      $members = array();
      foreach ( $resource as $member ) :
        // Skip the /system/ directory, as there is no need to see this
        if ( (
               ( $path === '/' ) &&
               ( $member === 'system/' )
             ) || (
               substr( $member, -1 ) !== '/'
             )
        ) {
          continue;
        }
        $members[] = $member;
      endforeach;
      usort( $members, 'strnatcasecmp' );
      $last = count( $members ) - 1;
      for ( $counter = 0; $counter <= $last; $counter++ ) :
        $member = $members[ $counter ];
        $memberResource = $registry->resource( $path . $member );
        $hasChildren = false;
        foreach( $memberResource as $submember ) {
          $hasChildren = ( $registry->resource( $memberResource->path . $submember )->prop_resourcetype() === DAV_Collection::RESOURCETYPE );
          $registry->forget( $memberResource->path . $submember );
          if ( $hasChildren ) {
            break;
          }
        }
        $expanded = isset( $treeState[ $memberResource->path ] ) && $hasChildren ? $treeState[ $memberResource->path ] : false;
        ?><li <?= ( $counter === $last ) ? 'class="dynatree-lastsib"' : '' ?>
          ><span class="dynatree-node dynatree-folder
                       <?= $hasChildren ? 'dynatree-has-children' : '' ?>
                       <?= $expanded ? 'dynatree-expanded' : ( $hasChildren ? 'dynatree-lazy' : '' ) ?>
                       dynatree-exp-<?= $expanded ? 'e' : 'cd' ?><?= $counter === $last ? 'l dynatree-lastsib' : '' ?>
                       dynatree-ico-<?= $expanded ? 'e' : 'c' ?>f
                       <?= $memberResource->path === $selectedPath ? 'dynatree-focused' : '' ?>
                "
            ><span class="dynatree-<?= $hasChildren ? 'expander' : 'connector' ?>"></span
            ><span class="dynatree-icon"></span
            ><a class="dynatree-title" href="<?= DAV::xmlescape( $memberResource->path ) ?>"><?= DAV::xmlescape( $memberResource->user_prop_displayname() ) ?></a
          ></span
          <?php if ( $expanded && $hasChildren ) : ?>
            ><ul><?= printTree( $memberResource->path, $treeState, $selectedPath ); ?></ul
          <?php endif; ?>
        ></li>
        <?php
        $registry->forget( $path . $member );
      endfor;
    }
    ?>
  </ul>
</div>
<!-- End tree slide out -->

<!-- Tab content -->
<div class="tab-content bh-dir-tab-content">

  <!-- Fixed divs don't use space -->
  <div class="bh-dir-allocate-space"></div>

  <!-- Contents tab -->
  <div id="bh-dir-panel-contents" class="tab-pane fade in active">
    <table id="bh-dir-content-table" class="table table-striped table-hover table-condensed">
      <thead class="bh-dir-content-table-header">
        <tr>
          <!-- Rename icon header -->
          <th class="bh-dir-small-column"></th> 
          <th class="bh-dir-small-column"><input type="checkbox" class="bh-dir-content-checkboxgroup"></th>
          <th>Name</th>
          <!-- Hidden rename column -->
          <th hidden="hidden"></th>
          <th>Size</th>
          <th>Type</th>
          <th>Modified</th>
          <th>Owner</th>
          <!-- share file, not yet implemented -->
          <!--  <th class="bh-dir-small-column"></th> -->          
        </tr>
      </thead>
      <tbody>
        <?php
        // Get up to 250 child resources so we can sort them (for more resources, we won't sort anymore)
        $subResources = array();
        $counter = 0;
        $sortResources = true;
        foreach ( $this as $resource ) {
          if ( ++$counter > 250 ) {
            $sortResources = false;
            break;
          }
          $subResources[] = $resource;
        }

        // Check whether to sort the sub-resources and sort them if needed
        if ( $sortResources ) {
          usort( $subResources, 'strnatcasecmp' );
        }else{
          // If we reached the threshold, there are too many resource to sort them.
          $subResources = $this;
        }

        // For all resources, fill table
        $current_user_privilege_set_collection = $this->user_prop_current_user_privilege_set();
        foreach ( $subResources as $inode ) :
          $member = DAV::$REGISTRY->resource($this->path . $inode);
          if (DAV::unslashify($member->path) === '/system') {
            continue;
          }
          $owner = BeeHub_Registry::inst()->resource( $member->user_prop_owner() );
          ?>
          <tr id="<?= DAV::xmlescape( DAV::unslashify($member->path) ) ?>">
            <td>
            	<div class="dropdown">
    						<a class="dropdown-toggle bh-dir-content-menu" data-toggle="dropdown" href="#"><i class="icon-align-justify" style="cursor: pointer"></i></a>
    						<ul class="dropdown-menu bh-dir-contents-menu" role="menu" aria-labelledby="dLabel">
                  <?php if ( in_array( DAVACL::PRIV_WRITE, $member->user_prop_current_user_privilege_set() ) && in_array( DAVACL::PRIV_UNBIND, $current_user_privilege_set_collection ) ) : ?>
                    <li><a class="bh-dir-content-edit" href="#">Rename</a></li>
                  <?php endif; ?>
                  <li>
                    <a class="bh-dir-content-acl" href="#">
                      Share
                      <?php if ( count( $member->user_prop_acl_internal() ) > 0 ) : ?>
                        <span title="Resource specific ACL set!">!</span>
                      <?php endif; ?>
                    </a>
                  </li>
                </ul>
                <?php if ( count( $member->user_prop_acl_internal() ) > 0 ) : ?>
                  <span title="Resource specific ACL set!">!</span>
                <?php endif; ?>
              </div>
            </td>
            <!-- Select checkbox -->
            <td class="bh-dir-small-column"><input type="checkbox" class="bh-dir-content-checkbox" name="<?= DAV::xmlescape( DAV::unslashify( $member->path ) ) ?>" value="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>"></td>   
            <!-- Name -->
            <?php if (substr($member->path, -1) === '/'): ?>
              <td class="bh-dir-content-name displayname" name="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>">
                <a href="<?= DAV::xmlescape( DAV::unslashify( $member->path ) ) ?>"><span style="font-weight: bold"><?= DAV::xmlescape( $member->user_prop_displayname() ) ?>/</span></a>
              </td>
              <!-- File -->
            <?php else : ?>
              <td class="bh-dir-content-name displayname" name="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>">
                <a href="<?= DAV::xmlescape( DAV::unslashify( $member->path ) ) ?>"><?= DAV::xmlescape( $member->user_prop_displayname() ) ?></a>
              </td>
            <?php endif; ?>
            <!-- Hidden rename -->
            <td class="bh-dir-content-rename-td" hidden="hidden">
              <input class="bh-dir-content-rename-form" name="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>" value="<?= DAV::xmlescape( $member->user_prop_displayname() ) ?>" />
            </td>
            <!--             Size -->
            <td class="contentlength" name="<?= DAV::xmlescape( $member->user_prop_getcontentlength() ) ?>">
              <?php
              // Calculate size
              $size = $member->user_prop_getcontentlength();
              if ( ! empty( $size ) ) {
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
                print( round( $size, 2 ) . ' ' . $unit );
              } elseif ( $member->prop_resourcetype() !== DAV_Collection::RESOURCETYPE ) {
                print( '0 B' );
              }
              ?>
            </td>
            <?php if ( $member->prop_resourcetype() === DAV_Collection::RESOURCETYPE ) : ?>
              <td class="type" name="collection">
                <i name="<?= DAV::xmlescape( DAV::unslashify($member->path) ) ?>" class="icon-folder-close bh-dir-content-openselected" style="cursor: pointer"></i>
              </td> 
            <?php else : ?>
              <td class="type" name="<?= DAV::xmlescape( $member->user_prop_getcontenttype() ) ?>">
                <?= DAV::xmlescape( $member->user_prop_getcontenttype() ) ?>
              </td>
            <?php endif; ?> 
      
            <!-- Date, has to be the same as shown with javascript -->
            <td class="lastmodified" name="<?= DAV::xmlescape( date( 'r', $member->user_prop_getlastmodified() ) ) ?>">
              <?= DAV::xmlescape( date('j-n-Y G:i', $member->user_prop_getlastmodified() ) ) ?>
            </td>
      
            <!-- Owner -->
            <td class="owner" name="<?= DAV::xmlescape( $owner->path ) ?>">
              <?= DAV::xmlescape( $owner->user_prop_displayname() ) ?>
            </td>
          </tr>
          <?php
          DAV::$REGISTRY->forget( $this->path . $inode );
        endforeach;
        ?>
      </tbody>
    </table>
  </div>
  <!-- End contents tab -->

  <!-- Acl tab -->
  <div id="bh-dir-panel-acl" class="tab-pane fade">
  	<div id="bh-dir-acl-directory-acl">
	    <table id="bh-dir-acl-table" class="table table-striped table-hover table-condensed">
	      <thead class="bh-dir-acl-table-header">
	        <tr>
	          <th>Principal</th>
	          <th>Permissions</th>
	          <!-- Hidden dropdown column -->
	          <th hidden="hidden"></th>
	          <th>Comment</th>
	          <!-- Move up -->
	          <th class="bh-dir-small-column"></th>
	          <!-- Move down -->
	          <th class="bh-dir-small-column"></th>
	          <!-- Delete row -->
	          <th class="bh-dir-small-column"></th>
	        </tr>
	      </thead>
	      <tbody class="bh-dir-acl-contents" name="<?= DAV::xmlescape( DAV::unslashify($this->path) ) ?>">
	        <?php
	        $acl = $this->user_prop_acl();
	        $acl_length = count( $acl );
	        for ( $key = 0; $key < $acl_length; $key++ ) :
	          $ace = $acl[ $key ];
	        
	          // The protected property which grants everybody the 'DAV: unbind' privilege will be omitted from the list
	          if ( $ace->protected &&
	               ( $ace->principal === DAVACL::PRINCIPAL_ALL ) &&
	               ! $ace->deny &&
	               ( count( $ace->privileges ) === 1 ) &&
	               in_array( DAVACL::PRIV_UNBIND, $ace->privileges )
	             )
	          {
	            continue;
	          }
	          ?>
	          <tr class="bh-dir-acl-row <?= ( $ace->protected || $ace->inherited ) ? 'info' : '' ?>">
	            <!-- Principal -->
	            <?php
	            // Determine how to show the principal
	            switch ( $ace->principal ) {
	              case 'DAV: owner':
	                $displayname = '<span style="font-weight: bold">Owner</span>';
	                break;
	              case DAVACL::PRINCIPAL_ALL:
	                $displayname = '<span style="font-weight: bold">Everybody</span>';
	                break;
	              case DAVACL::PRINCIPAL_AUTHENTICATED:
	                $displayname = '<span style="font-weight: bold">All BeeHub users</span>';
	                break;
	              case DAVACL::PRINCIPAL_UNAUTHENTICATED:
	                $displayname = '<span style="font-weight: bold">All unauthenticated users</span>';
	                break;
	              case DAVACL::PRINCIPAL_SELF:
	                $displayname = '<span style="font-weight: bold">This resource itself</span>';
	                break;
	              default:
	                $principal = DAV::$REGISTRY->resource( $ace->principal );
	                if ( $principal instanceof DAVACL_Principal ) {
	                  $displayname = DAV::xmlescape($principal->user_prop( DAV::PROP_DISPLAYNAME ));
	                }else{
	                  $displayname = '<span style="font-weight: bold">Unrecognized principal!</span>';
	                }
	              break;
	            }
	            $icon= '<i class="icon-user"></i><i class="icon-user"></i>';
	            if ( ( strpos( $ace->principal, BeeHub::USERS_PATH ) !== false ) || ( $ace->principal == 'DAV: owner' ) ) {
	              $icon= '<i class="icon-user"></i>';
	            }
	            ?>
	            <td class="bh-dir-acl-principal" name="<?= DAV::xmlescape($ace->principal) ?>" data-invert="<?= $ace->invert?>" data-toggle="tooltip" title="<?= DAV::xmlescape($ace->principal)?>" >
	              <span style="font-weight: bold"><?= ( $ace->invert ? 'Everybody except ' : '' ) . $displayname ?> </span>(<?= $icon?>)
	            </td>
	            				
	            <?php 
	            // make permissions string
	            $tooltip = "";
	            $class = "";
	            $permissions = ""; 
	            if ( $ace->deny ) {
	              $permissions = "deny ";
	              $class = "bh-dir-acl-deny";
	              if ( ( count( $ace->privileges ) === 1 ) && in_array( DAVACL::PRIV_WRITE_ACL, $ace->privileges ) ) {
	                $permissions .= "change acl";
	                $tooltip = "deny change acl";
	              } elseif ( ( count( $ace->privileges ) === 2 ) && in_array( DAVACL::PRIV_WRITE, $ace->privileges ) && in_array( DAVACL::PRIV_WRITE_ACL, $ace->privileges) ) {
	                $permissions .= "write, change acl";
	                $tooltip = "deny write, change acl";
	              } elseif ( ( ( count( $ace->privileges ) === 3 ) && in_array( DAVACL::PRIV_READ, $ace->privileges ) && in_array( DAVACL::PRIV_WRITE, $ace->privileges ) && in_array( DAVACL::PRIV_WRITE_ACL, $ace->privileges) ) || ( in_array( DAVACL::PRIV_ALL, $ace->privileges ) ) ) {
	                $permissions .= "read, write, change acl";
	                $tooltip = "deny read, write, change acl";
	              } else {
	                $permissions .= "unknown privilege (combination)";
	                $tooltip = "deny " . implode( '; ', $ace->privileges );
	              }
	            } else { 
	              $permissions = "allow ";
	              $class = "bh-dir-acl-allow";
	              if ( ( count( $ace->privileges ) === 1 ) && in_array( DAVACL::PRIV_READ, $ace->privileges ) ) {
	                $permissions .= "read";
	                $tooltip="allow read";
	              } elseif ( ( count( $ace->privileges ) === 2 ) && in_array( DAVACL::PRIV_WRITE, $ace->privileges ) && in_array( DAVACL::PRIV_READ, $ace->privileges) ) {
	                $permissions .= "read, write";
	                $tooltip="allow read, write";
	              } elseif ( ( ( count( $ace->privileges ) === 3 ) && in_array( DAVACL::PRIV_WRITE_ACL, $ace->privileges ) && in_array( DAVACL::PRIV_WRITE, $ace->privileges ) && in_array( DAVACL::PRIV_READ, $ace->privileges ) ) || ( in_array( DAVACL::PRIV_ALL, $ace->privileges ) ) ) {
	                $permissions .= "read, write, change acl";
	                $tooltip="allow read, write, change acl";
	              } else {
	                $permissions .= "unknown privilege (combination)";
	                $tooltip="allow " . implode( '; ', $ace->privileges );
	              }
	            }
	            
	            $changePermissionsClass = "bh-dir-acl-change-permissions";
	            $style = 'style="cursor: pointer"';
	            if  ( $ace->protected  || $ace->inherited ) {
	              $changePermissionsClass = "";
	              $style = "";
	            }
	            ?>
	            <td class="bh-dir-acl-permissions-select" hidden="hidden">
	              <select class="bh-dir-acl-table-permissions">
	                <option value="allow read" <?= ( $permissions === 'allow read' ) ? 'selected="selected"' : '' ?> >allow read</option>
	                <option value="allow read, write" <?= ( $permissions === 'allow read, write' ) ? 'selected="selected"' : '' ?> >allow read, write</option>
	                <option value="allow read, write, change acl" <?= ( $permissions === 'allow read, write, change acl' ) ? 'selected="selected"' : '' ?> >allow read, write, change acl</option>
	                <option value="deny read, write, change acl" <?= ( $permissions === 'deny read, write, change acl' ) ? 'selected="selected"' : '' ?> >deny read, write, change acl</option>
	                <option value="deny write, change acl" <?= ( $permissions === 'deny write, change acl' ) ? 'selected="selected"' : '' ?> >deny write, change acl</option>
	                <option value="deny change acl" <?= ( $permissions === 'deny change acl' ) ? 'selected="selected"' : '' ?> >deny change acl</option>
	              </select>
	            </td>
	            <td class="bh-dir-acl-permissions <?= $changePermissionsClass ?> <?= $class?>" <?= $style ?> data-toggle="tooltip" title="<?= $tooltip?>">
	              <span class="presentation"><?= $permissions ?></span>
	              <?php if ( strpos( $permissions, 'unknown' ) !== false ) : ?>
	                <span class="original" hidden="hidden"><?= implode( ' ', $ace->privileges ) ?></span>
	              <?php endif; ?>
	            </td>
	            <!-- Info -->
	            <?php
	            $info = '';
	            $message = '';
	            $class = '';
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
	            <td class="bh-dir-acl-comment <?= $class ?>" name="<?= $info ?>" ><?= $message ?></td>
	            <!-- When ace is not protected, inherited and previous ace exists and is not protected  -->
	            <?php if ( ! $ace->protected &&
	                       ( is_null( $ace->inherited ) ) &&
	                       ( $key !== 0 ) &&
	                       ( ! $acl[$key-1]->protected ) )
	            :?>
	              <!-- Move up -->
	              <td class="bh-dir-acl-up"><i title="Move up" class="icon-chevron-up bh-dir-acl-icon-up" style="cursor: pointer"></i></td>
	            <?php else : ?>
	              <!-- No move up possible -->
	              <td class="bh-dir-acl-up"></td>
	            <?php endif; ?>
	            <!-- When ace is not protected, inherited and next ace exists and is not inherited  -->
	            <?php if ( ! $ace->protected &&
	                       ( is_null( $ace->inherited ) ) &&
	                       ( $key !== $acl_length - 1) &&
	                       is_null( $acl[$key+1]->inherited ) )
	            : ?>
	              <!-- Move down -->
	              <td class="bh-dir-acl-down"><i title="Move down" class="icon-chevron-down bh-dir-acl-icon-down" style="cursor: pointer"></i></td>
	            <?php else : ?>
	              <!-- No move down possible -->
	              <td class="bh-dir-acl-down"></td>
	            <?php endif; ?>
	            <?php if ( $ace->protected || ! is_null( $ace->inherited ) ) :?>
	              <!-- no delete possible -->
	              <td></td>
	            <?php else : ?>
	              <!-- Delete icon -->
	              <td><i title="Delete" class="icon-remove bh-dir-acl-icon-remove" style="cursor: pointer"></i></td>
	            <?php endif; ?>
	          </tr>  
	          <?php
	        endfor;
	      ?>  	
	      </tbody>
	    </table>
	  </div>
  </div>
  <!-- End Acl tab -->

</div>
<!-- End tab div -->

<!-- Mask input -->
<div id="bh-dir-mask" hidden="hidden"></div>

<!-- Disable input -->
<div id="bh-dir-mask-transparant" hidden="hidden"></div>

<!-- Loading mask -->
<div id="bh-dir-mask-loading" hidden="hidden"></div>

<?php
$footer = '
  <script type="text/javascript" src="/system/js/plugins/jquery.cookie.js"></script>
  <script type="text/javascript" src="/system/js/plugins/tablesorter/js/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="/system/js/plugins/tablesorter/js/jquery.tablesorter.widgets.js"></script>
  <script type="text/javascript" src="/system/js/directory.js"></script>
  <script type="text/javascript" src="/system/js/directory_controller.js"></script>
  <script type="text/javascript" src="/system/js/directory_view.js"></script>
  <script type="text/javascript" src="/system/js/directory_view_content.js"></script>
  <script type="text/javascript" src="/system/js/directory_view_tree.js"></script>
  <script type="text/javascript" src="/system/js/directory_view_dialog.js"></script>
  <script type="text/javascript" src="/system/js/directory_view_acl.js"></script>
  <script type="text/javascript" src="/system/js/directory_resource.js"></script>
';
require 'views/footer.php';

// End of file
