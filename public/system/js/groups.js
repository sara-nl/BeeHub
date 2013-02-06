$('.accordion-group').on('show', function (e) {
   $(e.target).parent().addClass('customaccordionactive');
});

$('.accordion-group').on('hide', function (e) {
	$(e.target).parent().removeClass('customaccordionactive');
});
$('#buttontest').on('click', function (e) {
//	$(e.target).parent().removeClass('customaccordionactive');
	alert("button action")
});
$('#requestmembershipbutton').on('click', function (e) {
	alert("button action")
});
