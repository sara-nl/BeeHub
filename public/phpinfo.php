<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  phpinfo();
  exit;
}
?><html><body><form action="phpinfo.php" method="post">
<input type="text" name="name" value=""/>
<input type="submit"/>
</form></body></html>