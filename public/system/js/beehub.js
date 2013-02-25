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

  nl.sara.beehub.show_notifications = function(data) {
    notification_window.empty();

    $.each(data, function(key, content) {
      var notification = $('<li class="notification_item"></li>');
      var contentDiv = $('<div class="notification_content" style="float: left"></div>');
      contentDiv.append(content);
      notification.append(contentDiv);
      notification.append('<div class="icon-trash" style="float: right"></div>');
      notification.append('<div style="clear: both"></div>');
      notification_window.append(notification);
    });
    notification_counter.html($('.notification_item', notification_window).length.toString());
  };

  nl.sara.beehub.reload_notifications = function() {
    $.getJSON('/system/notifications.php', nl.sara.beehub.show_notifications);
  };

  setInterval(nl.sara.beehub.reload_notifications, 60000);
})();
