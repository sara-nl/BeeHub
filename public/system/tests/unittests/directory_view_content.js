/*
 * Copyright ©2013 SURFsara bv, The Netherlands
 *
 * This file is part of js-webdav-client.
 *
 * js-webdav-client is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * js-webdav-client is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with js-webdav-client.  If not, see <http://www.gnu.org/licenses/>.
 */
"use strict";

module("view content")
/**
 * Test home and up buttons click handlers
 */
test( 'Init: Home and up buttons click handlers', function() {
  expect( 2 );

  // Home and up button
  $("#qunit-fixture").append('<button id="/home/testuser/" class="bh-dir-content-gohome"></button>');
  $("#qunit-fixture").append('<button id="/home/testuser/" class="bh-dir-content-up"></button>');
  
  // Rewrite controller goToPage
  var rememberGoToPage = nl.sara.beehub.controller.goToPage;
  nl.sara.beehub.controller.goToPage = function(location){
    deepEqual(location, "/home/testuser/", "Location should be /home/testuser" );
  };
  // Call init function
  nl.sara.beehub.view.content.init();
  
  // Call click handlers
  // Buttons
  $('.bh-dir-content-gohome').click();
  $('.bh-dir-content-up').click();
  
  // Original environment
  nl.sara.beehub.controller.goToPage = rememberGoToPage;
  nl.sara.beehub.view.content.init();
});

/**
 * Test delete, copy and move buttons click handlers
 */
test( 'Init: Delete, copy and move buttons click handlers', function() {
  expect( 12 );

  $("#qunit-fixture").append('<button class="bh-dir-content-copy"></button>');
  $("#qunit-fixture").append('<button class="bh-dir-content-move"></button>');
  $("#qunit-fixture").append('<button class="bh-dir-content-delete"></button>');

  $("#qunit-fixture").append('<input type="checkbox" value="testfolder1" name="/home/laura/testfolder1" class="bh-dir-content-checkbox" checked>');
  $("#qunit-fixture").append('<input type="checkbox" value="testfile1" name="/home/laura/testfile1" class="bh-dir-content-checkbox" checked>');
  $("#qunit-fixture").append('<input type="checkbox" value="testfile2" name="/home/laura/testfile2" class="bh-dir-content-checkbox">');
  
  var rememberInitAction = nl.sara.beehub.controller.initAction;
  nl.sara.beehub.controller.initAction = function(resources, action){
    deepEqual(resources[0].path, "/home/laura/testfolder1", action+": Name of first resource should be /home/laura/testfolder1");
    deepEqual(resources[0].displayname, "testfolder1", action+": Displayname of first resource shpuld be testfolder1");
    deepEqual(resources.length, 2, action+": The length of the selected resources should be 2");
    ok(true, action+": Click handler is succesfully set");
  };
  
  // Call init function
  nl.sara.beehub.view.content.init();
  
  $('.bh-dir-content-delete').click();
  $('.bh-dir-content-copy').click();
  $('.bh-dir-content-move').click();
 
  // Original environment
  nl.sara.beehub.controller.initAction = rememberInitAction;
  nl.sara.beehub.view.content.init();
});

/**
 * Test upload button click handler
 */
test( 'Init: Upload click and change handlers', function() {
  expect( 2 );  
  
  $("#qunit-fixture").append('<button class="bh-dir-content-upload"></button>');
  $("#qunit-fixture").append('<button class="bh-dir-content-upload-hidden"></button>');
  
  var rememberInitAction = nl.sara.beehub.controller.initAction;
  nl.sara.beehub.controller.initAction = function(resources, action){
    ok(true, "Upload click handler is set");
  };
  
  var rememberShowError = nl.sara.beehub.controller.showError;
  nl.sara.beehub.controller.showError = function(){
    ok(true, "IE should show an error when uploading files is clicked")
  }; 
  
  // Call init function
  nl.sara.beehub.view.content.init();
  
  $('.bh-dir-content-upload-hidden').unbind().click(function(){
    // This should be done bu clicking the upload button
      ok(true, "Upload hidden click handler field is set");
  });
  
  $('.bh-dir-content-upload').click();
 
  // Call init function
  nl.sara.beehub.view.content.init();
  
  $('.bh-dir-content-upload-hidden').change();
  
  // Original environment
  nl.sara.beehub.controller.initAction = rememberInitAction;
  nl.sara.beehub.controller.showError = rememberShowError;
  nl.sara.beehub.view.content.init();
});

/**
 * Test new folder click handler
 */
test( 'Init: New folder button click handler', function() {
  expect( 1 );
  
  $("#qunit-fixture").append('<button class="bh-dir-content-newfolder"></button>');
  
  // Call init function
  nl.sara.beehub.view.content.init();
 
  var rememberCreateNewFolder = nl.sara.beehub.controller.createNewFolder;
  nl.sara.beehub.controller.createNewFolder = function(){
    ok(true, "Click handler new folder button is set");
  };
  
  $('.bh-dir-content-newfolder').click();
  
  // Original environment
  nl.sara.beehub.controller.createNewFolder = rememberCreateNewFolder;
  nl.sara.beehub.view.content.init();
});

/**
 * Test select checkboxes
 */
test( 'Init: Select checboxes handlers', function() {
  expect( 16 );
  
  $("#qunit-fixture").append('<button class="bh-dir-content-copy"></button>');
  $("#qunit-fixture").append('<button class="bh-dir-content-move"></button>');
  $("#qunit-fixture").append('<button class="bh-dir-content-delete"></button>');
  
  $("#qunit-fixture").append('<input type="checkbox" value="testfolder1" name="/home/laura/testfolder1" class="bh-dir-content-checkbox" checked>');
  $("#qunit-fixture").append('<input type="checkbox" value="testfile1" name="/home/laura/testfile1" class="bh-dir-content-checkbox" checked>');
  $("#qunit-fixture").append('<input type="checkbox" value="testfile2" name="/home/laura/testfile2" class="bh-dir-content-checkbox">');
  
  $("#qunit-fixture").append('<input class="bh-dir-content-checkboxgroup" type="checkbox">');
  
  // Call init function
  nl.sara.beehub.view.content.init();
 
  // Select all checkboxes
  $('.bh-dir-content-checkboxgroup').click();
  deepEqual($('.bh-dir-content-checkbox:checked').length, 3, "All checkboxes should be selected");
  deepEqual($('.bh-dir-content-copy').attr("disabled"), undefined, "Copy button should be enabled");
  deepEqual($('.bh-dir-content-move').attr("disabled"), undefined, "Move button should be enabled");
  deepEqual($('.bh-dir-content-delete').attr("disabled"), undefined, "Delete button should be enabled");
  // Unselect all checkboxes
  $('.bh-dir-content-checkboxgroup').click();
  deepEqual($('.bh-dir-content-checkbox:checked').length, 0, "Zero checkboxes should be selected");
  deepEqual($('.bh-dir-content-copy').attr("disabled"), "disabled", "Copy button should be disabled");
  deepEqual($('.bh-dir-content-move').attr("disabled"), "disabled", "Move button should be disabled");
  deepEqual($('.bh-dir-content-delete').attr("disabled"), "disabled", "Delete button should be disabled");
  
  // Select 1 checkbox
  $('.bh-dir-content-checkbox')[0].click();
  deepEqual($('.bh-dir-content-checkbox:checked').length, 1, "One checkbox should be selected");
  deepEqual($('.bh-dir-content-copy').attr("disabled"), undefined, "Copy button should be enabled");
  deepEqual($('.bh-dir-content-move').attr("disabled"), undefined, "Move button should be enabled");
  deepEqual($('.bh-dir-content-delete').attr("disabled"), undefined, "Delete button should be enabled");
  // Deselect this checkbox again
  $('.bh-dir-content-checkbox')[0].click();
  deepEqual($('.bh-dir-content-checkbox:checked').length, 0, "Zere checkboxes should be selected");
  deepEqual($('.bh-dir-content-copy').attr("disabled"), "disabled", "Copy button should be disabled");
  deepEqual($('.bh-dir-content-move').attr("disabled"), "disabled", "Move button should be disabled");
  deepEqual($('.bh-dir-content-delete').attr("disabled"), "disabled", "Delete button should be disabled");
});

/**
 * Test open selected folder icon
 */
test( 'Init: Open selected folder icon', function() {
  expect( 1 );

  // Home and up button
  
  $("#qunit-fixture").append('<i class="bh-dir-content-openselected" name="/home/testfolder/"></i>');

  // Rewrite controller goToPage
  var rememberGoToPage = nl.sara.beehub.controller.goToPage;
  nl.sara.beehub.controller.goToPage = function(location){
    deepEqual(location, "/home/testfolder/", "Location should be /home/testfolder" );
  };
  
  // Call init function
  nl.sara.beehub.view.content.init();
  
  // Call click handlers
  // Buttons
  $('.bh-dir-content-openselected').click();
  
  // Original environment
  nl.sara.beehub.controller.goToPage = rememberGoToPage;
  nl.sara.beehub.view.content.init();
});

/**
 * Test edit icon
 */
test( 'Init: Edit icon', function() {
  expect( 10 );

  // Create environment
  $("#qunit-fixture").append('<table><tbody><tr id="/home/testuser/testfolder">\
      <td title="Rename"><i class="icon-edit bh-dir-content-edit"></i></td>\
      <td name="new_folder" class="bh-dir-content-name displayname">\
        <a href="/home/testuser/testfolder"><b>testfolder/</b></a>\
      </td>\
      <td hidden="" class="bh-dir-content-rename-td">\
        <input value="testfolder" name="testfolder" class="bh-dir-content-rename-form">\
      </td>\
    </tr></tbody></table>');
  
  // Call init function
  nl.sara.beehub.view.content.init();
  
  var rememberRenameResource = nl.sara.beehub.controller.renameResource;
  nl.sara.beehub.controller.renameResource = function(resource, value, overwrite){
    deepEqual(resource.path, "/home/testuser/testfolder", "Location should be /home/testuser/testfolder" );
    deepEqual(value, "newFolderName", "Value should be newFolderName" );
  };
  
  // Call events
  // Check click event
  $('.bh-dir-content-edit').click();
  deepEqual($(".bh-dir-content-name").is(':hidden'), true, 'Name field should be hidden');
  deepEqual($(".bh-dir-content-rename-td").is(':hidden'), false, 'Input field should be shown');
  deepEqual($(".bh-dir-content-rename-td").find(':input').val(),'testfolder','Input field value should be testfolder');
  deepEqual($(".bh-dir-content-rename-td").find(':input').is(':focus'), true, 'Mouse should be focused in input field');

  $(".bh-dir-content-rename-td").find(':input').val("newFolderName");
  
  // Check change event
  $('.bh-dir-content-rename-form').change();
  
  // Check keypress event
  var e = jQuery.Event("keypress");
  e.which = 13; // # Some key code value
  $('.bh-dir-content-rename-form').trigger(e);
  
  // Check blur event
  $('.bh-dir-content-rename-form').blur();
  deepEqual($(".bh-dir-content-name").is(':hidden'), false, 'Name field should be shown');
  deepEqual($(".bh-dir-content-rename-td").is(':hidden'), true, 'Input field should be hidden');
  
  // Original environment
  nl.sara.beehub.controller.renameResource = rememberRenameResource;
  nl.sara.beehub.view.content.init();
});
// End of file