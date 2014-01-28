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
  
  var autocompleteLength = 5;

  module("dialog view");
  
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
   * Test
   */
  test('nl.sara.beehub.view.dialog.showAcl', function(){
    expect(2);
    
    nl.sara.beehub.view.acl.setView("resource", currentDirectory);
    var html = nl.sara.beehub.view.acl.createDialogViewHtml();
    
    var rememberDialog = $.fn.dialog; 
    //Overwrite dialog
    $.fn.dialog = function(){
      var formView = nl.sara.beehub.view.acl.getFormView();

      // Test if html is added in dialog
      deepEqual($(dialog).find(aclResourceForm).length, 1, "Dialog content should be -Show error test-.");
      // Test if autocomplete is configured
      deepEqual($(dialog).find( aclTableSearch ).autocomplete("option", "source").length, autocompleteLength, "Length of autocomplete sources should be "+autocompleteLength);
//      $(dialog).find( aclTableSearch ).data("ui-autocomplete")._trigger("change");
//      $(dialog).find( aclTableSearch ).data("ui-autocomplete")._trigger("select");
    }
    
    nl.sara.beehub.view.dialog.showAcl(html, currentDirectory);


    // Put back original dialog function
    $.fn.dialog = rememberDialog;
  });
})();
// End of file