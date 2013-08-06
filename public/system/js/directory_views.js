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

// TODO vast ergens uit te lezen
nl.sara.beehub.view.userspath = '/system/users/';
nl.sara.beehub.view.groupspath = '/system/groups/';
/*
 * Initialize all views
 * 
 */
nl.sara.beehub.view.init = function() {
//CONTENTVIEW
//make table sortable with tablesorter plugin
  $("#bh-dir-content-table").tablesorter({
    headers: { 
      0 : { sorter: false },
      1 : { sorter: false },
      8: { sorter:false }
    },
    widthFixed: false,
    widgets : ['stickyHeaders'],
    widgetOptions : {
      // apply sticky header top below the top of the browser window
      stickyHeaders_offset : 186,
    }
  });
 
  // Go to users homedirectory handler
  $('.bh-dir-gohome').click(function() { window.location.href=$(this).attr("id");});
  // Go up one directory button
  $('.bh-dir-group').click(function() { window.location.href=$(this).attr("id");});
  // New folder button
  $('#bh-dir-newfolder').click(nl.sara.beehub.controller.createNewFolder);
  // All handlers that belong to a row
  nl.sara.beehub.view.contents.setRowHandlers();
}

/*
* Set all handlers that belong to a row.
* 
*/
nl.sara.beehub.view.contents.setRowHandlers = function(){
  // Checkbox select all handler: select or deselect all checkboxes
  $('.bh-dir-checkboxgroup').click(nl.sara.beehub.view.contents.handle_checkall_checkbox_click);
  // Checkbox handler: select or deselect checkbox
  $('.bh-dir-checkbox').click(nl.sara.beehub.view.contents.handle_checkbox_click);
  // Open selected handler: this can be a file or a directory
  $('.bh-dir-openselected').click(function() {window.location.href=$(this).attr('name');});
  // Edit icon
  $('.bh-dir-edit').click(nl.sara.beehub.view.contents.handle_edit_icon_click);
  // Rename handler
  $('.bh-dir-rename-form').change(nl.sara.beehub.view.contents.handle_rename_form_change);
  // Blur: erase rename form field
  $('.bh-dir-rename-form').blur(function(){
    $(this).closest("tr").find(".bh-dir-name").show();
    $(this).closest("tr").find(".bh-dir-rename-td").hide();
  })
}
/*
 * Returns displayname from object
 * 
 * @param String {name} object
 * 
 * @return String Displayname
 */
nl.sara.beehub.view.getDisplayName = function(name){
  if (name.contains(nl.sara.beehub.view.userspath)) {
    var displayName = nl.sara.beehub.principals.users[name.replace(nl.sara.beehub.view.userspath,'')];
    return displayName;
  };
  if (name.contains(nl.sara.beehub.view.groupspath)) {
    var displayName = nl.sara.beehub.principals.groups[name.replace(nl.sara.beehub.view.groupspath,'')];
    return displayName;
  };
}

/*
 * Returns size from resource
 * 
 * @param Resource {resource} object
 * 
 * @return Integer size
 */
nl.sara.beehub.view.getSize = function(resource){
  // Nog niet getest
  // Calculate size
  if (resource.contentlength !== ""){
   var size = resource.contentlength;
   if (size !== '' && size != 0) {
     var unit = null;
     units = array('B', 'KB', 'MB', 'GB', 'TB');
     for (var i = 0, c = count(units); i < c; i++) {
       if (size > 1024) {
         size = size / 1024;
       } else {
         unit = units[i];
         break;
       }
     }
     showsize = round(size, 0) + ' ' + unit;
   } else {
     showsize = '';
   }
   size = size;
  } else {
   size = contentlength;
  }
  return size;
}

/*
 * Create contentview row from resource object
 * 
 * @param Object {resource} Resource object
 * 
 * @return Array {row}
 */
nl.sara.beehub.view.contents.createRow = function(resource){
  var row = [];
  row.push('<tr>');
  // Edit column
  row.push('<td width="10px" data-toggle="tooltip" title="Rename file">');
  row.push('<i class="icon-edit bh-dir-edit" style="cursor: pointer"></i></td>');
  // Checkboxes
//  console.log(nl.sara.beehub.view.getDisplayName(resource));
  row.push('<td width="10px"><input type="checkbox" class="bh-dir-checkbox" value='+resource.displayname+'></td>');
  // Name
  if (resource.type==='collection') {
    row.push('<td class="bh-dir-name"><a href="'+resource.path+'"><b>'+resource.displayname+'/</b></a></td>');
  } else {
    row.push('<td class="bh-dir-name"><a href="'+resource.path+'">'+resource.displayname+'</a></td>');
  }
  row.push('<td class="bh-dir-rename-td" hidden="true"><input class="bh-dir-rename-form" name='+resource.displayname+' value='+resource.displayname+'></td>');
  
  if (resource.type==='collection') {
    // Size
    row.push('<td></td>');
    // Type
    row.push('<td><i name='+resource.path+' class="icon-folder-close bh-dir-openselected" style="cursor: pointer">></i></td>');
  } else {
    // Size
    row.push('<td>'+nl.sara.beehub.view.getSize(resource)+'</td>');
    //Type
    row.push('<td>'+resource.type+'</td>');

  }
  // Last Modified
  row.push('<td>'+resource.lastmodified+'</td>');
  // Owner
  row.push('<td>'+nl.sara.beehub.view.getDisplayName(resource.owner)+'</td>');
  // Share link
  row.push('<td></td>');
  return row.join("");
};

/*
 * Add resource to all views
 * 
 * @param {Object} resource Resource object
 */
nl.sara.beehub.view.addClientResource = function(resource){
  var row = nl.sara.beehub.view.contents.createRow(resource);
  $("#bh-dir-content-table tbody").append(row);
  $("#bh-dir-content-table tbody").trigger("update");
  // Set handlers again
  nl.sara.beehub.view.contents.setRowHandlers();
  // TODO add to other views
};

// CONTENTVIEW: FUNCTIONS
/*
 * Enable copy, move, delete buttons
 */
nl.sara.beehub.view.contents.enable_action_buttons = function() {
  $('#bh-dir-copy').removeAttr("disabled");
  $('#bh-dir-move').removeAttr("disabled");
  $('#bh-dir-delete').removeAttr("disabled");
}

/*
 * Disable copy, move, delete buttons
 */
nl.sara.beehub.view.contents.disable_action_buttons = function() {
  $('#bh-dir-copy').attr("disabled","disabled");
  $('#bh-dir-move').attr("disabled","disabled");
  $('#bh-dir-delete').attr("disabled","disabled");
}

/*
 * On click handler select all checkbox
 * Check or uncheck all checkboxes in contents view
 */
nl.sara.beehub.view.contents.handle_checkall_checkbox_click = function() {
  if ($(this)[0].checked) {
    $('.bh-dir-checkbox').prop('checked',true);
    nl.sara.beehub.view.contents.enable_action_buttons();
  } else {
    $('.bh-dir-checkbox').prop('checked',false);
    nl.sara.beehub.view.contents.disable_action_buttons();
  }
}

/*
 * On click handler select checkbox
 * Enable or disable buttons
 */
nl.sara.beehub.view.contents.handle_checkbox_click = function() {
  if ($('.bh-dir-checkbox:checked').length > 0) {
    nl.sara.beehub.view.contents.enable_action_buttons();
  } else {
    nl.sara.beehub.view.contents.disable_action_buttons();
  }
}

/*
 * Onclick handler edit icon in contents view
 */
nl.sara.beehub.view.contents.handle_edit_icon_click = function(){
  // Search nearest name field and hide
  $(this).closest("tr").find(".bh-dir-name").hide();
  // Show form
  $(this).closest("tr").find(".bh-dir-rename-td").show();
  $(this).closest("tr").find(".bh-dir-rename-td").find(':input').focus();
};
/*
 * Onchange handler rename form in contents view
 */
nl.sara.beehub.view.contents.handle_rename_form_change = function(){
  // create resource object
  var resource = new nl.sara.beehub.ClientResource($(this).closest('tr').attr('id'));
  nl.sara.beehub.controller.renameResource(resource, $(this).val(), nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
};

// DIALOG: FUNCTIONS
/*
 * Show dialog with error
 * 
 * @param {String} error The error to show
 */
nl.sara.beehub.view.dialog.showError = function(error) {
  $('#bh-dir-dialog').html(error);
  $('#bh-dir-dialog').dialog({
    modal: true,
    maxHeight: 400,
    title: " Error!",
    closeOnEscape: false,
    dialogClass: "no-close",
    buttons: [{
      text: "Ok",
      click: function() {
        $(this).dialog("close");
      }
    }]
  });
};
/*
 * Show dialog with error
 * 
 * @param {String} fileNew filename of the original file name
 * @param {Object} element DOM element from the 
 */
nl.sara.beehub.view.dialog.showOverwriteDialog = function(resource, fileNew) {
  var overwriteButton='<button id="bh-dir-rename-overwrite-button" class="btn btn-danger">Overwrite</button>'
  var cancelButton='<button id="bh-dir-rename-cancel-button" class="btn btn-success">Cancel</button>'
  $("#bh-dir-dialog").html('<h5><b><i>'+fileNew+'</b></i> already exist in the current directory!</h5><br><center>'+overwriteButton+' '+cancelButton)+'</center>';
  $("#bh-dir-dialog").dialog({
       modal: true,
       title: "Warning"
        });
  $("#bh-dir-rename-overwrite-button").click(function(){
    nl.sara.beehub.controller.renameResource(resource, fileNew, nl.sara.webdav.Client.SILENT_OVERWRITE);
  })
  $("#bh-dir-rename-cancel-button").click(function(){
    $("tr[id*='"+resource.path+"']").find(".bh-dir-rename-td").find(':input').val(resource.displayname);
    $("#bh-dir-dialog").dialog("close");
  })
};