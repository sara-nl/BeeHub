// Kleur bij openklappen aanpassen
$('.accordion-group').on('show', function (e) {
   $(e.target).parent().addClass('custom-accordion-active');
});

// Kleur bij inklappen weer verwijderen
$('.accordion-group').on('hide', function (e) {
	$(e.target).parent().removeClass('custom-accordion-active');
});

// TODO accept action uitvoeren
$('#acceptinvitationbutton').on('click', function (e) {
	alert("button accept inivtation clicked")
});

// TODO request action uitvoeren
$('#requestmembershipbutton').on('click', function (e) {
	alert("button request membership clicked")
});

//TODO request action uitvoeren
$('#cancelrequestinvitationbutton').on('click', function (e) {
	alert("button cancel request invitation clicked")
});
