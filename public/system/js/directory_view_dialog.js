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

(function(){
  /*
   * Clear dialog
   * 
   * Public function
   * 
   */
  nl.sara.beehub.view.dialog.clearView = function(){
    // Close dialog
    $('#bh-dir-dialog').dialog("close");
  };
  
  /*
   * Show dialog with error
   * 
   * Public function
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
   * Public function
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
    });
  };
  
  /*
  * Show progress bar
  * 
  * Public function
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
  * Public function
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
  * Public function
  * 
  * @param {Object} resource Resource
  * @param {Function} overwriteFunction Overwrite handler
  * @param {Function} renameFunction    Rename handler
  * @param {Function} cancelFunction    Cancel handler.
  */
  nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwriteFunction, renameFunction, cancelFunction){
    var overwriteButton = '<button class="btn btn-danger overwritebutton">Overwrite</button>';
    var renameButton = '<button class="btn btn-success renamebutton">Rename</button>';
    var cancelButton = '<button class="btn btn-success cancelbutton">Cancel</button>';
    
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
  * Public function
  * 
  * @param Integer number Position to scroll to
  */
  nl.sara.beehub.view.dialog.scrollTo = function(number){
    $("#bh-dir-dialog").scrollTop(number);
  };
  
   
  /*
   * Show dialog with resources to copy, move, upload or delete
   * 
   * Public function
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
       appendString = appendString + '<tr id="dialog_tr_'+item.path+'"><td>'+item.displayname+'</td><td width="60%" class="info"></td></tr>';
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
   * Public function
   * 
   * @param Object    resource            Resource to update
   * @param String    fileNew             filename of the original file name
   * @param Function  overwriteFunction   Overwrite handler 
   */
  nl.sara.beehub.view.dialog.showOverwriteDialog = function(resource, fileNew, overwriteFunction) {
    var overwriteButton='<button id="bh-dir-rename-overwrite-button" class="btn btn-danger">Overwrite</button>';
    var cancelButton='<button id="bh-dir-rename-cancel-button" class="btn btn-success">Cancel</button>';
    $("#bh-dir-dialog").html('<h5><b><i>'+fileNew+'</b></i> already exist in the current directory!</h5><br><center>'+overwriteButton+' '+cancelButton)+'</center>';
    $("#bh-dir-dialog").dialog({
         modal: true,
         title: "Warning"
          });
    $("#bh-dir-rename-overwrite-button").click(overwriteFunction);
    $("#bh-dir-rename-cancel-button").click(function(){
      $("tr[id='"+resource.path+"']").find(".bh-dir-rename-td").find(':input').val(resource.displayname);
      $("#bh-dir-dialog").dialog("close");
    });
  };
  
  /*
   * Close the dialog
   * 
   * Public function
   * 
   */
  nl.sara.beehub.view.dialog.closeDialog = function() {
    $("#bh-dir-dialog").dialog("close");
  };  
  
  // ACL
  /**
   * Create html for acl form
   * 
   * @return {String} html
   * 
   */
  createHtmlAclForm = function() {
    return '\
        <table>\
        <tr>\
          <td class="bh-dir-acl-table-label"><label><b>Principal</b></label></td>\
          <td><label class="radio"><input type="radio" name="bh-dir-view-acl-optionRadio" id="bh-dir-acl-add-radio1" value="authenticated" unchecked>All BeeHub users</label></td>\
        </tr>\
        <tr>\
          <td class="bh-dir-acl-table-label"></td>\
          <td><label class="radio"><input type="radio" name="bh-dir-view-acl-optionRadio" id="bh-dir-acl-add-radio2" value="all" unchecked>Everybody</label></td>\
        </tr>\
        <tr>\
          <td class="bh-dir-acl-table-label"></td>\
          <td>\
            <div class="radio">\
              <input type="radio" name="bh-dir-view-acl-optionRadio" id="bh-dir-acl-add-radio3" value="user_or_group" checked>\
              <input id="bh-dir-acl-table-autocomplete" class="bh-dir-acl-table-search" type="text"  value="" placeholder="Search user or group...">\
            </div></td>\
        </tr>\
        <tr>\
          <td class="bh-dir-acl-table-label"><label><b>Permisions</b></label></td>\
          <td><select class="bh-dir-acl-table-permisions">\
            <option value="allow read">allow read (read)</option>\
            <option value="allow write">allow write (read, write)</option>\
            <option value="allow manage">allow manage (read, write, change acl)</option>\
            <option value="deny read">deny read (read, write, change acl)</option>\
            <option value="deny write">deny write (write, change acl)</option>\
            <option value="deny manage">deny manage (write acl)</option>\
          </select></td>\
        </tr>\
      </table>\
    ';
  };
  
  /**
   * Initialize autocomplete for searching users and groups
   */
  setupAutoComplete = function(){
    var searchList = [];
    $.each(nl.sara.beehub.principals.users, function (username, displayname) {
      searchList.push({
         "label"        : displayname+' ('+username+') ',
         "name"         : nl.sara.beehub.users_path+username,
         "displayname"  : displayname,
         "icon"         : '<i class="icon-user"></i>'
      });
    });
    
    $.each(nl.sara.beehub.principals.groups, function (groupname, displayname) {
      searchList.push({
         "label"        : displayname+' ('+groupname+') ',
         "name"         : nl.sara.beehub.groups_path+groupname,
         "displayname"  : displayname,
         "icon"         : '<i class="icon-user"></i><i class="icon-user"></i>'
      });
    });

    $( "#bh-dir-acl-table-autocomplete" ).autocomplete({
      source:searchList,
      select: function( event, ui ) {
        $("#bh-dir-acl-table-autocomplete").val(ui.item.label);
        $("#bh-dir-acl-table-autocomplete").attr('name' ,ui.item.name);
        $("#bh-dir-aclform-add-button").button('enable');
        return false;
      },
      change: function (event, ui) {
        if(!ui.item){
            //http://api.jqueryui.com/autocomplete/#event-change -
            // The item selected from the menu, if any. Otherwise the property is null
            //so clear the item for force selection
            $("#bh-dir-aclform-add-button").button('disable');
            $("#bh-dir-acl-table-autocomplete").val("");
        }

      }
    }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
      return $( "<li></li>" )
        .data( "ui-autocomplete-item", item )
        .append( "<a><strong>" +item.icon +"  "+ item.label + "</strong></a>" )
        .appendTo( ul );
    };  
  };
  
  /**
   * Add radio buttons handlers in Add acl rule form
   * 
   */
  setAddRadioButtons = function(){
    $("#bh-dir-acl-add-radio1").click(function(){
      $("#bh-dir-acl-table-autocomplete").attr("disabled",true);
      $("#bh-dir-acl-table-autocomplete").val("");
      $("#bh-dir-aclform-add-button").button('enable');

    });
    $("#bh-dir-acl-add-radio2").click(function(){
      $("#bh-dir-acl-table-autocomplete").attr("disabled",true);
      $("#bh-dir-acl-table-autocomplete").val("");
      $("#bh-dir-aclform-add-button").button('enable');
    });
    $("#bh-dir-acl-add-radio3").click(function(){
      $("#bh-dir-acl-table-autocomplete").attr("disabled",false);
      $("#bh-dir-aclform-add-button").button('disable');
    });
  };
  
  /**
   * Get value from the Acl add rule form
   * 
   * @return {Object} Principal and permissions
   */
  getFormAce= function(){
    var principal = '';
    switch($('input[name = "bh-dir-view-acl-optionRadio"]:checked').val())
    {
    // all
    case "all":
      principal="all";
      break;
    // Everybody
    case "authenticated":
      principal="authenticated";
      break;
    // User or group
    case "user_or_group":
      principal=$("#bh-dir-acl-table-autocomplete").attr('name');
      break;
    default:
      // This should never happen
    }
    var ace = {
        "principal": principal,
        "permissions": $(".bh-dir-acl-table-permisions option:selected").val()
    };
    
    return ace;
  }
  
  /*
   * Show add rule dialog
   * 
   * Public function
   * 
   * @param {String} error The error to show
   */
  nl.sara.beehub.view.dialog.showAddRuleDialog = function(addFunction) {
    // createForm
    $('#bh-dir-dialog').html(createHtmlAclForm());

    // auto complete for searching users and groups
    setupAutoComplete();
 
    // radiobutton handlers
    setAddRadioButtons();
        
    $('#bh-dir-dialog').dialog({
      title: " Add acl rule",
      modal: true,
      maxHeight: 400,
      closeOnEscape: false,
      dialogClass: "custom-dialog",
      resizable: false,
      width: 370,
      buttons: [{
        text: "Cancel",
        click: function() {
          $(this).dialog("close");
        }
      },{
        text: "Add rule",
        id: "bh-dir-aclform-add-button",
        click: function() {
          addFunction(getFormAce());
          $(this).dialog("close");
        }
      }]
    });
    $("#bh-dir-aclform-add-button").button('disable');
  };
})();