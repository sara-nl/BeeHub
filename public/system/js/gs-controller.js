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

(function() {
 nl.sara.beehub.gs.Controller = function() {  
   this.path = nl.sara.beehub.utils.getPath();
   this.group_or_sponsor = nl.sara.beehub.utils.getGroupOrSponsor();
   this.views = [];
    
   var controller = this;
   
   // Workaround for bug in mouse item selection
   $.fn.typeahead.Constructor.prototype.blur = function() {
    var that = this;
    setTimeout(function () { that.hide(); }, 250);
   };
 };
 
 nl.sara.beehub.gs.Controller.STATUS_LAST_ADMIN = 409;
 nl.sara.beehub.gs.Controller.STATUS_NOT_ALLOWED = 403;
 nl.sara.beehub.gs.Controller.STATUS_OK = 200;
 
 /**
  * Add view to controller
  * 
  * @param {nl.sara.beehub.gs.view} view View to add
  * 
  */
 nl.sara.beehub.gs.Controller.prototype.addView = function(view){
   this.views.push(view);
 };
 
 /**
  * Add user request
  * 
  * @param {String}    user           Username to add
  */
 nl.sara.beehub.gs.Controller.prototype.addUser = function(user) {
   var views = this.views;
    
   function callback(status, data) {
     if (status === nl.sara.beehub.gs.Controller.STATUS_OK) {
       views.forEach(function(view) {
         if (view.updateAddUserSucceeded !== undefined){
           view.updateAddUserSucceeded(user);
         };
       });
     } else {
       views.forEach(function(view) {
         if (view.updateAddUserFailed !== undefined) {
           view.updateAddUserFailed(status);
         };
       }); 
     };
   };
 
   var client = new nl.sara.webdav.Client();
   client.post(window.location.pathname, callback, 'add_members[]='+user);
 };
 
 /**
  * Remove user request
  * 
  * @param {String}    user           Username to remove
  */
 nl.sara.beehub.gs.Controller.prototype.removeUser = function(user){
  var views = this.views;
  
  function callback(status, data) {
    if (status === nl.sara.beehub.gs.Controller.STATUS_OK) {
      views.forEach(function(view) {
        if (view.updateRemoveUserSucceeded !== undefined) {
          view.updateRemoveUserSucceeded(user);
        };
      });
    } else {
      views.forEach(function(view) {
        if (view.updateRemoveUserFailed !== undefined) {
          view.updateRemoveUserFailed(status);
        };
      }); 
    };
  };
  
 //send request to server
  var client = new nl.sara.webdav.Client();
  client.post(window.location.pathname, callback , 'delete_members[]='+user);
 };
 
 /**
  * Promote user request
  * 
  * @param {String}    user           Username to promote
  */
 nl.sara.beehub.gs.Controller.prototype.promoteUser = function(user) { 
  var views = this.views;

  function callback(status, data) {
    if (status === nl.sara.beehub.gs.Controller.STATUS_OK) {
      views.forEach(function(view) {
        if (view.updatePromoteUserSucceeded !== undefined) {
          view.updatePromoteUserSucceeded(user);
        };
      });
    } else {
      views.forEach(function(view) {
        if (view.updatePromoteUserFailed !== undefined) {
          view.updatePromoteUserFailed(status);
        };
      }); 
    };
  };
  
  // send request to server
  var client = new nl.sara.webdav.Client();
  client.post(window.location.pathname, callback, 'add_admins[]='+user);
 };
 
 /**
  * Demote user request
  * 
  * @param {String}    user           Username to demote
  * 
  */
 nl.sara.beehub.gs.Controller.prototype.demoteUser = function(user) {  
   var views = this.views;

   function callback(status, data) {
     if (status === nl.sara.beehub.gs.Controller.STATUS_OK) {
       views.forEach(function(view) {
         if (view.updateDemoteUserSucceeded !== undefined) {
           view.updateDemoteUserSucceeded(user);
         };
       });
     } else {
       views.forEach(function(view) {
         if (view.updateDemoteUserFailed !== undefined) {
           view.updateDemoteUserFailed(status);
         };
       }); 
     };
   };
     
   var client = new nl.sara.webdav.Client();
   client.post(window.location.pathname, callback, 'delete_admins[]='+user);
 };
 
 /**
  * Join user request
  * 
  * @param {String}    user           Username to join
  * 
  */
 nl.sara.beehub.gs.Controller.prototype.joinRequest = function(user) {
   var views = this.views;

   function callback(status, data) {
     if (status === nl.sara.beehub.gs.Controller.STATUS_OK) {
       views.forEach(function(view) {
         if (view.updateJoinSucceeded !== undefined) {
           view.updateJoinSucceeded(user);
         };
       });
     } else {
       views.forEach(function(view) {
         if (view.updateJoinFailed !== undefined) {
           view.updateJoinFailed(status);
         };
       }); 
     };
   };
  // Send leave request to server
  var client = new nl.sara.webdav.Client();
  client.post(user, callback , 'join=1');
 };
 
 /**
  * Leave user request
  * 
  * @param {String}    user           Username to leave
  * 
  */
 nl.sara.beehub.gs.Controller.prototype.leaveRequest = function(user){
   var views = this.views;

   function callback(status, data) {
     if (status === nl.sara.beehub.gs.Controller.STATUS_OK) {
       views.forEach(function(view) {
         if (view.updateLeaveSucceeded !== undefined) {
           view.updateLeaveSucceeded(user);
         };
       });
     } else {
       views.forEach(function(view) {
         if (view.updateLeaveFailed !== undefined) {
           view.updateLeaveFailed(status);
         };
       }); 
     };
   };
   var client = new nl.sara.webdav.Client();
   client.post(user, callback, 'leave=1');
 };
 
 /**
  * Change group or sponsorname
  * 
  * @param {Location}  location       Group or sponsor location to change
  * @param {String}    disp           New displayname
  * @param {String}    desc           New description 
  */
 nl.sara.beehub.gs.Controller.prototype.changeGroupOrSponsor = function(location, disp, desc){
   var views = this.views
   
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
       views.forEach(function(view) {
         if (view.updateChangeGroupSponsorSucceeded !== undefined) {
           view.updateChangeGroupSponsorSucceeded();
         };
       });
     } else {
       views.forEach(function(view) {
         if (view.updateChangeGroupSponsorFailed !== undefined) {
           view.updateChangeGroupSponsorFailed(status);
         };
       }); 
     };
   };
   client.proppatch(location, callback, setProps);
 };
 
 /**
  * Get usage data
  * 
  * @param {Location}  location       Group or sponsor location to get usage data from
  * 
  */
 nl.sara.beehub.gs.Controller.prototype.getUsageData = function(location){
   var views = this.views;

   function callback(error, data) {
     if (error) {
       views.forEach(function(view) {
         if (view.updateUsageDataFailed !== undefined) {
           view.updateUsageDataFailed(error);
         };
       });
     } else {
       views.forEach(function(view) {
         if (view.updateUsageDataSucceeded !== undefined) {
           view.updateUsageDataSucceeded(data[0].usage);
         };
       }); 
     };
   };
   d3.json(location, callback);
 };
})();  