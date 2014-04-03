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
   createView();
 });
 
 var createView = function(){
   $("#bh-profile-usage-graph").html("");
   
   var width = 760,
   height = 500,
   radius = Math.min(width, height) / 2,
   color = d3.scale.category20c();
   
   var svg = d3.select("#bh-profile-usage-graph")
               .append("svg")
               .attr("width", width)
               .attr("height", height)
               .append("g")
               .attr("transform", "translate(" + width / 2 + "," + height * .52 + ")");
 
   var partition = d3.layout.partition()
                     .sort(null)
                     .size([2 * Math.PI, radius * radius])
                     .value(function(d) { return d.size; });
 
   var arc = d3.svg.arc()
               .startAngle(function(d) { return d.x; })
               .endAngle(function(d) { return d.x + d.dx; })
               .innerRadius(function(d) { return Math.sqrt(d.y); })
               .outerRadius(function(d) { return Math.sqrt(d.y + d.dy); });
 
   d3.json(location.href+"?usage", function(error, response) {
//   d3.json("/system/flare.json", function(error, root) {
     var root = rewriteUsageResponse(response[0].usage);
//     var root = rewriteUsageResponse(response);
     var path = svg.datum(root)
                   .selectAll("path")
                   .data(partition.nodes)
                   .enter()
                   .append("path")
                   .attr("display", function(d) { return d.depth ? null : "none"; }) // hide inner ring
                   .attr("d", arc)
                   .style("stroke", "#fff")
                   .style("fill", function(d) { return color((d.children ? d : d.parent).name); })
                   .style("fill-rule", "evenodd")
                   .on("mouseover", mouseover)
                   .on("mouseout", mouseout)
                   .each(stash);
   
//     d3.selectAll("input").on("change", function change() {
//       var value = this.value === "count" ? function() { return 1; } : function(d) { return d.size; };
//       path.data(partition.value(value).nodes).transition().duration(1500).attrTween("d", arcTween);
//     });
   });
 
  //Stash the old values for transition.
  function stash(d) {
    d.x0 = d.x;
    d.dx0 = d.dx;
  }
 
  //Interpolate the arcs in data space.
  function arcTween(a) {
    var i = d3.interpolate({x: a.x0, dx: a.dx0}, a);
    return function(t) {
     var b = i(t);
     a.x0 = b.x;
     a.dx0 = b.dx;
     return arc(b);
    };
    }
  
  var tooltip;
  var mouseover = function(d){
    tooltip = d3.select("#bh-profile-usage-header")
    .append("div")
    .style("position", "absolute")
    .style("z-index", "10")
    .style("visibility", "hidden")
    .text(d.path+" ("+d.size+")");
    return tooltip.style("visibility", "visible");
  };
  
  var mouseout = function(d){
    return tooltip.style("visibility", "hidden");
  }
 
  d3.select(self.frameElement).style("height", height + "px");
 }
 

 
 var rewriteUsageResponse = function(data){
  var returnValue = {
      name: "root",
      children: []
  };
  
  $.each(data, function(i, value){
    var children = returnValue.children;
    
    var dirs = value["_id"].split("/");
    
    for (var i=1; i < dirs.length; i++) {
      // als laatste dan toevoegen en value toevoegen
      if (i === (dirs.length -1)){
        var add = {
            "name": dirs[i],
            "size": value["value"],
            "children": [],
            "path": value["_id"]
        };
        children.push(add);
      } else {
        // als niet laatste
        var exist = false;
        var child = 0;
        for (var j=0 ; j < children.length; j++){
          
          // check of match dan exist = true
          if (children[j].name === dirs[i]){
            exist = true;
            child = j;
          }
        };
        // bestaat hij bij de children 
        if (exist) {
          children = children[child].children;
          // pas children aan
          // volgende
        } else {
          // bestaat hij niet bij de children
          // maak nieuwe aan
          var add = {
              "name": dirs[i],
              "children": [],
              "size": 0,
              // TODO hele path
              "path": dirs[i]
          };
          children.push(add);
          // pas children aan
          children = add.children;

          // volgende
        } 
      }
    }
  })
  
  console.log(returnValue);
  return returnValue;
 }
});
