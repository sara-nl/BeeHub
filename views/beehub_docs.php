<?php
/*
 * Available variables:
 * $sponsor  The BeeHub_Sponsor instance representing the current sponsor
 * $members  A 2 dimensional array containing all members. Each member array
 *   contains 5 keys: user_name, displayname, admin, invited and requested.
 *   For example: $members[0]['user_name']
 */

$active = "groups";
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

require 'views/header.php';
?>
<h1>Documentation</h1>
<p>There’ll be some documentation here...</p>
<?php
$footer='';
require 'views/footer.php';
