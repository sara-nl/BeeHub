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

$(function (){	
  // Workaround for bug in mouse item selection
  $.fn.typeahead.Constructor.prototype.blur = function() {
	var that = this;
	setTimeout(function () { that.hide(); }, 250);
  };
  
  var path = location.pathname;
  // add slash to the end of path
  if (!path.match(/\/$/)) {
    path=path+'/'; 
  } 
  // Check if it is group or sponsor page 
  var group_or_sponsor="";
  if ( path.substr(0, nl.sara.beehub.groups_path.length) === nl.sara.beehub.groups_path ) {
    group_or_sponsor = "group";
  } else if ( path.substr(0, nl.sara.beehub.sponsors_path.length) === nl.sara.beehub.sponsors_path ) {
    group_or_sponsor = "sponsor";
  }

  var map = {};
  var invitedUser = '';

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
	    	invitedUser=map[item];
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
	        invitedUser = ""; 
	      }
	  });
  
  /*
   * Action when the invite button is clicked
   */
  $('#bh-gs-invite-gs-form').submit(function (event) {
    // Closure for ajax request
    function callback(group_or_sponsor, invitedUser) {
      return function(status){
        if (status === 409) {
          alert('You are not allowed to remove all the '+group_or_sponsor+' administrators from a '+group_or_sponsor+'. Leave at least one '+group_or_sponsor+' administrator in the '+group_or_sponsor+' or appoint a new '+group_or_sponsor+' administrator!');
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
        $('#bh-gs-invite-typeahead').val("");
        if (group_or_sponsor === "group") {
           alert(nl.sara.beehub.principals.users[invitedUser] + " has been invited.");
        } else if (group_or_sponsor === "sponsor") {
          alert(nl.sara.beehub.principals.users[invitedUser] + " has been added.");
          window.location.reload();
        }
      };
    }

	  event.preventDefault();
	  if (invitedUser !== ""){
		  var client = new nl.sara.webdav.Client();
		  
			client.post(window.location.pathname, callback(group_or_sponsor, invitedUser), 'add_members[]='+invitedUser);
	  }
  });
  
  
 /*
  * Action when the save button is clicked
  */
 $('#bh-gs-edit-form').submit(function (e) {
	e.preventDefault();
    var setProps = new Array();
    var displayname = new nl.sara.webdav.Property();
    displayname.namespace = 'DAV:';
    displayname.tagname = 'displayname';
    displayname.setValueAndRebuildXml($('input[name="displayname"]').val());
    setProps.push(displayname);
    var description = new nl.sara.webdav.Property();
    description.namespace = 'http://beehub.nl/';
    description.tagname = 'description';
    description.setValueAndRebuildXml($('textarea[name="description"]').val());
    setProps.push(description);
    var client = new nl.sara.webdav.Client();
    client.proppatch(
      location.pathname,
      function(status, data) {
        if (status === 207) {
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
        } else {
          alert( "Something went wrong. The "+group_or_sponsor+" is not changed." );
          $('input[name="displayname"]').val($('#bh-gs-display-name').attr("data-org-name")); 
          $('textarea[name="description"]').val($('#bh-gs-sponsor-description').attr("data-org-name"));
        }
      }, setProps);

    return false;
  });
 
//Change tab listeners
 // Usage tab sponsors
 $('a[href="#bh-gs-panel-usage"]').unbind('click').click(function(e){
   $("#bh-gs-panel-usage").html("");
       var width = 960,
       height = 500,
       radius = Math.min(width, height) / 2;
    
    var color = d3.scale.ordinal()
       .range(["#98abc5", "#8a89a6", "#7b6888", "#6b486b", "#a05d56", "#d0743c", "#ff8c00"]);
    
    var arc = d3.svg.arc()
       .outerRadius(radius - 10)
       .innerRadius(0);
    
    var pie = d3.layout.pie()
       .sort(null)
       .value(function(d) { return d.used; });
    
    var svg = d3.select("#bh-gs-panel-usage").append("svg")
       .attr("width", width)
       .attr("height", height)
     .append("g")
       .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");
    
//    d3.csv("/system/data.csv", function(error, data) {
//    
//     data.forEach(function(d) {
//       d.used = +d.used;
//       console.log(d.used);
//     });
     
    var data;
    
     d3.json("/system/data.json", function(error, data) {
       data.forEach(function(d) {
         d.usage = +d.usage;
         console.log(d);
     });
    
     var g = svg.selectAll(".arc")
         .data(pie(data))
       .enter().append("g")
         .attr("class", "arc");
    
     g.append("path")
         .attr("d", arc)
         .style("fill", function(d) { return color(d.data.user); });
    
     g.append("text")
         .attr("transform", function(d) { return "translate(" + arc.centroid(d) + ")"; })
         .attr("dy", ".35em")
         .style("text-anchor", "middle")
         .text(function(d) { return d.data.user; });
    
    });
 });

// });
// Not used in new layout
//  $('#bh-gs-cancel-button').click(
//	function() {
//	  $('input[name="displayname"]').val($('#bh-gs-display-name-value').text());
//	  $('textarea[name="description"]').val($('#bh-gs-description-value').text());
//	  $('#bh-gs-display').removeClass('hide');
//      $('#bh-gs-edit').addClass('hide');
//  }); // End of button click event listener
//  
//  $('#bh-gs-edit-button').click(
//    function() {
//      $('#bh-gs-display').addClass('hide');
//      $('#bh-gs-edit').removeClass('hide'); 
//    }
//  );
	
  var handleDemote = function(event){
	var button = $(event.target);
	// send request to server
	  var client = new nl.sara.webdav.Client();
	  
	  function callback(group_or_sponsor, button) {
	    return function(status){
	      if (status === 409) {
	        alert('You are not allowed to remove all the '+group_or_sponsor+' administrators from a '+group_or_sponsor+'. Leave at least one '+group_or_sponsor+' administrator in the '+group_or_sponsor+' or appoint a new '+group_or_sponsor+' administrator!');  
	        return;
	      }
	      if (status === 403) {
	       alert('You are not allowed to perform this action!');  
	       return;
	      }
	      if ( status !== 200 ) {
	      alert('Something went wrong on the server. No changes were made.');
	      return;
	      };
	      // if succeeded, change button to promote to admin
	      var promotebutton = $('<button type="button" value="'+button.val()+'" class="btn btn-primary promote_link">Promote to admin</button>');
	      promotebutton.click(handlePromote);
	      var cell = button.parent('td');
	        cell.prepend(promotebutton);
	        button.remove();
	    };
	  }
		client.post(window.location.pathname, callback(group_or_sponsor, button), 'delete_admins[]='+button.val());
  };

  $('.demote_link').click(handleDemote);

  var handlePromote = function(event){
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
        var demotebutton = $('<button type="button" value="'+button.val()+'" class="btn btn-primary demote_link">Demote to member</button>');
        demotebutton.click(handleDemote);
        var cell = button.parent('td');
        cell.prepend(demotebutton);
        button.remove();
      };
	  }
		client.post(window.location.pathname, callback(button), 'add_admins[]='+button.val());
  };
  $('.promote_link').click(handlePromote);
  
  var handleRemove = function(button){
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
  }; // End of remove_link event listener
  $('.remove_link').on('click', function (e) {
		handleRemove($(this));
	  });
});
