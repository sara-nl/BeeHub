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
  nl.sara.beehub.controller.goToPage = function(location){
    deepEqual(location, "/home/testuser/", "Location should be /home/testuser" );
  };
  
  // Call init function
  nl.sara.beehub.view.content.init();
  
  // Call click handlers
  // Buttons
  $('.bh-dir-content-gohome').click();
  $('.bh-dir-content-up').click();
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
 
});

/**
 * Test upload button click handler
 */
test( 'Init: Upload click and change handlers', function() {
  expect( 4 );

  window.File = true;
  window.FileList = true;
  
  $("#qunit-fixture").append('<button class="bh-dir-content-upload"></button>');
  $("#qunit-fixture").append('<button class="bh-dir-content-upload-hidden"></button>');
  
  nl.sara.beehub.controller.initAction = function(resources, action){
    ok(true, "Upload click handler is set");
  };
  
  nl.sara.beehub.controller.showError = function(){
    ok(true, "IE should show an error when uploading files is clicked")
  }; 
  
  // Call init function
  nl.sara.beehub.view.content.init();
  
  $('.bh-dir-content-upload-hidden').unbind().click(function(){
    // This should be done bu clicking the upload button
      ok(true, "Upload hidden click handler field is set");
  });
  window.File = true;
  window.FileList = true;
  $('.bh-dir-content-upload').click();

  window.File = false;
  $('.bh-dir-content-upload').click();

  window.File = true;
  window.FileList = false;
  $('.bh-dir-content-upload').click();

 
  // Call init function
  nl.sara.beehub.view.content.init();
  
  $('.bh-dir-content-upload-hidden').change();
});

/**
 * Test new folder click handler
 */
test( 'Init: New folder button click handler', function() {
  expect( 1 );
  
  $("#qunit-fixture").append('<button class="bh-dir-content-newfolder"></button>');
  
  // Call init function
  nl.sara.beehub.view.content.init();
 
  nl.sara.beehub.controller.createNewFolder = function(){
    ok(true, "Click handler new folder button is set");
  };
  
  $('.bh-dir-content-newfolder').click();
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


// End of file