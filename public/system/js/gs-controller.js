/**
 * Copyright ©2014 SURFsara bv, The Netherlands
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

nl.sara.beehub.gs.Controller = function(view) {  
  this.path = nl.sara.beehub.utils.getPath();
  this.group_or_sponsor = nl.sara.beehub.utils.getGroupOrSponsor(); 
  
  this.view = new nl.sara.beehub.gs.view.View(view, this);
 
  // Workaround for bug in mouse item selection
  $.fn.typeahead.Constructor.prototype.blur = function() {
   var that = this;
   setTimeout(function () { that.hide(); }, 250);
  };
}

nl.sara.beehub.gs.Controller.prototype.addUser = function(user,callback){
  function addUserCallback(status) {
    if (status === 409) {
      alert('You are not allowed to remove all the '+this.group_or_sponsor+' administrators from a '+this.group_or_sponsor+'. Leave at least one '+this.group_or_sponsor+' administrator in the '+this.group_or_sponsor+' or appoint a new '+this.group_or_sponsor+' administrator!');
      return;
    }
    if (status === 403) {
     alert('You are not allowed to perform this action!');
     return;
    }
    if (status !== 200) {
      alert('Something went wrong on the server. No changes were made.');
      return;
    };
    callback();
  }
  
  var client = new nl.sara.webdav.Client();
  client.post(window.location.pathname, addUserCallback, 'add_members[]='+user);
}

nl.sara.beehub.gs.Controller.prototype.changeGroupOrSponsor = function(disp, desc, callbackOk, callbackNotOk){
  var setProps = new Array();
  var displayname = new nl.sara.webdav.Property();
  displayname.namespace = 'DAV:';
  displayname.tagname = 'displayname';
  displayname.setValueAndRebuildXml(disp);
  setProps.push(displayname);
  var description = new nl.sara.webdav.Property();
  description.namespace = 'http://beehub.nl/';
  description.tagname = 'description';
  description.setValueAndRebuildXml(desc);
  setProps.push(description);
  var client = new nl.sara.webdav.Client();
  client.proppatch(
    location.pathname,
    function(status, data) {
      if (status === 207) {
       callbackOk();
      } else {
       calbackNotOk();
      }
    }, setProps);
}

nl.sara.beehub.gs.Controller.prototype.demoteUser = function(user, callbackOk){
  //send request to server
  function callback(status) {
   if (status === 409) {
     alert('You are not allowed to remove all the '+group_or_sponsor+' administrators from a '+group_or_sponsor+'. Leave at least one '+group_or_sponsor+' administrator in the '+group_or_sponsor+' or appoint a new '+group_or_sponsor+' administrator!');  
     return;
   };
   if (status === 403) {
    alert('You are not allowed to perform this action!');  
    return;
   };
   if ( status !== 200 ) {
   alert('Something went wrong on the server. No changes were made.');
   return;
   };
   callbackOk();
  }
    
  var client = new nl.sara.webdav.Client();
  client.post(window.location.pathname, callback, 'delete_admins[]='+user);
}

/*
 * Action when the join button at a group is clicked
 */
nl.sara.beehub.gs.Controller.prototype.joinRequest = function(value, callback) {
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

nl.sara.beehub.gs.Controller.prototype.leaveRequest = function(value, callback){
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
