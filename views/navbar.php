    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="<?= htmlspecialchars(BeeHub::$CONFIG['namespace']['system_path'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">BeeHub<sup><small><strong><em>RC1</em></strong></small></sup></a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li id="navbar-li-files"><a href="/">Files</a></li>
              <?php if (BeeHub_Auth::inst()->is_authenticated()) : ?>
                <li id="navbar-li-groups"><a href="<?= htmlspecialchars(BeeHub::$CONFIG['namespace']['groups_path'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">Groups</a></li>
<?php /*                <li id="navbar-li-sponsors"><a href="<?= htmlspecialchars(BeeHub::$CONFIG['namespace']['sponsors_path'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">Sponsors</a></li> */ ?>
              <?php endif; ?>
            </ul>
            <ul class="nav pull-right">
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" id="notification_button" data-toggle="dropdown"><span id="notification_counter">0</span> notifications <span class="caret"></span></a>
                <ul id="notifications" class="dropdown-menu" style="width: 800px; padding: 1em; background-color: #d1e2d2">
                </ul>
              </li>
              <?php if ( $meResource = BeeHub_Auth::inst()->current_user() ) : ?>
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <?= $meResource->prop_displayname() ?> <span class="caret"></span>
                  </a>
                  <ul class="dropdown-menu">
                    <li><a href="<?= DAV::xmlescape($meResource->path) ?>">Profile</a></li>
                    <?php if (@BeeHub_Auth::inst()->surfconext()) : ?>
                      <li><a href="<?= BeeHub::request_uri() . '?logout=yes' ?>">Log out</a></li>
                    <?php endif ?>
                    <li><a href="<?= BeeHub::urlbase(false) . BeeHub::$CONFIG['namespace']['system_path'] ?>">Go anonymous</a></li>
                  </ul>
                </li>
              <?php else : ?>
                <li><a href="<?= BeeHub::$CONFIG['namespace']['users_path'] ?>">Sign up</a></li>
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Log in <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li><a href="<?= BeeHub::urlbase(true) . BeeHub::request_uri() . '?login=passwd' ?>">With username/password</a></li>
                    <?php if (@BeeHub_Auth::inst()->simpleSaml()->isAuthenticated()) : ?>
                      <li><a href="<?= BeeHub::request_uri() . '?logout=yes' ?>">Log out from SURFconext</a></li>
                    <?php else: ?>
                      <li><a href="<?= BeeHub::urlbase(true) . BeeHub::request_uri() . '?login=conext' ?>">With SURFconext</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif ?>
              <li class="beehub-spacer-surfsara-logo visible-desktop"></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="beehub-spacer-navbar-fixed-top visible-desktop"></div>
    <a href="http://www.surfsara.nl/"><img src="/system/img/surfsara.png" class="surfsara-logo visible-desktop" /></a>
