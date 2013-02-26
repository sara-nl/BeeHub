<?php
require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'beehub.php');

$auth = BeeHub_Auth::inst();

$auth->handle_authentication(false);
header('Content-type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$notifications = BeeHub::notifications();

print(json_encode($notifications));