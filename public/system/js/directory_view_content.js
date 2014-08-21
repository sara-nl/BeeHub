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
 */

"use strict";

/** 
 * Beehub Client Content
 * 
 * Content contains: resource handle buttons, table with resources
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */
(function(){
  /**
   * Initialize content view
   *
   * Public function
   */
  nl.sara.beehub.view.content.init = function() {
    // This is needed to sort the size table on the right way
    $.tablesorter.addParser({
      id: 'filesize', 
      is: function(s) { 
       return s.match(new RegExp( /[0-9]+(\.[0-9]+)?\ (KB|B|GB|MB|TB)/ ));
      }, 
      format: function(s) {
        // If not a dir
        if (s !== "") {
          var suf = s.match(new RegExp( /(KB|B|GB|MB|TB)$/ ))[1];
          var num = parseFloat(s.match( new RegExp( /^[0-9]+(\.[0-9]+)?/ ))[0]);
        } else {
          var suf = "DIR";
        }
        switch(suf) {
          case 'DIR':
            return 0;
          case 'B':
            return num;
          case 'KB':
            return num * 1024;
          case 'MB':
            return num * 1024 * 1024;
          case 'GB':
            return num * 1024 * 1024 * 1024;
          case 'TB':
            return num * 1024 * 1024 * 1024 * 1024;
          }
      }, 
      type: 'numeric' 
    }); 
    
    // Sort on name attribute
    $.tablesorter.addParser({ 
      // set a unique id 
      id: 'name', 
      is: function(s) { 
        // return false so this parser is not auto detected 
        return false; 
      }, 
      format: function(s, table, cell, cellIndex) { 
        // get data attributes from $(cell).attr('data-something');
        // check specific column using cellIndex
        return $(cell).attr('data-value');
      }, 
      // set type, either numeric or text 
      type: 'text' 
    }); 
  
  //make table sortable with tablesorter plugin
    $("#bh-dir-content-table").tablesorter({
      // which columns are sortable
      headers: { 
        0 : { sorter: false },
        1 : { sorter: false },
        4 : { sorter: 'filesize'},
        5 : { sorter: 'data-value'},
        8 : { sorter:false }
      },
      dateFormat : "ddmmyyyy", // set the default date format
      widthFixed: false,
      // Fixed header on top of the table
      widgets : ['stickyHeaders'],
      // Start with sort asc
      sortRestart: true,
      sortList: [ [ 2, 0 ] ],
      widgetOptions : {
        // apply sticky header top below the top of the browser window
        stickyHeaders_offset : 186
      }
    });

   // Remember previous sort columns and use it for second sort column
    $("#bh-dir-content-table").unbind( 'sortStart' ).bind("sortStart",function(sorter) {
     sorter.target.config.sortAppend = sorter.target.config.sortList;
    });

    // Add listeners
    // Go to users homedirectory handler
    $('.bh-dir-content-gohome').unbind('click').click(function(){nl.sara.beehub.controller.goToPage($(this).attr("id"));});
    
    // Go up one directory button
    $('.bh-dir-content-up').unbind('click').click(function(){nl.sara.beehub.controller.goToPage($(this).attr("id"));});
    
    // Upload button
    $('.bh-dir-content-upload').unbind('click').click(handle_upload_button_click);
    
    // When upload files are choosen
    $('.bh-dir-content-upload-hidden').unbind('change').change(handle_upload_change);
    
    // New folder button
    $('.bh-dir-content-newfolder').unbind('click').click(handle_newfolder_button_click);
    
    // Delete button click handler
    $('.bh-dir-content-delete').unbind('click').click(handle_delete_button_click);
    
    // Copy button click handler
    $('.bh-dir-content-copy').unbind('click').click(handle_copy_button_click);
    
    // Move button click handler
    $('.bh-dir-content-move').unbind('click').click(handle_move_button_click);
 
    // All handlers that belong to a row
    setRowHandlers();
  };
  
  /*
   * Clear view
   * 
   * Public function
   */
  nl.sara.beehub.view.content.clearView = function(){
    // uncheck checkboxes
    $('.bh-dir-content-checkboxgroup').prop('checked',false);
    $('.bh-dir-content-checkbox').prop('checked',false);
    // disable buttons
    disable_action_buttons();
  };
  
  /*
   * Action for all buttons in the fixed view on the top of the contents table
   * 
   * Public function
   * 
   * @param String action 'hide' or 'show'
   * 
   */
  nl.sara.beehub.view.content.allFixedButtons = function(action){
    switch(action)
    {
      case 'hide':
        $('.bh-dir-content-gohome').hide();
        $('.bh-dir-content-up').hide();
        $('.bh-dir-content-upload').hide();
        $('.bh-dir-content-newfolder').hide();
        $('.bh-dir-content-delete').hide();
        $('.bh-dir-content-copy').hide();
        $('.bh-dir-content-move').hide();
        break;
      case 'show':
        $('.bh-dir-content-gohome').show();
        $('.bh-dir-content-up').show();
        $('.bh-dir-content-upload').show();
        $('.bh-dir-content-newfolder').show();
        $('.bh-dir-content-delete').show();
        $('.bh-dir-content-copy').show();
        $('.bh-dir-content-move').show();
        break;
      default:
        // This should never happen
    };
  };
  
  /*
  * Set all handlers that belong to a row.
  * 
  */
  var setRowHandlers = function(){
    // Checkbox select all handler: select or deselect all checkboxes
    $('.bh-dir-content-checkboxgroup').unbind( 'click' ).click(handle_checkall_checkbox_click);
    
    // Checkbox handler: select or deselect checkbox
    $('.bh-dir-content-checkbox').unbind( 'click' ).click(handle_checkbox_click);
    
    // Open selected handler: this can be a file or a directory
    $('.bh-dir-content-openselected').unbind('click').click(function(){nl.sara.beehub.controller.goToPage($(this).attr('data-value'));});

    // Edit icon
    $('.bh-dir-content-edit').unbind('click').click(handle_edit_menu_click);
   
    // View acl
    $('.bh-dir-content-acl').unbind( 'click' ).click(handle_acl_menu_click);
    
    // Sponsor
    $('.bh-dir-sponsor').unbind( 'click' ).click(handle_sponsor_click);
    
  };
  
  /*
   * Trigger rename click on resource
   * 
   * Public function
   * 
   * @param Object resource Resource to trigger rename click
   */
  nl.sara.beehub.view.content.triggerRenameClick = function(resource){
    $("tr[id='"+resource.path+"']").find('.bh-dir-content-edit').trigger('click');
  };
  
  /*
   * Create contentview row from resource object
   * 
   * @param Object resource Resource object
   * 
   * @return String Row string 
   */
  var createRow = function(resource){
    var row = [];
    
    row.push('<tr id="'+nl.sara.beehub.controller.htmlEscape(resource.path)+'">');
    
    // Dropdown menu
    row.push('<td>\
      <div class="dropdown">\
        <a class="dropdown-toggle bh-dir-content-menu" data-toggle="dropdown" href="#">\
          <i class="icon-align-justify" style="cursor: pointer"></i></a>\
        <ul class="dropdown-menu  bh-dir-contents-menu" role="menu" aria-labelledby="dLabel">\
          <li><a class="bh-dir-content-edit" href="#">Rename</a></li>\
          <li><a class="bh-dir-content-acl" href="#">Share</a></li>\
        </ul>\
      </div>\
     </td>');
    
    // Checkbox
    row.push('<td width="10px"><input type="checkbox" class="bh-dir-content-checkbox" name="'+nl.sara.beehub.controller.htmlEscape(resource.path)+'" value="'+nl.sara.beehub.controller.htmlEscape(resource.displayname)+'"></td>');
    
    // Name
    if (resource.type==='collection') {
      row.push('<td class="bh-dir-content-name displayname" data-value="'+nl.sara.beehub.controller.htmlEscape(resource.displayname)+'"><a href="'+nl.sara.beehub.controller.htmlEscape(resource.path)+'"><b>'+nl.sara.beehub.controller.htmlEscape(resource.displayname)+'/</b></a></td>');
    } else {
      row.push('<td class="bh-dir-content-name displayname" data-value="'+nl.sara.beehub.controller.htmlEscape(resource.displayname)+'"><a href="'+nl.sara.beehub.controller.htmlEscape(resource.path)+'">'+nl.sara.beehub.controller.htmlEscape(resource.displayname)+'</a></td>');
    }
    row.push('<td class="bh-dir-content-rename-td" hidden="true"><input class="bh-dir-content-rename-form" name="'+nl.sara.beehub.controller.htmlEscape(resource.displayname)+'" value="'+nl.sara.beehub.controller.htmlEscape(resource.displayname)+'"></td>');
    
    if (resource.type==='collection') {
      // Size
      row.push('<td class="contentlength" data-value="'+nl.sara.beehub.controller.htmlEscape(resource.contentlength)+'"></td>');
      // Type
      row.push('<td class="type" data-value="'+nl.sara.beehub.controller.htmlEscape(resource.type)+'"><i data-value="'+nl.sara.beehub.controller.htmlEscape(resource.path)+'" class="icon-folder-close bh-dir-content-openselected" style="cursor: pointer">></i></td>');
    } else {
      // Size
      row.push('<td class="contentlength" data-value="'+nl.sara.beehub.controller.htmlEscape(resource.contentlength)+'">'+nl.sara.beehub.utils.bytesToSize(resource.contentlength, 2)+'</td>');
      //Type
      row.push('<td class="type" data-value="'+nl.sara.beehub.controller.htmlEscape(resource.type)+'">'+nl.sara.beehub.controller.htmlEscape(resource.type)+'</td>');
  
    }
    // Last Modified
    var date = new Date(resource.lastmodified); 
    // Make same show string as shown with php
    var day = date.getDate();
    var month = date.getMonth()+1;
    var year = date.getFullYear();
    var hours = date.getHours();
    var minutes = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
    var dateString = (day+"-"+month+"-"+year+" "+hours+":"+minutes);
    
    row.push('<td class="lastmodified" data-value="'+nl.sara.beehub.controller.htmlEscape(resource.lastmodified)+'">'+dateString+'</td>');
    
    // Owner
    row.push('<td class="owner" data-value="'+nl.sara.beehub.controller.htmlEscape(resource.owner)+'">'+nl.sara.beehub.controller.htmlEscape(nl.sara.beehub.controller.getDisplayName(resource.owner))+'</td>');
    
    // Sponsor
    row.push('<td class="bh-dir-sponsor" style="cursor: pointer" data-value="'+nl.sara.beehub.controller.htmlEscape(resource.sponsor)+'">'+nl.sara.beehub.controller.htmlEscape(nl.sara.beehub.controller.getDisplayName(resource.sponsor))+'</td>');

    // Select sponsor
    row.push('<td class="bh-dir-sponsor-dropdown" hidden="hidden"><select class="bh-dir-sponsor-select"> </select></td>');
   
    // ACL on resource row
    row.push('<td class="bh-resource-specific-acl"></td>');
    // TODO Share link, not implemented yet
  //  row.push('<td></td>');
    
    row.push('</tr>');
    return row.join("");
  };
  
  
  /*
   * Put all selected resources in an array
   * 
   * @return Array resources All selected resources in an array
   */
  var getSelectedResources = function(){
    var resources=[];
    $.each($('.bh-dir-content-checkbox:checked'), function(i, val){
      var resource = new nl.sara.beehub.ClientResource(val.name);
      resource.displayname = val.value;
      resources.push(resource);
    });
    return resources;
  };
  
  /*
   * Get unknown values of resource in table
   * 
   * Public function
   * 
   * @param Object resource Resource object
   */
  nl.sara.beehub.view.content.getUnknownResourceValues = function(resource){
    // Displayname
    if (resource.displayname === undefined) {
      resource.displayname = $("tr[id='"+resource.path+"']").find('.displayname').attr('data-value');
    }
    
    // Type
    if (resource.type === undefined) {
      resource.type = $("tr[id='"+resource.path+"']").find('.type').attr('data-value');
    }
    
    // Owner
    if (resource.owner === undefined) {
      resource.owner = $("tr[id='"+resource.path+"']").find('.owner').attr('data-value');
    }
    
    // Sponsor
    if (resource.sponsor === undefined) {
      resource.sponsor = $("tr[id='"+resource.path+"']").find('.bh-dir-sponsor').attr('data-value');
    }
    
    // Contentlenght
    if (resource.contentlength === undefined) {
      resource.contentlength = $("tr[id='"+resource.path+"']").find('.contentlength').attr('data-value');
    }
    
    // Last modiefied
    if (resource.lastmodified === undefined) {
      resource.lastmodified = $("tr[id='"+resource.path+"']").find('.lastmodified').attr('data-value');
    }
    return resource;
  };
  
  /*
   * Add resource to content view
   * 
   * Public function
   * 
   * @param {Object} resource Resource object
   */
  nl.sara.beehub.view.content.addResource = function(resource){
    var collection = resource.path.substr( 0, resource.path.lastIndexOf( '/', resource.path.length - 2 ) + 1 );
    var currentPath = location.pathname;

    if ( currentPath.substr( -1 ) !== '/' ) {
      currentPath += '/';
    }

    if ( collection !== currentPath ) {
      return;
    }

    var row = createRow(resource);

    $("#bh-dir-content-table").append(row);
    $("#bh-dir-content-table").trigger("update");
    // Set handlers again
    setRowHandlers();
  };
  
  /*
   * Delete resource from content view
   * 
   * Public function
   * 
   * @param {Object} resource Resource object
   */
  nl.sara.beehub.view.content.deleteResource = function(resource){
    $("tr[id='"+resource.path+"']").remove();
  };
  
  /**
   * Set acl on resource on or off - icon at de last column
   * 
   * @param {Boolean}   ownACL        true or false
   * @param {String}    resourcePath  Path of resource
   * 
   */
  nl.sara.beehub.view.content.setCustomAclOnResource = function(ownACL, resourcePath){
    // If the resource has it's own ACE, set the view appropriately
    var resourceDiv = $( 'tr[id="' + resourcePath + '"]' );
    var exclamation = resourceDiv.find( '.bh-resource-specific-acl' );
    if ( ownACL && ( exclamation.html() === "" )) {
      exclamation.replaceWith( '<td title="Resource specific ACL set!" class="bh-resource-specific-acl"><i class="icon-star-empty"></i></td>' );
    } else if ( ! ownACL ) {
      exclamation.replaceWith( '<td class="bh-resource-specific-acl"></td>');
    }
  };
  
  /*
   * Update resource from content view
   * 
   * Public function
   * 
   * @param {Object} resourceOrg Original resource object
   * @param {Object} resourceOrg New resource object
   */
  nl.sara.beehub.view.content.updateResource = function(resourceOrg, resourceNew){
    var collectionOrg = resourceOrg.path.substr( 0, resourceOrg.path.lastIndexOf( '/', resourceOrg.path.length - 2 ) + 1 );
    var collectionNew = resourceNew.path.substr( 0, resourceNew.path.lastIndexOf( '/', resourceNew.path.length - 2 ) + 1 );
    var currentPath = location.pathname;
    if ( currentPath.substr( -1 ) !== '/' ) {
      currentPath += '/';
    }

    if ( collectionOrg === currentPath ) {
//       delete current row
      nl.sara.beehub.view.content.deleteResource(resourceOrg);
    }

    if ( collectionNew === currentPath ) {
      // add new row
      nl.sara.beehub.view.content.addResource(resourceNew);
    }
  };
  
  
  /*
   * Enable copy, move, delete buttons
   */
  var enable_action_buttons = function() {
    $('.bh-dir-content-copy').removeAttr("disabled");
    $('.bh-dir-content-move').removeAttr("disabled");
    $('.bh-dir-content-delete').removeAttr("disabled");
  };
  
  /*
   * Disable copy, move, delete buttons
   */
  var disable_action_buttons = function() {
    $('.bh-dir-content-copy').attr("disabled","disabled");
    $('.bh-dir-content-move').attr("disabled","disabled");
    $('.bh-dir-content-delete').attr("disabled","disabled");
  };
  
  /*
   * On click handler select all checkbox
   * Check or uncheck all checkboxes in content view
   */
  var handle_checkall_checkbox_click = function() {
    // When selected, check all
    if ($(this)[0].checked) {
      $('.bh-dir-content-checkbox').prop('checked',true);
      if ($('.bh-dir-content-checkbox:checked').length > 0) {
        enable_action_buttons();
      };
    // Else uncheck all
    } else {
      $('.bh-dir-content-checkbox').prop('checked',false);
      disable_action_buttons();
    }
  };
  
  /*
   * On click handler select checkbox
   * Enable or disable buttons
   */
  var handle_checkbox_click = function() {
    // When more then one resource is selected, enable buttons
    if ($('.bh-dir-content-checkbox:checked').length > 0) {
      enable_action_buttons();
    // else disable buttons
    } else {
      disable_action_buttons();
    }
  };
  
  /*
   * Onclick handler edit icon in content view
   */
  var handle_edit_menu_click = function(e){
    e.preventDefault();
    // TODO - instead show and hide, replace to prevent table colums move
    // Search nearest name field and hide
    $(this).closest("tr").find(".bh-dir-content-name").hide();
    // Show form
    $(this).closest("tr").find(".bh-dir-content-rename-td").show();
    
    // When giving the input field the value of the field again it will
    // be empty after cancel a overwrite. Bug???
    var name = $(this).closest("tr").find(".bh-dir-content-rename-td").find(':input').attr('name');
    $(this).closest("tr").find(".bh-dir-content-rename-td").find(':input').val(name);
    
    // Focus mouse
    $(this).closest("tr").find(".bh-dir-content-rename-td").find(':input').focus(
      function() {
        $(this).select();
        }
     );
    $(this).closest("tr").find(".bh-dir-content-rename-td").find(':input').focus();

    // Set rename handler
    $(this).closest("tr").find(".bh-dir-content-rename-td").find(':input').unbind( 'change' ).change(handle_rename_form_change);
    $(this).closest("tr").find(".bh-dir-content-rename-td").find(':input').keypress(function(e) {
      if(e.which === 13) {
        e.preventDefault();
        $(this).closest("tr").find(".bh-dir-content-rename-td").find(':input').trigger('change');
      }
    });
    // Blur: erase rename form field
    $(this).closest("tr").find(".bh-dir-content-rename-td").find(':input').blur(function(){
      $(this).closest("tr").find(".bh-dir-content-name").show();
      $(this).closest("tr").find(".bh-dir-content-rename-td").hide();
    });
  }; 
  
  /*
   * Onclick handler menu icon hoverin content view
   */
  var handle_menu_icon_hover = function(){
    $(this).dropdown('toggle');
  }; 
  
  /*
   * Onclick handler acl menu in content view
   */
  var handle_acl_menu_click = function(e){
    e.preventDefault();
    var path = $(this).closest('tr').attr('id');
    if (!path.match(/\/$/)) {
      path=path+'/'; 
    };
    nl.sara.beehub.controller.getAclFromServer(path);
  }; 
  
  /**
   * Onchange handler rename form in content view
   */
  var handle_rename_form_change = function(){
    // create resource object
    var resource = new nl.sara.beehub.ClientResource($(this).closest('tr').attr('id'));
    // start rename
    nl.sara.beehub.controller.renameResource(resource, $(this).val(), nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
  };
  
  /**
   * Set sponsor dropdown
   * 
   * @param {Object} resource Resource object
   * @param {Object} sponsors Sponsors object with name and displayname
   */
  nl.sara.beehub.view.content.setSponsorDropdown = function(path, sponsors){
    var row = $("tr[id='"+path+"']");
    var selected = row.find('.bh-dir-sponsor').attr('data-value');
    var dropdown = "";
    for (var i in sponsors){
      if (sponsors[i].name === selected) {
        dropdown += '<option value="'+sponsors[i].name+'" selected>'+sponsors[i].displayname+'</option>';
      } else {
        dropdown += '<option value="'+sponsors[i].name+'">'+sponsors[i].displayname+'</option>';
      }
    };
    row.find('.bh-dir-sponsor').hide();
    row.find('.bh-dir-sponsor-dropdown').show();
    row.find('.bh-dir-sponsor-select').focus();
    row.find('.bh-dir-sponsor-select').html(dropdown);
    
    row.find('.bh-dir-sponsor-select').unbind( 'blur' ).blur(function(e){
      row.find('.bh-dir-sponsor-dropdown').hide();
      row.find('.bh-dir-sponsor-select').html("");
      row.find('.bh-dir-sponsor').show();
    });
    row.find('.bh-dir-sponsor-dropdown').unbind('change').on('change', function(e){
      nl.sara.beehub.view.maskView("transparant", true);
      var name = $(e.target).find(":selected").val();
      var displayname = $(e.target).find(":selected").text();
      var sponsor = {
          "name":        name,
          "displayname": displayname
      };
      nl.sara.beehub.controller.setSponsor(path, sponsor);
    });
    nl.sara.beehub.view.maskView("transparant", false);
  };
  
  /**
   * Set error get sponsors
   * 
   * @param {Integer} status Failure status
   */
  nl.sara.beehub.view.content.errorGetSponsors = function(status){
    nl.sara.beehub.view.dialog.showError( 'Something went wrong with retrieving available sponsors.' );
    nl.sara.beehub.view.maskView("transparant", false);
  };
  
  /**
   * Set new sponsor
   * 
   * @param {String} path       Path from resource
   * @param {Object} sponsor    Sponsor object with name and displayname of the sponsor
   */
  nl.sara.beehub.view.content.setNewSponsor = function(path, sponsor){
    var row = $("tr[id='"+path+"']");
    row.find('.bh-dir-sponsor-dropdown').hide();
    row.find('.bh-dir-sponsor-select').html("");
    row.find('.bh-dir-sponsor').attr('data-value', sponsor.name);
    row.find('.bh-dir-sponsor').text(sponsor.displayname);
    row.find('.bh-dir-sponsor').show();
    nl.sara.beehub.view.maskView("transparant", false);
  };
  
  /**
   * Show error when sponsor change failed
   * 
   * @param {Integer} status  Status code of failure
   */
  nl.sara.beehub.view.content.errorNewSponsor = function(status){  
    if (status === nl.sara.beehub.controller.STATUS_NOT_ALLOWED ){
      nl.sara.beehub.view.dialog.showError( nl.sara.beehub.controller.ERROR_STATUS_NOT_ALLOWED );
    } else {
      nl.sara.beehub.view.dialog.showError( nl.sara.beehub.controller.ERROR_UNKNOWN );
    };
    nl.sara.beehub.view.maskView("transparant", false);
  };
  
  /**
   * Handle sponsor click
   * 
   * @param {Event} e
   */ 
  var handle_sponsor_click = function(e){
 // create resource object
    var owner = $(e.target).closest('tr').find('.owner').attr('data-value');
    var path = $(e.target).closest('tr').attr('id');
    nl.sara.beehub.view.maskView("transparant", true);
    nl.sara.beehub.controller.getSponsors(owner, path);
  };
  
  /**
   * Onclick handler upload button content view
   */
  var handle_upload_button_click = function() {
    if (window.File && window.FileList) {
      // Great success! All the File APIs are supported.
     // show local files and directories
        $('.bh-dir-content-upload-hidden').click();
    } else {
      nl.sara.beehub.controller.showError('Unfortunately this browser does not support file uploads. You could try it using Internet Explorer 10 or a recent version of Chrome, Firefox or Safari. Alternatively see the documentation on how to access BeeHub from your PC.');
    }   
  };
  
  /*
   * Handler upload files (triggered when files to upload are selected)
   */
  var handle_upload_change = function() {
    var files = $('.bh-dir-content-upload-hidden')[0].files;
    // init and start action "upload"
    nl.sara.beehub.controller.initAction(files,"upload");
  };
  
  /*
   * Onclick handler delete button content view
   */
  var handle_newfolder_button_click = function(){
    nl.sara.beehub.controller.createNewFolder();
  };
  
  /*
   * Onclick handler delete button content view
   */
  var handle_delete_button_click = function(){
    var resources = getSelectedResources();
    // init and start action "delete"
    nl.sara.beehub.controller.initAction(resources,"delete");
  };
  
  /*
   * Onclick handler copy button content view
   */
  var handle_copy_button_click = function() {
    var resources = getSelectedResources();
    // init and start action "copy"
    nl.sara.beehub.controller.initAction(resources, "copy");
  };
  
  /*
   * Onclick handler move button content view
   */
  var handle_move_button_click = function() {
    var resources = getSelectedResources();
    // init and start action "move"
    nl.sara.beehub.controller.initAction(resources, "move");
  };

  /**
   * Gets the path to the home dir of the user
   * 
   * @returns  {String}  The path to the home dir of the current user, or undefined if this is not known (e.g. there is no user logged in)
   */
  nl.sara.beehub.view.content.getHomePath = function() {
    return $( '.bh-dir-content-gohome' ).attr( 'id' );
  };

})();
