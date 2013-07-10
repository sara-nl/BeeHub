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
  $('#bh-dir-checkboxgroup').click(nl.sara.beehub.view.contents.toggleCheckAllCheckBoxes);
  // Open selected handler: this can be a file or a directory
  $('.bh-dir-openselected').click(function() {window.location.href=$(this).attr('name');});
  // Go to users homedirectory handler
  $('.bh-dir-gohome').click(function() { window.location.href=$(this).attr("id");});
  // Go up one directory button
  $('.bh-dir-group').click(function() { window.location.href=$(this).attr("id");});
  // New folder button
  $('#bh-dir-newfolder').click(nl.sara.beehub.controller.createNewFolder);
  // Edit icon
  $('.bh-dir-edit').click(nl.sara.beehub.view.contents.handle_edit_icon_click);
  // Rename handler
  $('.bh-dir-rename-form').change(function(){
    moveObject($(this).attr('name'),$(this).val(), nl.sara.webdav.Client.FAIL_ON_OVERWRITE, $(this));
  })
  // Blur: erase rename form field
  $('.bh-dir-rename-form').blur(function(){
    $(this).closest("tr").find(".bh-dir-name").show();
    $(this).closest("tr").find(".bh-dir-rename-td").hide();
  })
}

// CONTENTVIEW: FUNCTIONS
nl.sara.beehub.view.contents.toggleCheckAllCheckBoxes = function() {
  if ($('#bh-dir-checkboxgroup').prop('checked')) {
    $('.bh-dir-checkbox').each(function(){
      $(this).prop('checked',true);
    });
    $('#bh-dir-copy').removeAttr("disabled");
    $('#bh-dir-move').removeAttr("disabled");
    $('#bh-dir-delete').removeAttr("disabled");
  } else {
    $('.bh-dir-checkbox').each(function(){
      $(this).prop('checked',false);
    });
    $('#bh-dir-copy').attr("disabled","disabled");
    $('#bh-dir-move').attr("disabled","disabled");
    $('#bh-dir-delete').attr("disabled","disabled");
  }
}

nl.sara.beehub.view.contents.handle_edit_icon_click = function(){
  // Search nearest name field and hide
  $(this).closest("tr").find(".bh-dir-name").hide();
  // Show form
  $(this).closest("tr").find(".bh-dir-rename-td").show();
  $(this).closest("tr").find(".bh-dir-rename-td").find(':input').focus();
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
