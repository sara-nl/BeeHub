$(function (){	
  // Workaround for bug in mouse item selection
  $.fn.typeahead.Constructor.prototype.blur = function() {
	var that = this;
	setTimeout(function () { that.hide() }, 250);
  };
  
  var path = location.pathname;
  // add slash to the end of path
  if (!path.match(/\/$/)) {
    path=path+'/'; 
  } 
  // Check if it is group or sponsor page 
  var group_or_sponsor="";
  if (path == '/system/group/') {
    group_or_sponsor = "group";
  } else if ((path == '/system/sponsor/')) {
    group_or_sponsor = "sponsor";
  }
  $('#bh-'+group_or_sponsor+'-invite-typeahead').typeahead({
	  source: function (query, process) {
	        // implementation
		    users = [];
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
	    	if (item.toLowerCase().indexOf(this.query.trim().toLowerCase()) != -1) {
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
	    if(map[$(this).val()] == null) {
	        $('#bh-'+group_or_sponsor+'-invite-typeahead').val('');
	        invitedUser = ""; 
	      }
	  });
  
  /*
   * Action when the invite button is clicked
   */
  $('#bh-'+group_or_sponsor+'-invite-'+group_or_sponsor+'-form').submit(function (event) {
	  event.preventDefault();
	  if (invitedUser !== ""){
		  var client = new nl.sara.webdav.Client();
			client.post(window.location.pathname, function(status){
			  if (status === 409) {
			    alert('You are not allowed to remove all the '+group_or_sponsor+' administrators from a '+group_or_sponsor+'. Leave at least one '+group_or_sponsor+' administrator in the '+group_or_sponsor+' or appoint a new '+group_or_sponsor+' administrator!');  
			    return;
			  }
			  if (status === 403) {
				 alert('You are not allowed to perform this action!');  
				 return;
			  }
			  if (status != 200) {
				alert('Something went wrong on the server. No changes were made.');
				return;
			  };
			  $('#bh-'+group_or_sponsor+'-invite-typeahead').val("");
			  alert(nl.sara.beehub.principals.users[invitedUser] + " has been invited.");
			}, 'add_members[]='+invitedUser);
	  }
  });
  
  
 /*
  * Action when the save button is clicked
  */
 $('#bh-'+group_or_sponsor+'-edit-form').submit(function (e) {
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
	        $('#bh-'+group_or_sponsor+'-display-name-value').html($('input[name="displayname"]').val());
	        $('#bh-'+group_or_sponsor+'-description-value').html($('textarea[name="description"]').val());
	        $('#bh-'+group_or_sponsor+'-display').removeClass('hide');
	        $('#bh-'+group_or_sponsor+'-edit').addClass('hide');
    	} else {
    		alert("Something went wrong. The '+group_or_sponsor+' is not changed.")
    	}
      }, setProps);

    return false;
  });
	
  $('#bh-'+group_or_sponsor+'-cancel-button').click(
	function() {
	  $('input[name="displayname"]').val($('#bh-'+group_or_sponsor+'-display-name-value').html());
	  $('textarea[name="description"]').val($('#bh-'+group_or_sponsor+'-description-value').html());
	  $('#bh-'+group_or_sponsor+'-display').removeClass('hide');
      $('#bh-'+group_or_sponsor+'-edit').addClass('hide');
  }); // End of button click event listener
  
  $('#bh-'+group_or_sponsor+'-edit-button').click(
    function() {
      $('#bh-'+group_or_sponsor+'-display').addClass('hide');
      $('#bh-'+group_or_sponsor+'-edit').removeClass('hide');
    }
  );
	
  var handleDemote = function(event){
	var button = $(event.target);
	// send request to server
	  var client = new nl.sara.webdav.Client();
		client.post(window.location.pathname, function(status){
		  if (status === 409) {
		    alert('You are not allowed to remove all the '+group_or_sponsor+' administrators from a '+group_or_sponsor+'. Leave at least one '+group_or_sponsor+' administrator in the '+group_or_sponsor+' or appoint a new '+group_or_sponsor+' administrator!');  
		    return;
		  }
		  if (status === 403) {
			 alert('You are not allowed to perform this action!');  
			 return;
		  }
		  if (status != 200) {
			alert('Something went wrong on the server. No changes were made.');
			return;
		  };
			// if succeeded, change button to promote to admin
		  var promotebutton = $('<button type="button" value="'+button.val()+'" class="btn btn-primary promote_link">Promote to admin</button>');
		  promotebutton.click(handlePromote);
		  var cell = button.parent('td');
	      cell.prepend(promotebutton);
	      button.remove();
		}, 'delete_admins[]='+button.val());
  }

  $('.demote_link').click(handleDemote);

  var handlePromote = function(event){
	  var button = $(event.target);
	// send request to server
	  var client = new nl.sara.webdav.Client();
		client.post(window.location.pathname, function(status){
		  if (status === 403) {
			alert('You are not allowed to perform this action!');  
			return;
		  }
		  if (status !== 200) {
			alert('Something went wrong on the server. No changes were made.'+status);
			return;
		  };
		  var demotebutton = $('<button type="button" value="'+button.val()+'" class="btn btn-primary demote_link">Demote to member</button>');
		  demotebutton.click(handleDemote);
		  var cell = button.parent('td');
	      cell.prepend(demotebutton);
	      button.remove();
		}, 'add_admins[]='+button.val());
  }
  $('.promote_link').click(handlePromote);
  
  var handleRemove = function(button){
	// send request to server
    var client = new nl.sara.webdav.Client();
	client.post(window.location.pathname, function(status){
	  if (status === 409) {
	    alert('You are not allowed to remove all the '+group_or_sponsor+' administrators from a '+group_or_sponsor+'. Leave at least one '+group_or_sponsor+' administrator in the '+group_or_sponsor+' or appoint a new '+group_or_sponsor+' administrator!');  
	    return;
	  }
	  if (status !== 200) {
			alert('Something went wrong on the server. No changes were made.');
			return;
	  };
	  $('#bh-'+group_or_sponsor+'-user-'+button.val()).remove();
	}, 'delete_members[]='+button.val());
  }; // End of remove_link event listener
  $('.remove_link').on('click', function (e) {
		handleRemove($(this));
	  });
});
