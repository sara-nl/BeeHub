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

/**
 * @class Group and sponsor view
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 * 
 */
nl.sara.beehub.gs.view.GroupSponsorView = function(controller, parent) {
  this.controller = controller;
  this.parent = parent
  this.init();
};


/**
 * Initialize view
 */
nl.sara.beehub.gs.view.GroupSponsorView.prototype.init = function() { 
  this.setHandlers();
};

/**
 * Set handlers in view
 */
nl.sara.beehub.gs.view.GroupSponsorView.prototype.setHandlers = function() {
  var map = {};
  
  // Search user
  var view = this;
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
  $('#bh-gs-invite-gs-form').submit(this.handleAddUser.bind(this));
 
  // Action when the save button is clicked
  $('#bh-gs-edit-form').submit(this.handleSubmit.bind(this));
  
  // Demote group or sponsor handler
  $('.bh-gs-demote-gs').click(this.handleDemote.bind(this));
  
  // Promote group or sponsor handler
  $('.bh-gs-promote-gs').click(this.handlePromote.bind(this));
  
  // Remove group or sponsor handler
  $('.bh-gs-remove-gs').on('click', this.handleRemove.bind(this));
  
  //Change tab listeners
   // Usage tab sponsors
   $('a[href="#bh-gs-panel-usage"]').unbind('click').click(this.createUsageView.bind(this));
}

/**
 * Handle submit button click
 * 
 * @param {Event} e
 */
nl.sara.beehub.gs.view.GroupSponsorView.prototype.handleSubmit = function (e) {
  e.preventDefault();
  
  var group_or_sponsor = this.controller.group_or_sponsor;
    
  function callbackOk(){
   var newDisplayname = $('input[name="displayname"]').val();
   var newDescription = $('textarea[name="description"]').val();
   $('#bh-gs-display-name-value').text(newDisplayname);
   $('#bh-gs-description-value').text(newDescription);
   var orgValue= $('#bh-gs-display-name').attr("data-org-name");
   var newHeader = $('#bh-gs-header').html().replace(orgValue,newDisplayname);
   $('#bh-gs-header').html(newHeader);
   $('#bh-gs-display-name').attr("data-org-name", newDisplayname);
   $('#bh-gs-sponsor-description').attr("data-org-name", newDescription);
   alert("The "+group_or_sponsor+" is changed.")
  };
  
  function callbackNotOk(){
    alert( "Something went wrong. The "+group_or_sponsor+" is not changed." );
    $('input[name="displayname"]').val($('#bh-gs-display-name').attr("data-org-name")); 
    $('textarea[name="description"]').val($('#bh-gs-sponsor-description').attr("data-org-name"));
  };
  
  var displayName = $('input[name="displayname"]').val();
  var description = $('textarea[name="description"]').val();
  
  this.controller.changeGroupOrSponsor(displayName, description, callbackOk, callbackNotOk);
};
  
/**
 * Handle add user button click
 * 
 * @param {Event} event
 * 
 */
nl.sara.beehub.gs.view.GroupSponsorView.prototype.handleAddUser = function (event) {
 event.preventDefault();

 var group_or_sponsor = this.controller.group_or_sponsor;
 var invitedUser = this.invitedUser;
  
 function callback() {
  $('#bh-gs-invite-typeahead').val("");
  $("#bh-dir-mask-transparant").hide();
  if (group_or_sponsor === "group") {
    alert(nl.sara.beehub.principals.users[invitedUser] + " has been invited.");
  } else if (group_or_sponsor === "sponsor") {
    alert(nl.sara.beehub.principals.users[invitedUser] + " has been added.");
    window.location.reload();
  }
 }

 if (this.invitedUser !== "") {
  $("#bh-dir-mask-transparant").show();
  this.controller.addUser(this.invitedUser, callback);
 };
};

/**
 * Handles demote group or sponsor
 * 
 * @param {Event} e
 */
nl.sara.beehub.gs.view.GroupSponsorView.prototype.handleDemote = function(event) {
  var button = $(event.target);
  var view = this;
  
  function callbackOk(){
    //if succeeded, change button to promote to admin
    var promotebutton = $('<button type="button" value="'+button.val()+'" class="btn btn-primary bh-gs-promote-gs">Promote to admin</button>');
    promotebutton.click(view.handlePromote.bind(view));
    var cell = button.parent('td');
    cell.prepend(promotebutton);
    button.remove();
  };

  this.controller.demoteUser(button.val(), callbackOk);
};

nl.sara.beehub.gs.view.GroupSponsorView.prototype.handlePromote = function(event){
     var button = $(event.target);
   // send request to server
     var client = new nl.sara.webdav.Client();
     
     // Closure for ajax request
     function callback(button) {
       return function(status){
          if (status === 403) {
            alert('You are not allowed to perform this action!');
            return;
          }
          if (status !== 200) {
            alert('Something went wrong on the server. No changes were made.');
            return;
          };
          var demotebutton = $('<button type="button" value="'+button.val()+'" class="btn btn-primary bh-gs-demote-gs">Demote to member</button>');
          demotebutton.click(handleDemote);
          var cell = button.parent('td');
          cell.prepend(demotebutton);
          button.remove();
        };
     }
    client.post(window.location.pathname, callback(button), 'add_admins[]='+button.val());
    };
    
nl.sara.beehub.gs.view.GroupSponsorView.prototype.handleRemove = function(button){
  var view = this;
  
   // send request to server
      var client = new nl.sara.webdav.Client();
      
      function callback(group_or_sponsor) {
        return function(status){
          if (status === 409) {
            alert('You are not allowed to remove all the '+group_or_sponsor+' administrators from a '+group_or_sponsor+'. Leave at least one '+group_or_sponsor+' administrator in the '+group_or_sponsor+' or appoint a new '+group_or_sponsor+' administrator!');
            return;
          }
          if (status !== 200) {
            alert('Something went wrong on the server. No changes were made.');
            return;
          };
          $('#bh-gs-user-'+button.val()).remove();
        };
      }
      client.post(window.location.pathname, callback(group_or_sponsor) , 'delete_members[]='+button.val());
    }; // End of bh-gs-remove-gs event listener

    
   
/**
 * Create vertical bar chart with usage data.
 * 
 */
nl.sara.beehub.gs.view.GroupSponsorView.prototype.createUsageView = function() {   
 d3.json(location.href+"?usage", function(error,inputdata) {
  // Stop when error
  if (error) return alert(error);
  
  var data = inputdata[0].usage;
  
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
 });
}