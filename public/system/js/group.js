$(function (){
	
  // TODO same function in groups.js, merge
  var groupNameListener = function(groupNameField){
var showError = function(error){
			 groupNameField.parent().parent().addClass('error');
		 var error = $('<span class="help-inline">'+error+'</span>');
		 groupNameField.parent().append(error);
	 }

	 // TODO make tooltip with field specifications
	 // This is included in bootstrap with patern
	
	 // clear error
	 groupNameField.next().remove();
	 groupNameField.parent().parent().removeClass('error');
	
	 // value not system
	 if (RegExp('^system$|^home$','i').test(groupNameField.val())) {
		showError(groupNameField.val()+' is not a valid groupname.');
		return false;
	 }
	
	// Seperate regular expressions to make the errors more specific.
	// value starts with a-zA-Z0-9, else return
	 if (!RegExp('^[a-zA-Z0-9]{1}.*$').test(groupNameField.val())) {
		 showError('First character must be a alphanumeric character.');
		 return false;
	 }
	// value only contain a-zA-Z0-9_-., else return
	 if (!RegExp('^[a-zA-Z0-9]{1}[a-zA-Z0-9_\\-\\.]*$').test(groupNameField.val())) {
		 showError('This field can contain aphanumeric characters, numbers, "-", "_" and ".".');
		 return false;
	 }
	// value contain 1-255 characters, else return
	 if (!RegExp('^[a-zA-Z0-9]{1}[a-zA-Z0-9_\\-\\.]{0,255}$').test(groupNameField.val())) {
		showError('This field can contain maximal 255 characters.');
		return false;
	 }
	 if (nl.sara.beehub.principals.groups[groupNameField.val()] !== undefined) {
		 showError('This groupname is already in use.');
			 return false;
		 };
	
		 return true;
	};

	/*
	 * Action when the groupsname field will change
	 */
	 $('#groupDisplayName').change(function () {
			 groupNameListener($(this));
		 })
	  
		 	 /*
	 * Action when the Create group button is clicked
	 */
	 $('#editGroupForm').submit(function (e) {
		 e.preventDefault();
		 if (groupNameListener($('#groupDisplayName'))) {
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
		 }
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
	
	  function handleDemote(){
	    var cell = $(this).parent('td');
	    var row = cell.parent('tr.member_row');
	    var adder = $('input[name="add_admins[]"][value="' + row.attr('id') + '"]');
	    if (adder.size() > 0) {
	      adder.remove();
	    }else{
	      $('#membership_form').append('<input type="hidden" name="delete_admins[]" value="' + row.attr('id') + '" />');
	    }
	    cell.empty();
	    cell.append('nope <a href="#" class="promote_link">promote</a>');
	    $('.promote_link', cell).click(handlePromote);
	  }
	  $('.demote_link').click(handleDemote);
	
	  function handlePromote(){
	    var cell = $(this).parent('td');
	    var row = cell.parent('tr.member_row');
	    var remover = $('input[name="delete_admins[]"][value="' + row.attr('id') + '"]');
	    if (remover.size() > 0) {
	      remover.remove();
	    }else{
	      $('#membership_form').append('<input type="hidden" name="add_admins[]" value="' + row.attr('id') + '" />');
	    }
	    cell.empty();
	    cell.append('jep <a href="#" class="demote_link">demote</a>');
	    $('.demote_link', cell).click(handleDemote);
	  }
	  $('.promote_link').click(handlePromote);
	
	  $('.accept_link').click(function() {
	    var cell = $(this).parent('td');
	    var row = cell.parent('tr.member_row');
	    $('#membership_form').append('<input type="hidden" name="add_members[]" value="' + row.attr('id') + '" />');
	    cell.empty();
	    cell.append('nope <a href="#" class="promote_link">promote</a>');
	    $('.promote_link', cell).click(handlePromote);
	    $('#current_members').append(row);
	    return false;
	  }); // End of accept_link event listener
});
