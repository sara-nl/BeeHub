  <?php

/*
Available variables:

$this       The beehub_directory object representing the current directory
$members    All members of this directory
*/


/**
 * @var $path string a slashified path to a directory
 */
function createTree2( $path, $oldpath = null, $oldmembers = null ) {
  $dir = BeeHub_Registry::inst()->resource( $path );
  $members = array();
  foreach ($dir as $member) {
    if ( '/' !== substr( $member, -1 ) )
      continue;
    $tmp = array(
      'text'     => rawurldecode( DAV::unslashify( $member ) ),
      'id'       => $path . $member,
      'leaf'     => 'false',
      'expanded' => ( $oldpath === $path . $member ? 'true' : 'false' )
    );
    if ( $tmp['expanded'] === 'true' )
      $tmp['children'] = $oldmembers;
    $members[] = $tmp;
  }
  if ( '/' === $path )
    return $members;
  return createTree2(
    DAV::slashify( dirname( $path ) ),
    $path, $members
  );
}

#/**
# * @var $treePath
# * @var $treeDir
# * @todo TODO Documentation
# */
#function createTree($treePath, $treeDir) {
#  $tree = array();
#  $nextChild = substr($treePath, 0, strpos($treePath, '/') + 1);
#  foreach ($treeDir as $item) {
#    $path = $treeDir->path . $item;
#    if (!is_dir(BeeHub::$CONFIG['environment']['datadir'] . DIRECTORY_SEPARATOR . urldecode($path))) {
#      continue;
#    }
#    $text = $item;
#    if (substr($text, -1) == '/' ) {
#      $text = substr($text, 0, -1);
#    }
#    $treeItem = array('text' => urldecode($text),
#                  'id'   => DAV::unslashify($path).'/',
#                  'leaf' => 'false',
#    );
#    if ($item == $nextChild) {
#      $treeItem['expanded'] = 'true';
#      $treeItem['children'] = createTree(substr($treePath, strlen($nextChild)), DAV::$REGISTRY->resource($path));
#    }
#    $tree[] = $treeItem;
#  }
#  return $tree;
#}


#$treePath = dirname($this->path);
#while (substr($treePath, 0, 1) == '/') {
#  $treePath = substr($treePath, 1);
#}
# The following lines could also be done with:
# $treePath = DAV::slashify($treePath);
# But apart from that: AFAICS the if() condition will NEVER be false...?
#if (substr($treePath, -1) != '/') {
#  $treePath .= '/';
#}


#$testtree = createTree($treePath, DAV::$REGISTRY->resource('/'));
$testtree = createTree2( DAV::slashify( dirname( $this->path ) ) );
$testfilebrowserpanel = array();
foreach ($members as $member) {
  $owner = BeeHub_Registry::inst()->resource(
    #$member->prop('DAV: owner')->URIs[0]
    $member->user_prop_owner()
  );
//  $group = DAV::$REGISTRY->resource($member->prop('DAV: group')->URIs[0]);
  $testfilebrowserpanel[] = array(
    'path'                => $member->path,
    'name'                => $member->user_prop_displayname(),
    'size'                => $member->user_prop_getcontentlength(),
    'type'                => $member->user_prop_getcontenttype(),
    'date_modified'       => $member->user_prop_getlastmodified(),
    'owner_original_name' => $owner->path,
    'owner_display_name'  => $owner->user_prop_displayname(),
//                                  'group_original_name' => $group->path,
//                                  'group_display_name'  => $group->prop('DAV: displayname')
  );
}

$aclAllowed = $this->property_priv_read(array(DAV::PROP_ACL));
$aclAllowed = $aclAllowed[DAV::PROP_ACL];

$header = '
  <link rel="stylesheet" href="/system/client/resources/css/surfsara/app.css"/>
  <!-- page specific -->
  <!-- <link rel="stylesheet" type="text/css" href="/system/client/resources/css/beehub.css"/> -->
  <link rel="shortcut icon" href="http://www.sara.nl/sites/default/files/sara_url.ico"/>
  <!-- menu bar -->
  <link rel="stylesheet" href="/system/css/beehub.css"/>
';
// TODO make this xml
$footer = '
  <script type="text/javascript">
    var treecontents = ' . json_encode($testtree)  . ';
    var selectedtreenode = \'' . $this->path . '\';
    var filebrowsercontents = ' . json_encode($testfilebrowserpanel) . ';
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

  <!-- <x-compile> -->
    <!-- <x-bootstrap> -->
      <!--script src="/system/client/ext/ext-dev.js"></script-->
      <!--script src="/system/client/bootstrap.js"></script-->
    <!-- </x-bootstrap> -->
    <!--script src="/system/client/app/app.js"></script-->
  <!-- </x-compile> -->
  <script src="/system/client/all-classes.js"></script>
';
include('views/header.php');
include('views/footer.php');
