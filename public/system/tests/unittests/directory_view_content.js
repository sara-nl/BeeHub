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

(function(){
  var home = "/home/john/";
  // Current dir is /foo/
  var up = "/foo";
  
  var contentCount = 4;
  
  var directory1 = '/foo/client_tests/directory';
  var directory1Displayname = 'directory';
  
  var file1 = '/foo/client_tests/file.txt';
  var file1Displayname = 'file.txt';
  var file1Owner = '/system/users/john';
  var file1Type = "text/plain; charset=UTF-8";
  var file1LastModified = "Wed, 01 Jan 2014 12:34:56 +0100";
  var file1Size = "26";
  
  var upButton = '.bh-dir-content-up';
  var deleteButton = '.bh-dir-content-delete';
  var copyButton = '.bh-dir-content-copy';
  var moveButton = '.bh-dir-content-move';
  var uploadButton = '.bh-dir-content-upload';
  var newFolderButton = '.bh-dir-content-newfolder';
  var homeButton = '.bh-dir-content-gohome';
  
  var checkAllCheckBox = '.bh-dir-content-checkboxgroup';
  var checkBoxes = '.bh-dir-content-checkbox';
  var selectDirIcon = '.bh-dir-content-openselected';
  var menuRename = '.bh-dir-content-edit';
  var renameForm = '.bh-dir-content-rename-form';
  var renameField = '.bh-dir-content-name';
  var renameColumn = '.bh-dir-content-rename-td';
  var uploadHiddenField = '.bh-dir-content-upload-hidden';
  var resourceSpecificAcl = '.bh-resource-specific-acl';
  
  module("content view",{
    setup: function(){
      // Call init function
      nl.sara.beehub.view.content.init();
    }, 
    teardown: function() {
      // clean up after each test
      backToOriginalEnvironment();
    }
  });

  var rememberGoToPage = nl.sara.beehub.controller.goToPage;
  var rememberRenameResource = nl.sara.beehub.controller.renameResource;
  var rememberInitAction = nl.sara.beehub.controller.initAction;
  var rememberShowError = nl.sara.beehub.controller.showError;
  var rememberCreateNewFolder = nl.sara.beehub.controller.createNewFolder;

  var backToOriginalEnvironment = function(){
    nl.sara.beehub.controller.goToPage = rememberGoToPage;
    nl.sara.beehub.controller.renameResource = rememberRenameResource;
    nl.sara.beehub.controller.initAction = rememberInitAction;
    nl.sara.beehub.controller.showError = rememberShowError;
    nl.sara.beehub.controller.createNewFolder = rememberCreateNewFolder;
  }
  
  /**
   * Check if selecting checkboxes is working
   * 
   * @param {Integer} count Total amount of resources in current directory
   * 
   */
  var checkCheckboxes = function( count ) {    
    // Select all checkboxes
    $(checkAllCheckBox).click();
    deepEqual($(checkBoxes+':checked').length, count , "All checkboxes should be selected");
    deepEqual($(copyButton).attr("disabled"), undefined, "Copy button should be enabled");
    deepEqual($(moveButton).attr("disabled"), undefined, "Move button should be enabled");
    deepEqual($(deleteButton).attr("disabled"), undefined, "Delete button should be enabled");
    
    // Unselect all checkboxes
    $(checkAllCheckBox).click();
    deepEqual($(checkBoxes+':checked').length, 0, "Zero checkboxes should be selected");
    deepEqual($(copyButton).attr("disabled"), "disabled", "Copy button should be disabled");
    deepEqual($(moveButton).attr("disabled"), "disabled", "Move button should be disabled");
    deepEqual($(deleteButton).attr("disabled"), "disabled", "Delete button should be disabled");
    
    // Select 1 checkbox
    $(checkBoxes)[0].click();
    deepEqual($(checkBoxes+':checked').length, 1, "One checkbox should be selected");
    deepEqual($(copyButton).attr("disabled"), undefined, "Copy button should be enabled");
    deepEqual($(moveButton).attr("disabled"), undefined, "Move button should be enabled");
    deepEqual($(deleteButton).attr("disabled"), undefined, "Delete button should be enabled");
    // Deselect this checkbox again
    $(checkBoxes)[0].click();
    deepEqual($(checkBoxes+':checked').length, 0, "Zere checkboxes should be selected");
    deepEqual($(copyButton).attr("disabled"), "disabled", "Copy button should be disabled");
    deepEqual($(moveButton).attr("disabled"), "disabled", "Move button should be disabled");
    deepEqual($(deleteButton).attr("disabled"), "disabled", "Delete button should be disabled");
  };
  
  /**
   * Check if click on resource will open the resource
   * 
   * @param {String} check Resource to check
   */
  var checkOpenSelected = function(check) {      
    // Rewrite controller goToPage
    nl.sara.beehub.controller.goToPage = function(location){
      deepEqual(location, check, "Location should be "+ check );
    };
    
    var row = $("tr[id='"+check+"']");
    // Call click handlers
    row.find(selectDirIcon).click();
  };
  
  /**
   * Check if renaming a resource is working
   * 
   * @param {String} check        Resource to rename
   * @param {String} displayname  Displaynam of resource to rename
   * 
   */
  var checkEditMenu = function(check , displayname){      
    nl.sara.beehub.controller.renameResource = function(resource, value, overwrite){
      deepEqual(resource.path, check , "Location should be "+check );
      deepEqual(value, "newFolderName", "Value should be newFolderName" );
    };

    var row = $("tr[id='"+check+"']");

    // Call events
    // Check click event 
    row.find(menuRename).click();

    deepEqual(row.find(renameField).is(':hidden'), true, 'Name field should be hidden');
    deepEqual(row.find(renameColumn).is(':hidden'), false, 'Input field should be shown');
    deepEqual(row.find(renameColumn).find(':input').val(), displayname,'Input field value should be testfolder');
    deepEqual(row.find(renameColumn).find(':input').is(':focus'), true, 'Mouse should be focused in input field');
  
    row.find(renameColumn).find(':input').val("newFolderName");
    
    // Check change event
    row.find(renameForm).change();
  
    // Check keypress event
    var e = jQuery.Event("keypress");
    e.which = 13; // # Some key code value
    row.find(renameForm).trigger(e);

    // Check blur event
    row.find(renameForm).blur();
    deepEqual(row.find(renameField).is(':hidden'), false, 'Name field should be shown');
    deepEqual(row.find(renameColumn).is(':hidden'), true, 'Input field should be hidden');
  };
  
  var checkSetRowHandlers = function(count, check , displayname){
    checkCheckboxes( count );
    checkOpenSelected( check );
    checkEditMenu( check, displayname );
  };
  
  /**
   * Test home and up buttons click handlers
   */
  test( 'nl.sara.beehub.view.content.init: Home button click handler', function() {
    expect( 1 );   

    // Rewrite controller goToPage
    nl.sara.beehub.controller.goToPage = function(location){
      deepEqual(location, home, "Home location should be "+home );
    };
    
    // Call click handlers
    // Buttons
    $(homeButton).click();
  });
  
  /**
   * Test up button click handlers
   */
  test( 'nl.sara.beehub.view.content.init: Up button click handler', function() {
    expect( 1 );   
    nl.sara.beehub.controller.goToPage = function(location){
      deepEqual(location, up, "Up location should be "+up );
    };
 
    // Call click handler
    $(upButton).click();
  });
  
  /**
   * Test delete, copy and move buttons click handlers
   */
  test( 'nl.sara.beehub.view.content.init: Delete, copy and move buttons click handlers', function() {
    expect( 18 );
          
    // Select resources
    $("tr[id='"+directory1+"']").find(checkBoxes).prop('checked',true);
    $("tr[id='"+file1+"']").find(checkBoxes).prop('checked',true);
    
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
  });
  
  /**
   * Test upload button click handler
   */
  test( 'nl.sara.beehub.view.content.init: Upload click and change handlers', function() {
    expect( 2 );      
    nl.sara.beehub.controller.initAction = function(resources, action){
      // TODO klopt resources
      ok(true, "Upload click handler is set");
    };
    
    nl.sara.beehub.controller.showError = function(){
      ok(true, "IE should show an error when uploading files is clicked")
    }; 
    
    // First test change before changing the field
    $(uploadHiddenField).change();

    $(uploadHiddenField).replaceWith('<input class="bh-dir-content-upload-hidden" hidden="hidden">');
    
    $(uploadHiddenField).unbind('click').click(function(){
      ok(true, "Upload hidden file field is clicked");
    });
    $(uploadButton).click();
  });
  
  /**
   * Test new folder click handler
   */
  test( 'nl.sara.beehub.view.content.init: New folder button click handler', function() {
    expect( 1 );
   
    nl.sara.beehub.controller.createNewFolder = function(){
      ok(true, "Click handler new folder button is set");
    };
    
    $(newFolderButton).click();
  });
  
  /**
   * Test select checkboxes
   */
  test( 'nl.sara.beehub.view.content.init: Select checboxes handlers', function() {
    expect( 16 );
    checkCheckboxes( contentCount );
  });
  
  /**
   * Test open selected folder icon
   */
  test( 'nl.sara.beehub.view.content.init: Open selected folder icon', function() {
    expect( 1 );
    checkOpenSelected(directory1);
  });
  
  /**
   * Test edit icon
   */
  test( 'nl.sara.beehub.view.content.init: Edit menu', function() {
    expect( 20 );
    checkEditMenu(directory1, directory1Displayname);
    checkEditMenu(file1, file1Displayname);
  });
  
  /**
   * Test clear view
   */
  test( 'nl.sara.beehub.view.content.clearView', function() {
    expect( 5 );
  
    nl.sara.beehub.view.content.clearView();
    deepEqual($(checkAllCheckBox+":checked").length, 0, 'Checkbox group should be unchecked');
    deepEqual($(checkBoxes+':checked').length, 0, "Zero checkboxes should be selected");
    deepEqual($(copyButton).attr('disabled'), 'disabled', 'Copy button should be disabled');
    deepEqual($(moveButton).attr('disabled'), 'disabled', 'Move button should be disabled');
    deepEqual($(deleteButton).attr('disabled'), 'disabled', 'Delete button should be disabled');
  });
  
  /**
   * Test clear view
   */
  test( 'nl.sara.beehub.view.content.allFixedButtons', function() {
    expect( 12 );
    
    nl.sara.beehub.view.content.allFixedButtons('hide');
    deepEqual($(homeButton).is(':hidden'), true, 'Home button should be hidden');
    deepEqual($(upButton).is(':hidden'), true, 'Up button should be hidden');
    deepEqual($(uploadButton).is(':hidden'), true, 'Upload button should be hidden');
    deepEqual($(copyButton).is(':hidden'), true, 'Copy button should be hidden');
    deepEqual($(moveButton).is(':hidden'), true, 'Move button should be hidden');
    deepEqual($(deleteButton).is(':hidden'), true, 'Delete button should be hidden');
  
    nl.sara.beehub.view.content.allFixedButtons('show');
    deepEqual($(homeButton).is(':hidden'), false, 'Home button should be shown');
    deepEqual($(upButton).is(':hidden'), false, 'Up button should be shown');
    deepEqual($(uploadButton).is(':hidden'), false, 'Upload button should be shown');
    deepEqual($(copyButton).is(':hidden'), false, 'Copy button should be shown');
    deepEqual($(moveButton).is(':hidden'), false, 'Move button should be shown');
    deepEqual($(deleteButton).is(':hidden'), false, 'Delete button should be shown');
   
  });
  
  /**
   * Test trigger rename click
   */
  test('nl.sara.beehub.view.content.triggerRenameClick', function(){
    expect ( 1 );
    
    $(menuRename).unbind('click').click(function(){
      ok(true, "Rename handler click should be triggered.");
    })
    
    var resource = new nl.sara.beehub.ClientResource(directory1);
    nl.sara.beehub.view.content.triggerRenameClick(resource);
  });
  
  /**
   * Test getUnknownResourceValues
   */
  test('nl.sara.beehub.view.content.getUnknownResourceValues', function(){
    expect( 5 );
    
    var resource = new nl.sara.beehub.ClientResource(file1);
    resource = nl.sara.beehub.view.content.getUnknownResourceValues(resource);

    deepEqual(resource.displayname, file1Displayname, "Displayname should be "+file1Displayname);
    deepEqual(resource.type, file1Type, "Type should be "+file1Type);
    deepEqual(resource.owner, file1Owner, "Owner should be "+file1Owner);
    deepEqual(resource.contentlength, file1Size, "Contentlength should be "+file1Size);
    deepEqual(resource.lastmodified, file1LastModified, "Lastmodified should be "+file1LastModified);
  });
  
  /**
   * Test add resource
   */
  test('nl.sara.beehub.view.content.addResource', function(){
    expect( 39 );
    
    var resource = new nl.sara.beehub.ClientResource( location.pathname+"newfolder");
    resource.displayname = "newfolder";
    resource.type = "collection";
    resource.contentlength = "undefined";
    resource.lastmodified = "Thu Nov 21 2013 14:27:03 GMT+0100 (CET)";
    resource.owner = 'Test User'

    var testresource1 = new nl.sara.beehub.ClientResource(location.pathname+"newfolder");
    testresource1 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource1);
    
    deepEqual(testresource1.displayname, undefined, "Displayname should be undefined");
    deepEqual(testresource1.type, undefined, "Type should be undefined");
    deepEqual(testresource1.contentlength, undefined, "Contentlength should be undefined");
    deepEqual(testresource1.lastmodified, undefined, "Lastmodified should be undefined");
    deepEqual(testresource1.owner, undefined, "Owner should be undefined");
    
    nl.sara.beehub.view.content.addResource(resource);
    
    var testresource2 = new nl.sara.beehub.ClientResource(location.pathname+"newfolder");
    testresource2 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource2);

    deepEqual(testresource2.path, resource.path, "Path should be "+resource.path);
    deepEqual(testresource2.displayname, resource.displayname, "Displayname should be "+resource.displayname);
    deepEqual(testresource2.type, resource.type, "Type should be "+resource.type);
    deepEqual(testresource2.contentlength, resource.contentlength, "Contentlength should be "+resource.contentlength);
    deepEqual(testresource2.lastmodified, resource.lastmodified, "Lastmodified should be "+resource.lastmodified);
    deepEqual(testresource2.owner, resource.owner, "Owner should be "+resource.owner);

    deepEqual($("tr[id='"+directory1+"']").find('td').length, $("tr[id='"+testresource2.path+"']").find('td').length, "Count of total columns is ok");

    checkSetRowHandlers( 5, resource.path , resource.displayname );
  })
  
   /**
   * Test delete resource
   */
  test('nl.sara.beehub.view.content.deleteResource', function(){
    expect( 10 );
    
    var resource = new nl.sara.beehub.ClientResource(file1);
    
    var testresource1 = new nl.sara.beehub.ClientResource(file1);
    testresource1 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource1);
    
    deepEqual(testresource1.displayname, file1Displayname, "Displayname should be "+file1Displayname);
    deepEqual(testresource1.type, file1Type, "Type should be "+file1Type);
    deepEqual(testresource1.contentlength, file1Size, "Contentlength should be "+file1Size);
    deepEqual(testresource1.lastmodified, file1LastModified, "Lastmodified should be "+file1LastModified);
    deepEqual(testresource1.owner, file1Owner, "Owner should be "+file1Owner);
    
    nl.sara.beehub.view.content.deleteResource(resource);
    
    var testresource2 = new nl.sara.beehub.ClientResource(file1);
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
    
    var testresource1 = new nl.sara.beehub.ClientResource(file1);
    testresource1 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource1);
    
    deepEqual(testresource1.displayname, file1Displayname, "Displayname should be "+file1Displayname);
    deepEqual(testresource1.type, file1Type, "Type should be "+file1Type);
    deepEqual(testresource1.contentlength, file1Size, "Contentlength should be "+file1Size);
    deepEqual(testresource1.lastmodified, file1LastModified, "Lastmodified should be "+file1LastModified);
    deepEqual(testresource1.owner, file1Owner, "Owner should be "+file1Owner);
    
    var resourcenew = new nl.sara.beehub.ClientResource(location.pathname+"testfile2");
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
   
    var testresource2 = new nl.sara.beehub.ClientResource(file1);
    testresource2 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource2);
    
    deepEqual(testresource2.displayname, undefined, "Displayname should be undefined");
    deepEqual(testresource2.type, undefined, "Type should be undefined");
    deepEqual(testresource2.contentlength, undefined, "Contentlength should be undefined");
    deepEqual(testresource2.lastmodified, undefined, "Lastmodified should be undefined");
    deepEqual(testresource2.owner, undefined, "Owner should be undefined");
    
    var testresource3 = new nl.sara.beehub.ClientResource(location.pathname+"testfile2");
    testresource3 = nl.sara.beehub.view.content.getUnknownResourceValues(testresource3);
    
    deepEqual(testresource3.displayname, "testfile2", "Displayname should be testfile2");
    deepEqual(testresource3.type, "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "Type should be application/vnd.openxmlformats-officedocument.wordprocessingml.document");
    deepEqual(testresource3.contentlength, "40000", "Contentlength should be 40000");
    deepEqual(testresource3.lastmodified, "Thu Nov 22 2013 14:27:03 GMT+0100 (CET)", "Lastmodified should be Thu Nov 22 2013 14:27:03 GMT+0100 (CET)");
    deepEqual(testresource3.owner, "/system/users/testuser2", "Owner should be /system/users/testuser2");
    
    checkSetRowHandlers( 4, resourcenew.path , resourcenew.displayname );
  })

})();
// End of file