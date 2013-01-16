<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>BeeHub</title>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />
    <link rel="stylesheet" href="/system/css/bootstrap.css" />
    <link rel="stylesheet" href="/system/css/bootstrap-responsive.css" />
    <link rel="stylesheet" href="/system/css/beehub.css" />
    <?= $header ?>
  </head>
  <body>
    <div class="beehub-navbar">
      <div class="beehub-navbar-inner">
        <ul>
          <li<?= ($active == 'beehub' ? ' class="active"' : '') ?>><a href="<?= htmlspecialchars(BeeHub::$CONFIG['webdav_namespace']['homepage'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">BeeHub</a></li>
          <li<?= ($active == 'profile' ? ' class="active"' : '') ?>><a href="<?= htmlspecialchars(BeeHub_ACL_Provider::inst()->CURRENT_USER_PRINCIPAL, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">Profile</a></li>
          <li<?= ($active == 'groups' ? ' class="active"' : '') ?>><a href="<?= htmlspecialchars(BeeHub::$CONFIG['webdav_namespace']['groups_path'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">Groups</a></li>
          <li<?= ($active == 'sponsors' ? ' class="active"' : '') ?>><a href="<?= htmlspecialchars(BeeHub::$CONFIG['webdav_namespace']['sponsors_path'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">Sponsors</a></li>
          <li<?= ($active == 'files' ? ' class="active"' : '') ?>><a href="/">Files</a></li>
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
