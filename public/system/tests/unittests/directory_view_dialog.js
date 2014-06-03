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
  
  module( "dialog view", {
    teardown: function() {
      // clean up after each test
      backToOriginalEnvironment();
    }
  });
  
  var currentDirectory =            "/foo/client_tests/";
  var testFile =                    "/foo/client_tests/file2.txt";
    
  var dialog =                      '#bh-dir-dialog';
  
  var aclTableSearch =              '.bh-dir-acl-table-search';
  var aclResourceForm =             '#bh-dir-acl-resource-form';
  var aclDirectoryForm =             '#bh-dir-acl-directory-form';
  var aclRadio1 =                   '.bh-dir-acl-add-radio1';
  var aclRadio2 =                   '.bh-dir-acl-add-radio2';
  var aclRadio3 =                   '.bh-dir-acl-add-radio3';
  var dialogButton=                 '#bh-dir-dialog-button';
  var aclDirectoryButton =          '#bh-dir-acl-directory-button';
  var dialogCancelButton=           '#bh-dir-cancel-dialog-button';
  var dialogRenameOverwriteButton=  '#bh-dir-rename-overwrite-button';
  var dialogRenameCancelButton=     "#bh-dir-rename-cancel-button";

  // Needed for testing autocomplete 
  var autocompleteLength = 6;
  var ui = {
      item: {
        displayname:    "Bar",
        icon:           '<i class="icon-user"></i><i class="icon-user"></i>',
        label:          "Bar (bar) ",
        name:           "/system/groups/bar",
        value:          "Bar (bar) "
      }
  };
  
  var rememberDialog = $.fn.dialog; 
  var rememberClearAllViews = nl.sara.beehub.controller.clearAllViews;

  var backToOriginalEnvironment = function(){
    $.fn.dialog = rememberDialog;
    nl.sara.beehub.controller.clearAllViews = rememberClearAllViews;
  };
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
    deepEqual($(dialog).find(aclTableSearch).attr('name'), undefined, "Attribute name should be undefined");
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled'), true, "Add button should be disabled");
    // Select
    $(dialog).find( aclTableSearch ).data("ui-autocomplete")._trigger("select", 'autocompleteselect', ui);
    // Test values after select
    deepEqual($(dialog).find(aclTableSearch).val(), ui.item.label, "Value should be "+ui.item.label);
    deepEqual($(dialog).find(aclTableSearch).attr('data-value'), ui.item.name, "Attribute name should be "+ui.item.name);
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
  };
  
  /**
   * Setup enviroment of info field en calls testFunction
   * 
   * @param {Function}  testFunction  Function to call after environment setup
   * @param {object}    resources     The resources
   */
  var testInfoDialog = function(testFunction, resources){
    // Setup environment    
    nl.sara.beehub.controller.actionAction = "copy";
    nl.sara.beehub.controller.actionResources = resources;
    
    //Overwrite dialog
    $.fn.dialog = function(input){
      // Do nothing
    };
    
    var actionFunction = function() {
      // Do nothing;
    };
    
    nl.sara.beehub.view.dialog.showResourcesDialog(actionFunction);   
    
    testFunction(resources);
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
        
    //Overwrite dialog
    $.fn.dialog = function(){
      deepEqual($(dialog).html(), "Show error test", "Dialog content should be -Show error test-.");
    };

    nl.sara.beehub.view.dialog.showError("Show error test");
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
    
    //Overwrite dialog
    $.fn.dialog = function(){
      // Do not open dialog
    };
    
    nl.sara.beehub.view.dialog.showAcl(html, currentDirectory);
    nl.sara.beehub.view.acl.getAddAclButton().button();
    
    // Test if html is added in dialog
    deepEqual($(dialog).find(aclResourceForm).length, 1, "Dialog content should be not empty.");
    
    // Add button should be disabled
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled') , true, "Add button should be disabled");
    
    testAutocomplete();
    
    testRadioButtons();
  });
  
  /**
   * Test
   */
  test('nl.sara.beehub.view.dialog.showAddRuleDialog', function(){
   expect(24);
    
    nl.sara.beehub.view.acl.setView("directory", currentDirectory);
    var html = nl.sara.beehub.view.acl.createHtmlAclForm("tab");
    
    var testFunction = function(){
      ok(true, "Test function is called.");
    };
   
    //Overwrite dialog
    $.fn.dialog = function(input){
      var html = '<div id="bh-dir-acl-directory-button"></div></div>';
      $(dialog).append(html);
      
      // Initialize button
      var button = nl.sara.beehub.view.acl.getAddAclButton().button();
      button.unbind('click').click(input.buttons[1].click);
      
      ok(true, "Dialog is called.");
    };

    nl.sara.beehub.view.dialog.showAddRuleDialog(testFunction, html);
    
    nl.sara.beehub.view.acl.getAddAclButton().click();

    // Test if html is added in dialog
    deepEqual($(dialog).find(aclDirectoryForm).length, 1, "Dialog content should be not empty.");
    
    // Add button should be disabled
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().prop('disabled') , true, "Add button should be disabled");
    
    testAutocomplete();
    
    testRadioButtons();
  });
  
  /**
   * Test
   */
  test('nl.sara.beehub.view.dialog.getFormAce', function(){
    expect(5);
    
    nl.sara.beehub.view.acl.setView("resource", currentDirectory);
    var html = nl.sara.beehub.view.acl.createDialogViewHtml();
    
    //Overwrite dialog
    $.fn.dialog = function(){
      // Do not open dialog
    };
    
    nl.sara.beehub.view.dialog.showAcl(html, currentDirectory);
    var aclForm = nl.sara.beehub.view.acl.getFormView();
    
    aclForm.find(aclTableSearch).attr('data-value',"test");
    var ace = nl.sara.beehub.view.dialog.getFormAce();
    deepEqual(ace.grantdeny, nl.sara.webdav.Ace.GRANT, "Permissions should be granted.");
    deepEqual(ace.getPrivilegeNames( 'DAV:' ), [ 'read' ], "Privileges should be read.");
    deepEqual(ace.principal, "test", "Principal should be test.");
    
    $(dialog).find(".bh-dir-acl-add-radio1").prop("checked", true); 
    ace = nl.sara.beehub.view.dialog.getFormAce();
    deepEqual(ace.principal, nl.sara.webdav.Ace.AUTHENTICATED, "Principal should be DAV: authenticated.");

    $(dialog).find(".bh-dir-acl-add-radio2").prop("checked", true); 
    ace = nl.sara.beehub.view.dialog.getFormAce();
    deepEqual(ace.principal, nl.sara.webdav.Ace.ALL, "Principal should be DAV: all.");
  });
  
  /**
   * Test setDialogReady
   */
  test('nl.sara.beehub.view.dialog.setDialogReady', function(){
    expect(10);
    
    // Setup environment
    var html = '<div><div id="bh-dir-cancel-dialog-button"></div>\
      <div id="bh-dir-dialog-button"></div></div>';
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
      ok(true, "Action function is called.");
    };
    
    //Overwrite dialog
    $.fn.dialog = function(input){
      deepEqual(input,"close", "Dialog close should be called");
    };

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
  });
  
  /**
   * Test showProgressBar
   * 
   */
  test('nl.sara.beehub.view.dialog.showProgressBar', function(){
    expect(7);
  
    var testFunction = function(resources){

      
      var info = $( "tr[id='dialog_tr_" + resources[0].path + "']" ).find( '.info' );
      // Test values before
      deepEqual(info.find(".bar").length, 0, "No progress bar should be found.");
      
      nl.sara.beehub.view.dialog.showProgressBar(resources[0], 70);

      deepEqual(info.find('.bar').length, 1, "One progressbar should be found.");
      deepEqual(info.find('.bar').attr('style'), "width: 70%", "Width should be 70% here.");
      deepEqual(info.find('.bar').html(), "70%", "Text should be 70% here.");
      deepEqual(info.children([0]).hasClass('progress'), true, "Class progress should be set.");
      deepEqual(info.children([0]).hasClass('progress-success'), true, "Class progress should be set.");
      deepEqual(info.children([0]).hasClass('progress-striped'), true, "Class progress should be set.");
    };
    var resource = new nl.sara.beehub.ClientResource(currentDirectory);
    resource.displayname = "currentDirectory";
    
    testInfoDialog(testFunction, [resource]);
  });
  
   /**
   * Test updateResourceInfo
   * 
   */
  test('nl.sara.beehub.view.dialog.updateResourceInfo', function(){
    expect(1);
  
    var testFunction = function(resources){
      nl.sara.beehub.view.dialog.updateResourceInfo(resources[0], "Test info");
      
      var info = $( "tr[id='dialog_tr_" + resources[0].path + "']" ).find( '.info' );
      
      // Progress bar should be overwritten.
      deepEqual(info.html(), "<b>Test info</b>", "Info should be Test info in bold.");
    };
    
    var resource = new nl.sara.beehub.ClientResource(currentDirectory);
    resource.displayname = "currentDirectory";
    
    testInfoDialog(testFunction,[resource]);
  });
  
  /**
   * Test setAlreadyExist
   */
  test('nl.sara.beehub.view.dialog.setAlreadyExist', function(){
    expect(5);
    
    var testFunction = function(resources){
      var overwriteFunction = function(){
        ok(true, "Overwrite function is called");
      };
      
      var renameFunction = function(){
        ok(true, "Rename function is called");
      };
      
      var cancelFunction = function(){
        ok(true, "Cancel function is called");
      };
      
      var info = $("tr[id='dialog_tr_"+resources[0].path+"']").find('.info');
      
      // Test value before
      deepEqual(info.children().length,0, "Info should be empty");
      
      nl.sara.beehub.view.dialog.setAlreadyExist(resource, overwriteFunction, renameFunction, cancelFunction);
      
      deepEqual(info.children().length,4, "Info field should have 4 children");
      info.find('.overwritebutton').click();
      info.find('.cancelbutton').click();
      info.find('.renamebutton').click();
    };
    
    var resource = new nl.sara.beehub.ClientResource(currentDirectory);
    resource.displayname = "currentDirectory";
    
    testInfoDialog(testFunction,[resource]);
  });
  
  /**
   * Test scrollTo
   */
  test('nl.sara.beehub.view.dialog.showResourcesDialog', function(){ 
    expect(46);
    // Setup environment    
    nl.sara.beehub.controller.actionAction = "copy";
    nl.sara.beehub.controller.actionDestination = currentDirectory;
    
    var resources = [];
    
    for(var i=0; i<20; i++){
      var resource = new nl.sara.beehub.ClientResource("/test/test"+i);
      resource.displayname = "displayname"+i;
      resources.push(resource);
    };
    nl.sara.beehub.controller.actionResources = resources;
    
    //Overwrite dialog
    $.fn.dialog = function(input){
      if (input === "close") {
        ok(true, "Cancel close button is clicked");
      } else {
        deepEqual(input.title, "Copy to "+currentDirectory, "Title should be Copy to "+currentDirectory);
        // Append buttons to dialog
        $(dialog).append('<button id="'+input.buttons[0].id+'">'+input.buttons[0].text+'</button>');
        $(dialog).append('<button id="'+input.buttons[1].id+'">'+input.buttons[1].text+'</button>');
        // Set click handlers on buttons
        $(dialog).find('#'+input.buttons[0].id).click(input.buttons[0].click);
        $(dialog).find('#'+input.buttons[1].id).click(input.buttons[1].click);
      }
    };
    
    nl.sara.beehub.controller.clearAllViews = function(){
      ok(true, "clearAllViews is called");
    };
    
    var actionFunction = function() {
      ok(true, "Action function is called");
    };
    
    nl.sara.beehub.view.dialog.showResourcesDialog(actionFunction);  
    
    // Test if all resource fields are set
    for(var i=0; i<20; i++){
      var val = $(dialog).find("tr[id='dialog_tr_/test/test"+i+"']").find('td').eq(0).html();
      var info = $(dialog).find("tr[id='dialog_tr_/test/test"+i+"']").find('td').eq(1).hasClass('info');
      deepEqual(val, 'displayname'+i, "Table value is "+i);
      deepEqual(info, true, "Class info should be set");
    };
    // Test click handlers
    $(dialog).find(dialogCancelButton).click();
    var button = $(dialog).find(dialogButton);

    button.click();
    
    deepEqual(button.is(':enabled'), false, "Button should be disabled");
    deepEqual(button.text(),"Copy items...", "Button text should be Copy items..."); 
  });
  
  /**
   * Test showOverwriteDialog
   */
  test('nl.sara.beehub.view.dialog.showOverwriteDialog', function(){
    expect(4);
    
    // Overwrite dialog
    $.fn.dialog = function(input){
      if (input === "close") {
        ok(true, "Dialog is called with value close");
      } else {
        ok(true, "Dialog is called.");
      }
    };
    
    var resource = new nl.sara.beehub.ClientResource(testFile);
    var fileNew = testFile;
    
    var overwriteFunction = function(){
      ok(true, "Overwrite function is called.");
    };
    
    nl.sara.beehub.view.dialog.showOverwriteDialog(resource, fileNew, overwriteFunction);
    
    // Test if title is ok
    deepEqual($(dialog).find('i').html(), testFile, "File should be "+testFile);
    // Test Overwrite button click handler
    $(dialogRenameOverwriteButton).click();
    // Test Cancel button click handler
    $(dialogRenameCancelButton).click();
  });
  
   /**
   * Test closeDialog
   */
  test('nl.sara.beehub.view.dialog.closeDialog', function(){
    expect(1);
    
    // Overwrite dialog
    $.fn.dialog = function(input){
      deepEqual(input,"close", "Input should be close.");
    };
    
    nl.sara.beehub.view.dialog.closeDialog();
  });
  
})();
// End of file