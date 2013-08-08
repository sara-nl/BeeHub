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
 * Show dialog with resource to delete
 * 
 * @param {Array} resources Array with resources
 */
nl.sara.beehub.view.dialog.setDeleteDialogReady = function(){
  $('#bh-dir-delete-button').button({label:"Ready"});
  $('#bh-dir-delete-button').button("enable");
  $('#bh-dir-delete-button').removeClass("btn-danger");
  $('#bh-dir-cancel-delete-button').hide();
  $('#bh-dir-delete-button').unbind('click').click(function(){
    $("#bh-dir-dialog").dialog("close");
  })
};

/*
* Update info column in delete dialog
* 
* @param {Resource} resource Resource to update
*/
nl.sara.beehub.view.dialog.updateResourceDeleteInfo = function(resource, info){
  $("tr[id='delete_tr_"+resource.path+"']").find('.delete_info').html("<b>"+info+"</b>");
};

/*
* scroll to position in dialog
* 
* @param {Integer} number Position to scroll to
*/
nl.sara.beehub.view.dialog.scrollTo = function(number){
  $("#bh-dir-dialog").scrollTop(number);
};

/*
 * Show dialog with resource to delete
 * 
 * @param {Array} resources Array with resources
 */
nl.sara.beehub.view.dialog.showDeleteDialog = function(resources) {
  $("#bh-dir-dialog").html("");
  var appendString='';
  appendString = appendString + '<table class="table"><tbody>';
  var deleteArray=[];
  $.each(resources, function(i, resource){
    appendString = appendString + '<tr id="delete_tr_'+resource.path+'"><td>'+resource.displayname+'</td><td width="20%" class="delete_info"></td></tr>'
  });
  appendString = appendString +'</tbody></table>';
  $("#bh-dir-dialog").append(appendString);
  $("#bh-dir-dialog").dialog({
    modal: true,
    maxHeight: 400,
    title: "Delete",
    closeOnEscape: false,
    dialogClass: "no-close",
    minWidth: 400,
    buttons: [{
      text: "Cancel",
      id: 'bh-dir-cancel-delete-button',
      click: function() {
       $(this).dialog("close");
      }
    }, {
      text: "Delete",
      id: 'bh-dir-delete-button',
      click: function() {
        $('#bh-dir-delete-button').button({label:"Deleting..."});
        $('#bh-dir-delete-button').button("disable");
        // put all values in an array with resources
        nl.sara.beehub.controller.deleteResources(resources,0);
      }
    }]
  });
  $("#bh-dir-delete-button").addClass("btn-danger");
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
    $("tr[id='"+resource.path+"']").find(".bh-dir-rename-td").find(':input').val(resource.displayname);
    $("#bh-dir-dialog").dialog("close");
  })
};