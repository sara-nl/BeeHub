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
	/*
	 * Action when submit button in profile tab is clicked.
	 */
   var old_sponsor_value = $('#sponsor').val();
	 $('#myprofile_form').submit(function(event) {
		event.preventDefault();
	 		
		var setProps = new Array();
		
		var email = new nl.sara.webdav.Property();
	    email.namespace = 'http://beehub.nl/';
	    email.tagname = 'email';
	    email.setValueAndRebuildXml($('input[name="email"]').val());
	    setProps.push(email);
	    
	    var displayname = new nl.sara.webdav.Property();
	    displayname.namespace = 'DAV:';
	    displayname.tagname = 'displayname';
	    displayname.setValueAndRebuildXml($('input[name="displayname"]').val());
	    setProps.push(displayname);
	    
      if ( $('#sponsor').val() !== old_sponsor_value ) {
  	    var sponsor = new nl.sara.webdav.Property();
  	    sponsor.namespace = 'http://beehub.nl/'; 
  	    sponsor.tagname = 'sponsor';
  	    sponsor.setValueAndRebuildXml($('#sponsor').val()); 
  	    setProps.push(sponsor);
      }
	    
	    var client = new nl.sara.webdav.Client();
	    client.proppatch(location.pathname, function(status, data) {
	    	//TODO check voor elke property
	    	if (status === 207) {
	    		alert( "Your profile is changed!" );
	    		$('#verify_password').val("");
	    		$('#verification_code').val("");
          location.reload();
	    	} else {
	    		alert( "Something went wrong. Your profile is not updated!" );
	    	}
	    }, setProps);
	   
	 }); // End of submit myprofile_form listener
	 
	var passwordListener = function(passwordConfirmationField){
		// clear error
		passwordConfirmationField.next().remove();
		passwordConfirmationField.parent().parent().removeClass('error');
		
		var showError = function(error){
			passwordConfirmationField.parent().parent().addClass('error');
			var errorSpan = $('<span class="help-inline"></span>');
      errorSpan.text( error );
			passwordConfirmationField.parent().append( errorSpan );
		};
		
		if(passwordConfirmationField.val() !== $('#new_password').val()) {
			showError('Password mismatch.');
			return false;
		};
		return true;
	};

	/*
	* Action when the password field will change
	*/
	$('#new_password2').change(function (e) {
		 passwordListener($(this));
	});
	
		/*
	* Action when the password field will change
	*/
	$('#password').change(function () {
		// clear error
		$(this).next().remove();
		$(this).parent().parent().removeClass('error');
	});
	
	/*
	* Action when the change password button is clicked
	*/
	$('#change-password').submit(function (e) {
		 e.preventDefault();
		 var client = new nl.sara.webdav.Client();
		    client.post(location.pathname, function(status, data) {
		    	if (status===200) {
		    		// TODO check if user is logged on with SURFconext.
		    		alert("Your password is changed now!");
		    		$('#change-password').each (function(){
		    			  this.reset();
		    		});
		    	}
		    	if (status===403) {
		    		$("#password").parent().parent().addClass('error');
					var error = $( '<span class="help-inline"></span>' );
          error.text( 'Wrong password.' );
					$("#password").parent().append(error);
		    	};
		    }, $("#change-password").serialize());
	});
	
	/*
	* Action when the unlink button is clicked
	*/
	$('#unlink').click(function (event) {
	    var delProps;
	    if (confirm('Are you sure you want to unlink your SURFconext account?')) {
	      delProps = new Array();
	      
	      var saml_unlink = new nl.sara.webdav.Property();
	      saml_unlink.namespace = 'http://beehub.nl/';
	      saml_unlink.tagname = 'surfconext-description';
	      delProps.push(saml_unlink);
	      
	      saml_unlink = new nl.sara.webdav.Property();
	      saml_unlink.namespace = 'http://beehub.nl/';
	      saml_unlink.tagname = 'surfconext';
	      delProps.push(saml_unlink);
	      
	      var client = new nl.sara.webdav.Client();
		    client.proppatch(location.pathname, function(status, data) {
		    var notlinked = $('<br/> <h5>Your BeeHub account is not linked to a SURFconext account.</h5>'+
		    		'<p><a type="button" href="/system/saml_connect.php" class="btn btn-success">Link SURFconext account</a></p>');
		    $('#surfconext_linked').remove();
		    $('#surfconext').append(notlinked);
		    }, undefined ,delProps);
	    };
	});
	
	/*
	* Action when the verify email button is clicked
	*/
	$('#verify_email').submit(function (e) {
		 e.preventDefault();
		 var client = new nl.sara.webdav.Client();
		    client.post(location.pathname, function(status, data) {
		    	if (status===200) {
			    	location.reload();
		    	}else if (status===403) {
		    		alert( "Wrong verification code or password mismatch!" );
		    	} else {
		    		alert( "Something went wrong. Your email is not verified.!" );
		    	};
		    }, $("#verify_email").serialize());
	});
	
 // Usage tab
 $('a[href="#bh-profile-panel-usage"]').unbind('click').click(function(e){
   if ($("#bh-dir-loading").css('display') === "none" && $("#bh-profile-usage-graph").html() === "" ){ 
     createUsageView(); 
   };
 });
  
 /**
  * Create sunburst accounting graphic with 3d.js
  * 
  */
 var createUsageView = function(){
   var totalSize = 0;
   
   var width = 640,
   height = 420,
   radius = Math.min(width, height) / 2;

   var x = d3.scale.linear()
       .range([0, 2 * Math.PI]);
   
   var y = d3.scale.sqrt()
       .range([0, radius]);
   
   var color = d3.scale.category20c();
   
   var svg = d3.select("#bh-profile-usage-graph").append("svg")
       .attr("width", width)
       .attr("height", height)
       .append("g")
       .attr("transform", "translate(" + width / 2 + "," + (height / 2) + ")");
   
   var partition = d3.layout.partition()
       .value(function(d) { return d.size; });
   
   var arc = d3.svg.arc()
       .startAngle(function(d) { return Math.max(0, Math.min(2 * Math.PI, x(d.x))); })
       .endAngle(function(d) { return Math.max(0, Math.min(2 * Math.PI, x(d.x + d.dx))); })
       .innerRadius(function(d) { return Math.max(0, y(d.y)); })
       .outerRadius(function(d) { return Math.max(0, y(d.y + d.dy)); });
   
   // Tooltip div
   var div = d3.select("#bh-profile-usage-graph").append("div")   
    .attr("class", "bh-user-usage-tooltip")               
    .style("opacity", 0);
   
   $("#bh-dir-loading").show();
   // Read data from server
   d3.json(location.href+"?usage", function(error, response) {
     if (error) {
       alert("Something went wrong with retrieving data from the server.");
       $("#bh-dir-loading").hide();
       return;
     };
     
     $("#bh-dir-loading").hide();
     // Get data from response
     var root = rewriteUsageResponse(response[0].usage);
     
     // Remember totalSize of the root
     totalSize = root.size;
     
     // Update header
     $("#bh-profile-usage-header").html(root.path+"<br>"+nl.sara.beehub.utils.bytesToSize(root.size,1)+", "+(100 * root.size / totalSize).toPrecision(3)+" % of total usage");
     
     var nodes = partition.nodes(root);
     
     var path = svg.selectAll("path")
      .data(nodes)
      .enter().append("path")
      .attr("d", arc)
      .style("stroke", "#fff")
      .style("fill", function(d) { return determineColor(d); })
      .on("click", click)
      .on("mouseover", mouseover)   
      .on("mouseout", mouseout);

     // Click handler, when value not empty update header and zoom sunburst graphic
     function click(d, i) {
       if (d.name !== "empty") {
         $("#bh-profile-usage-header").html("");
         var breadcrumb = '<ul id="bh-profile-graphic" class="breadcrumb bh-dir-breadcrumb"></ul>';
         breadcrumb = breadcrumb + nl.sara.beehub.utils.bytesToSize(d.size,1)+', '+(100 * d.size / totalSize).toPrecision(3)+' % of total usage';
         $("#bh-profile-usage-header").html(breadcrumb);
         if (d.parent){
           makeHeader(path, i);
         } else {
           $('#bh-profile-graphic').prepend('<li data-index="'+i+'">BeeHub root</li>');
         };
       };
       changePosition(path, i);
     }
     
     // make header of graphic view
     function makeHeader(path,i){
       if (nodes[i].name === "root") {
         $('#bh-profile-graphic').prepend('<li style="cursor: pointer" data-index="'+i+'">BeeHub root <span class ="divider"> &raquo; </span></li>');
       } else {
         $('#bh-profile-graphic').prepend('<li style="cursor: pointer" data-index="'+i+'">'+nodes[i].name+'<span class ="divider">/</span></li>');
       };
       $('#bh-profile-graphic').find('li').first().unbind('click').on('click', function(){
         click(nodes[i], i);
       });
       
       if (nodes[i].parent){
         makeHeader(path, findIndex(nodes[i].parent));
       };
     };
     
     // find the index of a node in an array with nodes
     function findIndex(node){
       for (var i in nodes) {
         if (nodes[i].path === node.path) {
           return i;
         };
       };
     };
     
     // Change graphic view
     function changePosition(path,i){
       path.transition()
       .duration(750)
       .attrTween("d", arcTween(nodes[i]));
     };
     
     // Mouseover handler, when value not empty show tooltip
     function mouseover(d) {
       if (d.name !== "empty") {
        div.transition()        
            .duration(200)      
            .style("opacity", .9);      
        div .html("<b>"+d.path+"</b><br>"+(100 * d.size / totalSize).toPrecision(3)+" % of total usage ("+nl.sara.beehub.utils.bytesToSize(d.size,1)+")")  
            .style("left", (d3.event.pageX+5) + "px")     
            .style("top", (d3.event.pageY - 28) + "px");   
       };
     }
     
     // Mouseout handler, when value not empty hide tooltip
     function mouseout(d) {  
       if (d.name !== "empty") {
         div.transition()        
             .duration(500)      
             .style("opacity", 0);   
       }
     }
   });
   
   d3.select(self.frameElement).style("height", height + "px");
   
   // Interpolate the scales!
   function arcTween(d) {
     var xd = d3.interpolate(x.domain(), [d.x, d.x + d.dx]),
         yd = d3.interpolate(y.domain(), [d.y, 1]),
         yr = d3.interpolate(y.range(), [d.y ? 20 : 0, radius]);
     return function(d, i) {
       return i
           ? function(t) { return arc(d); }
           : function(t) { x.domain(xd(t)); y.domain(yd(t)).range(yr(t)); return arc(d); };
     };
   }
   
   // Color of graphic part
   function determineColor(d){
//    var percentage = (100 * d.value / totalSize).toPrecision(3);
     if (d.name === "empty"){
       return "#FFFFFF";
     } else {
       return color((d.children ? d : d.parent).name); 
     }
   } 
 };

 /**
  * Check if name object exists in object
  * 
  * @param {Object} children Child object to check in
  * @param {String} name     String to check
  */
 var checkExist = function(children, name){
   var exist = false;
   var child = 0;
   for (var j=0 ; j < children.length; j++){
     
     // check of match dan exist = true
     if (children[j].name === name){
       exist = true;
       child = j;
     }
   };
  // bestaat hij bij de children 
  if (exist) {
    return child;
  } else {
    return null;
  }
 };
 
 /**
  * Put json response of accounting data request in object for 3d.js
  * 
  * @param {Json} data Json object to rewrite
  * 
  */
 var rewriteUsageResponse = function(data){
  var returnValue = {
      "name" : "root",
      "children" : []     
  };
  
  $.each(data, function( path, size){
    if (path === "/"){
      returnValue.size = size;
      returnValue.path = "BeeHub root";
      return;
    };
   var children = returnValue.children;  
   var dirs = path.split("/");
    
   for (var i=1; i < dirs.length-1; i++) { 
     var exist = checkExist(children,dirs[i]);
     
      // last one
      if (i === (dirs.length -2)) {
        if (exist !== null) {
          // change size
          children[exist].size = size;
        } else {
           //create object with size
           var add = {
            "name": dirs[i],
            "size": size,
            "children": [],
            "path": path
           };
           children.push(add);
        }
      // not last one
      } else {
        if (exist !== null) {
          // do nothing
          children = children[exist].children;
        } else {
          // create object without size
          var add = {
           "name": dirs[i],
           "children": [],
           "size": 0,
           "path": path
          };
          children.push(add);
          // pas children aan
          children = add.children;
        }
      }
    }
  });
 
  calculateSizes(returnValue);
  return returnValue;
 };
 
 /**
  * Calculates the sizes of child object and create an empty child for not used space. 
  * 
  * Sunburst graphic of 3d.js fills the whole child. This function add free 
  * space child object in the childs so that free space can be colored white.
  * 
  * @param {Object} returnValue Object with 3d.js input
  * 
  */
 var calculateSizes = function(returnValue) {
   var size = 0;
   for (var key in returnValue.children) {
     size = size + returnValue.children[key].size;
     if (returnValue.children[key].children.length > 0){
       calculateSizes(returnValue.children[key]);
     }
   }
   
   if ((returnValue.size - size) > 0) {
    var add = {
        "name": "empty",
        "children": [],
        "size": returnValue.size - size,
        "path": "empty"
       };
    returnValue.children.push(add);
   }
 };
});
