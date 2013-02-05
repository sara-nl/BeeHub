"use strict";
// in a anonymous function, to not pollute the global namespace:
(function () {
	var active;
	if ( RegExp('^/groups/$').test( window.location.pathname ) ) {
		active = 'groups';
	} else if ( RegExp('^/sponsors/$').test( window.location.pathname ) ) {
		active = 'sponsors';
	} else if ( RegExp('^/users/[^/]+$').test( window.location.pathname ) ) {
		active = 'profile';
	} else if ( RegExp('^/system/$').test( window.location.pathname ) ) {
		active = 'beehub';
	} else if ( RegExp('^/users/+$').test( window.location.pathname ) ) {
    active = 'signup';
  } else {
		active = 'files';
	}
	$('.navbar-fixed-top li#navbar-li-' + active).addClass('active');
})();