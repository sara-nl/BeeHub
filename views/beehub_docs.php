<?php

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
$footer= '<script type="text/javascript" src="/system/js/docs.js"></script>';

require 'views/header.php';
?>
<div class="container-fluid">
<div class="row-fluid">
<div class="span12">
<h1>Documentation</h1>
<ul class="nav nav-tabs" id="top-level-tab">
  <li class="active"><a href="#pane-mounting" data-toggle="tab">Mounting BeeHub</a></li>
  <li><a href="#pane-tac" data-toggle="tab">Terms and Conditions</a></li>
  <!--li><a href="#pane-sla" data-toggle="tab">Service Level Agreement</a></li-->
  <li><a href="#pane-backup" data-toggle="tab">Backup policy</a></li>
</ul>

<div class="tab-content">
  <div class="tab-pane active" id="pane-mounting">
    <p>After you’ve completed getting an account, a new BeeHub account has been created for you. What happens next depends on which operating system you're running. Please jump to the appropriate section on this page. If your favorite operating system or client is not on this page, please <a href="mailto:support@beehub.nl" rel="nofollow">let us know</a>.</p>
    <ul class="nav nav-tabs" id="mounting-tab">
      <li class="active"><a href="#pane-mounting-windows" data-toggle="tab">Windows</a></li>
      <li><a href="#pane-mounting-osx" data-toggle="tab">Mac OS-X</a></li>
      <li><a href="#pane-mounting-linux" data-toggle="tab">Linux</a></li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane active" id="pane-mounting-windows">
        <ol>
          <li>Open "Computer" from (the right part of) the "start" menu.</li>
          <li>Right click with your mouse on "Computer" (in the left part of the window).</li>
          <li>Select "Add a Network Location".</li>
          <li>
            Click "Next":<br/>
            <img style="margin-bottom: 1em;" src="/system/img/docs/windows_step1_add_network_location.png"/><br />
          </li>
          <li>
            Select "Choose a custom network location" and click "Next":<br/>
            <img style="margin-bottom: 1em;" src="/system/img/docs/windows_step2_where_to_create.png"/><br />
          </li>
          <li>
            Enter <tt>https://beehub.nl/</tt> and click "Next":<br/>
            <img style="margin-bottom: 1em;" src="/system/img/docs/windows_step3_location.png"/><br />
          </li>
          <li>
            Enter your BeeHub user name and password and click "OK":<br/>
            <img style="margin-bottom: 1em;" src="/system/img/docs/windows_step4_authentication.png"/><br />
          </li>
          <li>
            Enter a name for your BeeHub connection. The default is probably fine, but you can call the connection anything you like:<br/>
            <img style="margin-bottom: 1em;" src="/system/img/docs/windows_step5_name.png"/><br />
          </li>
          <li>
            Click "Finish":<br/>
            <img style="margin-bottom: 1em;" src="/system/img/docs/windows_step6_finish.png"/><br />
          </li>
        </ol>
        <p>You now have created a BeeHup connection. Click "Start", and then "My Network Places" to connect the next time.</p>
        <p>
          Double click your BeeHub connection:<br/>
          <img style="margin-bottom: 1em;" src="/system/img/docs/windows_step7_computer.png"/><br />
        </p>
        <p>
          Now you can browse your files like in any other folder on your computer.
        </p>
      </div>
      <div class="tab-pane" id="pane-mounting-osx">
        <ol>
          <li>In the finder, select <strong>Connect to Server…</strong> from the <strong>Go</strong> menu.</li>
          <li>Enter <tt>https://beehub.nl/</tt> in the <strong>Server Address</strong> input field, and click <strong>Connect</strong>.</li>
          <li>Provide your login and password. BeeHub is now mounted as an extra volume.</li>
        </ol>
      </div>
      <div class="tab-pane" id="pane-mounting-linux">
        <p>Under Linux, there are quadruzillion ways to mount BeeHub. The most common ones are documented below.</p>
        <ul class="nav nav-tabs" id="mounting-linux-tab">
          <li class="active"><a href="#pane-mounting-linux-nautilus" data-toggle="tab">Nautilus</a></li>
          <li><a href="#pane-mounting-linux-dolphin" data-toggle="tab">Dolphin &amp; Konqueror</a></li>
          <li><a href="#pane-mounting-linux-wdfs" data-toggle="tab">wdfs</a></li>
          <li><a href="#pane-mounting-linux-cadaver" data-toggle="tab">cadaver</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="pane-mounting-linux-nautilus">
            <ol>
              <li>
                At the topbar, choose <strong>Places → Connect to server…</strong>:<br/>
                <img style="margin-bottom: 1em;" src="/system/img/docs/nautilus_connect_to_server.jpg"/>
              </li>
              <li>
                Connect with <strong>Secure WebDAV (HTTPS)</strong> to <tt>beehub.nl</tt> on port 443:<br/>
                <img style="margin-bottom: 1em;" src="/system/img/docs/nautilus_connect_to_server_https1.jpg"/>
              </li>
              <li>
                Now you can browse your files:<br/>
                <img style="margin-bottom: 1em;" src="/system/img/docs/nautilus_file_browser.jpg"/>
              </li>
            </ol>
          </div>
          <div class="tab-pane" id="pane-mounting-linux-dolphin">
            <ol>
              <li>Enter <tt>remote:/</tt> into the navigation bar in Dolphin or Konqueror.</li>
              <li>Click <strong>Add Network Folder</strong></li>
              <li>You then have a choice of WebDAV, FTP, Microsoft Windows network drive, or SSH.
                Choose <strong>WebDAV</strong></li>
              <li>The server name is <tt>beehub.nl</tt>. Enter username, and password information as requested.</li>
              <li>Click <strong>Save &amp; Connect</strong>.</li>
            </ol>
          </div>
          <div class="tab-pane" id="pane-mounting-linux-wdfs">
            <p>Firstly, as root, make sure you have the proper packages installed, create a mountpoint, and mount BeeHub:</p>
            <pre>$&gt; yum install fuse
$&gt; yum install wdfs
$&gt; mkdir /mnt/beehub
$&gt; wdfs https://beehub.nl/ /mnt/webdav -o username=******,password=******,allow_other</pre>
            <p>The <tt>allow_other</tt> option is essential; without it, only root can view the mounted files.
              If you omit the username and password, you’ll be prompted for them.</p>
            <p>The remote file system is now mounted. To unmount just do <code>umount /mnt/webdav</code> as you would with any mounted file system.</p>
          </div>
          <div class="tab-pane" id="pane-mounting-linux-cadaver">
<a href="http://www.webdav.org/cadaver/">Cadaver</a> is an Open Source, command-line, WebDAV client for UN*X:
<pre>$&gt; <b>cadaver https://beehub.nl/</b>
Authentication required for BeeHub on server `beehub.nl':
Username: <b>johndoe</b>
Password:
dav:/> <strong>help</strong>
Available commands:
 ls         cd         pwd        put        get        mget       mput
 edit       less       mkcol      cat        delete     rmcol      copy
 move       lock       unlock     discover   steal      showlocks  version
 checkin    checkout   uncheckout history    label      propnames  chexec
 propget    propdel    propset    search     set        open       close
 echo       quit       unset      lcd        lls        lpwd       logout
 help       describe   about
Aliases: rm=delete, mkdir=mkcol, mv=move, cp=copy, more=less, quit=exit=bye
dav:/> <b>▍</b></pre>
<p>Some commands relevant to content management, include:</p>
<ul>
  <li>Upload a file: <code>put filename</code></li>
  <li>Download a file: <code>get filename</code></li>
  <li>Upload multiple files at once: <code>mput common*</code>, where <tt>common</tt> is the part of the filename that all files being uploaded have in common. For example, to upload all files with names that start with <tt>hr</tt>, such as <tt>hr_benefits</tt>, <tt>hr_policies</tt>, <tt>hr_forms</tt>, the command would be: <code>mput hr*</code></li>
  <li>Download multiple files at once: <code>mget common*</code></li>
  <li>Create a folder: <code>mkcol new_folder_name</code></li>
  <li>Delete a folder: <code>rmcol folder_name</code></li>
  <li>Rename a file: <code>move filename new_filename</code></li>
  <li>Move a file: <code>move filename folder_name</code></li>
  <li>Copy a file: <code>copy filename new_filename</code></li>
  <li>Delete a file: <code>delete filename</code></li>
  <li>Lock a file: <code>lock filename</code></li>
  <li>Unlock a file: <code>unlock filename</code></li>
</ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="tab-pane" id="pane-tac">
    <h2>Terms and Conditions</h2>
    <p>Researchers affiliated with Dutch research centers, universities and academical medical centers can register with BeeHub and use 100GB of storage for scientific purposes. SURFsara can at any time decide to decrease or increase this quota. If your quota is decreased, we will not remove any data directly. If we intent to delete data, we will notify you two weeks in advance on the registered e-mail address.</p>
    <p>If you require more space, you could either apply for storage space through e-infra. See <a href="https://www.surfsara.nl/systems/beehub/new-users">the SURFsara website</a> for more information on this.</p>
    <p>If you are not affiliated to a Dutch research center or university, you could contact us for a sales quote by sending an e-mail to <a href="mailto:support@beehub.nl">support@beehub.nl</a>.</p>
    <p><strong>By registering with <em>BeeHub</em> you shall be deemed to accept these conditions of use:</strong></p>
    <ol>
      <li>You shall only use BeeHub to perform work, or transmit or store data consistent with the stated goals and policies of your sponsor and in compliance with these conditions of use.</li>
      <li>You shall not use BeeHub for any unlawful purpose and not (attempt to) breach or circumvent any BeeHub administrative or security controls. You shall respect copyright and confidentiality agreements and protect your BeeHub credentials (e.g. private keys, passwords), sensitive data and files.</li>
      <li>You shall immediately report any known or suspected security breach or misuse of BeeHub or BeeHub credentials to <a href="mailto:support@beehub.nl">support@beehub.nl</a>.</li>
      <li>Use of BeeHub is at your own risk. There is no guarantee that BeeHub will be available at any time or that it will suit any purpose.</li>
      <li>Logged information, including information provided by you for registration purposes, shall be used for administrative, operational, accounting, monitoring and security purposes only. Although efforts are made to maintain confidentiality, no guarantees are given.</li>
      <li>BeeHub operators are entitled to regulate and terminate access for administrative, operational and security purposes and you shall immediately comply with their instructions.</li>
      <li>You are liable for the consequences of any violation by you of these conditions of use.</li>
    </ol>
    <p>Please also note our <a href="#pane-backup" class="second_backup_policy_link">backup policy!</a></p>
  </div>
  <div class="tab-pane" id="pane-sla">
    <h2>Service Level Agreement (SLA)</h2>
  </div>
  <div class="tab-pane" id="pane-backup">
    <h2>Backup policy</h2>
    <p>All data on BeeHub is backup on a daily basis. At 23:00h an incremental backup is made, copying all changes to the backup system.</p>
    <ul>
      <li>The most recent version of files will be kept in backup indefinitely.</li>
      <li>Older versions of files will be kept for 21 days.</li>
      <li>If a file is removed, old versions will be kept for 30 days. This means that after 30 days a removed file is no longer recoverable!</li>
    </ul>
    <p>Please note that the backup system only checks once a day for different versions of a file. New files and changes within a file which are removed before 23:00h the same day can not be recovered from backup.</p>
  </div>
</div>

</div></div></div><!-- cell, row, and outer container-fluid -->
<?php
require 'views/footer.php';
