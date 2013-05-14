$(function (){
	$('#beehub-docs-find-sponsor').click(function(){
		$('#top-level-tab a[href="#pane-sponsors"]').tab('show'); // Select tab by name
	});
	
	$('#beehub-docs-create-account').click(function(){
		$('#top-level-tab a[href="#pane-account"]').tab('show'); // Select tab by name
	});
	
	$('#beehub-docs-mount').click(function(){
		$('#top-level-tab a[href="#pane-mounting"]').tab('show'); // Select tab by name
	});
	
	$('#beehub-docs-share').click(function(){
		$('#top-level-tab a[href="#pane-share"]').tab('show'); // Select tab by name
	});
});
