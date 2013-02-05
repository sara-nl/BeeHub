$('.accordion-group').on('show', function (e) {
   $(e.target).parent().addClass('customaccordionactive');
});

$('.accordion-group').on('hide', function (e) {
	$(e.target).parent().removeClass('customaccordionactive');
});
