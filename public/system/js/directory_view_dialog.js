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
 * Show dialog with ready buttons
 * 
 *  */
nl.sara.beehub.view.dialog.setDialogReady = function(){
  $('#bh-dir-dialog-button').button({label:"Ready"});
  $('#bh-dir-dialog-button').button("enable");
  $('#bh-dir-dialog-button').removeClass("btn-danger");
  $('#bh-dir-cancel-dialog-button').hide();
  $('#bh-dir-dialog-button').unbind('click').click(function(){
    $("#bh-dir-dialog").dialog("close");
  })
};

/*
* Update info column in delete dialog
* 
* @param {Resource} resource Resource to update
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
//  // Overwrite click handler
  $("tr[id='dialog_tr_"+resource.path+"']").find('.overwritebutton').click(function(){
    overwriteFunction();
  });
  // Cancel click handler
  $("tr[id='dialog_tr_"+resource.path+"']").find('.cancelbutton').click(function(){
    cancelFunction();
  });
//  // Rename click handler
  $("tr[id='dialog_tr_"+resource.path+"']").find('.renamebutton').click(function(){   
    renameFunction();
  });
};

//
///**
// * Rename Copy/Move/Delete handler
// * 
// * @param string fileName
// * @param string fileNameOrg
// * @param object filesHash
// */
//function setActionRenameHandler(actionConfig){
//  var fileName = actionConfig.contents[actionConfig.counter].value;
//  $("#bh-dir-dialog").find('button[id="bh-dir-upload-rename-'+fileName+'"]').click(function(){
//    // search fileName td and make input field
//    var fileNameOrg= $("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').prev().html();
//    actionConfig.filenameOrg = fileNameOrg;
//    var buttonsOrg = $("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').html();
//    $("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').prev().html("<input id='bh-dir-upload-rename-input-"+fileName+"' value='"+fileName+"'></input>");
//    // change buttons - cancel and upload
//    var renameUploadButton = '<button id="bh-dir-upload-rename-upload-'+fileNameOrg+'" name="'+fileNameOrg+'" class="btn btn-success">Upload</button>'
//    var renameCancelButton = '<button id="bh-dir-upload-rename-cancel-'+fileNameOrg+'" class="btn btn-danger">Cancel</button>'
//    $("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').html(renameUploadButton+" "+renameCancelButton);
//    // handler cancel rename
//    $("#bh-dir-dialog").find('button[id="bh-dir-upload-rename-cancel-'+fileName+'"]').click(function(){
//      $("#bh-dir-dialog").find('td[id="bh-dir-'+fileNameOrg+'"]').html(buttonsOrg);
//      $("#bh-dir-dialog").find('td[id="bh-dir-'+fileNameOrg+'"]').prev().html(fileNameOrg);
//      setActionOverwriteHandler(actionConfig);
//      setActionCancelHandler(actionConfig);
//      setActionRenameHandler(actionConfig);
//    })
//    // add handler upload rename
//    $("#bh-dir-dialog").find('button[id="bh-dir-upload-rename-upload-'+fileName+'"]').click(function(){
//      var newName = $("#bh-dir-dialog").find('input[id="bh-dir-upload-rename-input-'+fileName+'"]').val();
//      if (newName !== fileName) {
//        $("#bh-dir-dialog").find('td[id="bh-dir-'+fileNameOrg+'"]').prev().html(fileName+" <br/> <b>renamed to</b> <br/> "+newName);
//      };
//      checkFileName(newName, filesHash[fileName], null, filesHash);
//    })
//  })
//};


/*
* scroll to position in dialog
* 
* @param {Integer} number Position to scroll to
*/
nl.sara.beehub.view.dialog.scrollTo = function(number){
  $("#bh-dir-dialog").scrollTop(number);
};

 
/*
 * Show dialog with resources to copy, move, upload or delete
 * 
 * @param {Array} resources Array with resources
 * @param {Object} destination Resource Destination resource
 */
nl.sara.beehub.view.dialog.showResourcesDialog = function(resources, actionConfig, actionFunction){
  var config = {};
  switch(actionConfig.action)
  {
  case "copy":
    config.title = "Copy to "+actionConfig.destinationPath;
    config.buttonLabel = "Copy items...";
    config.buttonText = "Copy";
    break;
  case "move":
    config.title = "nog doen";
    config.buttonLabel = "Nog doen...";
    config.buttonText = "Copy";
    break;
  case "delete":
    config.title = "Delete";
    config.buttonLabel = "Deleting...";
    config.buttonText = "Delete";
    break;
  default:
    // This should never happen
  }
  $("#bh-dir-dialog").html("");
  var appendString='';
  appendString = appendString + '<table class="table"><tbody>';
  $.each(resources, function(i, resource){
    appendString = appendString + '<tr id="dialog_tr_'+resource.path+'"><td>'+resource.displayname+'</td><td width="60%" class="info"></td></tr>'
  });
  appendString = appendString +'</tbody></table>';
  $("#bh-dir-dialog").append(appendString);
  $("#bh-dir-dialog").dialog({
    modal: true,
    maxHeight: 400,
    title: config.title,
    closeOnEscape: false,
    dialogClass: "no-close",
    minWidth: 450,
    buttons: [{
      text: "Cancel",
      id: 'bh-dir-cancel-dialog-button',
      click: function() {
       $(this).dialog("close");
       $(".bh-dir-tree-slide-trigger").trigger('click');
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
 * Show dialog with overwrite buttons
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