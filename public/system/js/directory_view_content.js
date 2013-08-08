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

/*
 * Initialize content views
 * 
 */
nl.sara.beehub.view.content.init = function() {
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
  nl.sara.beehub.view.content.setRowHandlers();
}

/*
* Set all handlers that belong to a row.
* 
*/
nl.sara.beehub.view.content.setRowHandlers = function(){
  // Checkbox select all handler: select or deselect all checkboxes
  $('.bh-dir-checkboxgroup').click(nl.sara.beehub.view.content.handle_checkall_checkbox_click);
  // Checkbox handler: select or deselect checkbox
  $('.bh-dir-checkbox').click(nl.sara.beehub.view.content.handle_checkbox_click);
  // Open selected handler: this can be a file or a directory
  $('.bh-dir-openselected').click(function() {window.location.href=$(this).attr('name');});
  // Edit icon
  $('.bh-dir-edit').click(nl.sara.beehub.view.content.handle_edit_icon_click);
  // Rename handler
  $('.bh-dir-rename-form').change(nl.sara.beehub.view.content.handle_rename_form_change);
  // Blur: erase rename form field
  $('.bh-dir-rename-form').blur(function(){
    $(this).closest("tr").find(".bh-dir-name").show();
    $(this).closest("tr").find(".bh-dir-rename-td").hide();
  })
};

/*
 * Create contentview row from resource object
 * 
 * @param Object {resource} Resource object
 * 
 * @return Array {row}
 */
nl.sara.beehub.view.content.createRow = function(resource){
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
nl.sara.beehub.view.content.addResource = function(resource){
  var row = nl.sara.beehub.view.content.createRow(resource);
  $("#bh-dir-content-table tbody").append(row);
  $("#bh-dir-content-table tbody").trigger("update");
  // Set handlers again
  nl.sara.beehub.view.content.setRowHandlers();
};

/*
 * Enable copy, move, delete buttons
 */
nl.sara.beehub.view.content.enable_action_buttons = function() {
  $('#bh-dir-copy').removeAttr("disabled");
  $('#bh-dir-move').removeAttr("disabled");
  $('#bh-dir-delete').removeAttr("disabled");
}

/*
 * Disable copy, move, delete buttons
 */
nl.sara.beehub.view.content.disable_action_buttons = function() {
  $('#bh-dir-copy').attr("disabled","disabled");
  $('#bh-dir-move').attr("disabled","disabled");
  $('#bh-dir-delete').attr("disabled","disabled");
}

/*
 * On click handler select all checkbox
 * Check or uncheck all checkboxes in content view
 */
nl.sara.beehub.view.content.handle_checkall_checkbox_click = function() {
  if ($(this)[0].checked) {
    $('.bh-dir-checkbox').prop('checked',true);
    nl.sara.beehub.view.content.enable_action_buttons();
  } else {
    $('.bh-dir-checkbox').prop('checked',false);
    nl.sara.beehub.view.content.disable_action_buttons();
  }
}

/*
 * On click handler select checkbox
 * Enable or disable buttons
 */
nl.sara.beehub.view.content.handle_checkbox_click = function() {
  if ($('.bh-dir-checkbox:checked').length > 0) {
    nl.sara.beehub.view.content.enable_action_buttons();
  } else {
    nl.sara.beehub.view.content.disable_action_buttons();
  }
}

/*
 * Onclick handler edit icon in content view
 */
nl.sara.beehub.view.content.handle_edit_icon_click = function(){
  // Search nearest name field and hide
  $(this).closest("tr").find(".bh-dir-name").hide();
  // Show form
  $(this).closest("tr").find(".bh-dir-rename-td").show();
  $(this).closest("tr").find(".bh-dir-rename-td").find(':input').focus();
};
/*
 * Onchange handler rename form in content view
 */
nl.sara.beehub.view.content.handle_rename_form_change = function(){
  // create resource object
  var resource = new nl.sara.beehub.ClientResource($(this).closest('tr').attr('id'));
  nl.sara.beehub.controller.renameResource(resource, $(this).val(), nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
};
