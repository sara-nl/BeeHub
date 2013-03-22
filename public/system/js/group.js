$(function (){	
  // Workaround for bug in mouse item selection
  $.fn.typeahead.Constructor.prototype.blur = function() {
	var that = this;
	setTimeout(function () { that.hide() }, 250);
  };
  
  $('#inviteTypeahead').typeahead({
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
	        $('#inviteTypeahead').val('');
	        invitedUser = ""; 
	      }
	  });
  
  /*
   * Action when the invite button is clicked
   */
  $('#inviteGroupForm').submit(function (event) {
	  event.preventDefault();
	  if (invitedUser !== ""){
		  var client = new nl.sara.webdav.Client();
			client.post(window.location.pathname, function(status){
			  if (status === 409) {
			    alert('You are not allowed to remove all the group administrators from a group. Leave at least one group administrator in the group or appoint a new group administrator!');  
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
			  $('#inviteTypeahead').val("");
			  alert("User "+invitedUser+" is invited.");
			}, 'add_members[]='+invitedUser);
	  }
  });
  
  
 /*
  * Action when the save button is clicked
  */
 $('#editGroupForm').submit(function (e) {
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
	        $('#groupDisplayNameValue').html($('input[name="displayname"]').val());
	        $('#groupDescriptionValue').html($('textarea[name="description"]').val());
	        $('#beehub-group-display').removeClass('hide');
	        $('#beehub-group-edit').addClass('hide');
    	} else {
    		alert("Something went wrong. The group is not changed.")
    	}
      }, setProps);

    return false;
  });
	
  $('#cancel-button').click(
	function() {
	  $('input[name="displayname"]').val($('#groupDisplayNameValue').html());
	  $('textarea[name="description"]').val($('#groupDescriptionValue').html());
	  $('#beehub-group-display').removeClass('hide');
      $('#beehub-group-edit').addClass('hide');
  }); // End of button click event listener
  
  $('#edit-group-button').click(
    function() {
      $('#beehub-group-display').addClass('hide');
      $('#beehub-group-edit').removeClass('hide');
    }
  );
	
  var handleDemote = function(event){
	var button = $(event.target);
	// send request to server
	  var client = new nl.sara.webdav.Client();
		client.post(window.location.pathname, function(status){
		  if (status === 409) {
		    alert('You are not allowed to remove all the group administrators from a group. Leave at least one group administrator in the group or appoint a new group administrator!');  
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
	    alert('You are not allowed to remove all the group administrators from a group. Leave at least one group administrator in the group or appoint a new group administrator!');  
	    return;
	  }
	  if (status !== 200) {
			alert('Something went wrong on the server. No changes were made.');
			return;
	  };
	  $('#user-'+button.val()).remove();
	}, 'delete_members[]='+button.val());
  }; // End of remove_link event listener
  $('.remove_link').on('click', function (e) {
		handleRemove($(this));
	  });
});
