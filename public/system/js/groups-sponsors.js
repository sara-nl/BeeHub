/**
 * Copyright ©2013 SURFsara bv, The Netherlands
 *
 * This file is part of the beehub client
 *
 * beehub client is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * beehub-client is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with beehub.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */

"use strict";

$(function() {
  var path = location.pathname;
  // add slash to the end of path
  if (!path.match(/\/$/)) {
    path=path+'/'; 
  } 
  // Check if it is group or sponsor page 
  var group_or_sponsor="";
  if ( path.substr(0, nl.sara.beehub.groups_path.length) === nl.sara.beehub.groups_path ) {
    group_or_sponsor = "group";
  } else if ( path.substr(0, nl.sara.beehub.sponsors_path.length) === nl.sara.beehub.sponsors_path ) {
    group_or_sponsor = "sponsor";
  }
  
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
        if (status !== 200) {
          alert('Something went wrong on the server. No changes were made.');
          return;
        }

        if ( button.text() === 'Accept invitation' ) {
          // TODO: this should be handled more gracefully
          location.reload();
          return;
        }
        // Change button to join button
        var cancelrequestbutton = $('<button type="button" value="'+button.val()+'" class="btn btn-danger bh-gs-join-leave-button">Cancel request</button>');
        cancelrequestbutton.click(function () {
                joinLeaveListener($(this));
        });
        button.closest('a').append(cancelrequestbutton);
        button.remove();
			};
		}
		
		client.post(button.val(), callback(button) , 'join=1');
	};

	$('.bh-gs-join-button').on('click', function (e) {
		joinListener($(this));
	});

	var joinLeaveListener = function(button){
    // Closure for ajax request
    function callback(button){
      return function(status){
        if (status === 409) {
          alert("You can't leave this group, you're the last administrator! Don't leave your herd without a shepherd, please appoint a new administrator before leaving them!");
          return;
        }else if ( status !== 200 ) {
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
            // Remove group from mygroups or my sponsors
            var leavebutton = $('[id="bh-gs-panel-mygs"]').find('[value="'+button.val()+'"]');
            if ( leavebutton.length !== 0 ) {
              leavebutton.closest('.accordion-group').remove();
            }
      };
    }

    // Are you sure?
    if (confirm('Are you sure you want to cancel membership of the '+group_or_sponsor+' '+button.closest('td').prev().html()+' ?')) {
      // Send leave request to server
      var client = new nl.sara.webdav.Client();
			client.post(button.val(), callback(button), 'leave=1');
	  }
	};

	/*
	 * Action when the leave button in a group is clicked
	 */
	$('.bh-gs-join-leave-button').on('click', function (e) {
		joinLeaveListener($(this));
	});
	
	/*
	 * Action when the leave button in a group is clicked
	 */
	$('.bh-gs-mygs-leave-button').on('click', function (e) {
    function callback(button){
      return function(status, data){
        if (status === 409) {
          alert("You can't leave this "+group_or_sponsor+", you're the last administrator! Don't leave your "+group_or_sponsor+" without a leader, please appoint a new administrator before leaving them!");
          return;
        } else if (status !== 200) {
          alert('Something went wrong on the server. No changes were made.');
          return;
        };
        button.closest('.accordion-group').remove();
        // TODO zorgen dat dit goed gaat voor sponsors
        var leavebutton = $('[id="bh-gs-panel-join"]').find('[value="'+button.val()+'"]');
        var joinbutton = $('<button type="button" value="'+button.val()+'" class="btn btn-success bh-gs-join-button">Join</button>');
        joinbutton.click(function () {
          joinListener($(this));
        });
        leavebutton.closest('a').append(joinbutton);
        leavebutton.remove();
      };
    }

    var button = $(this);
    // Are you sure?
    if ( confirm( 'Are you sure you want to leave the '+group_or_sponsor+' '+button.parent().prev().html()+' ?' ) ) {
      // Send leave request to server
      var client = new nl.sara.webdav.Client();
			client.post(button.val(), callback(button) , 'leave=1');
		}
	});

	function filterByName(){ 
	  filterfield=$(this);
    // when field is empty, filter icon
    $(this).parent().find('[id="bh-gs-icon-erase"]').remove();
    $(this).parent().find('[id="bh-gs-icon-filter"]').remove();
    if ( filterfield.val().length === 0 ){
      // Zorgen dat dit ook werkt voor sponsors
      var iconfilter = $('<span class="add-on" id="bh-gs-icon-filter"><i class="icon-filter" ></i></span>');
      $(this).parent().prepend(iconfilter);
    // when field is not empty, erase icon with listener
    } else {
      var iconerase = $('<span class="add-on" id="bh-gs-icon-erase"><i class="icon-remove-circle" ></i></span>');
      $(this).parent().prepend(iconerase);
      $('#bh-gs-icon-erase').on('click', function (e) {
        filterfield.val("");
        filterfield.trigger('keyup');
      });
    }
    var regex = new RegExp(filterfield.val(), 'gi' );
    $('div#bh-gs-join-gs.accordion').find('.accordion-group').filter(function(index) {
      $(this).hide();
      return $(this).find('th').html().match(regex) !== null;
    }).show();
	}
	
	/*
	 * Action when the filter field is changed
	 */
	 $('#bh-gs-filter-by-name').keyup(filterByName);

	var groupNameListener = function(groupNameField){
		 var showError = function(error){
			 groupNameField.parent().parent().addClass('error');
			 var errorSpan = $( '<span class="help-inline"></span>' );
       errorSpan.text( error );
			 groupNameField.parent().append( errorSpan );
		 };

		 // TODO make tooltip with field specifications
		 // This is included in bootstrap with patern

		 // clear error
		 groupNameField.next().remove();
		 groupNameField.parent().parent().removeClass('error');

		 // value not system
		 if ( nl.sara.beehub.forbidden_group_names.indexOf( groupNameField.val() ) > -1 ) {
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
	 $('#bh-gs-group-name').change(function () {
		 groupNameListener($(this));
	 });
	 /*
	 * Action when the Create group button is clicked
	 */
	 $('#bh-gs-create-group-form').submit(function (e) {
		 if (!groupNameListener($('#bh-gs-group-name'))) {
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
