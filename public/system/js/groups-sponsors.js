$(function() {
  // Check if it is group or sponsor page
  var groups = false;
  var sponsors = false;
  if (location.pathname == '/system/groups') {
    groups = true;
  } else if ((location.pathname == '/system/sponsors')) {
    sponsors = true;
  }
  console.log(location.pathname);
  console.log(groups+"-"+sponsors);
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
		
		// closure for ajax request
		function callback(button){
			return function(status){
				  if (status != 200) {
						alert('Something went wrong on the server. No changes were made.');
						return;
				  };
				  // Change button to join button
				  var cancelrequestbutton = $('<button type="button" value="'+button.val()+'" class="btn btn-danger bh-gs-join-leave-button">Cancel request</button>');
				  cancelrequestbutton.click(function () {
			            joinLeaveListener($(this));
			        });
			      button.closest('a').append(cancelrequestbutton);
			      button.remove();
				}
		}
		
		client.post(button.val(), callback(button) , 'join=1');
	}

	$('.bh-gs-join-button').on('click', function (e) {
		// TODO zorgen dat dit voor sponsors ook werkt.
		joinListener($(this));
	});

	var joinLeaveListener = function(button){
	   // Are you sure?
		if (confirm('Are you sure you want to cancel membership of the group '+button.closest('td').prev().html()+' ?')) {
			// Send leave request to server
			var client = new nl.sara.webdav.Client();
			client.post(button.val(), function(status){
        if (status === 409) {
          alert("You can't leave this group, you're the last administrator! Don't leave your herd without a shepherd, please appoint a new administrator before leaving them!");
          return;
        }else if (status != 200) {
					alert('Something went wrong on the server. No changes were made.');
					return;
				};
				// Change button to join button
				var joinbutton = $('<button type="button" value="'+button.val()+'" class="btn btn-success bh-gs-join-button">Join</button>');
				joinbutton.click(function () {
		            joinListener($(this));
		        });
		        button.closest('a').append(joinbutton);
		        button.remove();
		        // Remove group from mygroups
		        // TODO zorgen dat dit goed gaat voor sponsors
		        var leavebutton = $('[id="bh-groups-panel-mygroups"]').find('[value="'+button.val()+'"]');
		        if (leavebutton.length !=0) {
		        	leavebutton.closest('.accordion-group').remove();
		        }
			}, 'leave=1');
	    }
	}

	/*
	 * Action when the leave button in a group is clicked
	 */
	$('.bh-gs-join-leave-button').on('click', function (e) {
		// Zorgen dat dit voor sponsors ook werkt
		joinLeaveListener($(this));
	});
	
	/*
	 * Action when the leave button in a group is clicked
	 */
	$('.bh-gs-mygs-leave-button').on('click', function (e) {
		// TODO actie voor sponsors toevoegen
		var button = $(this);
	   // Are you sure?
		if (confirm('Are you sure you want to leave the group '+button.parent().prev().html()+' ?')) {
			// Send leave request to server
			var client = new nl.sara.webdav.Client();

			function callback(button){
				return function(status, data){
				  if (status === 409) {
				    alert("You can't leave this group, you're the last administrator! Don't leave your group without a leader, please appoint a new administrator before leaving them!");
			      return;
			    } else if (status !== 200) {
			      alert('Something went wrong on the server. No changes were made.');
						return;
			    };
          button.closest('.accordion-group').remove();
          // TODO zorgen dat dit goed gaat voor sponsors
          var leavebutton = $('[id="bh-groups-panel-join"]').find('[value="'+button.val()+'"]');
          var joinbutton = $('<button type="button" value="'+button.val()+'" class="btn btn-success bh-gs-join-button">Join</button>');
          joinbutton.click(function () {
            joinListener($(this));
          });
          leavebutton.closest('a').append(joinbutton);
          leavebutton.remove();
				}
			}
			client.post(button.val(), callback(button) , 'leave=1');
		}
	});

	/*
	 * Action when the filter field is changed
	 */
	// TODO voor sponsors, functie eruit
	 $('#bh-groups-filter-by-name').keyup(function () {
		var filterfield = $(this);
		// when field is empty, filter icon
		$(this).parent().find('[id="iconerase"]').remove();
		// TODO zorgen dat dit ook werkt voor sponsors
		$(this).parent().find('[id="bh-groups-icon-filter"]').remove();
		if (filterfield.val().length == 0){
			// Zorgen dat dit ook werkt voor sponsors
			var iconfilter = $('<span class="add-on" id="bh-groups-icon-filter"><i class="icon-filter" ></i></span>');
			$(this).parent().prepend(iconfilter);
		// when field is not empty, erase icon with listener
		} else {
			var iconerase = $('<span class="add-on" id="iconerase"><i class="icon-remove-circle" ></i></span>');
			$(this).parent().prepend(iconerase);
			$('#iconerase').on('click', function (e) {
				filterfield.val("");
				filterfield.trigger('keyup');
			});
		}
		var regex = new RegExp(filterfield.val(), 'gi' );
		// TODO sponsors
		$('div#bh-groups-join-groups.accordion').find('.accordion-group').filter(function(index) {
			$(this).hide();
			return $(this).find('th').html().match(regex) != null;
		}).show();
	 });

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
	 $('#bh-groups-group-name').change(function () {
		 groupNameListener($(this));
	 })

	 /*
	 * Action when the Create group button is clicked
	 */
	 $('#bh-groups-create-group-form').submit(function (e) {
		 if (!groupNameListener($('#bh-groups-group-name'))) {
			 e.preventDefault();
		 }
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