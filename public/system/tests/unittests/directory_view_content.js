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

//// Call init function
//nl.sara.beehub.view.content.init();

(function(){
  var fixture = $("#qunit-fixture").clone(true);
  
  var home = "/home/john/";
  // Current dir is /foo/
  var up = "/";
  var homeButton = '.bh-dir-content-gohome';
  var directory1 = '/foo/directory';
  var directory1Displayname = 'directory';
  var file1 = '/foo/file.txt';
  var file1Displayname = 'file.txt';
  var upButton = '.bh-dir-content-up';
  var deleteButton = '.bh-dir-content-delete';
  var copyButton = '.bh-dir-content-copy';
  var moveButton = '.bh-dir-content-move';
  
  module("view content",{
    setup: function(){
      $('#qunit-fixture').replaceWith(fixture);
      // Call init function
      nl.sara.beehub.view.content.init();
//      $('body').append(fixture.clone(true));
    }
  });
  
  var checkCheckboxes = function(length) {    
    // Select all checkboxes
    $('.bh-dir-content-checkboxgroup').click();
    deepEqual($('.bh-dir-content-checkbox:checked').length, length , "All checkboxes should be selected");
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
  };
  
  var checkOpenSelected = function(check) {      
    // Rewrite controller goToPage
    var rememberGoToPage = nl.sara.beehub.controller.goToPage;
    nl.sara.beehub.controller.goToPage = function(location){
      deepEqual(location, check, "Location should be "+ check );
    };
    
    var row = $("tr[id='"+check+"']");
    
    // Call init function
    nl.sara.beehub.view.content.init();
    
    // Call click handlers
    row.find('.bh-dir-content-openselected').click();
    
    // Original environment
    nl.sara.beehub.controller.goToPage = rememberGoToPage;
    nl.sara.beehub.view.content.init();
  };
  
  var checkEditMenu = function(check , displayname){    
    // Call init function
    nl.sara.beehub.view.content.init();
    
    var rememberRenameResource = nl.sara.beehub.controller.renameResource;
    nl.sara.beehub.controller.renameResource = function(resource, value, overwrite){
      deepEqual(resource.path, check , "Location should be "+check );
      deepEqual(value, "newFolderName", "Value should be newFolderName" );
    };

    var row = $("tr[id='"+check+"']");

    // Call events
    // Check click event 
    row.find('.bh-dir-content-edit').click();

    deepEqual(row.find(".bh-dir-content-name").is(':hidden'), true, 'Name field should be hidden');
    deepEqual(row.find(".bh-dir-content-rename-td").is(':hidden'), false, 'Input field should be shown');
    deepEqual(row.find(".bh-dir-content-rename-td").find(':input').val(), displayname,'Input field value should be testfolder');
    deepEqual(row.find(".bh-dir-content-rename-td").find(':input').is(':focus'), true, 'Mouse should be focused in input field');
  
    row.find(".bh-dir-content-rename-td").find(':input').val("newFolderName");
    
    // Check change event
    row.find('.bh-dir-content-rename-form').change();
   
    
    // Check keypress event
    var e = jQuery.Event("keypress");
    e.which = 13; // # Some key code value
    row.find('.bh-dir-content-rename-form').trigger(e);

    // Check blur event
    row.find('.bh-dir-content-rename-form').blur();
    deepEqual(row.find(".bh-dir-content-name").is(':hidden'), false, 'Name field should be shown');
    deepEqual(row.find(".bh-dir-content-rename-td").is(':hidden'), true, 'Input field should be hidden');
   
    // Original environment
    nl.sara.beehub.controller.renameResource = rememberRenameResource;
    nl.sara.beehub.view.content.init();
  };
  
  var checkSetRowHandlers = function(length, check , displayname){
    checkCheckboxes( length );
    checkOpenSelected( check );
    checkEditMenu( check, displayname );
  };
  
  /**
   * Test home and up buttons click handlers
   */
  test( 'nl.sara.beehub.view.content.init: Home button click handler', function() {
    expect( 1 );   
    // Rewrite controller goToPage
    var rememberGoToPage = nl.sara.beehub.controller.goToPage;
    nl.sara.beehub.controller.goToPage = function(location){
      deepEqual(location, home, "Home location should be "+home );
    };
    
    // Call click handlers
    // Buttons
    $(homeButton).click();
    
    // Original environment
    nl.sara.beehub.controller.goToPage = rememberGoToPage;
  });
  
  /**
   * Test home and up buttons click handlers
   */
  test( 'nl.sara.beehub.view.content.init: Up button click handler', function() {
    expect( 1 );   
    // Rewrite controller goToPage
    var rememberGoToPage = nl.sara.beehub.controller.goToPage;
    nl.sara.beehub.controller.goToPage = function(location){
      deepEqual(location, up, "Up location should be "+up );
    };
 
    // Call click handler
    $(upButton).click();
    
    // Original environment
    nl.sara.beehub.controller.goToPage = rememberGoToPage;
  });
  
  /**
   * Test delete, copy and move buttons click handlers
   */
  test( 'nl.sara.beehub.view.content.init: Delete, copy and move buttons click handlers', function() {
    expect( 18 );
          
    // Select resources
    $("tr[id='"+directory1+"']").find('.bh-dir-content-checkbox').prop('checked',true);
    $("tr[id='"+file1+"']").find('.bh-dir-content-checkbox').prop('checked',true);

    var rememberInitAction = nl.sara.beehub.controller.initAction;
    
    nl.sara.beehub.controller.initAction = function(resources, action){
      deepEqual(resources[0].path, directory1, action+": Name of first resource should be "+directory1);
      deepEqual(resources[0].displayname, directory1Displayname, action+": Displayname of first resource shpuld be "+directory1Displayname);
      deepEqual(resources[1].path, file1, action+": Name of first resource should be "+file1);
      deepEqual(resources[1].displayname, file1Displayname, action+": Displayname of first resource shpuld be "+file1Displayname);
      deepEqual(resources.length, 2, action+": The length of the selected resources should be 2");
      ok(true, action+": Click handler is succesfully set");
    };
    
    $(deleteButton).click();
    $(copyButton).click();
    $(moveButton).click();
   
    // Original environment
    nl.sara.beehub.controller.initAction = rememberInitAction;
  });
  
  /**
   * Test upload button click handler
   */
  test( 'nl.sara.beehub.view.content.init: Upload click and change handlers', function() {
    expect( 2 );      
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
  test( 'nl.sara.beehub.view.content.init: New folder button click handler', function() {
    expect( 1 );
    
//    $("#qunit-fixture").append('<button class="bh-dir-content-newfolder"></button>');
    
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
  test( 'nl.sara.beehub.view.content.init: Select checboxes handlers', function() {
    expect( 16 );
//    $("#qunit-fixture").append(getTable());
    checkCheckboxes( 2 );
  });
  
  /**
   * Test open selected folder icon
   */
  test( 'nl.sara.beehub.view.content.init: Open selected folder icon', function() {
    expect( 1 );
    
//    $("#qunit-fixture").append(getTable());

    checkOpenSelected("/home/testuser/testfolder/");
  });
  
  /**
   * Test edit icon
   */
  test( 'nl.sara.beehub.view.content.init: Edit menu', function() {
    expect( 10 );
    
//    $("#qunit-fixture").append(getTable());
    
    checkEditMenu("/home/testuser/testfolder/", "testfolder");
  });
  
  /**
   * Test clear view
   */
  test( 'nl.sara.beehub.view.content.clearView', function() {
    expect( 5 );
  
//    // Create environment
//    $("#qunit-fixture").append('<button class="bh-dir-content-copy"></button>');
//    $("#qunit-fixture").append('<button class="bh-dir-content-move"></button>');
//    $("#qunit-fixture").append('<button class="bh-dir-content-delete"></button>');
//    
//    $("#qunit-fixture").append('<input type="checkbox" value="testfolder1" name="/home/testuser/testfolder1" class="bh-dir-content-checkbox" checked>');
//    $("#qunit-fixture").append('<input type="checkbox" value="testfile1" name="/home/testuser/testfile1" class="bh-dir-content-checkbox" checked>');
//    $("#qunit-fixture").append('<input type="checkbox" value="testfile2" name="/home/testuser/testfile2" class="bh-dir-content-checkbox" checked>');
//    
//    $("#qunit-fixture").append('<input type="checkbox" class="bh-dir-content-checkboxgroup" checked> ');
  
    nl.sara.beehub.view.content.clearView();
    deepEqual($(".bh-dir-content-checkboxgroup:checked").length, 0, 'Checkbox group should be unchecked');
    deepEqual($('.bh-dir-content-checkbox:checked').length, 0, "Zero checkboxes should be selected");
    deepEqual($('.bh-dir-content-copy').attr('disabled'), 'disabled', 'Copy button should be disabled');
    deepEqual($('.bh-dir-content-move').attr('disabled'), 'disabled', 'Move button should be disabled');
    deepEqual($('.bh-dir-content-delete').attr('disabled'), 'disabled', 'Delete button should be disabled');
  });
  
  /**
   * Test clear view
   */
  test( 'nl.sara.beehub.view.content.allFixedButtons', function() {
    expect( 12 );
  
//    // Create environment
//    // Home and up button
//    $("#qunit-fixture").append('<button class="bh-dir-content-gohome"></button>');
//    $("#qunit-fixture").append('<button class="bh-dir-content-up"></button>');
//    $("#qunit-fixture").append('<button class="bh-dir-content-upload"></button>');
//    $("#qunit-fixture").append('<button class="bh-dir-content-copy"></button>');
//    $("#qunit-fixture").append('<button class="bh-dir-content-move"></button>');
//    $("#qunit-fixture").append('<button class="bh-dir-content-delete"></button>');
    
    nl.sara.beehub.view.content.allFixedButtons('hide');
    deepEqual($(".bh-dir-content-gohome").is(':hidden'), true, 'Home button should be hidden');
    deepEqual($(".bh-dir-content-up").is(':hidden'), true, 'Up button should be hidden');
    deepEqual($(".bh-dir-content-upload").is(':hidden'), true, 'Upload button should be hidden');
    deepEqual($(".bh-dir-content-copy").is(':hidden'), true, 'Copy button should be hidden');
    deepEqual($(".bh-dir-content-move").is(':hidden'), true, 'Move button should be hidden');
    deepEqual($(".bh-dir-content-delete").is(':hidden'), true, 'Delete button should be hidden');
  
    nl.sara.beehub.view.content.allFixedButtons('show');
    deepEqual($(".bh-dir-content-gohome").is(':hidden'), false, 'Home button should be shown');
    deepEqual($(".bh-dir-content-up").is(':hidden'), false, 'Up button should be shown');
    deepEqual($(".bh-dir-content-upload").is(':hidden'), false, 'Upload button should be shown');
    deepEqual($(".bh-dir-content-copy").is(':hidden'), false, 'Copy button should be shown');
    deepEqual($(".bh-dir-content-move").is(':hidden'), false, 'Move button should be shown');
    deepEqual($(".bh-dir-content-delete").is(':hidden'), false, 'Delete button should be shown');
   
  });
  
  /**
   * Test trigger rename click
   */
  test('nl.sara.beehub.view.content.triggerRenameClick', function(){
    expect ( 1 );
    
//    // Create environment
//    $("#qunit-fixture").append('<table><tbody><tr id="/home/testuser/testfolder">\
//        <td><i class="bh-dir-content-edit"></i></td>\
//      </tr></tbody></table>');
    
    $('.bh-dir-content-edit').unbind().click(function(){
      ok(true, "Rename handler click should be triggered.")
    })
    
    var resource = new nl.sara.beehub.ClientResource("/home/testuser/testfolder");
    nl.sara.beehub.view.content.triggerRenameClick(resource);
  });
  
  /**
   * Test getUnknownResourceValues
   */
  test('nl.sara.beehub.view.content.getUnknownResourceValues', function(){
    expect( 5 );
  
//    // Create environment
//    $("#qunit-fixture").append(getTable());
    
    var resource = new nl.sara.beehub.ClientResource("/home/testuser/testfile/");
    resource = nl.sara.beehub.view.content.getUnknownResourceValues(resource);

    deepEqual(resource.displayname, "testfile", "Displayname should be testfile");
    deepEqual(resource.type, "text/plain; charset=UTF-8", "Type should be text/plain; charset=UTF-8");
    deepEqual(resource.owner, "/system/users/testuser", "Owner should be /system/users/testuser");
    deepEqual(resource.contentlength, "39424", "Contentlength should be 39424");
    deepEqual(resource.lastmodified, "Thu Nov 21 2013 14:27:03 GMT+0100 (CET)", "Lastmodified should be testfolder");
  });
  
  /**
   * Test add resource
   */
  test('nl.sara.beehub.view.content.addResource', function(){
    expect( 38 );
    
//    $("#qunit-fixture").append(getTable());
    
    // Init for initializing table
    nl.sara.beehub.view.content.init();
    
    var resource = new nl.sara.beehub.ClientResource( location.href + "/subfolder/newfolder/");
    resource.displayname = "newfolder";
    resource.type = "collection";
    resource.contentlength = "undefined";
    resource.lastmodified = "Thu Nov 21 2013 14:27:03 GMT+0100 (CET)";
    resource.owner = 'Test User'

    var testresource1 = new nl.sara.beehub.ClientResource("/home/testuser/newfolder/");
    testresource1 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource1);
    
    deepEqual(testresource1.displayname, undefined, "Displayname should be undefined");
    deepEqual(testresource1.type, undefined, "Type should be undefined");
    deepEqual(testresource1.contentlength, undefined, "Contentlength should be undefined");
    deepEqual(testresource1.lastmodified, undefined, "Lastmodified should be undefined");
    deepEqual(testresource1.owner, undefined, "Owner should be undefined");
    
    nl.sara.beehub.view.content.addResource(resource);
    
    var testresource2 = new nl.sara.beehub.ClientResource("/home/testuser/newfolder/");
    testresource2 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource2);

    deepEqual(testresource2.path, resource.path, "Path should be "+resource.path);
    deepEqual(testresource2.displayname, resource.displayname, "Displayname should be "+resource.displayname);
    deepEqual(testresource2.type, resource.type, "Type should be "+resource.type);
    deepEqual(testresource2.contentlength, resource.contentlength, "Contentlength should be "+resource.contentlength);
    deepEqual(testresource2.lastmodified, resource.lastmodified, "Lastmodified should be "+resource.lastmodified);
    deepEqual(testresource2.owner, resource.owner, "Owner should be "+resource.owner);

    checkSetRowHandlers( 3, resource.path , resource.displayname );
  })
  
   /**
   * Test delete resource
   */
  test('nl.sara.beehub.view.content.deleteResource', function(){
    expect( 10 );
    
//    $("#qunit-fixture").append(getTable());
    
    // Init for initializing table
    nl.sara.beehub.view.content.init();
    
    var resource = new nl.sara.beehub.ClientResource("/home/testuser/testfile/");
    
    var testresource1 = new nl.sara.beehub.ClientResource("/home/testuser/testfile/");
    testresource1 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource1);
    
    deepEqual(testresource1.displayname, "testfile", "Displayname should be testfile");
    deepEqual(testresource1.type, "text/plain; charset=UTF-8", "Type should be text/plain; charset=UTF-8");
    deepEqual(testresource1.contentlength, "39424", "Contentlength should be 39424");
    deepEqual(testresource1.lastmodified, "Thu Nov 21 2013 14:27:03 GMT+0100 (CET)", "Lastmodified should be Thu Nov 21 2013 14:27:03 GMT+0100 (CET)");
    deepEqual(testresource1.owner, "/system/users/testuser", "Owner should be /system/users/testuser");
    
    nl.sara.beehub.view.content.deleteResource(resource);
    
    var testresource2 = new nl.sara.beehub.ClientResource("/home/testuser/testfile/");
    testresource2 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource2);
    
    deepEqual(testresource2.displayname, undefined, "Displayname should be undefined");
    deepEqual(testresource2.type, undefined, "Type should be undefined");
    deepEqual(testresource2.contentlength, undefined, "Contentlength should be undefined");
    deepEqual(testresource2.lastmodified, undefined, "Lastmodified should be undefined");
    deepEqual(testresource2.owner, undefined, "Owner should be undefined");
  })
  
     /**
   * Test delete resource
   */
  test('nl.sara.beehub.view.content.updateResource', function(){
    expect( 46 );
    
//    $("#qunit-fixture").append(getTable());
    
    // Init for initializing table
    nl.sara.beehub.view.content.init();
    
    var testresource1 = new nl.sara.beehub.ClientResource("/home/testuser/testfile/");
    testresource1 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource1);
    
    deepEqual(testresource1.displayname, "testfile", "Displayname should be testfile");
    deepEqual(testresource1.type, "text/plain; charset=UTF-8", "Type should be text/plain; charset=UTF-8");
    deepEqual(testresource1.contentlength, "39424", "Contentlength should be 39424");
    deepEqual(testresource1.lastmodified, "Thu Nov 21 2013 14:27:03 GMT+0100 (CET)", "Lastmodified should be Thu Nov 21 2013 14:27:03 GMT+0100 (CET)");
    deepEqual(testresource1.owner, "/system/users/testuser", "Owner should be /system/users/testuser");
    
    var resourcenew = new nl.sara.beehub.ClientResource("/home/testuser/testfile2/");
    resourcenew = nl.sara.beehub.view.content.getUnknownResourceValues(resourcenew);
    
    deepEqual(resourcenew.displayname, undefined, "Displayname should be undefined");
    deepEqual(resourcenew.type, undefined, "Type should be undefined");
    deepEqual(resourcenew.contentlength, undefined, "Contentlength should be undefined");
    deepEqual(resourcenew.lastmodified, undefined, "Lastmodified should be undefined");
    deepEqual(resourcenew.owner, undefined, "Owner should be undefined");
    
    resourcenew.displayname = "testfile2";
    resourcenew.type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
    resourcenew.contentlength = "40000";
    resourcenew.lastmodified = "Thu Nov 22 2013 14:27:03 GMT+0100 (CET)";
    resourcenew.owner = "/system/users/testuser2";
      
    nl.sara.beehub.view.content.updateResource(testresource1, resourcenew);
   
    var testresource2 = new nl.sara.beehub.ClientResource("/home/testuser/testfile/");
    testresource2 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource2);
    
    deepEqual(testresource2.displayname, undefined, "Displayname should be undefined");
    deepEqual(testresource2.type, undefined, "Type should be undefined");
    deepEqual(testresource2.contentlength, undefined, "Contentlength should be undefined");
    deepEqual(testresource2.lastmodified, undefined, "Lastmodified should be undefined");
    deepEqual(testresource2.owner, undefined, "Owner should be undefined");
    
    var testresource3 = new nl.sara.beehub.ClientResource("/home/testuser/testfile2/");
    testresource3 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource3);
    
    deepEqual(testresource3.displayname, "testfile2", "Displayname should be testfile2");
    deepEqual(testresource3.type, "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "Type should be application/vnd.openxmlformats-officedocument.wordprocessingml.document");
    deepEqual(testresource3.contentlength, "40000", "Contentlength should be 40000");
    deepEqual(testresource3.lastmodified, "Thu Nov 22 2013 14:27:03 GMT+0100 (CET)", "Lastmodified should be Thu Nov 22 2013 14:27:03 GMT+0100 (CET)");
    deepEqual(testresource3.owner, "/system/users/testuser2", "Owner should be /system/users/testuser2");
    
    checkSetRowHandlers( 2, resourcenew.path , resourcenew.displayname );
  }) 
})();
// End of file