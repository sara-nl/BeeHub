<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>BeeHub</title>
    <link href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />
    <link href="/system/css/bootstrap.css" rel="stylesheet" />
    <link href="/system/css/bootstrap-responsive.css" rel="stylesheet" />
    <link href="/system/css/beehub.css" rel="stylesheet" />
    <?= $header ?>
  </head>
  <body>
    <div class="beehub-navbar">
      <div class="beehub-navbar-inner">
        <ul>
          <li<?= ($active == 'beehub' ? ' class="active"' : '') ?>><a href="/system.php">BeeHub</a></li>
          <li<?= ($active == 'profile' ? ' class="active"' : '') ?>><a href="/system/users.php">Profile</a></li>
          <li<?= ($active == 'groups' ? ' class="active"' : '') ?>><a href="/system/groups.php">Groups</a></li>
          <li<?= ($active == 'sponsors' ? ' class="active"' : '') ?>><a href="/system/sponsors.php">Sponsors</a></li>
          <li><a href="http://beehub-devel.grid.sara.nl:8080/">Files</a></li>
        </ul>
      </div>
    </div>

    <div class="container">
      <?= $content ?>
    </div>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
    <?= $footer ?>
  </body>
</html>
