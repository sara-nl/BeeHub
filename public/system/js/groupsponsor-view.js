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
 */

"use strict";

(function() { 
  /**
  * @class Group and sponsor view
  * 
  * @author Laura Leistikow (laura.leistikow@surfsara.nl)
  * 
  */
 nl.sara.beehub.gs.view.GroupSponsorView = function(controller) {
   var view = this;
   this.controller = controller;
   controller.addView(view);
   setHandlers(view);
 };
 
 /**
  * Set handlers in view
  */
 function setHandlers(view) {
  var map = {};
  
  // Search user
  view.invitedUser = '';
  
  $('#bh-gs-invite-typeahead').typeahead({
  source: function (query, process) {
    // implementation
  var users = [];
  map = {};
  $.each(nl.sara.beehub.principals.users, function (userName, displayName) {
      map[displayName+" ("+userName+")"] = userName;
      users.push(displayName+" ("+userName+")");
   });
   process(users);
  },
  updater: function (item) {
   view.invitedUser=map[item];
   return item;
  },
  matcher: function (item) {
   // implementation
   if ( item.toLowerCase().indexOf(this.query.trim().toLowerCase()) !== -1 ) {
    return true;
   }
  },
  sorter: function (items) {
   // implementation
   return items.sort();
  },
  highlighter: function (item) {
   // implementation
  var regex = new RegExp( '(' + this.query + ')', 'gi' );
  return item.replace( regex, "<strong>$1</strong>" );
  }
  // check if username is valid
  }).blur(function(){
   if( map[ $( this ).val() ] === null ) {
     $('#bh-gs-invite-typeahead').val('');
  view.invitedUser = ""; 
   }
  });
  
  // Action when the invite button is clicked
  $('#bh-gs-invite-gs-form').submit(handleAddUser.bind(view));
  
  // Action when the save button is clicked
  $('#bh-gs-edit-form').submit(handleSubmit.bind(view));
  
  // Demote group or sponsor handler
  $('.bh-gs-demote-gs').click(handleDemote.bind(view));
  
  // Promote group or sponsor handler
  $('.bh-gs-promote-gs').click(handlePromote.bind(view));
  
  // Remove group or sponsor handler
  $('.bh-gs-remove-gs').on('click', handleRemove.bind(view));
  
  //Change tab listeners
  // Usage tab sponsors
  $('a[href="#bh-gs-panel-usage"]').unbind('click').click(createUsageView.bind(view));
 }
 
 /**
  * Handle submit button click (change group)
  * 
  * @param {Event} e
  */
 function handleSubmit(e) {
   var view = this;
   e.preventDefault();
      
   var displayName = $('input[name="displayname"]').val();
   var description = $('textarea[name="description"]').val();
   
   view.changeGroupSponsorStarted = true;
   
   nl.sara.beehub.gs.view.utils.mask(true);
   view.controller.changeGroupOrSponsor(location.pathname, displayName, description);
 };
 
 /**
  * Update view after succesfully group changed request
  * 
  */
 nl.sara.beehub.gs.view.GroupSponsorView.prototype.updateChangeGroupSponsorSucceeded = function(){
   var view = this;
   var group_or_sponsor = nl.sara.beehub.utils.getGroupOrSponsor();
   
   var newDisplayname = $('input[name="displayname"]').val();
   var newDescription = $('textarea[name="description"]').val();
   $('#bh-gs-display-name-value').text(newDisplayname);
   $('#bh-gs-description-value').text(newDescription);
   var orgValue= $('#bh-gs-display-name').attr("data-org-name");
   var newHeader = $('#bh-gs-header').html().replace(orgValue,newDisplayname);
   $('#bh-gs-header').html(newHeader);
   $('#bh-gs-display-name').attr("data-org-name", newDisplayname);
   $('#bh-gs-sponsor-description').attr("data-org-name", newDescription);
   if (view.changeGroupSponsorStarted) {
     alert("The "+group_or_sponsor+" is changed.")
     view.changeGroupSponsorStarted = false;
     nl.sara.beehub.gs.view.utils.mask(false);
   }
 }
 
 /**
  * Update view after failed group changed request
  * 
  * @param {Integer} status Failure status from server
  * 
  */
 nl.sara.beehub.gs.view.GroupSponsorView.prototype.updateChangeGroupSponsorFailed = function(status) {
   var view = this;
   var group_or_sponsor = nl.sara.beehub.utils.getGroupOrSponsor();

   $('input[name="displayname"]').val($('#bh-gs-display-name').attr("data-org-name")); 
   $('textarea[name="description"]').val($('#bh-gs-sponsor-description').attr("data-org-name"));
   
   if (view.changeGroupSponsorStarted) {
     alert( "Something went wrong. The "+group_or_sponsor+" is not changed." );
     view.changeGroupSponsorStarted = false;
     nl.sara.beehub.gs.view.utils.mask(false);
   }
 }
   
 /**
  * Handle add user button click
  * 
  * @param {Event} event
  * 
  */
 function handleAddUser(event) {
  event.preventDefault();
  
  var view = this;

  var group_or_sponsor = nl.sara.beehub.utils.getGroupOrSponsor();
  var invitedUser = this.invitedUser;
 
  if (view.invitedUser !== "") {
   view.addUserStarted = true;
   nl.sara.beehub.gs.view.utils.mask(true);
   this.controller.addUser(view.invitedUser);
  };
 };
 
 /**
  * Update view after succesfull add user request
  *
  */
 nl.sara.beehub.gs.view.GroupSponsorView.prototype.updateAddUserSucceeded = function(user){
   var view = this;
   
   $('#bh-gs-invite-typeahead').val("");
 // TODO remove reload page
 //  nl.sara.beehub.gs.view.utils.mask(false);
   if (nl.sara.beehub.utils.getGroupOrSponsor() === "group") {
     alert(nl.sara.beehub.principals.users[user] + " has been added.");
   } else if (nl.sara.beehub.utils.getGroupOrSponsor() === "sponsor") {
     alert(nl.sara.beehub.principals.users[user] + " has been added.");
   }
   // TODO remove if when reload will be removed
   if (view.addUserStarted) {
     window.location.reload();
   };
   view.addUserStarted = false;
 }
 
 /**
  * Update view after failed add user request
  * 
  * @param {Integer} status Failure status from server
  * 
  */
 nl.sara.beehub.gs.view.GroupSponsorView.prototype.updateAddUserFailed = function(status){
   var view = this;
   if (view.addUserStarted) {
    switch(status) {
     case nl.sara.beehub.gs.Controller.STATUS_LAST_ADMIN:
      alert(nl.sara.beehub.gs.view.utils.STATUS_LAST_ADMIN_ALERT);
      break;
     case nl.sara.beehub.gs.Controller.STATUS_NOT_ALLOWED:
       alert(nl.sara.beehub.gs.view.utils.STATUS_NOT_ALLOWED_ALERT);
      break;
     default:
       alert(nl.sara.beehub.gs.view.utils.STATUS_UNKNOWN_ERROR_ALERT);
    }; 
    view.addUserStarted = false;
    nl.sara.beehub.gs.view.utils.mask(false);
   };
 }

 /**
  * Handles demote group or sponsor
  * 
  * @param {Event} e
  */
 function handleDemote(event) {
   var user = $(event.target).val();
   var view = this;
  
   view.demoteUserStarted = true;
   nl.sara.beehub.gs.view.utils.mask(true);
   view.controller.demoteUser(user);
 };
 
 /**
  * Update view after succesfull demote user request
  * 
  */
 nl.sara.beehub.gs.view.GroupSponsorView.prototype.updateDemoteUserSucceeded = function(user){
  var view = this;
//  var button = $('button[type="button"][value="'+user+'"][class="bs-gs-demote-gs"')
  var button = $('button[type="button"][value="'+user+'"].bh-gs-demote-gs');

   //if succeeded, change button to promote to admin
   button.text("Promote to admin");
   button.removeClass("bh-gs-demote-gs").addClass("bh-gs-promote-gs");
   button.unbind('click').on('click',handlePromote.bind(view));
  
   if (view.demoteUserStarted) {
     nl.sara.beehub.gs.view.utils.mask(false);
     view.demoteUserStarted = false;
   };
 };
 
 /**
  * Update view after failed demote user request
  * 
  * @param {Integer} status Failure status from server
  * 
  */
 nl.sara.beehub.gs.view.GroupSponsorView.prototype.updateDemoteUserFailed = function(status){
   var view = this;

   if (view.demoteUserStarted) {
    switch(status) {
     case nl.sara.beehub.gs.Controller.STATUS_LAST_ADMIN:
      alert(nl.sara.beehub.gs.view.utils.STATUS_LAST_ADMIN_ALERT);
      break;
     case nl.sara.beehub.gs.Controller.STATUS_NOT_ALLOWED:
       alert(nl.sara.beehub.gs.view.utils.STATUS_NOT_ALLOWED_ALERT);
      break;
     default:
       alert(nl.sara.beehub.gs.view.utils.STATUS_UNKNOWN_ERROR_ALERT);
    }; 
    nl.sara.beehub.gs.view.utils.mask(false);
    view.demoteUserStarted = false;
   };
 }
 
 /**
  * Handle promote to admin button
  * 
  * @param {Event} e
  */
 function handlePromote(event){
  var view = this;
  
  view.promoteUserStarted = true;
  nl.sara.beehub.gs.view.utils.mask(true);
  this.controller.promoteUser($(event.target).val());
 };
 
 /** 
  * Update view after successfull promote user request 
  */
 nl.sara.beehub.gs.view.GroupSponsorView.prototype.updatePromoteUserSucceeded = function(user){
   var view = this;
   
   var button = $('button[type="button"][value="'+user+'"].bh-gs-promote-gs');

   //if succeeded, change button to promote to admin
   button.text("Demote to member");
   button.removeClass("bh-gs-promote-gs").addClass("bh-gs-demote-gs");
   button.unbind('click').on('click',handleDemote.bind(view));
   
   if (view.promoteUserStarted) {
     view.promoteUserStarted = true;
     nl.sara.beehub.gs.view.utils.mask(false);
   };
 };
 
 /**
  * Update view after failed promote user request
  * 
  * @param {Integer} status Failure status from server
  * 
  */
 nl.sara.beehub.gs.view.GroupSponsorView.prototype.updatePromoteUserFailed = function(status){
   var view = this;
   if (view.promoteUserStarted) {
    switch(status) {
     case nl.sara.beehub.gs.Controller.STATUS_NOT_ALLOWED:
       alert(nl.sara.beehub.gs.view.utils.STATUS_NOT_ALLOWED_ALERT);
      break;
     default:
       alert(nl.sara.beehub.gs.view.utils.STATUS_UNKNOWN_ERROR_ALERT);
    }; 
    nl.sara.beehub.gs.view.utils.mask(false);
    view.promoteUserStarted = false;
   };
 };
    
 /**
  * Handle remove button
  * 
  * @param {Event} e
  */
 function handleRemove(e){
   var view = this;
   
   view.removeUserStarted = true;
   nl.sara.beehub.gs.view.utils.mask(true);
   this.controller.removeUser($(e.target).val());
 }; // End of bh-gs-remove-gs event listener
 
 /**
  * Update view after successfull remove user request
  * 
  * @param {String} user Removed user
  * 
  */
 nl.sara.beehub.gs.view.GroupSponsorView.prototype.updateRemoveUserSucceeded = function(user){ 
   var view = this;
   
   $('#bh-gs-user-'+user).remove();
   if (view.removeUserStarted) {
     nl.sara.beehub.gs.view.utils.mask(false);
     view.removeUserStarted = false;
   };
 }
 
 /**
  * Update view after failed remove user request
  * 
  * @param {Integer} status Failure status from server
  * 
  */
 nl.sara.beehub.gs.view.GroupSponsorView.prototype.updateRemoveUserFailed = function(status){ 
   var view = this;
   if (view.removeUserStarted) {
    switch(status) {
     case nl.sara.beehub.gs.Controller.STATUS_LAST_ADMIN:
      alert(nl.sara.beehub.gs.view.utils.STATUS_LAST_ADMIN_ALERT);
      break;
     case nl.sara.beehub.gs.Controller.STATUS_NOT_ALLOWED:
       alert(nl.sara.beehub.gs.view.utils.STATUS_NOT_ALLOWED_ALERT);
      break;
     default:
       alert(nl.sara.beehub.gs.view.utils.STATUS_UNKNOWN_ERROR_ALERT);
    }; 
     nl.sara.beehub.gs.view.utils.mask(false);
     view.removeUserStarted = false;
   };
 };
    
 /**
  * Create vertical bar chart with usage data.
  * 
  */
 function createUsageView() { 
  var view = this;
  
  view.getUsageDataStarted = true;
  nl.sara.beehub.gs.view.utils.mask(true);
  view.controller.getUsageData(location.href+"?usage");
 }
 
 /**
  * Update view after successfull get usage data request
  * 
  * @param {JSON} data Usage data from server
  * 
  */
 nl.sara.beehub.gs.view.GroupSponsorView.prototype.updateUsageDataSucceeded = function(data){ 
   var view = this;
   
   // Stop when sponsor has no users
   if (data.length === 0){
     $('#bh-gs-panel-usage').html('<h5 style="margin-left:10px;">No storage used for this sponsor.</h5>'); 
     return;
   };
   
   $('#bh-gs-panel-usage').html('<h5 style="margin-left:160px;">Total data usage per user in GB</h5><div id="bh-gs-usage-div"></div>');

   var valueLabelWidth = 80; // space reserved for value labels (right)
   var barHeight = 20; // height of one bar
   var barLabelWidth = 150; // space reserved for bar labels
   var barLabelPadding = 10; // padding between bar and bar labels (left)
   var gridLabelHeight = 18; // space reserved for gridline labels
   var gridChartOffset = 3; // space between start of grid and first bar
   var maxBarWidth = 420; // width of the bar with the max value
    
   // accessor functions 
   var barLabel = function(d) { return nl.sara.beehub.principals['users'][d['props.DAV: owner']]; };
   var barValueGb = function(d) { return parseFloat(d['usage']/1024/1024/1024); };
   var barValue = function(d) { return parseFloat(d['usage']); };

   // sorting
   var sortedData = data.sort(function(a,b){
     return d3.descending(barValue(a), barValue(b));
   });
   
   // scales
   var yScale = d3.scale.ordinal().domain(d3.range(0, sortedData.length)).rangeBands([0, sortedData.length * barHeight]);
   var y = function(d, i) { return yScale(i); };
   var yText = function(d, i) { return y(d, i) + yScale.rangeBand() / 2; };
   var x = d3.scale.linear().domain([0, d3.max(sortedData, barValueGb)]).range([0, maxBarWidth]);
   
   // svg container element
   var chart = d3.select('#bh-gs-usage-div').append("svg")
     .attr('width', maxBarWidth + barLabelWidth + valueLabelWidth)
     .attr('height', gridLabelHeight + gridChartOffset + sortedData.length * barHeight);  
   
   // grid line labels
   var gridContainer = chart.append('g')
     .attr('transform', 'translate(' + barLabelWidth + ',' + gridLabelHeight + ')'); 
   
   gridContainer.selectAll("text").data(x.ticks(10)).enter().append("text")
     .attr("x", x)
     .attr("dy", -3)
     .attr("text-anchor", "middle")
     .attr("font-size", "10px")
     .text(String);
   
   // vertical grid lines
   gridContainer.selectAll("line").data(x.ticks(10)).enter().append("line")
     .attr("x1", x)
     .attr("x2", x)
     .attr("y1", 0)
     .attr("y2", yScale.rangeExtent()[1] + gridChartOffset)
     .style("stroke", "#ccc");
   
   // bar labels
   var labelsContainer = chart.append('g')
     .attr('transform', 'translate(' + (barLabelWidth - barLabelPadding) + ',' + (gridLabelHeight + gridChartOffset) + ')'); 
   
   labelsContainer.selectAll('text').data(sortedData).enter().append('text')
     .attr('y', yText)
     .attr('stroke', 'none')
     .attr('fill', '#414042')
     .attr("dy", ".35em") // vertical-align: middle
     .attr('text-anchor', 'end')
     .attr("font-size", "13px")
     .text(barLabel);
   
   // bars
   var barsContainer = chart.append('g')
     .attr('transform', 'translate(' + barLabelWidth + ',' + (gridLabelHeight + gridChartOffset) + ')'); 
   
   barsContainer.selectAll("rect").data(sortedData).enter().append("rect")
     .attr('y', y)
     .attr('height', yScale.rangeBand())
     .attr('width', function(d) { return x(barValueGb(d)); })
     .attr('stroke', 'white')
     .attr('fill', '#85B88E');
   
   // bar value labels
   barsContainer.selectAll("text").data(sortedData).enter().append("text")
     .attr("x", function(d) { return x(barValueGb(d)); })
     .attr("y", yText)
     .attr("dx", 3) // padding-left
     .attr("dy", ".35em") // vertical-align: middle
     .attr("text-anchor", "start") // text-align: right
     .attr("fill", "#414042")
     .attr("stroke", "none")
     .attr("font-size", "13px")
     .text(function(d) { return nl.sara.beehub.utils.bytesToSize(d3.round(barValue(d), 2),2); });
   
   // start line
   barsContainer.append("line")
     .attr("y1", -gridChartOffset)
     .attr("y2", yScale.rangeExtent()[1] + gridChartOffset)
     .style("stroke", "" +"#000");
   
   if (view.getUsageDataStarted) {
     nl.sara.beehub.gs.view.utils.mask(false);
     view.getUsageDataStarted = false;
   }
 };
 
 /**
  * Update view after failed get usage data request
  * 
  * @param {String} error Failure error from server
  * 
  */
 nl.sara.beehub.gs.view.GroupSponsorView.prototype.updateUsageDataFailed = function(error){ 
   if (view.getUsageDataStarted) {
     alert(error);
     nl.sara.beehub.gs.view.utils.mask(false);
     view.getUsageDataStarted = false;
   }
 };
})();