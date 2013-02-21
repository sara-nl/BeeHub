// prevent hide previous collaped item
$('.accordion-heading').click(function (e) {
  $(this).next().collapse("toggle");
});

// Kleur bij openklappen aanpassen
$('.accordion-group').on('show', function (e) {
   $(e.target).parent().addClass('accordion-group-active');
});

// Kleur bij inklappen weer verwijderen
$('.accordion-group').on('hide', function (e) {
  $(e.target).parent().removeClass('accordion-group-active');
});

/*
 * Action when the leave button at a group is clicked
 */
$('.leavebutton').on('click', function (e) {
	var button = $(this);
  // Are you sure?
	if (confirm('Are you sure you want to leave the group '+button.parent().prev().html()+' ?')) {
		// Send leave request to server
		var client = new nl.sara.webdav.Client();
		client.post(button.val(), function(status){
			  if (status != 200) {
					alert('Something went wrong on the server. No changes were made.');
					return;
			  };
			  button.closest('.accordion-group').hide();
		}, 'delete_members[]=' + encodeURIComponent('/system/users/marja'));
    }
});

// TODO request action uitvoeren
$('#requestmembershipbutton').on('click', function (e) {
  alert("button request membership clicked")
});

//TODO request action uitvoeren
$('#cancelrequestinvitationbutton').on('click', function (e) {
  alert("button cancel request invitation clicked")
});

// Pieterb:
$('ul#beehub-top-tabs a').click(function (e) {
  e.preventDefault();
  $(this).tab('show');
});

$('.accordion-heading a').click(function (e) { 
  e.stopPropagation(); 
});

$('.btn-danger').click(function (e) {
  e.stopPropagation();
});
