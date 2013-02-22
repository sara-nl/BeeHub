$(function() {
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
	 * Action when the join button at a group is clicked
	 */
	var joinListener = function(button){
		// Send leave request to server
		var client = new nl.sara.webdav.Client();
		client.post(button.val(), function(status){
		  if (status != 200) {
				alert('Something went wrong on the server. No changes were made.');
				return;
		  };
		  // Change button to join button
		  var cancelrequestbutton = $('<button type="button" value="'+button.val()+'" class="btn btn-danger joinleavebutton">Cancel request</button>');
		  cancelrequestbutton.click(function () {
	            joinLeaveListener($(this));
	        });
	      button.closest('a').append(cancelrequestbutton);
	      button.remove();
		}, 'join=1');
	}
	
	$('.joinbutton').on('click', function (e) {
		joinListener($(this));
	});
	
	var joinLeaveListener = function(button){
	   // Are you sure?
		if (confirm('Are you sure you want to cancel membership of the group '+button.closest('td').prev().html()+' ?')) {
			// Send leave request to server
			var client = new nl.sara.webdav.Client();
			client.post(button.val(), function(status){
				if (status != 200) {
					alert('Something went wrong on the server. No changes were made.');
					return;
				};
				// Change button to join button
				var joinbutton = $('<button type="button" value="'+button.val()+'" class="btn btn-success joinbutton">Join</button>');
				joinbutton.click(function () {
		            joinListener($(this));
		        });
		        button.closest('a').append(joinbutton);
		        button.remove();
		        // Remove group from mygroups
		        var leavebutton = $('[id="panel-mygroups"]').find('[value="'+button.val()+'"]');
		        if (leavebutton.length !=0) {
		        	leavebutton.closest('.accordion-group').remove(); 
		        }
			}, 'leave=1');
	    }
	}
		
	/*
	 * Action when the leave button in a group is clicked
	 */
	$('.joinleavebutton').on('click', function (e) {
		joinLeaveListener($(this));
	});
	
	/*
	 * Action when the leave button in a group is clicked
	 */
	$('.mygroupsleavebutton').on('click', function (e) {
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
			  button.closest('.accordion-group').remove(); 
			  var leavebutton = $('[id="panel-join"]').find('[value="'+button.val()+'"]');
			  var joinbutton = $('<button type="button" value="'+button.val()+'" class="btn btn-success joinbutton">Join</button>');
			  joinbutton.click(function () {
				joinListener($(this));
			  });
			  leavebutton.closest('a').append(joinbutton);
		      leavebutton.remove();
			}, 'leave=1');
	    }
	});
	
	// TODO listerer aan erase icon hangen 
	// gewoon om de laatste te zijn
	 $('#filterbyname').keyup(function () {
		var value = $(this).val();
		// when field is empty, filter icon
		$(this).parent().find('[id="iconerase"]').remove();
		$(this).parent().find('[id="iconfilter"]').remove();
		if (value.length == 0){
			var iconfilter = $('<span class="add-on" id="iconfilter"><i class="icon-filter" ></i></span>');
			$(this).parent().prepend(iconfilter);
		// when field is not empty, erase icon with listener
		} else {
			var iconerase = $('<span class="add-on" id="iconerase"><i class="icon-remove-circle" ></i></span>');
			$(this).parent().prepend(iconerase);
		}
		var regex = new RegExp( $(this).val(), 'gi' );
		$('div#joingroups.accordion').find('.accordion-group').filter(function(index) {
			$(this).hide();
			return $(this).find('th').html().match(regex) != null;
		}).show(); 
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
});