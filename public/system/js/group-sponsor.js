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
   createUsageView();
 });

// var createUsageView = function(){
//   // Clear div
//   $("#bh-gs-panel-usage").html("");
//   
//   var width = 960,
//   height = 500,
//   radius = Math.min(width, height) / 2;
//    
//   var color = d3.scale.ordinal().range(["#98abc5", "#8a89a6", "#7b6888", "#6b486b", "#a05d56", "#d0743c", "#ff8c00"]);
//   var arc = d3.svg.arc().outerRadius(radius - 10).innerRadius(0);
//   var pie = d3.layout.pie().sort(null).value(function(d) { return d.usage; });
//   
//   var svg = d3.select("#bh-gs-panel-usage")
//     .append("svg").attr("width", width).attr("height", height)
//     .append("g")
//     .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");
//     
//   // Get accounting data
//   d3.json(location.href+"?usage", function(error, inputdata) {
//     var data = inputdata[0].usage;
//     data.forEach(function(d) {
//       d.usage = +d.usage;
//     });
//    
//     var g = svg.selectAll(".arc").data(pie(data)).enter().append("g").attr("class", "arc");
//     g.append("path").attr("d", arc).style("fill", function(d) { return color(d.data["props.DAV: owner"]); });
//     g.append("text").attr("transform", function(d) { return "translate(" + arc.centroid(d) + ")"; })
//      .attr("dy", ".35em").style("text-anchor", "middle").text(function(d) { return d.data["props.DAV: owner"]; });
//    });
// };
 
// var createUsageView = function(){
//  var margin = {top: 20, right: 20, bottom: 30, left: 40},
//  width = 960 - margin.left - margin.right,
//  height = 500 - margin.top - margin.bottom;
//
//  var formatPercent = d3.format(".0%");
//  
//  var x = d3.scale.ordinal()
//     .rangeRoundBands([0, width], .1, 1);
//  
//  var y = d3.scale.linear()
//     .range([height, 0]);
//  
//  var xAxis = d3.svg.axis()
//     .scale(x)
//     .orient("bottom");
//  
//  var yAxis = d3.svg.axis()
//     .scale(y)
//     .orient("left")
//     .tickFormat(formatPercent);
//  
//  var svg = d3.select("#bh-gs-panel-usage").append("svg")
//     .attr("width", width + margin.left + margin.right)
//     .attr("height", height + margin.top + margin.bottom)
//   .append("g")
//     .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
//  
// d3.json(location.href+"?usage", function(error, inputdata) {
//   var data = inputdata[0].usage;
//   data.forEach(function(d) {
//     d.usage = +d.usage;
//   });
////  d3.tsv("data.tsv", function(error, data) {
////  
////   data.forEach(function(d) {
////     d.frequency = +d.frequency;
////   });
//  
//   x.domain(data.map(function(d) { return d["props.DAV: owner"]; }));
//   y.domain([0, d3.max(data, function(d) { return d.usage; })]);
//  
//   svg.append("g")
//       .attr("class", "x axis")
//       .attr("transform", "translate(0," + height + ")")
//       .call(xAxis);
//  
//   svg.append("g")
//       .attr("class", "y axis")
//       .call(yAxis)
//       .append("text")
//       .attr("transform", "rotate(-90)")
//       .attr("y", 6)
//       .attr("dy", ".71em")
//       .style("text-anchor", "end")
//       .text("Usage");
//  
//   svg.selectAll(".bar")
//       .data(data)
//       .enter().append("rect")
//       .attr("class", "bar")
//       .attr("x", function(d) { return x(d["props.DAV: owner"]); })
//       .attr("width", x.rangeBand())
//       .attr("y", function(d) { return y(d.usage); })
//       .attr("height", function(d) { return height - y(d.usage); });
//  
//   d3.select("input").on("change", change);
//  
//   var sortTimeout = setTimeout(function() {
//     d3.select("input").property("checked", true).each(change);
//   }, 2000);
//  
//   function change() {
//     clearTimeout(sortTimeout);
//  
//     // Copy-on-write since tweens are evaluated after a delay.
//     var x0 = x.domain(data.sort(this.checked
//         ? function(a, b) { return b.usage - a.usage; }
//         : function(a, b) { return d3.ascending(a.data["props.DAV: owner"], b.data["props.DAV: owner"]); })
//         .map(function(d) { return d["props.DAV: owner"]; }))
//         .copy();
//  
//     var transition = svg.transition().duration(750),
//         delay = function(d, i) { return i * 50; };
//  
//     transition.selectAll(".bar")
//         .delay(delay)
//         .attr("x", function(d) { return x0(d["props.DAV: owner"]); });
//  
//     transition.select(".x.axis")
//         .call(xAxis)
//       .selectAll("g")
//         .delay(delay);
//     }
//   });
// }
// 
// var createUsageView = function(){
//
//  d3.json(location.href+"?usage", function(error, inputdata) {
//    var root = inputdata[0].usage;
//    
////    var index = [];
//    var data = [];
//    
//    console.log(root);
//    root.forEach(function(d) {
//      if (d["usage"] > 0) {
//        data.push(d["usage"]);
//      }
////      index.push(d["props.DAV: owner"]);
//    });
//    
//    var margin = {top: 0, right: 10, bottom: 20, left: 10},
//    width = 960 - margin.left - margin.right,
//    height = 500 - margin.top - margin.bottom;
//
//    var index = d3.range(2);
////        data = index.map(d3.random.normal(100, 10));
//    console.log(index);
//    console.log(data);
//    
//    var x = d3.scale.linear()
//        .domain([0, d3.max(data)])
//        .range([0, width]);
//    
//    var y = d3.scale.ordinal()
//        .domain(index)
//        .rangeRoundBands([0, height], .1);
//    
//    var svg = d3.select("body").append("svg")
//        .attr("width", width + margin.left + margin.right)
//        .attr("height", height + margin.top + margin.bottom)
//      .append("g")
//        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
//    
//    var bar = svg.selectAll(".bar")
//        .data(data)
//      .enter().append("g")
//        .attr("class", "bar")
//        .attr("transform", function(d, i) { return "translate(0," + y(i) + ")"; });
//    
//    bar.append("rect")
//        .attr("height", y.rangeBand())
//        .attr("width", x);
//    
//    bar.append("text")
//        .attr("text-anchor", "end")
//        .attr("x", function(d) { return x(d) - 6; })
//        .attr("y", y.rangeBand() / 2)
//        .attr("dy", ".35em")
//        .text(function(d, i) { return i; });
//    
//    svg.append("g")
//        .attr("class", "x axis")
//        .attr("transform", "translate(0," + height + ")")
//        .call(d3.svg.axis()
//        .scale(x)
//        .orient("bottom"));
//    
//    var sort = false;
//    
//    setInterval(function() {
//    
//      if (sort = !sort) {
//        index.sort(function(a, b) { return data[a] - data[b]; });
//      } else {
//        index = d3.range(24);
//      }
//    
//      y.domain(index);
//    
//      bar.transition()
//          .duration(750)
//          .delay(function(d, i) { return i * 50; })
//          .attr("transform", function(d, i) { return "translate(0," + y(i) + ")"; });
//    
//    }, 5000);
//  });
// }
 var createUsageView = function(){
//   // define dimensions of svg
//   var h = 400,
//       w = 800;
//   
//   // create svg element
//   var chart = d3.select("#bh-gs-usage-div")
//                 .append('svg') // parent svg element will contain the cgart
//                 .attr('width', w)
//                 .attr('height', h)
//                 
//   d3.csv('/system/test.cvs', function(d){
//     return{
//       key: d.state,
//       value: +d.value
//     };
//   },
//   function(dataset){
//     console.log(dataset);
//      var barwidth = w/ dataset.length
//      console.log(barwidth);
//      
//      // create bars
//      chart.selectAll('rect')             // returns empty selection
//           .data(dataset)                 // parses & counts data
//           .enter()                       // binds data to placeholders
//           .append('rect')                // creates a rect svg element for every datum
//           .attr('x', function(d,i) {     // left-to-right position of left edge of each
//              return i * barwidth;        // bar
//            })
//           .attr('y', function(d) {
//               return h - d.value
//            })
//           .attr('width', barwidth)
//           .attr('height', function(d){
//             return d.value;
//           })
//           .attr('fill', 'red');
//   })
//   
   // define dimensions of svg
   var h = 400,
       w = 800;
   
   // create svg element
   var chart = d3.select("#bh-gs-usage-div")
                 .append('svg') // parent svg element will contain the cgart
                 .attr('width', w)
                 .attr('height', h)
                 
   d3.json(location.href+"?usage", function(error,inputdata) {
     // Stop when error
     if (error) return alert(error);
     
     var dataset = inputdata[0].usage;
     
     // calculate with of each bar
     var barwidth = w/ dataset.length
     
     var chartPadding = 50;
     var chartBottom = h - chartPadding; // 350;
     var chartRight = w - chartPadding; // 750
     
     // add variable to set spacing
     var spacing = 1;
     
     var maxValue = d3.max(dataset, function(d){
       return d.usage/1024/1024
     })
     var yScale = d3.scale.linear().domain([0,maxValue]).range([chartBottom,chartPadding])
                                   .nice();

     var barLabels = dataset.map(function(datum){
       return datum['props.DAV: owner'];
     });
     
     var xScale = d3.scale.ordinal()
                    .domain(barLabels)
                    .rangeRoundBands([chartPadding, chartRight] , 0.1);
                    // divides bands equally among total width, with 10% spacing
     
     // declare & configure the y axis function
     var yAxis = d3.svg.axis()
                   .scale(yScale)
                   .orient('left');
     // declare & configure the x axis
     var xAxis = d3.svg.axis()
                   .scale(xScale)
                   .orient('bottom')
                   .tickSize(0)
     
     // create bars
     chart.selectAll('rect')             // returns empty selection
          .data(dataset)                 // parses & counts data
          .enter()                       // binds data to placeholders
          .append('rect')                // creates a rect svg element for every datum
          .attr({
            'x' : function(d) {       // left-to-right position of left edge of each
               return xScale(d['props.DAV: owner']);        // bar
            },
            'y' : function(d) {
              return yScale(d.usage/1024/1024);
            },
            'width' : xScale.rangeBand(),
            'height': function(d){
              return chartBottom - yScale(d.usage/1024/1024);
            },
            'fill': 'red'
          })
          // attach event listener to each bar for mouseover
          .on('mouseover', function(d){
            d3.select(this)
              .attr('fill', 'yellow');
               showValue(d);
          })
          .on('mouseout', function(d){
            d3.select(this)
              .transition() // add a "smoothing" animation of the transmision
              .duration(500) // set the duration of the transition in ms (default 250)
              .attr('fill','red');
               hideValue();
          });
//     // add text
//     chart.selectAll('text')
//          .data(dataset)
//          .enter()
//          .append('text')
//          .text(function(d){
//            return d.usage/1024/1024
//          })
//          // multiple attibutes may be passed in as an object
//          .attr({
//            'x' : function(d){
//              return xScale(d['props.DAV: owner']) + xScale.rangeBand() / 2
//            }, // position text at the middle of each bar
//            'y' : function(d){
//              return h - yScale(d.usage/1024/1024);
//            }, // base of text will be at top of each bar
//            'font-family' : 'sans-serif',
//            'font-size': '13px',
//            'font-weight' : 'bold',
//            'fill' : 'black',
//            'text-anchor' : 'middle'
//          });
     
     // use transformation to adjust position of axis
     var y_axis = chart.append('g')
                 .attr('class','axis')
                 .attr('transform', 'translate('+chartPadding+',0)');
   
     // generate y Axis within group using yAxis function
     yAxis(y_axis);
     
     chart.append('g')
          .attr('class','axis xAxis')
          .attr('transform', 'translate(0,'+chartBottom+')') // Push to bottom
          .call(xAxis) //passes itself (g element) into xAxis function
          // rotate tick labels
          .selectAll('text')
          .style('text-anchor','end')
          .attr('transform','rotate(-65)');
     
     var showValue = function(d){
       chart.append('text')
            .text(bytesToSize(d.usage),2)
            .attr({
              'x' : xScale(d['props.DAV: owner']) + xScale.rangeBand() / 2,
              'y' : yScale(d.usage/1024/1024) + 15,
              'class' : 'value'
            })
     }
     
     var hideValue = function(){
       chart.select('text.value').remove();
     }
     
     var sortDir = 'asc'; // set a flasg for sort direction
     d3.select('button#bh-gs-sort') // add listener to button to activate sorting
       .on('click', function(){
         sortChart();
       });
     
     var sortChart = function(){
       var newDomain = [] // declare array to hold re-ordered ordinal domain for xScale
       chart.selectAll('rect')
            .sort(function(a,b){
              if (sortDir == 'asc'){
                return d3.ascending(a.usage, b.usage);
              } else {
                return d3.descending(a.usage, b.usage);
              }
            })
            .transition()
            .duration(1000)
            .attr('x', function(d,i){
              return xScale(i); // re-position bars based on sorted positions
            });
       sortDir = sortDir == 'asc' ? 'desc' : 'asc'; // flip the flag
     }
   });
 }

 /**
  * Calculate size from bytes to readable size
  * 
  * @param {Integer} bytes      Bytes to calculate
  * @param {Integer} precision  Precision
  * 
  */
 var bytesToSize = function(bytes, precision)
 {  
     var kilobyte = 1024;
     var megabyte = kilobyte * 1024;
     var gigabyte = megabyte * 1024;
     var terabyte = gigabyte * 1024;
    
     if ((bytes >= 0) && (bytes < kilobyte)) {
         return bytes + ' B';
  
     } else if ((bytes >= kilobyte) && (bytes < megabyte)) {
         return (bytes / kilobyte).toFixed(precision) + ' KB';
  
     } else if ((bytes >= megabyte) && (bytes < gigabyte)) {
         return (bytes / megabyte).toFixed(precision) + ' MB';
  
     } else if ((bytes >= gigabyte) && (bytes < terabyte)) {
         return (bytes / gigabyte).toFixed(precision) + ' GB';
  
     } else if (bytes >= terabyte) {
         return (bytes / terabyte).toFixed(precision) + ' TB';
  
     } else {
         return bytes + ' B';
     }
 };
 
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
