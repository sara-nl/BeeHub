/*
 * Copyright ©2013 SARA bv, The Netherlands
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
 */
"use strict";

/** 
 * Beehub Client
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */

// Create the namespace if that's not done yet
if (nl === undefined) {
  /** @namespace */
  var nl = {};
}
if (nl.sara === undefined) {
  /** @namespace */
  nl.sara = {};
}
if (nl.sara.beehub === undefined) {
  /** @namespace The entire client is in this namespace. */
  nl.sara.beehub = {};
}
if (nl.sara.beehub.controller === undefined) {
  /** @namespace Controller of all classes */
  nl.sara.beehub.controller = {};
}
if (nl.sara.beehub.view === undefined) {
  /** @namespace Holds all the view classes */
  nl.sara.beehub.view = {};
}
if (nl.sara.beehub.view.content === undefined) {
  /** @namespace Content view */
  nl.sara.beehub.view.content = {};
}
if (nl.sara.beehub.view.tree === undefined) {
  /** @namespace Tree view */
  nl.sara.beehub.view.tree = {};
}
if (nl.sara.beehub.view.dialog === undefined) {
  /** @namespace Dialog view */
  nl.sara.beehub.view.dialog = {};
}
if (nl.sara.beehub.view.acl === undefined) {
  /** @namespace Acl view */
  nl.sara.beehub.view.acl = {};
}

// On load
$(function() {
	// solving bug: https://github.com/twitter/bootstrap/issues/6094
	// conflict bootstrap and jquery
	var btn = $.fn.button.noConflict(); // reverts $.fn.button to jqueryui btn
	$.fn.btn = btn; // assigns bootstrap button functionality to $.fn.btn
	// Init all views
	nl.sara.beehub.view.init();
});