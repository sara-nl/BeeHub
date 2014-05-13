/**
 * Copyright ©2014 SURFsara bv, The Netherlands
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
 */

"use strict";

/**
 * @class Groups and sponsors view
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 * 
 */
nl.sara.beehub.gs.view.GroupsSponsorsView = function() {
  this.controller = new nl.sara.beehub.gs.Controller(); 
  this.init();
};

/**
 * Initialize view
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.init = function() { 
  this.setHandlers();
};

/**
 * Set handlers in view
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.setHandlers = function() {
  // prevent hide previous collaped item
  $('.accordion-heading').unbind('click').click(function (e) {
    $(this).next().collapse("toggle");
  });

  // Kleur bij openklappen aanpassen
  $('.accordion-group').unbind('show').on('show', function (e) {
     $(e.target).parent().addClass('accordion-group-active');
  });

  // Kleur bij inklappen weer verwijderen
  $('.accordion-group').unbind('hide').on('hide', function (e) {
    $(e.target).parent().removeClass('accordion-group-active');
  });
  
  // Add join button handler
  $('.bh-gs-join-button').unbind('click').on('click', this.joinListener.bind(this));
  
  // Action when the leave button in a group is clicked
  $('.bh-gs-join-leave-button').unbind('click').on('click', this.joinLeaveListener.bind(this));
  
  // Action when the filter field is changed
  $('#bh-gs-filter-by-name').unbind('keyup').keyup(this.filterByName);
  
  // Action when the groupsname field will change
  $('#bh-gs-name').unbind('change').on('change', this.gsNameListener.bind(this));
    
  // Submit handler
  $('#bh-gs-create-form').submit(this.submitButton.bind(this));
  
  // Action when the leave button in a group is clicked
  $('.bh-gs-mygs-leave-button').on('click', this.leaveButton.bind(this));
  
  // Do not collapse at button clicks
  $('.accordion-heading a').click(function (e) {
    e.stopPropagation();
  });
}

/**
 * Submit button click handler
 * 
 * Prevent default when name field is not correct.
 * 
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.submitButton = function (e) {
  if (!this.gsNameListener(null, $('#bh-gs-name'))) {
    e.preventDefault();
    return false;
  } else {
    return true;
  };
};

/**
 * Leave button click handler in my groups tab
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.leaveButton = function (e) {
  function callback(){
    $(e.target).closest('.accordion-group').remove();
   // TODO Update Join tab
  }
  
  // Are you sure?
  if ( confirm( 'Are you sure you want to leave the '+this.controller.group_or_sponsor+' '+$(e.target).parent().prev().html()+' ?' ) ) {
    // Send leave request to server
    this.controller.leaveRequest(e.target.value, callback);
  };
};

/**
 * Join button click handler in join tab
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.joinListener = function (e) { 
  var scope = this;
  var button = e.target;
  
  function callback(){
   if ( button.text === 'Accept invitation' ) {
     // TODO: this should be handled more gracefully
     location.reload();
     return;
   };
   
   // Change button to join button
   var cancelrequestbutton = $('<button type="button" value="'+button.value+'" class="btn btn-danger bh-gs-join-leave-button">Cancel request</button>');
   cancelrequestbutton.unbind('click').on ('click', scope.joinLeaveListener.bind(scope));
   $(button).closest('a').append(cancelrequestbutton);
   $(button).remove();
  };
  this.controller.joinRequest(e.target.value, callback);
};

/**
 * Cancel request button handler in join tab
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.joinLeaveListener = function(e){
  var button = e.target;
  var scope = this;
  
  function callback(){
   // Change button to join button
   var joinbutton = $('<button type="button" value="'+button.value+'" class="btn btn-success bh-gs-join-button">Join</button>');
   joinbutton.unbind('click').on('click', scope.joinListener.bind(scope));
       $(button).closest('a').append(joinbutton);
       $(button).remove();
       // Remove group from mygroups or my sponsors
       var leavebutton = $('[id="bh-gs-panel-mygs"]').find('[value="'+button.value+'"]');
       if ( leavebutton.length !== 0 ) {
         leavebutton.closest('.accordion-group').remove();
       };
   };
  
  // Are you sure?
  if (confirm('Are you sure you want to cancel membership of the '+this.controller.group_or_sponsor+' '+$(e.target).closest('td').prev().html()+' ?')) {
    this.controller.leaveRequest(e.target.value, callback);
  };
};

/**
 * Search function in join tab
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.filterByName = function(){ 
  var filterfield=$(this);
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
   };
   var regex = new RegExp(filterfield.val(), 'gi' );
   $('div#bh-gs-join-gs.accordion').find('.accordion-group').filter(function(index) {
     $(this).hide();
     return $(this).find('th').html().match(regex) !== null;
   }).show();
};

/**
 * Checks name field
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.gsNameListener = function(e, field){
  var gsNameField;
  if (e !== null) {
    gsNameField = $(e.target);
  } else {
    gsNameField = field;
  };

  var showError = function(error){
   gsNameField.parent().parent().addClass('error');
   var errorSpan = $( '<span class="help-inline"></span>' );
      errorSpan.text( error );
   gsNameField.parent().append( errorSpan );
  };

  // TODO make tooltip with field specifications
  // This is included in bootstrap with patern

  // clear error
  gsNameField.next().remove();
  gsNameField.parent().parent().removeClass('error');

  // value not system
  if (this.controller.group_or_sponsor === "group") {
   if ( nl.sara.beehub.forbidden_group_names.indexOf( gsNameField.val() ) > -1 ) {
   showError(gsNameField.val()+' is not a valid groupname.');
   return false;
   };
  };

 // Seperate regular expressions to make the errors more specific.
 // value starts with a-zA-Z0-9, else return
  if (!RegExp('^[a-zA-Z0-9]{1}.*$').test(gsNameField.val())) {
   showError('First character must be a alphanumeric character.');
   return false;
  }
 // value only contain a-zA-Z0-9_-., else return
  if (!RegExp('^[a-zA-Z0-9]{1}[a-zA-Z0-9_\\-\\.]*$').test(gsNameField.val())) {
   showError('This field can contain aphanumeric characters, numbers, "-", "_" and ".".');
   return false;
  }
 // value contain 1-255 characters, else return
  if (!RegExp('^[a-zA-Z0-9]{1}[a-zA-Z0-9_\\-\\.]{0,255}$').test(gsNameField.val())) {
  showError('This field can contain maximal 255 characters.');
  return false;
  }
  if (this.controller.group_or_sponsor === "group") {
   if (nl.sara.beehub.principals.groups[gsNameField.val()] !== undefined) {
    showError('This groupname is already in use.');
    return false;
   };
  };
  if (this.controller.group_or_sponsor === "sponsor") {
    if (nl.sara.beehub.principals.sponsors[gsNameField.val()] !== undefined) {
     showError('This sponsorname is already in use.');
     return false;
    };
  };

  return true;
};

