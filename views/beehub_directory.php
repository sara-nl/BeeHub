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
 * @var $path string a slashified path to a directory
 */
function createTree2($path, $oldpath = null, $oldmembers = null) {
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
  return createTree2(
          DAV::slashify(dirname($path)), $path, $members
  );
}
$testtree = createTree2(DAV::slashify(dirname($this->path)));
?>
<div class="bh-dir-fixed-header">
  <h4>
    <?php
    $crumb = explode("/", $this->path);
    print "<ul class='breadcrumb bh-dir-breadcrumb '>";
    print "<li><a href='/'>BeeHub root</a><span class='divider'>&raquo;</span></li>";
    $last = count($crumb) - 2;
    $i = 0;
    $newpath = '';
    foreach ($crumb as $value) {
      $value = urldecode($value);
      // first and last value are empty
      if ($value !== '') {
        $newpath .= '/' . $value;
        if ($i === $last) {
          print "<li class='active'>$value</li>";
        } else {
          print "<li><a href='" . $newpath . "'>$value</a><span class='divider'>/</span></li>";
        }
      }
      $i++;
    }
    print "</ul>";
    ?>
  </h4>
</div>
<!-- End class fixed header -->

<!-- Tabs-->
<div class="bh-dir-fixed-tabs">
  <ul id="bh-dir-tabs" class="nav nav-tabs">
    <li class="active"><a href="#bh-dir-panel-contents" data-toggle="tab">Contents</a>
    </li>
    <li><a href="#bh-dir-panel-acl" data-toggle="tab">ACL</a></li>
  </ul>
</div>
<!-- End class fixed tabs -->

<div class="bh-dir-fixed-buttons">
  <?php if (DAV::unslashify($this->collection()->path) != "") : ?>
    <button id="<?= DAV::unslashify($this->collection()->path) ?>"
            class="btn btn-small bh-dir-group">
      <i class="icon-chevron-up"></i> Up
    </button>
  <?php else: ?>
    <button id="<?= DAV::unslashify($this->collection()->path) ?>"
            class="btn btn-small bh-dir-group" disabled="disabled">
      <i class="icon-chevron-up"></i> Up
    </button>
  <?php endif; ?>
  <button
    id="<?= preg_replace('@^/system/users/(.*)@', '/home/\1/', BeeHub_Auth::inst()->current_user()->path) ?>"
    class="btn btn-small bh-dir-gohome" data-toggle="tooltip"
    title="Go to home folder">
    <i class="icon-home"></i> Home
  </button>
  <input id="bh-dir-upload-hidden" type="file" name="files[]"
         hidden='true' multiple>
  <button id="bh-dir-upload" data-toggle="tooltip"
          title="Upload to current folder" class="btn btn-small">
    <i class="icon-upload"></i> Upload
  </button>
  <button id="bh-dir-newfolder" data-toggle="tooltip"
          title="Create new folder in current folder" class="btn btn-small">
    <i class="icon-folder-close"></i> New
  </button>
  <button id="bh-dir-copy" data-toggle="tooltip"
          title="Copy selected to other folder" class="btn btn-small"
          disabled="disabled">
    <i class="icon-hand-right"></i> Copy
  </button>
  <button id="bh-dir-move" data-toggle="tooltip"
          title="Move selected to other folder" class="btn btn-small"
          disabled="disabled">
    <i class="icon-move"></i> Move
  </button>
  <button id="bh-dir-delete" data-toggle="tooltip"
          title="Delete selected" class="btn btn-small" disabled="disabled">
    <i class="icon-remove"></i> Delete
  </button>
</div>

<!-- Dialog -->
<div id="bh-dir-dialog" hidden='true'></div>

<!-- Tree slide out -->
<div id="bh-dir-tree" class="bh-dir-tree-slide">
  <ul>
    <?php
    $registry = BeeHub_Registry::inst();
    foreach ($this as $inode) :
      $member = $registry->resource($this->path . $inode);
      if (DAV::unslashify($member->path) === '/system') {
        continue;
      }
      if (substr($member->path, -1) === '/'):
        ?>
        <li id="<?= $member->user_prop_displayname() ?>" class="folder"><?= $member->user_prop_displayname() ?>
          <ul>
            <li></li>
          </ul> <?php
        endif;
        $registry->forget($this->path . $inode);
      endforeach;
      ?>

  </ul>
</div>

<a class="bh-dir-tree-slide-trigger" href="#"><i
    class="icon-chevron-left"></i> </a>

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
          <th width="10px"></th>
          <th width="10px"><input type="checkbox"
                                  class="bh-dir-checkboxgroup"></th>
          <th>Name</th>
          <!-- 			Hidden rename column -->
          <th hidden='true'></th>
          <th>Size</th>
          <th>Type</th>
          <th>Modified</th>
          <th>Owner</th>
          <th width="10px"></th>
        </tr>
      </thead>
      <tbody>
        <?php
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
          <tr id='<?= DAV::unslashify($member->path) ?>'>
            <td width="10px" data-toggle="tooltip" title="Rename file"><i
                class="icon-edit bh-dir-edit" style="cursor: pointer"></i></td>
            <td width="10px"><input type="checkbox" class="bh-dir-checkbox"
                                    value='<?= $member->user_prop_displayname() ?>'></td>
              <?php if (substr($member->path, -1) === '/'): ?>
              <td class="bh-dir-name"><a
                  href='<?= DAV::unslashify($member->path) ?>'><b><?= $member->user_prop_displayname() ?>/</b>
                </a></td>
            <?php else : ?>
              <td class="bh-dir-name"><a
                  href='<?= DAV::unslashify($member->path) ?>'><?= $member->user_prop_displayname() ?>
                </a></td>
            <?php endif; ?>
            <td class="bh-dir-rename-td" hidden='true'><input
                class="bh-dir-rename-form"
                name='<?= $member->user_prop_displayname() ?>'
                value='<?= $member->user_prop_displayname() ?>'></td>
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
                $showsize = round($size, 0) . ' ' . $unit;
              } else {
                $showsize = '';
              }
              ?>
            <td><?= $showsize ?></td>
            <?php if (substr($member->path, -1) === '/'): ?>
              <td><i name=<?= DAV::unslashify($member->path) ?>
                     class="icon-folder-close bh-dir-openselected"
                     style="cursor: pointer">></i></td>
              <?php else : ?>
              <td><?= $member->user_prop_getcontenttype() ?></td>
            <?php endif; ?>
            <td><?= date('Y-m-d H:i:s', $member->user_prop_getlastmodified()) ?>
            </td>
            <td><?= $owner->user_prop_displayname() ?></td>
            <?php if (substr($member->path, -1) !== '/'): ?>
              <td width="10px" data-toggle="tooltip"
                  title="Email read-only share link"><i class="icon-share"></i></td>
                <?php else : ?>
              <td></td>
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
    <!-- <h4>ACL <?= $this->path ?></h4> -->
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

<?php
$footer = '
 	  <script type="text/javascript">
 	  var treecontents = ' . json_encode($testtree) . ';
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
}else{ // IE
 		var aclxmldocument = new ActiveXObject("Microsoft.XMLDOM");
 		aclxmldocument.async = false;
 		aclxmldocument.loadXML(aclxml);
}
 	  </script>
 		<script type="text/javascript" src="/system/js/directory.js"></script>
 		<script type="text/javascript" src="/system/js/directory_view.js"></script>
 		<script type="text/javascript" src="/system/js/directory_view_content.js"></script>
 		<script type="text/javascript" src="/system/js/directory_view_tree.js"></script>
 		<script type="text/javascript" src="/system/js/directory_view_dialog.js"></script>
 		<script type="text/javascript" src="/system/js/directory_resource.js"></script>
 		<script type="text/javascript" src="/system/js/directory_controller.js"></script>
 	  <link href="/system/js/plugins/dynatree/src/skin/ui.dynatree.css" rel="stylesheet" type="text/css" />
 	  <script type="text/javascript" src="/system/js/plugins/dynatree/jquery/jquery.cookie.js"></script>
 	  <script type="text/javascript" src="/system/js/plugins/dynatree/src/jquery.dynatree.js"></script>
    <script type="text/javascript" src="/system/js/plugins/tablesorter/js/jquery.tablesorter.js"></script>
 	  <script type="text/javascript" src="/system/js/plugins/tablesorter/js/jquery.tablesorter.widgets.js"></script>
 	  ';
require 'views/footer.php';
