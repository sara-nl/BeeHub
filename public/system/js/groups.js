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

// TODO leave action uitvoeren
// Hier verder gaan, alleen de eerste leave button werkt.
// Uitzoeken hoe je alle buttons benadert
$('#leave').on('click', function (e) {
  // Send leave request to server
  // remove div from list
 console.log("clicked");
 console.log($(this).closest('.accordion-group'));
//  $(this).closest('.accordion-group').hide();
//  $(this).remove();
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
