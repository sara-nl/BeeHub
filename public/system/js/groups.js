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

var joinListener = function(){
	console.log("joinbutton clicked");
}

/*
 * Action when the join button at a group is clicked
 */
$('.joinbutton').on('click', function (e) {
	joinListener();
});

/*
 * Action when the leave button at a group is clicked
 */
$('.leavebutton').on('click', function (e) {
	var button = $(this);
	console.log(button.val());
   // Are you sure?
	if (confirm('Are you sure you want to leave the group '+button.parent().prev().html()+' ?')) {
		// Send leave request to server
		var client = new nl.sara.webdav.Client();
		client.post(button.val(), function(status){
			  if (status != 200) {
					alert('Something went wrong on the server. No changes were made.');
					return;
			  };
			  // Actions to take depending on tab panel
			  if (button.closest('.tab-pane').attr('id') == 'panel-mygroups') {
				// remove group from mygroups view
				  button.closest('.accordion-group').hide(); 
			  } else if (button.closest('.tab-pane').attr('id') == 'panel-join') {
				// Change button to join button
				button.html('Join');
				button.removeClass('leavebutton');
				button.removeClass('btn-danger');
				button.addClass('btn-info');
				button.addClass('joinbutton');
				button.on('click',joinListener);
			  }
		}, 'leave=1');
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
