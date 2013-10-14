/*
 * Copyright ©2013 SARA bv, The Netherlands
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

/** 
 * Beehub Client Content
 * 
 * Content contains: resource handle buttons, table with resources
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */
/*
 * Initialize content view
 * 
 * Public function
 * 
 */
(function(){
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
  
  //make table sortable with tablesorter plugin
    $("#bh-dir-content-table").tablesorter({
      // which columns are sortable
      headers: { 
        0 : { sorter: false },
        1 : { sorter: false },
        4 : { sorter: 'filesize'},
        8: { sorter:false }
      },
      widthFixed: false,
      // Fixed header on top of the table
      widgets : ['stickyHeaders'],
      widgetOptions : {
        // apply sticky header top below the top of the browser window
        stickyHeaders_offset : 186
      }
    });
   
    // Add listeners
    // Go to users homedirectory handler
    $('.bh-dir-content-gohome').click(function() { window.location.href=$(this).attr("id");});
    
    // Go up one directory button
    $('.bh-dir-content-up').click(function() { window.location.href=$(this).attr("id");});
    
    // Upload button
    $('.bh-dir-content-upload').click(handle_upload_button_click);
    
    // When upload files are choosen
    $('.bh-dir-content-upload-hidden').change(handle_upload_change);
    
    // New folder button
    $('.bh-dir-content-newfolder').click(nl.sara.beehub.controller.createNewFolder);
    
    // Delete button click handler
    $('.bh-dir-content-delete').click(handle_delete_button_click);
    
    // Copy button click handler
    $('.bh-dir-content-copy').click(handle_copy_button_click);
    
    // Move button click handler
    $('.bh-dir-content-move').click(handle_move_button_click);
    
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
    $('.bh-dir-content-checkboxgroup').unbind().click(handle_checkall_checkbox_click);
    
    // Checkbox handler: select or deselect checkbox
    $('.bh-dir-content-checkbox').unbind().click(handle_checkbox_click);
    
    // Open selected handler: this can be a file or a directory
    $('.bh-dir-content-openselected').unbind().click(function() {window.location.href=$(this).attr('name');});
    
    // Edit icon
    $('.bh-dir-content-edit').unbind().click(handle_edit_icon_click);
    
    // Rename handler
    $('.bh-dir-content-rename-form').unbind().change(handle_rename_form_change);
    
    // Blur: erase rename form field
    $('.bh-dir-content-rename-form').blur(function(){
      $(this).closest("tr").find(".bh-dir-content-name").show();
      $(this).closest("tr").find(".bh-dir-content-rename-td").hide();
    });
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
    
    row.push('<tr id="'+resource.path+'">');
    
    // Checkbox
    row.push('<td width="10px"><input type="checkbox" class="bh-dir-content-checkbox" name="'+resource.path+'" value="'+resource.displayname+'"></td>');
    
    // Edit column
    row.push('<td width="10px" data-toggle="tooltip" title="Rename">');
    row.push('<i class="icon-edit bh-dir-content-edit" style="cursor: pointer"></i></td>');
    
    // Name
    if (resource.type==='collection') {
      row.push('<td class="bh-dir-content-name displayname" name="'+resource.displayname+'"><a href="'+resource.path+'"><b>'+resource.displayname+'/</b></a></td>');
    } else {
      row.push('<td class="bh-dir-content-name displayname" name="'+resource.displayname+'"><a href="'+resource.path+'">'+resource.displayname+'</a></td>');
    }
    row.push('<td class="bh-dir-content-rename-td" hidden="true"><input class="bh-dir-content-rename-form" name="'+resource.displayname+'" value="'+resource.displayname+'"></td>');
    
    if (resource.type==='collection') {
      // Size
      row.push('<td class="contentlength" name="'+resource.contentlength+'"></td>');
      // Type
      row.push('<td class="type" name="'+resource.type+'"><i name="'+resource.path+'" class="icon-folder-close bh-dir-content-openselected" style="cursor: pointer">></i></td>');
    } else {
      // Size
      row.push('<td class="contentlength" name="'+resource.contentlength+'">'+nl.sara.beehub.controller.bytesToSize(resource.contentlength, 2)+'</td>');
      //Type
      row.push('<td class="type" name="'+resource.type+'">'+resource.type+'</td>');
  
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
    
    row.push('<td class="lastmodified" name="'+resource.lastmodified+'">'+dateString+'</td>');
    
    // Owner
    row.push('<td class="owner" name="'+resource.owner+'">'+nl.sara.beehub.controller.getDisplayName(resource.owner)+'</td>');
    
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
      resource.displayname = $("tr[id='"+resource.path+"']").find('.displayname').attr('name');
    }
    
    // Type
    if (resource.type === undefined) {
      resource.type = $("tr[id='"+resource.path+"']").find('.type').attr('name');
    }
    
    // Owner
    if (resource.owner === undefined) {
      resource.owner = $("tr[id='"+resource.path+"']").find('.owner').attr('name');
    }
    
    // Contentlenght
    if (resource.contentlength === undefined) {
      resource.contentlength = $("tr[id='"+resource.path+"']").find('.contentlength').attr('name');
    }
    
    // Last modiefied
    if (resource.lastmodified === undefined) {
      resource.lastmodified = $("tr[id='"+resource.path+"']").find('.lastmodified').attr('name');
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
    var row = createRow(resource);
    $("#bh-dir-content-table").append(row);
    $("#bh-dir-content-table").trigger("update");
    // Sort again
    $("#bh-dir-content-table").trigger("sorton", [$("#bh-dir-content-table")[0].config.sortList]);
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
  
  /*
   * Update resource from content view
   * 
   * Public function
   * 
   * @param {Object} resourceOrg Original resource object
   * @param {Object} resourceOrg New resource object
   */
  nl.sara.beehub.view.content.updateResource = function(resourceOrg, resourceNew){
    // delete current row
    nl.sara.beehub.view.content.deleteResource(resourceOrg);
    // add new row
    nl.sara.beehub.view.content.addResource(resourceNew);
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
  var handle_edit_icon_click = function(){
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
    $(this).closest("tr").find(".bh-dir-content-rename-td").find(':input').focus();
  }; 
  
  /*
   * Onchange handler rename form in content view
   */
  var handle_rename_form_change = function(){
    // create resource object
    var resource = new nl.sara.beehub.ClientResource($(this).closest('tr').attr('id'));
    // start rename
    nl.sara.beehub.controller.renameResource(resource, $(this).val(), nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
  };
  
  /*
   * Onclick handler upload button content view
   */
  var handle_upload_button_click = function() {
    // show local files and directories
    $('.bh-dir-content-upload-hidden').click();
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
})();