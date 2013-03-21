$(function (){

  $('#save-button').click(function() {
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
  }); // End of button click event listener
  
  $('#cancel-button').click(
	function() {
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
