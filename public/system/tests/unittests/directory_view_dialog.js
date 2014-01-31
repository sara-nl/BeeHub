  /*
 * Copyright ©2014 SURFsara bv, The Netherlands
 *
 * This file is part of the BeeHub webclient.
 *
 * BeeHub webclient is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * BeeHub webclient is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with BeeHub webclient.  If not, see <http://www.gnu.org/licenses/>.
 */
"use strict";

(function(){
  var currentDirectory =      "/foo/client_tests/";
  var dialog = '#bh-dir-dialog';
  
  var aclTableSearch =      '.bh-dir-acl-table-search';
  var aclResourceForm =     '#bh-dir-acl-resource-form';
  var aclRadio1 =           '.bh-dir-acl-add-radio1';
  var aclRadio2 =           '.bh-dir-acl-add-radio2';
  var aclRadio3 =           '.bh-dir-acl-add-radio3';
  var dialogButton=         '#bh-dir-dialog-button';
  var dialogCancelButton=   '#bh-dir-cancel-dialog-button';

  // Needed for testing autocomplete 
  var autocompleteLength = 5;
  var ui = {
      item: {
        displayname:    "Bar",
        icon:           '<i class="icon-user"></i><i class="icon-user"></i>',
        label:          "Bar (bar) ",
        name:           "/system/groups/bar",
        value:          "Bar (bar) "
      }
  }

  module("dialog view");
  
  /**
   * Test autocomplete in acl form
   * 
   */
  var testAutocomplete = function(){
    // Test if autocomplete is configured
    deepEqual($(dialog).find( aclTableSearch ).autocomplete("option", "source").length, autocompleteLength, "Length of autocomplete sources should be "+autocompleteLength);

    // Test select event
    // Value before select
    deepEqual($(dialog).find(aclTableSearch).val(),"", "Value should be empty string");
    deepEqual($(dialog).find(aclTableSearch).attr("name"), undefined, "Attribute name should be undefined");
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled'), true, "Add button should be disabled");
    // Select
    $(dialog).find( aclTableSearch ).data("ui-autocomplete")._trigger("select", 'autocompleteselect', ui);
    // Test values after select
    deepEqual($(dialog).find(aclTableSearch).val(), ui.item.label, "Value should be "+ui.item.label);
    deepEqual($(dialog).find(aclTableSearch).attr("name"), ui.item.name, "Attribute name should be "+ui.item.name);
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled'), false, "Add button should be enabled");

    // Change with value
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled'), false, "Add button should be enabled");
    $(dialog).find( aclTableSearch ).data("ui-autocomplete")._trigger("change", 'autocompletechange', ui);
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled'), false, "Add button should be enabled");
    
    // Change with no value
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled'), false, "Add button should be enabled");
    $(dialog).find( aclTableSearch ).data("ui-autocomplete")._trigger("change", 'autocompletechange', undefined);
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled'), true, "Add button should be disabled");
  };
  
  /**
   * Test radio button in acl form
   * 
   */
  var testRadioButtons = function(){
    // Button 1
    $(dialog).find(aclRadio1).click();
    deepEqual($(dialog).find(aclTableSearch).attr("disabled"), "disabled", "Search field should be disabled");
    deepEqual($(dialog).find(aclTableSearch).val(), "", "Search field value should be empty string");
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled') , false, "Add button should be enabled");
    
    // Button 2
    $(dialog).find(aclRadio2).click();
    deepEqual($(dialog).find(aclTableSearch).attr("disabled"), "disabled", "Search field should be disabled");
    deepEqual($(dialog).find(aclTableSearch).val(), "", "Search field value should be empty string");
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled') , false, "Add button should be enabled");
    
    // Button 3
    $(dialog).find(aclRadio3).click();
    deepEqual($(dialog).find(aclTableSearch).attr("disabled"), undefined, "Search field should be enabled");
    deepEqual($(dialog).find(aclTableSearch).val(), "", "Search field value should be empty string");
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled') , true, "Add button should be disabled");
  }
  
  /**
   * Setup enviroment of info field en calls testFunction
   * 
   * @param {Function}  testFunction  Function to call after environment setup
   */
  var testInfoDialog = function(testFunction){
    // Setup environment
    var resource = new nl.sara.beehub.ClientResource(currentDirectory);
    resource.displayname = "currentDirectory";
    
    nl.sara.beehub.controller.actionAction = "copy";
    nl.sara.beehub.controller.actionResources = [resource];
    
    var rememberDialog = $.fn.dialog; 
    //Overwrite dialog
    $.fn.dialog = function(input){
      // Do nothing
    }
    
    var actionFunction = function() {
      // Do nothing;
    };
    
    nl.sara.beehub.view.dialog.showResourcesDialog(actionFunction);   
    
    testFunction(resource);
    
    // Put back original dialog function
    $.fn.dialog = rememberDialog;
  };
  
  /**
   * Test if clear view is working
   */
  test( 'nl.sara.beehub.view.dialog.clearView: Clear view', function() {
    expect(2);
    
    // init and open dialog
    $(dialog).dialog();    
    deepEqual($(dialog).dialog("isOpen"), true, "Dialog should be open now.");
    
    // Clear dialog
    nl.sara.beehub.view.dialog.clearView();
    deepEqual($(dialog).dialog("isOpen"), false, "Dialog should be closed now.");
  });
  
  /**
   * Test dialog show error
   */ 
  test('nl.sara.beehub.view.dialog.showError: Show error', function(){
    expect(1);
    
    var rememberDialog = $.fn.dialog;
    
    //Overwrite dialog
    $.fn.dialog = function(){
      deepEqual($(dialog).html(), "Show error test", "Dialog content should be -Show error test-.");
    }

    nl.sara.beehub.view.dialog.showError("Show error test");
    
    // Put back original dialog function
    $.fn.dialog = rememberDialog;
  });
  
  /**
   * Test showAcl
   * 
   * changing resourcePath is not tested
   */
  test('nl.sara.beehub.view.dialog.showAcl', function(){
    expect(22);
    
    nl.sara.beehub.view.acl.setView("resource", currentDirectory);
    var html = nl.sara.beehub.view.acl.createDialogViewHtml();
    
    var rememberDialog = $.fn.dialog; 
    //Overwrite dialog
    $.fn.dialog = function(){
      // Do not open dialog
    }
    
    nl.sara.beehub.view.dialog.showAcl(html, currentDirectory);
    // Test if html is added in dialog
    deepEqual($(dialog).find(aclResourceForm).length, 1, "Dialog content should be -Show error test-.");
    
    // Add button should be disabled
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled') , true, "Add button should be disabled");
    
    testAutocomplete();
    
    testRadioButtons();
    
//    // Put back original dialog function
    $.fn.dialog = rememberDialog;
  });
  
  /**
   * Test setDialogReady
   */
  test('nl.sara.beehub.view.dialog.setDialogReady', function(){
    expect(10);
    
    // Setup environment
    var html = '<div><div id="bh-dir-cancel-dialog-button"></div>\
      <div id="bh-dir-dialog-button"></div></div>'
    $(dialog).html(html);
    $(dialog).show();
    
    // Initialize button
    var button = $(dialog).find(dialogButton).button({label:"Copy"});
    button.button("disable");
    button.addClass("btn-danger");
    
    // Initialize cancel button
    var cancelButton = $(dialog).find(dialogCancelButton).button({label:"Cancel"});
    cancelButton.show();
    
    var actionFunction = function(){
      ok(true, "Action function is called.")
    }
    
    var rememberDialog = $.fn.dialog; 
    //Overwrite dialog
    $.fn.dialog = function(input){
      deepEqual(input,"close", "Dialog close should be called");
    }

    // Test value before function call
    deepEqual(button.hasClass('btn-danger'), true, "btn-danger class should be set");
    deepEqual(button.is(':enabled'), false, "Button should be disabled");
    deepEqual(button.text(),"Copy", "Button text should be Copy");
    deepEqual(cancelButton.is(":hidden"), false, "Cancel button should be shown");

    nl.sara.beehub.view.dialog.setDialogReady(actionFunction);
    
    deepEqual(button.hasClass('btn-danger'), false, "btn-danger class should be unset");
    deepEqual(button.is(':enabled'), true, "Button should be enabled");
    deepEqual(button.text(),"Ready", "Button text should be Ready");
    deepEqual(cancelButton.is(":hidden"), true, "Cancel button should be hidden");
    
    button.click();

//  // Put back original dialog function
    $.fn.dialog = rememberDialog;
  });
  
  /**
   * Test showProgressBar
   * 
   */
  test('nl.sara.beehub.view.dialog.showProgressBar', function(){
    expect(7);
  
    var testFunction = function(resource){

      
      var info = $( "tr[id='dialog_tr_" + resource.path + "']" ).find( '.info' );
      // Test values before
      deepEqual(info.find(".bar").length, 0, "No progress bar should be found.");
      
      nl.sara.beehub.view.dialog.showProgressBar(resource, 70);

      deepEqual(info.find('.bar').length, 1, "One progressbar should be found.");
      deepEqual(info.find('.bar').attr('style'), "width: 70%", "Width should be 70% here.");
      deepEqual(info.find('.bar').html(), "70%", "Text should be 70% here.");
      deepEqual(info.children([0]).hasClass('progress'), true, "Class progress should be set.");
      deepEqual(info.children([0]).hasClass('progress-success'), true, "Class progress should be set.");
      deepEqual(info.children([0]).hasClass('progress-striped'), true, "Class progress should be set.");
    }
    
    testInfoDialog(testFunction);
  });
  
   /**
   * Test updateResourceInfo
   * 
   */
  test('nl.sara.beehub.view.dialog.updateResourceInfo', function(){
    expect(1);
  
    var testFunction = function(resource){
      nl.sara.beehub.view.dialog.updateResourceInfo(resource, "Test info");
      
      var info = $( "tr[id='dialog_tr_" + resource.path + "']" ).find( '.info' );
      
      // Progress bar should be overwritten.
      deepEqual(info.html(), "<b>Test info</b>", "Info should be Test info in bold.");
    }
    
    testInfoDialog(testFunction);
  })
  
  /**
   * Test setAlreadyExist
   */
  test('nl.sara.beehub.view.dialog.setAlreadyExist', function(){
    expect(5);
    
    var testFunction = function(resource){
      var overwriteFunction = function(){
        ok(true, "Overwrite function is called");
      };
      
      var renameFunction = function(){
        ok(true, "Rename function is called");
      };
      
      var cancelFunction = function(){
        ok(true, "Cancel function is called");
      };
      
      var info = $("tr[id='dialog_tr_"+resource.path+"']").find('.info');
      
      // Test value before
      deepEqual(info.children().length,0, "Info should be empty");
      
      nl.sara.beehub.view.dialog.setAlreadyExist(resource, overwriteFunction, renameFunction, cancelFunction);
      
      deepEqual(info.children().length,4, "Info field should have 4 children");
      info.find('.overwritebutton').click();
      info.find('.cancelbutton').click();
      info.find('.renamebutton').click();
    };
    
    testInfoDialog(testFunction);
  })
})();
// End of file