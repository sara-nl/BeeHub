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
<!-- <h1>Documentation</h1> -->
<ul class="nav nav-tabs" id="top-level-tab">
	<li class="active"><a href="#pane-getting-started" data-toggle="tab">Getting started</a></li>
<!-- 	<li><a href="#pane-sponsors" data-toggle="tab">Sponsors</a></li> -->
	<li><a href="#pane-account" data-toggle="tab">Accounts</a></li>
  <li><a href="#pane-mounting" data-toggle="tab">Connect</a></li>
  <li><a href="#pane-share" data-toggle="tab">Share</a></li>
  <li><a href="#pane-tac" data-toggle="tab">Terms and Conditions</a></li>
  <!--li><a href="#pane-sla" data-toggle="tab">Service Level Agreement</a></li-->
  <li><a href="#pane-backup" data-toggle="tab">Backup policy</a></li>
  <li><a href="#pane-faq" data-toggle="tab">Faq</a></li>
</ul>

<div class="tab-content">
  <div class="tab-pane active" id="pane-getting-started">
  	<h3>Getting started</h3>
	  <ol>
<!-- 	  	<li> -->
<!-- 	  		<b>Find a sponsor</b> -->
<!-- 	  		<ul> -->
<!-- 	  			<li> -->
<!-- 	  				You need at least one sponsor to store your data on BeeHub.  -->
<!-- 	  			</li> -->
<!-- 	  			<li> -->
<!-- 	  				More info: <a id='beehub-docs-find-sponsor'>sponsors</a> -->
<!-- 	  			</li> -->
<!-- 	  		</ul> -->
<!-- 	  	</li> -->
	  	<li>
	  		<b>Create an account</b>
	  		<ul>
	  			<li>
	  				More info: <a id="beehub-docs-create-account">accounts</a>
	  			</li>
	  		</ul>
	  	</li>
	  	<li>
	  			<b>Connect to BeeHub</b>
	  		  <ul>
	  		  	<li>To access your BeeHub data you need to make a connection with BeeHub. There are serveral ways
	  		  			to do this.
	  		  	</li>
	  		  	<li>
	  		  		More info: <a id='beehub-docs-mount'>connect</a>
	  		  	</li>
	  		  </ul>
	  	<li>
	  			<b>Share your data</b>
	  			<ul>
	  				<li>
	  					Your data can be shared to users, groups or the world.
	  				</li>
	  				<li>
	  					More info: <a id='beehub-docs-share'>share</a>
	  				</li>
	  			</ul>
	  	</li>
	  </ol>
  </div> 
  <!--   End getting started tab -->
  <div class="tab-pane" id="pane-sponsors">
  	<h3>Sponsors</h3>
  
   - wat is een sponsor
   - ...
  </div>
  <!--   End sponsors tab -->
  <div class="tab-pane" id="pane-account">
   <h3>Accounts</h3>
   There are two types of accounts:
   <ul><li>
   	<h5>BeeHub account</h5>
   	<p>A BeeHub account is an account created localy on the BeeHub server.<br/></p>
   	<p>With this account you can connect to BeeHub by:</p>
   	  <ul>
   	   	<li>a browser like Firefox, Chrome, Internet Explorer </li>
   	   	<li>a commandline tool like curl</li>
   	   	<li>a WebDAV client like Cyberduck, Nautilus, wdfs, cadaver</p></li>
   	  </ul>
   	<p>You can create a BeeHub account <a href="users">here</a>.</p>
   	</li><li>
   	<h5>SURFconext account</h5>
		<p>If you have a <a href="http://www.surfnet.nl/en/Thema/coin/Pages/default.aspx">SURFconext</a> account you can 
		  also use this account to connect to BeeHub with a browser. Your SURFconext account will be linked to a BeeHub account.
		</p>
		<p>The first time you log on with your SURFconext account you will be asked to link to an existing BeeHub account 
		 or create a new one. This is a one-time action. The next time you can log on directly with your SURFconext account.</p>
		<p>Commandline tools and webdav clients can not use SURFconext authentication yet. For this reason you also need a BeeHub 
		   account besides your SURFconext account.</p>
		</li></ul>
  </div>
  <!--   End account tab -->
  <div class="tab-pane" id="pane-mounting">
  	<h3>Connect to BeeHub</h3> 
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
	 <div class="tab-pane" id="pane-share">
		 <h3>Share your data</h3>    
		 <p>There are two ways to share your data:</p>
		 <ul>
		 	<li>
		 		<b>Create a group</b>
		 		<ul>
		 			<li>A directory with the groupname will be created</li>
		 			<li>By default all group members have all permissions to the contents of this directory</li>
		 			<li>Create or control a group <a href="groups">here</a></li>
		 		</ul>
		 	</li>
		 		<li>
		 		<b>Change an ACL</b>
		 		<ul>
		 			<li>This can be done with the <a href="/home">BeeHub Web Interface</a></li>
		 			<li>See below for more information about the BeeHub Web Interface</li>
		 		</ul>
		 	</li>
		 </ul>
		<br/>
		<h4>BeeHub Web Interface</h4>
		<p>With the <a title="BeeHub Web Interface" href="/home" target="_blank">BeeHub Web Interface</a>  
		it is possible to share your BeeHub data with others. At this moment the best browsers to use are firefox and chrome.</p>
		
		<p>The user interface contains three panels:</p>
		<ul> 
			<li>Directory Panel</li>
			<li>Content Panel</li>
			<li>Access Control List (ACL) Panel</li> 
		</ul>
		
		<p><img class="" title="BeeHub_Panels" src="/system/img/docs/BeeHub_Panels3-300x233.jpg" alt="" width="300" height="233" />
		&nbsp;</p>
		
		<p>When an entry in the directory panel is selected the contents of that directory will be shown in the content panel.
		When an entry in the directory panel or the content panel is selected the ACL panel will be enabled and the ACL will 
		be shown (when available).</p>

		<h5>ACL Explained</h5>
		<p>An <strong>access control list</strong> (<strong>ACL</strong>) is a list of permissions attached to an object  
		(your files or directories).  An ACL specifies which users or groups are granted access to objects.</p>
		
		<p>When an ACL is attached to an object the contents of the ACL will be read from top to bottom. After the first 
		match (grant or deny) the items below this match will not be read anymore. When no match is found access is denied 
		to the object the ACL belongs to.</p>
		
		<p>The BeeHub ACL consists of:</p>
		<ul>
			<li>Protected entries</li>
			<li>Normal entries</li>
			<li>Inherited entries</li>
		</ul>
		
		<p>An ACL starts with the <strong>protected entries. </strong>These are default system entries and can not be changed. 
		In the ACL panel the row is gray. An ACL ends with the <strong>inherited entries</strong>. These entries are inherited 
		from a parent directory of the object.  In the ACL panel the row is gray and in the column inherited the object from 
		which the ACL entry is inherited is shown as a link.</p>
		
		<p>An ACL entry consists of:</p>
		<ul>
			<li>principal</li>
			<li>privileges</li>
			<li>access</li>
			<li>inherited</li>
		</ul>
		
		<p>A <strong>principal</strong> is a user, a group or all (the world). <strong>Privileges </strong>are the 
		permissions (read, write, read ACL and write ACL). <strong>Access</strong> is grant or deny and inherited is
		empty or a link to a parent directory from which the ACL entry is inherited from.</p>
		
		<h5>An Example: grant access for a group to a file</h5>
		<p>Select the directory in the <em>directory panel</em></p>
		
		<p><img title="BeeHub_Select_Directory" src="/system/img/docs/BeeHub_Select_Directory.jpg" alt="" width="103" height="93" /></p>
		
		<p>Select the file in the <em>content panel</em></p>
		
		<p><img title="BeeHub_Select_File" src="/system/img/docs/BeeHub_Select_File-300x62.jpg" alt="" width="300" height="62" /></p>
		<br/>
		<p>Click "Add" in the <em>ACL Panel</em></p>
		
		<p><img title="BeeHub_Add_ACL_Item" src="/system/img/docs/BeeHub_Add_ACL_Item.jpg" alt="" width="178" height="120" /></p>
		
		<p>Select group in the Add ACL Rule window</p>
		
		<p>Search a group Principal.</p>
		
		<p>Select Grant at the access radio button.</p>
		
		<p>Select Read and Write at the privileges checkboxes and click the Ok button</p>
		
		<p><img title="BeeHub_Search_Principal" src="/system/img/docs/BeeHub_Search_Principal1-300x207.jpg" alt="" width="300" height="207" /></p>
		<br/>
		<p>In the <em>ACL panel</em> the ACL item is added. Click save to make this change definitive.</p>
		
		<p><img title="BeeHub_ACL_Save" src="/system/img/docs/BeeHub_ACL_Save2-300x110.jpg" alt="" width="300" height="110" /></p>
		
		<p>All members of the group can now access the file.</p>
  </div>
  <!--   End share tab -->
  <div class="tab-pane" id="pane-faq">
	 <h3>Frequently asked questions</h3> 
     <ol>
	  	<li>
	  		<b>Why do I have .DS_Store files in my BeeHub directories? </b>
	  		<ul>
	  			<li>These files are create by Mac OS X</li>
	  			<li>You can run the following command to disable storing .DS_Store files on network shares:</li>
	  				<ul>
  						<li><code>defaults write com.apple.desktopservices DSDontWriteNetworkStores true</code></li>
	  				</ul>
	  			<li>See <a href="http://en.wikipedia.org/wiki/.DS_Store">wikipedia</a> for more info</li>
	  		</ul>
	  	</li>
	  </ol>
  </div>
  <!--   End share tab -->
  <div class="tab-pane" id="pane-tac">
    <h3>Terms and Conditions</h3>
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
    <h3>Backup policy</h3>
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
