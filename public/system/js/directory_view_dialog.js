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
 * Beehub Client dialogs
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */

/*
 * Clear dialog
 */
nl.sara.beehub.view.dialog.clearView = function(){
  // Close dialog
  $('#bh-dir-dialog').dialog("close");
};

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
    resizable: false,
    title: " Error!",
    closeOnEscape: false,
    dialogClass: "custom_dialog",
    buttons: [{
      text: "Ok",
      click: function() {
        $(this).dialog("close");
      }
    }]
  });
};

/*
 * Show dialog with ready buttons
 * 
 * @param function actionFunction Function to call when ready is clicked
 * 
 */
nl.sara.beehub.view.dialog.setDialogReady = function(actionFunction){
  $('#bh-dir-dialog-button').button({label:"Ready"});
  $('#bh-dir-dialog-button').button("enable");
  $('#bh-dir-dialog-button').removeClass("btn-danger");
  $('#bh-dir-cancel-dialog-button').hide();
  $('#bh-dir-dialog-button').unbind('click').click(function(){
    $("#bh-dir-dialog").dialog("close");
    actionFunction();
  })
};

/*
* Show progress bar
* 
* @param Resource resource Resource to show progress from
* @param Integer  progress Progress of action
*/
nl.sara.beehub.view.dialog.showProgressBar = function(resource, progress){
  $("tr[id='dialog_tr_"+resource.path+"']").find('.info').html("<div class='progress progress-success progress-striped'><div class='bar' style='width: "+progress+"%;'>"+progress+"%</div></div>");
};

/*
* Update info column in dialog
* 
* @param Resource resource Resource to update
* @param String   info     Information for dialog
*/
nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
  $("tr[id='dialog_tr_"+resource.path+"']").find('.info').html("<b>"+info+"</b>");
};

/*
* Set overwrite, rename and cancel buttons;
* 
* @param {Object} resource Resource
* @param {Function} overwriteFunction Overwrite handler
* @param {Function} renameFunction    Rename handler
* @param {Function} cancelFunction    Cancel handler.
*/
nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwriteFunction, renameFunction, cancelFunction){
  var overwriteButton = '<button class="btn btn-danger overwritebutton">Overwrite</button>'
  var renameButton = '<button class="btn btn-success renamebutton">Rename</button>'
  var cancelButton = '<button class="btn btn-success cancelbutton">Cancel</button>'
  
  $("tr[id='dialog_tr_"+resource.path+"']").find('.info').html("Item exist on server!<br/>"+renameButton+" "+overwriteButton+" "+cancelButton);
  
  // Overwrite click handler
  $("tr[id='dialog_tr_"+resource.path+"']").find('.overwritebutton').click(function(){
    overwriteFunction();
  });
  
  // Cancel click handler
  $("tr[id='dialog_tr_"+resource.path+"']").find('.cancelbutton').click(function(){
    cancelFunction();
  });
  
  // Rename click handler
  $("tr[id='dialog_tr_"+resource.path+"']").find('.renamebutton').click(function(){   
    renameFunction();
  });
};

/*
* Scroll to position in dialog
* 
* @param Integer number Position to scroll to
*/
nl.sara.beehub.view.dialog.scrollTo = function(number){
  $("#bh-dir-dialog").scrollTop(number);
};

 
/*
 * Show dialog with resources to copy, move, upload or delete
 * 
 * @param function actionFunction Action handler
 */
nl.sara.beehub.view.dialog.showResourcesDialog = function(actionFunction){
  var config = {}; 
  // Set text and labels
  switch(nl.sara.beehub.controller.actionAction)
  {
  case "copy": 
    config.title = "Copy to "+nl.sara.beehub.controller.actionDestination;
    config.buttonLabel = "Copy items...";
    config.buttonText = "Copy";
    break;
  case "move":
    config.title = "Move to "+nl.sara.beehub.controller.actionDestination;
    config.buttonLabel = "Moving items...";
    config.buttonText = "Move";
    break;
  case "delete":
    config.title = "Delete";
    config.buttonLabel = "Deleting...";
    config.buttonText = "Delete";
    break;
  case "upload":
    config.title = "Upload";
    config.buttonLabel = "Uploading...";
    config.buttonText = "Upload";
    break;
  default:
    // This should never happen
  }
  
  // Put all resources in dialog
  $("#bh-dir-dialog").html("");
  var appendString='';
  appendString = appendString + '<table class="table"><tbody>';
  $.each(nl.sara.beehub.controller.actionResources, function(i, item){
     appendString = appendString + '<tr id="dialog_tr_'+item.path+'"><td>'+item.displayname+'</td><td width="60%" class="info"></td></tr>'
  });
  appendString = appendString +'</tbody></table>';
  $("#bh-dir-dialog").append(appendString);
  
  // Show dialog
  $("#bh-dir-dialog").dialog({
    modal: true,
    maxHeight: 400,
    title: config.title,
    closeOnEscape: false,
    dialogClass: "custom-dialog",
    resizable: false,
    width: 460,
    buttons: [{
      text: "Cancel",
      id: 'bh-dir-cancel-dialog-button',
      click: function() {
        $(this).dialog("close");
        nl.sara.beehub.controller.clearAllViews();
      }
    }, {
      text: config.buttonText,
      id: 'bh-dir-dialog-button',
      click: function() {
        $('#bh-dir-dialog-button').button({label:config.buttonLabel});
        $('#bh-dir-dialog-button').button("disable");
        actionFunction();
      }
    }]
  });
  $("#bh-dir-dialog-button").addClass("btn-danger");
};

/*
 * Show overwrite buttons in dialog
 * 
 * @param Object    resource            Resource to update
 * @param String    fileNew             filename of the original file name
 * @param Function  overwriteFunction   Overwrite handler 
 */
nl.sara.beehub.view.dialog.showOverwriteDialog = function(resource, fileNew, overwriteFunction) {
  var overwriteButton='<button id="bh-dir-rename-overwrite-button" class="btn btn-danger">Overwrite</button>'
  var cancelButton='<button id="bh-dir-rename-cancel-button" class="btn btn-success">Cancel</button>'
  $("#bh-dir-dialog").html('<h5><b><i>'+fileNew+'</b></i> already exist in the current directory!</h5><br><center>'+overwriteButton+' '+cancelButton)+'</center>';
  $("#bh-dir-dialog").dialog({
       modal: true,
       title: "Warning"
        });
  $("#bh-dir-rename-overwrite-button").click(overwriteFunction);
  $("#bh-dir-rename-cancel-button").click(function(){
    $("tr[id='"+resource.path+"']").find(".bh-dir-rename-td").find(':input').val(resource.displayname);
    $("#bh-dir-dialog").dialog("close");
  })
};

/*
 * Close the dialog
 * 
 */
nl.sara.beehub.view.dialog.closeDialog = function() {
  $("#bh-dir-dialog").dialog("close");
};
