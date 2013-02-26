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
  function create_notification(type, message) {
    var notification = $('<li class="notification_item"></li>');
    switch(type) {
      case 'double_authentication':
        var contentDiv = $('<div class="notification_content" style="float: left"></div>');
        contentDiv.append(message);
        notification.append(contentDiv);
        notification.append('<div class="icon-ok" style="float: right"></div>');
        $('.icon-ok', notification).click(function() {
          var client = new nl.sara.webdav.Client(undefined, true);
          client.post(location.href, nl.sara.beehub.reload_notifications, 'saml_connect=1');
        });
        notification.append('<div style="clear: both"></div>');
        break;
      default:
        var contentDiv = $('<div class="notification_content" style="float: left"></div>');
        contentDiv.append(message);
        notification.append('<div class="icon-trash" style="float: right"></div>');
        notification.append(contentDiv);
        notification.append('<div style="clear: both"></div>');
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
        notification_window.append(create_notification(content.type, content.message));
      });
      notification_counter.html($('.notification_item', notification_window).length.toString());
    }
  };

  nl.sara.beehub.reload_notifications = function() {
    $.getJSON('/system/notifications.php', nl.sara.beehub.show_notifications);
  };

  setInterval(nl.sara.beehub.reload_notifications, 60000);
})();