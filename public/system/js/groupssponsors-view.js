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
nl.sara.beehub.gs.view.GroupsSponsorsView = function(controller, parent) {
  this.controller = controller;
  this.parent = parent
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
  $('.bh-gss-join-button').unbind('click').on('click', this.joinListener.bind(this));
  
  // Action when the leave button in a group is clicked
  $('.bh-gss-join-leave-button').unbind('click').on('click', this.joinLeaveListener.bind(this));
  
  // Action when the filter field is changed
  $('#bh-gss-filter-by-name').unbind('keyup').keyup(this.filterByName);
  
  // Action when the groupsname field will change
  $('#bh-gss-name').unbind('change').on('change', this.gssNameListener.bind(this));
    
  // Submit handler
  $('#bh-gss-create-form').submit(this.submitButton.bind(this));
  
  // Action when the leave button in a group is clicked
  $('.bh-gss-mygss-leave-button').on('click', this.leaveButton.bind(this));
  
  // Do not collapse at button clicks
  $('.accordion-heading button').click(function (e) {
    e.stopPropagation();
  });
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
  if (!this.gssNameListener(null, $('#bh-gss-name'))) {
    e.preventDefault();
    return false;
  } else {
    return true;
  };
};

/**
 * Leave button click handler in my groups tab
 * 
 * @param {Event} e
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.leaveButton = function (e) {  
  var view = this;
  
  function callbackOk(){
    $(e.target).closest('.accordion-group').remove();
    view.parent.mask(false);
   // TODO Update Join tab
  }
  
  function callbackNotOk(){
    view.parent.mask(false);
  }
  
  // Are you sure?
  if ( confirm( 'Are you sure you want to leave the '+this.controller.group_or_sponsor+' '+$(e.target).parent().prev().html()+' ?' ) ) {
    view.parent.mask(true);
    // Send leave request to server
    this.controller.leaveRequest(e.target.value, callbackOk, callbackNotOk);
  };
};

/**
 * Join button click handler in join tab
 * 
 * @param {Event} e
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.joinListener = function (e) { 
  var view = this;
  var button = e.target;
  
  function callbackOk(){
   if ( button.text === 'Accept invitation' ) {
     // TODO: this should be handled more gracefully
     location.reload();
     return;
   };
   
   // Change button to join button
   var cancelrequestbutton = $('<button type="button" value="'+button.value+'" class="btn btn-danger bh-gss-join-leave-button">Cancel request</button>');
   cancelrequestbutton.unbind('click').on ('click', view.joinLeaveListener.bind(view));
   $(button).closest('a').append(cancelrequestbutton);
   $(button).remove();
   view.parent.mask(false);
  };
  
  function callbackNotOk(){
    view.parent.mask(false);
  };
  
  view.parent.mask(true);
  this.controller.joinRequest(e.target.value, callbackOk, callbackNotOk);
};

/**
 * Cancel request button handler in join tab
 * 
 * @param {Event} e
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.joinLeaveListener = function(e){
  var button = e.target;
  var view = this;
  
  function callbackOk(){
   // Change button to join button
   var joinbutton = $('<button type="button" value="'+button.value+'" class="btn btn-success bh-gss-join-button">Join</button>');
   joinbutton.unbind('click').on('click', view.joinListener.bind(view));
   $(button).closest('a').append(joinbutton);
   $(button).remove();
   // Remove group from mygroups or my sponsors
   var leavebutton = $('[id="bh-gss-panel-mygss"]').find('[value="'+button.value+'"]');
   if ( leavebutton.length !== 0 ) {
     leavebutton.closest('.accordion-group').remove();
   };
   
   view.parent.mask(false);
  };
   
  function callbackNotOk(){
    view.parent.mask(false);
  }
  
  // Are you sure?
  if (confirm('Are you sure you want to cancel membership of the '+this.controller.group_or_sponsor+' '+$(e.target).closest('td').prev().html()+' ?')) {
    view.parent.mask(true);
    view.controller.leaveRequest(e.target.value, callbackOk, callbackNotOk);
  };
};

/**
 * Search function in join tab
 * 
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.filterByName = function(){ 
  var filterfield=$(this);
  // when field is empty, filter icon
  $(this).parent().find('[id="bh-gss-icon-erase"]').remove();
  $(this).parent().find('[id="bh-gss-icon-filter"]').remove();
  if ( filterfield.val().length === 0 ){
    // Zorgen dat dit ook werkt voor sponsors
    var iconfilter = $('<span class="add-on" id="bh-gss-icon-filter"><i class="icon-filter" ></i></span>');
    $(this).parent().prepend(iconfilter);
  // when field is not empty, erase icon with listener
  } else {
    var iconerase = $('<span class="add-on" id="bh-gss-icon-erase"><i class="icon-remove-circle" ></i></span>');
    $(this).parent().prepend(iconerase);
    $('#bh-gss-icon-erase').on('click', function (e) {
      filterfield.val("");
      filterfield.trigger('keyup');
    });
  };
  var regex = new RegExp(filterfield.val(), 'gi' );
  $('div#bh-gss-join-gss.accordion').find('.accordion-group').filter(function(index) {
    $(this).hide();
    return $(this).find('th').html().match(regex) !== null;
  }).show();
};

/**
 * Checks name field
 * 
 * This function can be called as event handler but also with an input fiels as parameter
 * 
 * @param {Event}      e
 * @param {DOM object} field
 */
nl.sara.beehub.gs.view.GroupsSponsorsView.prototype.gssNameListener = function(e, field){
  var gssNameField;
  if (e !== null) {
    gssNameField = $(e.target);
  } else {
    gssNameField = field;
  };

  var showError = function(error){
   gssNameField.parent().parent().addClass('error');
   var errorSpan = $( '<span class="help-inline"></span>' );
      errorSpan.text( error );
   gssNameField.parent().append( errorSpan );
  };

  // TODO make tooltip with field specifications
  // This is included in bootstrap with patern

  // clear error
  gssNameField.next().remove();
  gssNameField.parent().parent().removeClass('error');

  // value not system
  if (this.controller.group_or_sponsor === "group") {
   if ( nl.sara.beehub.forbidden_group_names.indexOf( gssNameField.val() ) > -1 ) {
   showError(gssNameField.val()+' is not a valid groupname.');
   return false;
   };
  };

 // Seperate regular expressions to make the errors more specific.
 // value starts with a-zA-Z0-9, else return
  if (!RegExp('^[a-zA-Z0-9]{1}.*$').test(gssNameField.val())) {
   showError('First character must be a alphanumeric character.');
   return false;
  }
 // value only contain a-zA-Z0-9_-., else return
  if (!RegExp('^[a-zA-Z0-9]{1}[a-zA-Z0-9_\\-\\.]*$').test(gssNameField.val())) {
   showError('This field can contain aphanumeric characters, numbers, "-", "_" and ".".');
   return false;
  }
 // value contain 1-255 characters, else return
  if (!RegExp('^[a-zA-Z0-9]{1}[a-zA-Z0-9_\\-\\.]{0,255}$').test(gssNameField.val())) {
  showError('This field can contain maximal 255 characters.');
  return false;
  }
  if (this.controller.group_or_sponsor === "group") {
   if (nl.sara.beehub.principals.groups[gssNameField.val()] !== undefined) {
    showError('This groupname is already in use.');
    return false;
   };
  };
  if (this.controller.group_or_sponsor === "sponsor") {
    if (nl.sara.beehub.principals.sponsors[gssNameField.val()] !== undefined) {
     showError('This sponsorname is already in use.');
     return false;
    };
  };
  return true;
};

