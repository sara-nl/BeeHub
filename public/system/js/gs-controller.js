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

/**
 * Add user request
 * 
 * @param {String}    user           Username to add
 * @param {Function}  callbackOk     Function to call succeeded
 * @param {Function}  callbackNotOk  Function to call when something went wrong
 * 
 */
nl.sara.beehub.gs.Controller.prototype.addUser = function(user, callbackOk, callbackNotOk){
  var controller = this;
  
  function addUserCallback(status) {
    if (status === 409) {
      alert('You are not allowed to remove all the '+controller.group_or_sponsor+' administrators from a '+controller.group_or_sponsor+'. Leave at least one '+controller.group_or_sponsor+' administrator in the '+controller.group_or_sponsor+' or appoint a new '+controller.group_or_sponsor+' administrator!');
      callbackNotOk();
      return;
    }
    if (status === 403) {
     alert('You are not allowed to perform this action!');
     callbackNotOk();
     return;
    }
    if (status !== 200) {
      alert('Something went wrong on the server. No changes were made.');
      return;
    };
    callbackOk();
  }
  
  var client = new nl.sara.webdav.Client();
  client.post(window.location.pathname, addUserCallback, 'add_members[]='+user);
}

/**
 * Remove user request
 * 
 * @param {String}    user           Username to remove
 * @param {Function}  callbackOk     Function to call when succeeded
 * @param {Function}  callbackNotOk  Function to call when something went wrong
 * 
 */
nl.sara.beehub.gs.Controller.prototype.removeUser = function(user,callbackOk, callbackNotOk){
 var controller = this;
//send request to server
 var client = new nl.sara.webdav.Client();
 
 function callback(status) {
   if (status === 409) {
     alert('You are not allowed to remove all the '+controller.group_or_sponsor+' administrators from a '+controller.group_or_sponsor+'. Leave at least one '+controller.group_or_sponsor+' administrator in the '+controller.group_or_sponsor+' or appoint a new '+controller.group_or_sponsor+' administrator!');
     callbackNotOk();
     return;
   }
   if (status !== 200) {
     alert('Something went wrong on the server. No changes were made.');
     callbackNotOk();
     return;
   };
   callbackOk();
 };
 client.post(window.location.pathname, callback , 'delete_members[]='+user);
}

/**
 * Promote user request
 * 
 * @param {String}    user           Username to promote
 * @param {Function}  callbackOk     Function to call when succeeded
 * @param {Function}  callbackNotOk  Function to call when something went wrong
 * 
 */
nl.sara.beehub.gs.Controller.prototype.promoteUser = function(user,callbackOk, callbackNotOk) {
 // send request to server
 var client = new nl.sara.webdav.Client();
 
 function callback(status){
  if (status === 403) {
    alert('You are not allowed to perform this action!');
    callbackNotOk();
    return;
  }
  if (status !== 200) {
    alert('Something went wrong on the server. No changes were made.');
    callbackNotOk();
    return;
  };
  callbackOk();
 }
 client.post(window.location.pathname, callback, 'add_admins[]='+user);
}

/**
 * Demote user request
 * 
 * @param {String}    user           Username to demote
 * @param {Function}  callbackOk     Function to call when succeeded
 * @param {Function}  callbackNotOk  Function to call when something went wrong
 * 
 */
nl.sara.beehub.gs.Controller.prototype.demoteUser = function(user, callbackOk){
  var controller = this;
  
  //send request to server
  function callback(status) {
   if (status === 409) {
     alert('You are not allowed to remove all the '+controller.group_or_sponsor+' administrators from a '+controller.group_or_sponsor+'. Leave at least one '+controller.group_or_sponsor+' administrator in the '+controller.group_or_sponsor+' or appoint a new '+controller.group_or_sponsor+' administrator!');  
     callbackNotOk();
     return;
   };
   if (status === 403) {
    alert('You are not allowed to perform this action!'); 
    callbackNotOk();
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

/**
 * Join user request
 * 
 * @param {String}    user           Username to join
 * @param {Function}  callbackOk     Function to call when succeeded
 * @param {Function}  callbackNotOk  Function to call when something went wrong
 * 
 */
nl.sara.beehub.gs.Controller.prototype.joinRequest = function(user, callbackOk, callbackNotOk) {
  // closure for ajax request
  function joinCallback(status) {
    if (status !== 200) {
      alert('Something went wrong on the server. No changes were made.');
      callbackNotOk();
      return;
    }
    callbackOk();  
  };
  
 // Send leave request to server
 var client = new nl.sara.webdav.Client();
 client.post(user, joinCallback , 'join=1');
};

/**
 * Leave user request
 * 
 * @param {String}    user           Username to leave
 * @param {Function}  callbackOk     Function to call when succeeded
 * @param {Function}  callbackNotOk  Function to call when something went wrong
 * 
 */
nl.sara.beehub.gs.Controller.prototype.leaveRequest = function(user, callbackOk, callbackNotOk){
  function leaveCallback(status) {
    if ( status === 409 ) {
      alert("You can't leave this group, you're the last administrator! Don't leave your herd without a shepherd, please appoint a new administrator before leaving them!");
      callbackNotOk();
      return;
    } else if ( status !== 200 ) {
      alert('Something went wrong on the server. No changes were made.');
      callbackNotOk();
      return;
    };
    callbackOk();  
  };
  
  var client = new nl.sara.webdav.Client();
  client.post(user, leaveCallback, 'leave=1');
};

/**
 * Change group or sponsorname
 * 
 * @param {Location}  location       Group or sponsor location to change
 * @param {String}    disp           New displayname
 * @param {String}    desc           New description
 * @param {Function}  callbackOk     Function to call when succeeded
 * @param {Function}  callbackNotOk  Function to call when something went wrong
 * 
 */
nl.sara.beehub.gs.Controller.prototype.changeGroupOrSponsor = function(location, disp, desc, callbackOk, callbackNotOk){
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
  
  function callback(status, data) {
    if (status === 207) {
     callbackOk();
    } else {
     calbackNotOk();
    }
  };
  client.proppatch(location, callback, setProps);
}

/**
 * Get usage data
 * 
 * @param {Location}  location       Group or sponsor location to get usage data from
 * @param {Function}  callbackOk     Function to call when succeeded
 * @param {Function}  callbackNotOk  Function to call when something went wrong
 * 
 */
nl.sara.beehub.gs.Controller.prototype.getUsageData = function(location, callbackOk, callbackNotOk){
  function callback(error,inputdata){
    // Stop when error
    if (error) {
      alert(error);
      callbackNotOk();
      return
    }
    var data = inputdata[0].usage;
    callbackOk(data);   
  }
  d3.json(location, callback);
};
  
  

