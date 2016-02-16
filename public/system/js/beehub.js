/**
 * Copyright ©2013 SURFsara bv, The Netherlands
 *
 * This file is part of the beehub client
 *
 * beehub client is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * beehub-client is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with beehub.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Niek Bosch (niek.bosch@surfsara.nl)
 * @author Pieter van Beek
 */

"use strict";

if (nl === undefined) {
  var nl = {};
}
if (nl.sara === undefined) {
  nl.sara = {};
}
if (nl.sara.beehub === undefined) {
  nl.sara.beehub = {};
}
if (nl.sara.beehub.codec === undefined) {
  nl.sara.beehub.codec = {};
}
if (nl.sara.beehub.utils === undefined) {
  nl.sara.beehub.utils = {};
}

// Some static values change these also in /src/beehub.php
nl.sara.beehub.users_path            = "/system/users/";
nl.sara.beehub.groups_path           = "/system/groups/";
nl.sara.beehub.sponsors_path         = "/system/sponsors/";
nl.sara.beehub.forbidden_group_names = [
  "home",
  "system"
];

nl.sara.beehub.postAuth ='';

/**
 * URI encodes all elements in a full path
 *
 * In other words, URI encode a string, except for the slashes (/)
 *
 * @param   {String}  path  The path to URI encode
 * @return  {String}        The encoded path
 */
nl.sara.beehub.encodeURIFullPath = function( path ) {
  var pathElements = path.split('/'); // BeeHub does not accept / in a 
// file name, this is always a path delimiter
  for ( var index in pathElements ) {
    pathElements[ index ] = encodeURIComponent( pathElements[ index ] );
  }
  return pathElements.join('/');
}

/**
 * Retrieve new POST authentication code from server.
 * 
 */
nl.sara.beehub.retrieveNewPostAuth = function(){
 var client = new nl.sara.webdav.Client();
 // Set the new POST authentication code
 client.get( '/system/?POST_auth_code', function( status, data ) {
   if ( status === 200 ) {
     nl.sara.beehub.postAuth = data;
   } else if ( status !== 403 ) {
     alert("Something went wrong. Please reload the page. When this does not solve the problem contact helpdesk@surfsara.nl");
   };
 });
};

// in a anonymous function, to not pollute the global namespace:
(function () {
	var active;
	if ( RegExp('^/system/groups/[^/]*$').test( window.location.pathname ) ) {
		active = 'groups';
	} else if ( RegExp('^/system/docs.*$').test( window.location.pathname ) ) {
    active = 'docs';
  } else if ( RegExp('^/system/sponsors/[^/]*$').test( window.location.pathname ) ) {
    active = 'sponsors';
  } else if ( RegExp('^/system/users/[^/]+$').test( window.location.pathname ) ) {
		active = 'profile';
	} else if ( RegExp('^/system/$').test( window.location.pathname ) ) {
		active = 'beehub';
	} else if ( RegExp('^/system/users/$').test( window.location.pathname ) ) {
    active = 'signup';
  } else {
		active = 'files';
	}
	$('.navbar-fixed-top li#navbar-li-' + active).addClass('active');
})();

(function() {
  var notification_window = $('#notifications');
  var notification_counter = $('#notification_counter');

  /**
   * Returns the correct notification for a specific type
   * 
   * @param  {String}  type  The type of notification to create
   * @param  {mixed}   data  The data, belonging to the notification (differs for each notification type)
   */
  function create_notification(type, data) {
    var notification = $('<div class="notification_item well"></div>');
    var client = new nl.sara.webdav.Client();

    switch(type) {
      case 'group_invitation':
        notification.html('<div style="float:left">You are invited to join the group \'<span name="groupname"></span>\'</div><div style="float:right"><button class="btn btn-success">Join</button> <button class="btn btn-danger">Decline</button></div><div style="clear:both"></div>');
        $('span[name="groupname"]', notification).text(data.displayname);
        $('.btn-success', notification).click(function() {
          client.post(data.group, nl.sara.beehub.reload_notifications, 'join=1&POST_auth_code='+ nl.sara.beehub.postAuth);
        });
        $('.btn-danger', notification).click(function() {
          client.post(data.group, nl.sara.beehub.reload_notifications, 'leave=1&POST_auth_code='+ nl.sara.beehub.postAuth);
        });
        break;
      case 'group_request':
        notification.html('<div style="float:left"><span name="user_displayname"></span> (<span name="user_email"></span>) requests a membership of group \'<span name="group_displayname"></span>\'</div><div style="float:right"><button class="btn btn-success">Accept</button> <button class="btn btn-danger">Decline</button></div><div style="clear:both"></div>');
        $('span[name="user_displayname"]', notification).text(data.user_displayname);
        $('span[name="user_email"]', notification).text(data.user_email);
        $('span[name="group_displayname"]', notification).text(data.group_displayname);
        $('.btn-success', notification).click(function() {
          client.post(data.group, nl.sara.beehub.reload_notifications, 'add_members[]=' + data.user+'&POST_auth_code='+ nl.sara.beehub.postAuth);
        });
        $('.btn-danger', notification).click(function() {
          client.post(data.group, nl.sara.beehub.reload_notifications, 'delete_members[]=' + data.user+'&POST_auth_code='+ nl.sara.beehub.postAuth);
        });
        break;
      case 'sponsor_request':
        notification.html('<div style="float:left"><span name="user_displayname"></span> (<span name="user_email"></span>) requests requests membership of sponsor \'<span name="sponsor_displayname"></span>\'</div><div style="float:right"><button class="btn btn-success">Accept</button> <button class="btn btn-danger">Decline</button></div><div style="clear:both"></div>');
        $('span[name="user_displayname"]', notification).text(data.user_displayname);
        $('span[name="user_email"]', notification).text(data.user_email);
        $('span[name="sponsor_displayname"]', notification).text(data.sponsor_displayname);
        $('.btn-success', notification).click(function() {
          client.post(data.sponsor, nl.sara.beehub.reload_notifications, 'add_members[]=' + data.user+'&POST_auth_code='+ nl.sara.beehub.postAuth);
        });
        $('.btn-danger', notification).click(function() {
          client.post(data.sponsor, nl.sara.beehub.reload_notifications, 'delete_members[]=' + data.user+'&POST_auth_code='+ nl.sara.beehub.postAuth);
        });
        break;
      case 'no_sponsor':
        notification.html('You don\'t have a sponsor, therefore you can\'t store any data on BeeHub! See the (<a href="' + nl.sara.beehub.sponsors_path + '">sponsors page</a> to request a membership of a sponsor.');
        break;
    }

    return notification;
  }

  nl.sara.beehub.show_notifications = function(data) {
    notification_window.empty();

    if (data.length === 0) {
      notification_window.append('There are no notifications');
      notification_counter.html('0');
    }else{
      $.each(data, function(key, content) {
        notification_window.append(create_notification(content.type, content.data));
      });
      notification_counter.html($('.notification_item', notification_window).length.toString());
    }
  };

  nl.sara.beehub.reload_notifications = function() {
    nl.sara.beehub.retrieveNewPostAuth();
    $.getJSON('/system/notifications.php', nl.sara.beehub.show_notifications);
  };

  setInterval(nl.sara.beehub.reload_notifications, 60000);
})();

// If nl.sara.webdav.codec.GetlastmodifiedCodec is already defined, we have a namespace clash!

if (nl.sara.beehub.codec.Sponsor !== undefined) {
  throw new nl.sara.beehub.Exception('Namespace nl.sara.webdav.codec.Sponsor already taken, could not load JavaScript library for WebDAV connectivity.', nl.sara.webdav.Exception.NAMESPACE_TAKEN);
}

/**
 * @class Adds a codec that converts DAV: sponsor to String
 * @augments nl.sara.webdav.Codec
 */
nl.sara.beehub.codec.Sponsor = new nl.sara.webdav.Codec();
nl.sara.beehub.codec.Sponsor.namespace = 'http://beehub.nl/';
nl.sara.beehub.codec.Sponsor.tagname = 'sponsor';

nl.sara.beehub.codec.Sponsor.fromXML = function(nodelist) {
  var node;
  for (var counter = 0; counter < nodelist.length; counter++) {
    node = nodelist.item(counter);
    if ( (node.nodeType === 1) && (node.namespaceURI === 'DAV:') && (node.localName === 'href') ) {
      var childnode = node.childNodes.item(0);
      if ((childnode.nodeType === 3) || (childnode.nodeType === 4)) { // Make sure text and CDATA content is stored
        return childnode.nodeValue;
      }else{ // If the node is not text or CDATA, then we don't parse a value at all
        return null;
      }
    }
  }
};

nl.sara.beehub.codec.Sponsor.toXML = function(value, xmlDoc){
  var cdata = xmlDoc.createCDATASection( value );
  var href = xmlDoc.createElementNS( 'DAV:', 'href' );
  href.appendChild( cdata );
  xmlDoc.documentElement.appendChild( href );
  return xmlDoc;
};

nl.sara.webdav.Property.addCodec(nl.sara.beehub.codec.Sponsor);

/**
* @class Adds a codec that converts http://beehub.nl/: sponsor-membership to an array with the uri's object
* @augments nl.sara.webdav.Codec
*/
nl.sara.beehub.codec.Sponsor_membership_Codec = new nl.sara.webdav.Codec();
nl.sara.beehub.codec.Sponsor_membership_Codec.namespace = 'http://beehub.nl/';
nl.sara.beehub.codec.Sponsor_membership_Codec.tagname = 'sponsor-membership';

nl.sara.beehub.codec.Sponsor_membership_Codec.fromXML = function(nodelist) {
  var collections = [];
  for ( var key = 0; key < nodelist.length; key++ ) {
    var node = nodelist.item( key );
    if ( ( node.nodeType === 1 ) && ( node.localName === 'href' ) && ( node.namespaceURI === 'DAV:' ) ) { // Only extract data from DAV: href nodes
      var href = '';
      for ( var subkey = 0; subkey < node.childNodes.length; subkey++ ) {
        var childNode = node.childNodes.item( subkey );
        if ( ( childNode.nodeType === 3 ) || ( childNode.nodeType === 4 ) ) { // Make sure text and CDATA content is stored
          href += childNode.nodeValue;
        }
      }
      collections.push( href );
    }
  }
  return collections;
};

nl.sara.beehub.codec.Sponsor_membership_Codec.toXML = function(value, xmlDoc){
  for ( var key in value ) {
    var href = xmlDoc.createElementNS( 'DAV:', 'href' );
    href.appendChild( xmlDoc.createCDATASection( value[ key ] ) );
    xmlDoc.documentElement.appendChild( href );
  }
  return xmlDoc;
};

nl.sara.webdav.Property.addCodec(nl.sara.beehub.codec.Sponsor_membership_Codec);

/**
 * Calculate size from bytes to readable size
 * 
 * @param {Integer} bytes      Bytes to calculate
 * @param {Integer} precision  Precision
 * 
 */
nl.sara.beehub.utils.bytesToSize = function(bytes, precision)
{  
    var kilobyte = 1000;
    var megabyte = kilobyte * 1000;
    var gigabyte = megabyte * 1000;
    var terabyte = gigabyte * 1000;
   
    if ((bytes >= 0) && (bytes < kilobyte)) {
        return bytes + ' B';
 
    } else if ((bytes >= kilobyte) && (bytes < megabyte)) {
        return (bytes / kilobyte).toFixed(precision) + ' kB';
 
    } else if ((bytes >= megabyte) && (bytes < gigabyte)) {
        return (bytes / megabyte).toFixed(precision) + ' MB';
 
    } else if ((bytes >= gigabyte) && (bytes < terabyte)) {
        return (bytes / gigabyte).toFixed(precision) + ' GB';
 
    } else if (bytes >= terabyte) {
        return (bytes / terabyte).toFixed(precision) + ' TB';
 
    } else {
        return bytes + ' B';
    }
};

/**
 * Returns path with slash at the end
 */
nl.sara.beehub.utils.getPath = function() {
 var path = decodeURIComponent(location.pathname);
 // add slash to the end of path
 if (!path.match(/\/$/)) {
   path=path+'/'; 
 } 
 return path;
}

nl.sara.beehub.utils.getGroupOrSponsor = function() {
  // Check if it is group or sponsor page 
  var group_or_sponsor="";
  var path = nl.sara.beehub.utils.getPath();
  if ( path.substr(0, nl.sara.beehub.groups_path.length) === nl.sara.beehub.groups_path ) {
    group_or_sponsor = "group";
  } else if ( path.substr(0, nl.sara.beehub.sponsors_path.length) === nl.sara.beehub.sponsors_path ) {
    group_or_sponsor = "sponsor";
  }
  return group_or_sponsor;
};

/*
 * Returns displayname from object
 * 
 * @param   {String}  name  object
 * 
 * @return  {String}        Displayname
 */
nl.sara.beehub.utils.getDisplayName = function(name){
  if (name === undefined) {
    return "";
  };
  if (name.indexOf(nl.sara.beehub.users_path) !== -1){
    var displayName = nl.sara.beehub.principals.users[name.replace(nl.sara.beehub.users_path,'')];
    return displayName;
  };
  if (name.indexOf(nl.sara.beehub.groups_path) !== -1){
    var displayName = nl.sara.beehub.principals.groups[name.replace(nl.sara.beehub.groups_path,'')];
    return displayName;
  };
  if (name.indexOf(nl.sara.beehub.sponsors_path) !== -1){
    var displayName = nl.sara.beehub.principals.sponsors[name.replace(nl.sara.beehub.sponsors_path,'')];
    return displayName;
  };
  return "Unknown displayname";
};
// End of file
