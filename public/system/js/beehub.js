"use strict";
// in a anonymous function, to not pollute the global namespace:
(function () {
	var active;
	if ( RegExp('^/system/groups/[^/]*$').test( window.location.pathname ) ) {
		active = 'groups';
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

if (nl === undefined) {
  var nl = {};
}
if (nl.sara === undefined) {
  nl.sara = {};
}
if (nl.sara.beehub === undefined) {
  nl.sara.beehub = {};
}
(function() {
  var notification_window = $('#notifications');
  var notification_counter = $('#notification_counter');

  /**
   * Returns the correct notification for a specific type
   */
  function create_notification(type, data) {
    var notification = $('<div class="notification_item well"></div>');
    var client = new nl.sara.webdav.Client();

    switch(type) {
      case 'group_invitation':
        notification.html('<div style="float:left">You are invited to join the group \'' + data.displayname + '\'</div><div style="float:right"><button class="btn btn-success">Join</button> <button class="btn btn-danger">Decline</button></div><div style="clear:both"></div>');
        $('.btn-success', notification).click(function() {
          client.post(data.group, nl.sara.beehub.reload_notifications, 'join=1');
        });
        $('.btn-danger', notification).click(function() {
          client.post(data.group, nl.sara.beehub.reload_notifications, 'leave=1');
        });
        break;
      case 'group_request':
        notification.html('<div style="float:left">' + data.user_displayname + ' requests a membership of group \'' + data.group_displayname + '\'</div><div style="float:right"><button class="btn btn-success">Accept</button> <button class="btn btn-danger">Decline</button></div><div style="clear:both"></div>');
        $('.btn-success', notification).click(function() {
          client.post(data.group, nl.sara.beehub.reload_notifications, 'add_members[]=' + data.user);
        });
        $('.btn-danger', notification).click(function() {
          client.post(data.group, nl.sara.beehub.reload_notifications, 'delete_members[]=' + data.user);
        });
        break;
      case 'sponsor_request':
        notification.html('<div style="float:left">' + data.user_displayname + ' requests requests membership of sponsor \'' + data.sponsor_displayname + '\'</div><div style="float:right"><button class="btn btn-success">Accept</button> <button class="btn btn-danger">Decline</button></div><div style="clear:both"></div>');
        $('.btn-success', notification).click(function() {
          client.post(data.sponsor, nl.sara.beehub.reload_notifications, 'add_members[]=' + data.user);
        });
        $('.btn-danger', notification).click(function() {
          client.post(data.sponsor, nl.sara.beehub.reload_notifications, 'delete_members[]=' + data.user);
        });
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
    $.getJSON('/system/notifications.php', nl.sara.beehub.show_notifications);
  };

  setInterval(nl.sara.beehub.reload_notifications, 60000);
})();