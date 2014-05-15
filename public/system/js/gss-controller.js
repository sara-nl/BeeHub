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
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */

"use strict";

nl.sara.beehub.gss.Controller = function() {  
  this.path = location.pathname;
  // add slash to the end of path
  if (!this.path.match(/\/$/)) {
    this.path=this.path+'/'; 
  } 
  
  // Check if it is group or sponsor page 
  this.group_or_sponsor="";
  if ( this.path.substr(0, nl.sara.beehub.groups_path.length) === nl.sara.beehub.groups_path ) {
    this.group_or_sponsor = "group";
  } else if ( this.path.substr(0, nl.sara.beehub.sponsors_path.length) === nl.sara.beehub.sponsors_path ) {
    this.group_or_sponsor = "sponsor";
  }
}

/*
 * Action when the join button at a group is clicked
 */
nl.sara.beehub.gss.Controller.prototype.joinRequest = function(value, callback) {
  // closure for ajax request
  function joinCallback(status) {
    if (status !== 200) {
      alert('Something went wrong on the server. No changes were made.');
      return;
    }
    callback();  
  };
  
	// Send leave request to server
	var client = new nl.sara.webdav.Client();
	client.post(value, joinCallback , 'join=1');
};

nl.sara.beehub.gss.Controller.prototype.leaveRequest = function(value, callback){
  function leaveCallback(status) {
    if ( status === 409 ) {
      alert("You can't leave this group, you're the last administrator! Don't leave your herd without a shepherd, please appoint a new administrator before leaving them!");
      return;
    } else if ( status !== 200 ) {
      alert('Something went wrong on the server. No changes were made.');
      return;
    };
    callback();  
  };
  
  var client = new nl.sara.webdav.Client();
		client.post(value, leaveCallback, 'leave=1');
};