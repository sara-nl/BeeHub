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
  
  var aclTableSearch =    '.bh-dir-acl-table-search';
  var aclResourceForm =   '#bh-dir-acl-resource-form';
  var aclRadio1 =         '.bh-dir-acl-add-radio1';
  var aclRadio2 =         '.bh-dir-acl-add-radio2';
  var aclRadio3 =         '.bh-dir-acl-add-radio3';

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
})();
// End of file