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
  module("controller")
  
  var currentDirectory = "/foo/client_tests";
  
  var rememberClearAllViews =               nl.sara.beehub.view.clearAllViews;
  var rememberMaskView =                    nl.sara.beehub.view.maskView;
  var rememberInputDisable =                nl.sara.beehub.view.inputDisable;
  var rememberShowError =                   nl.sara.beehub.view.showError;
  var rememberClient =                      nl.sara.webdav.Client;
  var rememberProperty =                    nl.sara.webdav.Property;
  var rememberCreateTreeNode =              nl.sara.beehub.view.tree.createTreeNode;
  var rememberAddResource =                 nl.sara.beehub.view.addResource;
  var rememberTriggerRenameClick =          nl.sara.beehub.view.content.triggerRenameClick;
  var rememberShowOverwriteDialog =         nl.sara.beehub.view.dialog.showOverwriteDialog
  var rememberGetUnknownResourceValues =    nl.sara.beehub.view.content.getUnknownResourceValues
  var rememberUpdateResource =              nl.sara.beehub.view.updateResource;
  var rememberDeleteResource =              nl.sara.beehub.view.deleteResource;
  var rememberCloseDialog =                 nl.sara.beehub.view.dialog.closeDialog;
  var rememberCancelButton =                nl.sara.beehub.view.tree.cancelButton;
  var rememberMaskView =                    nl.sara.beehub.view.maskView;
  var rememberNoMask =                      nl.sara.beehub.view.tree.noMask;
  var rememberSlideTrigger =                nl.sara.beehub.view.tree.slideTrigger;   
  var rememberShowResourceDialog =          nl.sara.beehub.view.dialog.showResourcesDialog;
  var rememberSetAlreadyExist =             nl.sara.beehub.view.dialog.setAlreadyExist;
  var rememberResource =                    nl.sara.beehub.view.addResource;
  var rememberUpdateResource =              nl.sara.beehub.view.dialog.updateResourceInfo;
  var rememberUsersPath =                   nl.sara.beehub.users_path;
  var rememberGroupsPath =                  nl.sara.beehub.groups_path;

  var backToOriginalEnvironment = function(){
    nl.sara.beehub.view.clearAllViews =                     rememberClearAllViews;
    nl.sara.beehub.view.maskView =                          rememberMaskView;
    nl.sara.beehub.view.inputDisable =                      rememberInputDisable;
    nl.sara.beehub.view.showError =                         rememberShowError;
    nl.sara.webdav.Client =                                 rememberClient;
    nl.sara.webdav.Property =                               rememberProperty;
    nl.sara.beehub.view.tree.createTreeNode =               rememberCreateTreeNode;
    nl.sara.beehub.view.addResource =                       rememberAddResource;
    nl.sara.beehub.view.content.triggerRenameClick =        rememberTriggerRenameClick;
    nl.sara.beehub.view.dialog.showOverwriteDialog =        rememberShowOverwriteDialog;
    nl.sara.beehub.view.content.getUnknownResourceValues =  rememberGetUnknownResourceValues;
    nl.sara.beehub.view.updateResource =                    rememberUpdateResource;
    nl.sara.beehub.view.deleteResource =                    rememberDeleteResource;
    nl.sara.beehub.view.dialog.closeDialog =                rememberCloseDialog;
    nl.sara.beehub.view.tree.cancelButton =                 rememberCancelButton;
    nl.sara.beehub.view.maskView =                          rememberMaskView;
    nl.sara.beehub.view.tree.noMask =                       rememberNoMask;
    nl.sara.beehub.view.tree.slideTrigger =                 rememberSlideTrigger; 
    nl.sara.beehub.view.dialog.showResourcesDialog =        rememberShowResourceDialog;
    nl.sara.beehub.view.dialog.setAlreadyExist =            rememberSetAlreadyExist;
    nl.sara.beehub.view.addResource =                       rememberResource;
    nl.sara.beehub.view.dialog.updateResourceInfo =         rememberUpdateResource;
    nl.sara.beehub.users_path =                             rememberUsersPath;
    nl.sara.beehub.groups_path =                            rememberGroupsPath;
  }
  
  var getDataObject = function(path, testType){
    var propertyObject = function(namespace, property){
      this.property = property;
    };
    
    propertyObject.prototype.getParsedValue = function(){
      if (this.property === "resourcetype") {
        return testType;
      } else {
        return this.property;
      };
    }
    
    var responseObject = function(){
    };
    
    responseObject.prototype.getProperty = function(namespace, property){
      return new propertyObject(namespace, property);
    };
    
    var dataObject = function(){
    };
    dataObject.prototype.getResponseNames = function(){
      return [path];
    };
    
    dataObject.prototype.getResponse = function(){
      return new responseObject;
    };
    
    return new dataObject;
  };
  
  /**
   * Test htmlEscape
   */
  test( 'nl.sara.beehub.controller.htmlEscape', function() {
    deepEqual( nl.sara.beehub.controller.htmlEscape("&escape&"), "&amp;escape&amp;", "Escape &" );
    deepEqual( nl.sara.beehub.controller.htmlEscape('"escape"'), "&quot;escape&quot;", 'Escape "' );
    deepEqual( nl.sara.beehub.controller.htmlEscape("'escape'"), "&#39;escape&#39;", "Escape '" );
    deepEqual( nl.sara.beehub.controller.htmlEscape("<escape<"), "&lt;escape&lt;", "Escape <" );
    deepEqual( nl.sara.beehub.controller.htmlEscape(">escape>"), "&gt;escape&gt;", "Escape >" );
  } );
  
  /**
   * Test clearAllViews
   */
  test("nl.sara.beehub.controller.clearAllViews", function(){
    expect(1);
  
    nl.sara.beehub.view.clearAllViews = function(){
      ok(true, "nl.sara.beehub.view.clearAllViews is called.");
    };
    
    nl.sara.beehub.controller.clearAllViews();
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test maskView
   */
  test("nl.sara.beehub.controller.maskView", function(){
    expect(6);
    
    nl.sara.beehub.view.maskView = function(type, show){
      deepEqual(type, typeTest, "nl.sara.beehub.view.maskView should be called with type value "+typeTest);
      deepEqual(show, showTest, "nl.sara.beehub.view.maskView should be called with show value "+showTest);
    };
    
    var typeTest = "mask";
    var showTest = true;
    nl.sara.beehub.controller.maskView(typeTest, showTest);
    typeTest = "transparant";
    nl.sara.beehub.controller.maskView(typeTest, showTest);
    typeTest = "loading";
    showTest = false
    nl.sara.beehub.controller.maskView(typeTest, showTest);   
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test inputDisable
   */
  test("nl.sara.beehub.controller.inputDisable", function(){
    expect(2);
    
    nl.sara.beehub.view.inputDisable = function(value){
      deepEqual(value, testValue, "nl.sara.beehub.view.inputDisable should be called with value "+testValue);
    };
    
    var testValue = true;
    nl.sara.beehub.controller.inputDisable(testValue);
    testValue = false;
    nl.sara.beehub.controller.inputDisable(testValue);
  
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test showError
   */
  test("nl.sara.beehub.controller.showError", function(){
    expect(1);
    
    nl.sara.beehub.view.dialog.showError = function(value){
      deepEqual(value, "test", "nl.sara.beehub.view.showError should be called with value test.");
    };
    
    nl.sara.beehub.controller.showError("test");
  
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test getDisplayName
   */
  test( 'nl.sara.beehub.controller.getDisplayName', function() {
    expect(3);
    
    nl.sara.beehub.users_path = "/home/users/"
    var username = nl.sara.beehub.users_path + "laura"
    nl.sara.beehub.principals.users["laura"] = "Laura Leistikow";
    
    nl.sara.beehub.groups_path = "/home/groups/"
    var groupname = nl.sara.beehub.groups_path + "group"
    nl.sara.beehub.principals.groups["group"] = "Test group";
    
    var name = undefined;
    
    deepEqual( nl.sara.beehub.controller.getDisplayName(username), "Laura Leistikow", "User laura" );
    deepEqual( nl.sara.beehub.controller.getDisplayName(groupname), "Test group", "Group group" );
    deepEqual( nl.sara.beehub.controller.getDisplayName(name), "", "Name undefined" );
    
    // Put back original functions
    backToOriginalEnvironment();
  } );
  
  /**
   * Test bytesToSize
   * 
   */
  test('nl.sara.beehub.controller.bytesToSize', function(){
    expect(6);
    
    deepEqual( nl.sara.beehub.controller.bytesToSize(0, 2), "0 B", "0 bytes, precision 2");
    deepEqual( nl.sara.beehub.controller.bytesToSize(500, 2), "500 B", "500 bytes, precision 2");
    deepEqual( nl.sara.beehub.controller.bytesToSize(1500, 2), "1.46 KB", "1500 bytes, precision 2");
    deepEqual( nl.sara.beehub.controller.bytesToSize(15000000, 2), "14.31 MB", "15000000 bytes, precision 2");
    deepEqual( nl.sara.beehub.controller.bytesToSize(15000000000, 1), "14.0 GB", "15000000000 bytes, precision 1");
    deepEqual( nl.sara.beehub.controller.bytesToSize(15000000000000, 0), "14 TB", "15000000000000 bytes, precision 0");
  });
  
  /**
   * Test getTreeNode
   */
  test("nl.sara.beehub.controller.getTreeNode", function(){
    expect(5);
    
    var callback = function(){
      ok(true, "Callback function is called.");
    };
    
    nl.sara.webdav.Client = function(){
      this.propfind = function(path, callback ,val ,properties){
        deepEqual(path, "/test", "Propfind should be called with path value "+path);
        deepEqual(val, 1, "Propfind should be called with val value 1");
        deepEqual(properties[0].tagname, 'resourcetype', "Prop tagname should be resourcetype");
        deepEqual(properties[0].namespace, 'DAV:', "Prop namespace should be DAV:");
        callback();
      };
    };
    
    nl.sara.webdav.Property = function(){
      // Do nothing
    };
    
    nl.sara.beehub.controller.getTreeNode("/test", callback);
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test createGetTreeNodeCallback
   */
  test("nl.sara.beehub.controller.createGetTreeNodeCallback", function(){
    expect(6);
    
    // Setup environment
    var callback = function(){
      ok(true, "Callback function is called.");
    }
    
    nl.sara.beehub.view.dialog.showError = function(error){
      deepEqual(error,'Could not load the subdirectories.', "Show error should be called with value testError.");
    };
    
    nl.sara.beehub.view.tree.createTreeNode = function(data, url, parent, expander, callback){
      deepEqual(data, "data", "createTreeNode should be called with data value data.");
      deepEqual(url, "/test", "createTreeNode should be called with url value /test.");
      deepEqual(parent, "parent", "createTreeNode should be called with parent value parent.");
      deepEqual(expander, "expander", "createTreeNode should be called with expander value expander.");
      callback();
    };
    
    var testFunction = nl.sara.beehub.controller.createGetTreeNodeCallback("/test","parent", "expander", callback);
    // Test status 207
    testFunction(207, "data");
    // Test status not equal 207
    testFunction(200, "data");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
     
  /**
   * Test createNewFolder
   * 
   * This test includes private functions: createNewFolderCallback,
   * extractPropsFromPropfindRequest, getResourcePropsFromServer
   * 
   */
  test("nl.sara.beehub.controller.createNewFolder", function(){
    expect(78);
    
    // Set up environment
    var tested405 = false;
    var pathName = currentDirectory+"/new_folder";
    var testTypeResult = "";
    var testError="";
    
    nl.sara.webdav.Client = function(){
      this.mkcol = function(path, callback) {
        deepEqual(path, pathName, "Mkkol is called with path "+currentDirectory+"/new_folder");
        callback(201,"/test");
        if (!tested405) {
          tested405 = true;
          pathName = currentDirectory+"/new_folder_1";
          callback(405,"/test");
        }
        testError = "You are not allowed to create a new folder.";
        callback(403,"/test");
        // Unknown return value
        testError = "Unknown error.";
        callback(1,"/test");
      };
      
      this.propfind = function(resourcepath, callback ,val , properties){
        deepEqual(resourcepath, "/test", "Resource path should be test");
        deepEqual(val, 1, "Val should be 1.");
        deepEqual(properties.length, 6, "Properties");
        deepEqual(properties[0].tagname, "resourcetype", "Tagname should be resourcetype");
        deepEqual(properties[0].namespace, "DAV:", "Namespace should be DAV: ");
        deepEqual(properties[1].tagname, "getcontenttype", "Tagname should be getcontenttype");
        deepEqual(properties[1].namespace, "DAV:", "Namespace should be DAV:");
        deepEqual(properties[2].tagname, "displayname", "Tagname should be displayname");
        deepEqual(properties[2].namespace, "DAV:", "Namespace should be DAV:");
        deepEqual(properties[3].tagname, "owner", "Tagname should be owner");
        deepEqual(properties[3].namespace, "DAV:", "Namespace should be DAV:");
        deepEqual(properties[4].tagname, "getlastmodified", "Tagname should be getlastmodified");
        deepEqual(properties[4].namespace, "DAV:", "Namespace should be DAV:");
        deepEqual(properties[5].tagname, "getcontentlength", "Tagname should be getcontentlength");
        deepEqual(properties[5].namespace, "DAV:", "Namespace should be DAV:");
        
        
        var testType = nl.sara.webdav.codec.ResourcetypeCodec.COLLECTION;
        var data = getDataObject("testPath", testType);

        testTypeResult = "collection";
        testError = "Unknown error.";
        callback(207,data);
        callback(1,data);
        testType = null;
        data = getDataObject("testPath", testType);
        testTypeResult = "getcontenttype";
        callback(207,data);
      };
    };
  
    nl.sara.beehub.view.addResource = function(resource){
      deepEqual(resource.path, "testPath", "Resource path should be testPath.");
      deepEqual(resource.type, testTypeResult, "Resource type should be "+testTypeResult);
      deepEqual(resource.displayname, "displayname", "Resource displayname should be displayname.");
      deepEqual(resource.lastmodified, "getlastmodified", "Resource lastmodified should be getlastmodified.");
      deepEqual(resource.owner, "owner", "Resource owner should be owner.");
    }
    
    nl.sara.beehub.view.content.triggerRenameClick = function(resource){
      deepEqual(resource.path, "testPath", "Resource path should be testPath.");
      deepEqual(resource.type, testTypeResult, "Resource type should be "+testTypeResult);
      deepEqual(resource.displayname, "displayname", "Resource displayname should be displayname.");
      deepEqual(resource.lastmodified, "getlastmodified", "Resource lastmodified should be getlastmodified.");
      deepEqual(resource.owner, "owner", "Resource owner should be owner.");
    }
    
    nl.sara.beehub.view.dialog.showError = function(error){
      deepEqual(error, testError, "Error should be Unknown error.");
    }
    
    nl.sara.webdav.Property = function(){
      // Do nothing
    };
    
    nl.sara.beehub.controller.createNewFolder();
  
    // Put back original functions
    backToOriginalEnvironment();
  });
  
//  nl.sara.beehub.controller.renameResource = function(resource, fileNameNew, overwriteMode){
//    var webdav = new nl.sara.webdav.Client();
//    webdav.move(resource.path, createRenameCallback(resource, fileNameNew, overwriteMode), path +fileNameNew,  overwriteMode);
//  };
  
  /**
   * Test renameResource
   * 
   * This function also tests createRenameCallback
   */
  test("nl.sara.beehub.controller.renameResource", function(){
    expect(28);
   
    var testOverwrite = 3; 
    var testCallback = false;
    var firstTest = true;
    
    // Setup environment
    nl.sara.webdav.Client = function(){
      this.move = function(path, callback, newPath, overWriteMode) {
        deepEqual(path, currentDirectory+"/original", "Move should be called called with path original");
        deepEqual(newPath, currentDirectory+"/new", "Move should be called with new name new.")
        deepEqual(overWriteMode, testOverwrite, "Overwrite mode should be ");
        // Test callback function with different status
        if (firstTest) {
          firstTest = false;
          callback(412);
          callback(201);
          callback(204);
        };
      };
    };
    
    nl.sara.webdav.Client.FAIL_ON_OVERWRITE = 3;
    nl.sara.webdav.Client.SILENT_OVERWRITE = 5;
    
    nl.sara.beehub.view.dialog.showOverwriteDialog = function(resource, fileNameNew, callback){
      deepEqual(resource.path, currentDirectory+"/original", "Overwrite dialog should be called with resource "+currentDirectory+"/original");
      deepEqual(fileNameNew, "new", "Overwrite dialog should be called with resource new");
      testOverwrite = 5;
      // Test overwrite
      if (!testCallback) {
        testCallback = true;
        callback();
      };
    };
    
    nl.sara.beehub.view.content.getUnknownResourceValues = function(resource){
      resource.displayname   = "displayname";
      resource.type          = "type";
      resource.contentlength = "contentlength";
      resource.lastmodified  = "lastmodified";
      resource.owner         = "owner";
      return resource
    }
    
    nl.sara.beehub.view.updateResource = function(resource, resourceNew){
      deepEqual(resource.path, currentDirectory+"/original", "Resource path should be "+ currentDirectory+"/original");
      deepEqual(resource.type, "type", "Resource type should be type");
      deepEqual(resource.displayname, "displayname", "Resource displayname should be displayname.");
      deepEqual(resource.lastmodified, "lastmodified", "Resource lastmodified should be getlastmodified.");
      deepEqual(resource.owner, "owner", "Resource owner should be owner.");
      
      deepEqual(resourceNew.path, currentDirectory+"/new", "Resource path should be "+ currentDirectory+"/new");
      deepEqual(resourceNew.type, "type", "Resource type should be type.");
      deepEqual(resourceNew.displayname, "new", "Resource displayname should be new.");
      deepEqual(resourceNew.lastmodified, "lastmodified", "Resource lastmodified should be getlastmodified.");
      deepEqual(resourceNew.owner, "owner", "Resource owner should be owner.");
    };
    
    nl.sara.beehub.view.deleteResource = function(resource) {
      ok(false, "not ready");
    };
    
    nl.sara.beehub.view.dialog.closeDialog = function(){
      ok(true, "Close dialog is called.");
    }
    
    var resource = new nl.sara.beehub.ClientResource(currentDirectory+"/original");
    
    nl.sara.beehub.controller.renameResource(resource,"new", nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test setCopyMoveView
   */
  test("nl.sara.beehub.controller.setCopyMoveView", function(){
    expect(9);
    
    var showValue = "show";
    var maskValue = true;
    var noMaskValue = true;
    var slideTrigger = {
        first: "left",
        second:"hide"
    }
    
    // Setup environment
    nl.sara.beehub.view.tree.cancelButton = function(value){
      deepEqual(value, showValue, "Cancel button should be called with value "+showValue);
    };
    
    nl.sara.beehub.view.maskView = function(value){
      deepEqual(value, maskValue, "Mask view should be called with value "+showValue);
    };
    
    nl.sara.beehub.view.tree.noMask= function(value){
      deepEqual(value, noMaskValue, "Nomask should be called with value "+noMaskValue);
    };
    
    nl.sara.beehub.view.tree.slideTrigger = function(value){
      if ((value === slideTrigger.first) || (value === slideTrigger.second)) {
        ok(true, "slideTrigger is called.");
      } else {
        ok(false, "Slidetrigger is called with wrong value.");
      }
    }
    
    nl.sara.beehub.controller.setCopyMoveView(true);
    
    var showValue = "hide";
    var maskValue = false;
    var noMaskValue = false;
    var slideTrigger = {
        first: "show",
        second:"show"
    };
    
    nl.sara.beehub.controller.setCopyMoveView(false);
    
    // Put back original functions
    backToOriginalEnvironment();
  })
  
  /**
   * Test upload 1
   * 
   * Test upload with file 
   * - that excists on the server
   * - view should be updated and next action should start
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(5);
    
    // Set up environment
    var items =  [
       // File excists
       { "name": "file1" },
       // Next file
       { "name": "file2" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){    

        switch(path)
        {
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(200);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      }
    };
    
    nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwrite, rename, cancel){
      deepEqual(resource.path,currentDirectory+"/file1", "Already exists should be called with "+currentDirectory+"/file1");
    };
        
    nl.sara.beehub.controller.initAction(items, "upload");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 2
   * 
   * Test upload with file with status 201 or 204
   * - that not excists on the server
   * - not forbidden or other errors
   * - view should be updated and next action should start
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(40);
    
    // Set up environment
    var items =  [
       // File dows not excists
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    var putEmptyFileStatus = 0;
    var putFileStatus = 0;
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){    

        switch(path)
        {
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(404);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      };
      this.put = function(destination, callback, string, type){
        deepEqual(destination, currentDirectory+"/file1", "Destination should be "+currentDirectory+"/file1");
        deepEqual(type, "type", "Type should be type.");
        deepEqual(string, "", "String should be empty.");
        callback(putEmptyFileStatus);
      };    
      this.propfind = function(resourcepath, callback ,value ,properties){
        var data = getDataObject("file1", null);
        callback(207, data);
      }
    };
    
    nl.sara.beehub.view.addResource = function(resource){
      deepEqual(resource.path,"file1", "Path should be file1.");
    };
    
    nl.sara.webdav.Property = function(){
      // Do nothing
    };
    
    nl.sara.webdav.Client.getAjax = function(what, destination, callback, headers){
      deepEqual(what,"PUT","What should be PUT");
      deepEqual(destination,currentDirectory+"/file1","Destination should be "+currentDirectory+"/file1");
      
      var ajax = {
        upload : {
          addEventListener : function(){
            // not tested
          }
        },
        send : function(path){
          callback(putFileStatus);
        } 
      };
      return ajax;
    }
    
    // Test status 201 and 204
    putEmptyFileStatus = 201;
    putFileStatus = 201;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    putEmptyFileStatus = 201;
    putFileStatus = 204;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    putEmptyFileStatus = 204;
    putFileStatus = 201;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    putEmptyFileStatus = 204;
    putFileStatus = 204;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 3
   * 
   * Test upload with forbidden
   * - that not excists on the server
   * - view should be updated and next action should start
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(9);
    
    // Set up environment
    var items =  [
       // Forbidden
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){    

        switch(path)
        {
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(404);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      };
      this.put = function(destination, callback, string, type){
        deepEqual(destination, currentDirectory+"/file1", "Destination should be "+currentDirectory+"/file1");
        deepEqual(type, "type", "Type should be type.");
        deepEqual(string, "", "String should be empty.");
        callback(403);
      };    
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1","Resource path should be "+currentDirectory+"/file1.");
      deepEqual(info, "Forbidden","Info should be forbidden.");
    }
    
    nl.sara.beehub.controller.initAction(items, "upload");

    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 4
   * 
   * Test upload with file with status 201 or 204
   * - that not excists on the server
   * - not forbidden
   * - other status after upload file (not 201 or 204)
   * - view should be updated and next action should start
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(24);
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    var putEmptyFileStatus = 0;
    var putFileStatus = 0;
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){    

        switch(path)
        {
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(404);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      };
      this.put = function(destination, callback, string, type){
        deepEqual(destination, currentDirectory+"/file1", "Destination should be "+currentDirectory+"/file1");
        deepEqual(type, "type", "Type should be type.");
        deepEqual(string, "", "String should be empty.");
        callback(putEmptyFileStatus);
      };    
      this.remove = function(destination){
        deepEqual(destination, currentDirectory+"/file1", "Resource path should be "+currentDirectory+"/file1");
      };
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1","Resource path should be "+currentDirectory+"/file1.");
      deepEqual(info, "test","Info should be test.");
    };

    nl.sara.webdav.Client.getAjax = function(what, destination, callback, headers){
      deepEqual(what,"PUT","What should be PUT");
      deepEqual(destination,currentDirectory+"/file1","Destination should be "+currentDirectory+"/file1");
      
      var ajax = {
        upload : {
          addEventListener : function(){
            // not tested
          }
        },
        send : function(path){
          callback(1, "test");
        } 
      };
      return ajax;
    }
    
    // Test status 201 and 204
    putEmptyFileStatus = 201;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    putEmptyFileStatus = 204;
    nl.sara.beehub.controller.initAction(items, "upload");

    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 5
   * 
   * Test upload with unknow status on upload empty file
   * - that not excists on the server
   * - view should be updated and next action should start
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(9);
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){    

        switch(path)
        {
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(404);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      };
      this.put = function(destination, callback, string, type){
        deepEqual(destination, currentDirectory+"/file1", "Destination should be "+currentDirectory+"/file1");
        deepEqual(type, "type", "Type should be type.");
        deepEqual(string, "", "String should be empty.");
        callback(1, "test");
      };    
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1","Resource path should be "+currentDirectory+"/file1.");
      deepEqual(info, "test","Info should be test.");
    }
    
    nl.sara.beehub.controller.initAction(items, "upload");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 6
   * 
   * Test upload with unknown status at head request
   * - view should be updated and next action should start
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(6);
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    }; 
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){    

        switch(path)
        {
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(1);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      }; 
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1","Resource path should be "+currentDirectory+"/file1.");
      deepEqual(info, "Unknown error.","Info should be unknown error.");
    };
    
    nl.sara.beehub.controller.initAction(items, "upload");

    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 7
   * 
   * Test upload with file 
   * - that excists on the server
   * - view should be updated and next action should start
   * - rename button is pressed, unknown status response at head request
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(11);
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1", "Path should be "+currentDirectory+"/file1");
      deepEqual(info,"Unknown error.", "Info should ben unknown error.");
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){   
        switch(path)
        {
        case currentDirectory+"/file1_2":
          ok(true, "Path is ok.");
          deepEqual(string, undefined, "Teststring should be undefined.");
          callback(1);
          break;
        case currentDirectory+"/file1_1":
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(200);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      }; 
    };
      
    nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwrite, rename, cancel){
      deepEqual(resource.path,currentDirectory+"/file1", "Already exists should be called with "+currentDirectory+"/file1");
      rename();
    };
        
    nl.sara.beehub.controller.initAction(items, "upload");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 8
   * 
   * Test upload with file 
   * - that excists on the server
   * - view should be updated and next action should start
   * - rename button is pressed, 404 status response at head request
   * - forbidden to upload file
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(14);
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1", "Path should be "+currentDirectory+"/file1");
      deepEqual(info,"Unknown error.", "Info should ben unknown error.");
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){   
        switch(path)
        {
        case currentDirectory+"/file1_2":
          ok(true, "Path is ok.");
          deepEqual(string, undefined, "Teststring should be undefined.");
          callback(404);
          break;
        case currentDirectory+"/file1_1":
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(200);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      }; 
      this.put = function(destination, callback, string, type){
        deepEqual(destination, currentDirectory+"/file1_2", "Destination should be "+currentDirectory+"/file1_2");
        deepEqual(type, "type", "Type should be type.");
        deepEqual(string, "", "String should be empty.");
        callback(403);
      };
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1","Resource path should be "+currentDirectory+"/file1.");
      deepEqual(info, "Forbidden","Info should be forbidden.");
    };
    
    nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwrite, rename, cancel){
      deepEqual(resource.path,currentDirectory+"/file1", "Already exists should be called with "+currentDirectory+"/file1");
      rename();
    };
        
    nl.sara.beehub.controller.initAction(items, "upload");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 9
   * 
   * Test upload with file 
   * - that excists on the server
   * - view should be updated and next action should start
   * - rename button is pressed, 404 status response at head request
   * - unknown status response on upload empty file
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(14);
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1", "Path should be "+currentDirectory+"/file1");
      deepEqual(info,"Unknown error.", "Info should ben unknown error.");
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){   
        switch(path)
        {
        case currentDirectory+"/file1_2":
          ok(true, "Path is ok.");
          deepEqual(string, undefined, "Teststring should be undefined.");
          callback(404);
          break;
        case currentDirectory+"/file1_1":
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(200);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      }; 
      this.put = function(destination, callback, string, type){
        deepEqual(destination, currentDirectory+"/file1_2", "Destination should be "+currentDirectory+"/file1_2");
        deepEqual(type, "type", "Type should be type.");
        deepEqual(string, "", "String should be empty.");
        callback(1, "test");
      };
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1","Resource path should be "+currentDirectory+"/file1.");
      deepEqual(info, "test","Info should be test.");
    };
    
    nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwrite, rename, cancel){
      deepEqual(resource.path,currentDirectory+"/file1", "Already exists should be called with "+currentDirectory+"/file1");
      rename();
    };
        
    nl.sara.beehub.controller.initAction(items, "upload");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 10
   * 
   * Test upload with file 
   * - that excists on the server
   * - view should be updated and next action should start
   * - rename button is pressed, 404 status response at head request
   * - no errors - rename is succeeded after one already excists
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(60);
    
    var putEmptyFileStatus;
    var putFileStatus;
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1", "Path should be "+currentDirectory+"/file1");
      deepEqual(info,"Unknown error.", "Info should ben unknown error.");
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){   
        switch(path)
        {
        case currentDirectory+"/file1_2":
          ok(true, "Path is ok.");
          deepEqual(string, undefined, "Teststring should be undefined.");
          callback(404);
          break;
        case currentDirectory+"/file1_1":
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(200);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      }; 
      this.put = function(destination, callback, string, type){
        deepEqual(destination, currentDirectory+"/file1_2", "Destination should be "+currentDirectory+"/file1_2");
        deepEqual(type, "type", "Type should be type.");
        deepEqual(string, "", "String should be empty.");
        callback(putEmptyFileStatus);
      };
      this.propfind = function(resourcepath, callback ,value ,properties){
        var data = getDataObject("file1", null);
        callback(207, data);
      }
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1","Resource path should be "+currentDirectory+"/file1.");
      deepEqual(info, "test","Info should be test.");
    };
    
    nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwrite, rename, cancel){
      deepEqual(resource.path,currentDirectory+"/file1", "Already exists should be called with "+currentDirectory+"/file1");
      rename();
    };    
    
    nl.sara.beehub.view.addResource = function(resource){
      deepEqual(resource.path,"file1", "Path should be file1.");
    };
    
    nl.sara.webdav.Property = function(){
      // Do nothing
    };
    
    nl.sara.webdav.Client.getAjax = function(what, destination, callback, headers){
      deepEqual(what,"PUT","What should be PUT");
      deepEqual(destination,currentDirectory+"/file1_2","Destination should be "+currentDirectory+"/file1_2");
      
      var ajax = {
        upload : {
          addEventListener : function(){
            // not tested
          }
        },
        send : function(path){
          callback(putFileStatus);
        } 
      };
      return ajax;
    }
    
    // Test status 201 and 204
    putEmptyFileStatus = 201;
    putFileStatus = 201;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    putEmptyFileStatus = 201;
    putFileStatus = 204;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    putEmptyFileStatus = 204;
    putFileStatus = 201;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    putEmptyFileStatus = 204;
    putFileStatus = 204;
    nl.sara.beehub.controller.initAction(items, "upload");
            
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 11
   * 
   * Test upload with file 
   * - that excists on the server
   * - view should be updated and next action should start
   * - rename button is pressed, 404 status response at head request
   * - unknown status response on upload file
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(34);
    
    var putEmptyFileStatus;
    var putFileStatus;
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1", "Path should be "+currentDirectory+"/file1");
      deepEqual(info,"Unknown error.", "Info should ben unknown error.");
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){   
        switch(path)
        {
        case currentDirectory+"/file1_2":
          ok(true, "Path is ok.");
          deepEqual(string, undefined, "Teststring should be undefined.");
          callback(404);
          break;
        case currentDirectory+"/file1_1":
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(200);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      }; 
      this.put = function(destination, callback, string, type){
        deepEqual(destination, currentDirectory+"/file1_2", "Destination should be "+currentDirectory+"/file1_2");
        deepEqual(type, "type", "Type should be type.");
        deepEqual(string, "", "String should be empty.");
        callback(putEmptyFileStatus);
      };
      this.propfind = function(resourcepath, callback ,value ,properties){
        var data = getDataObject("file1", null);
        callback(207, data);
      };
      this.remove = function(destination){
        deepEqual(destination, currentDirectory+"/file1_2", "Resource path should be "+currentDirectory+"/file1_2");
      };
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1","Resource path should be "+currentDirectory+"/file1.");
      deepEqual(info, "test","Info should be test.");
    };
    
    nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwrite, rename, cancel){
      deepEqual(resource.path,currentDirectory+"/file1", "Already exists should be called with "+currentDirectory+"/file1");
      rename();
    };    
    
    nl.sara.beehub.view.addResource = function(resource){
      deepEqual(resource.path,"file1", "Path should be file1.");
    };
    
    nl.sara.webdav.Property = function(){
      // Do nothing
    };
    
    nl.sara.webdav.Client.getAjax = function(what, destination, callback, headers){
      deepEqual(what,"PUT","What should be PUT");
      deepEqual(destination,currentDirectory+"/file1_2","Destination should be "+currentDirectory+"/file1_2");
      
      var ajax = {
        upload : {
          addEventListener : function(){
            // not tested
          }
        },
        send : function(path){
          callback(1, "test");
        } 
      };
      return ajax;
    }
    
    // Test status 201 and 204
    putEmptyFileStatus = 201;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    putEmptyFileStatus = 204;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 12
   * 
   * Test upload with file 
   * - that excists on the server
   * - view should be updated and next action should start
   * - cancel button is pressed
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(7);
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1", "Path should be "+currentDirectory+"/file1");
      deepEqual(info,"Canceled", "Info should be canceled.");
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){   
        switch(path)
        {
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(200);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      }; 
    };
      
    nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwrite, rename, cancel){
      deepEqual(resource.path,currentDirectory+"/file1", "Already exists should be called with "+currentDirectory+"/file1");
      cancel();
    };
        
    nl.sara.beehub.controller.initAction(items, "upload");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 13
   * 
   * Test upload with file 
   * - that excists on the server
   * - view should be updated and next action should start
   * - overwrite button is pressed
   * - forbidden status response on upload file
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(10);
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1", "Path should be "+currentDirectory+"/file1");
      deepEqual(info,"Forbidden", "Info should be forbidden.");
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){   
        switch(path)
        {
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(200);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      }; 
      this.put = function(destination, callback, string, type){
        deepEqual(destination, currentDirectory+"/file1", "Destination should be "+currentDirectory+"/file1");
        deepEqual(type, "type", "Type should be type.");
        deepEqual(string, "", "String should be empty.");
        callback(403);
      };
    };
      
    nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwrite, rename, cancel){
      deepEqual(resource.path,currentDirectory+"/file1", "Already exists should be called with "+currentDirectory+"/file1");
      overwrite();
    };
        
    nl.sara.beehub.controller.initAction(items, "upload");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 13
   * 
   * Test upload with file 
   * - that excists on the server
   * - view should be updated and next action should start
   * - overwrite button is pressed
   * - unknown status response on upload file
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(10);
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1", "Path should be "+currentDirectory+"/file1");
      deepEqual(info,"test", "Info should be test.");
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){   
        switch(path)
        {
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(200);
          break;
        case currentDirectory+"/file2":
          // File does not excists
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      }; 
      this.put = function(destination, callback, string, type){
        deepEqual(destination, currentDirectory+"/file1", "Destination should be "+currentDirectory+"/file1");
        deepEqual(type, "type", "Type should be type.");
        deepEqual(string, "", "String should be empty.");
        callback(1, "test");
      };
    };
      
    nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwrite, rename, cancel){
      deepEqual(resource.path,currentDirectory+"/file1", "Already exists should be called with "+currentDirectory+"/file1");
      overwrite();
    };
        
    nl.sara.beehub.controller.initAction(items, "upload");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
  
  /**
   * Test upload 14
   * 
   * Test upload with file 
   * - that excists on the server
   * - view should be updated and next action should start
   * - overwrite button is pressed
   * - succesfull upload
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(44);
    
    var putEmptyFileStatus = 0;
    var putFileStatus = 0;
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){   
        switch(path)
        {
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(200);
          break;
        case currentDirectory+"/file2":
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      }; 
      this.put = function(destination, callback, string, type){
        deepEqual(destination, currentDirectory+"/file1", "Destination should be "+currentDirectory+"/file1");
        deepEqual(type, "type", "Type should be type.");
        deepEqual(string, "", "String should be empty.");
        callback(putEmptyFileStatus);
      };
      this.propfind = function(resourcepath, callback ,value ,properties){
        var data = getDataObject("file1", null);
        callback(207, data);
      };
    };
      
    nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwrite, rename, cancel){
      deepEqual(resource.path,currentDirectory+"/file1", "Already exists should be called with "+currentDirectory+"/file1");
      overwrite();
    };
    
    nl.sara.beehub.view.addResource = function(resource){
      deepEqual(resource.path,"file1", "Path should be file1.");
    };
    
    nl.sara.webdav.Property = function(){
      // Do nothing
    };
    
    nl.sara.webdav.Client.getAjax = function(what, destination, callback, headers){
      deepEqual(what,"PUT","What should be PUT");
      deepEqual(destination,currentDirectory+"/file1","Destination should be "+currentDirectory+"/file1");
      
      var ajax = {
        upload : {
          addEventListener : function(){
            // not tested
          }
        },
        send : function(path){
          callback(putFileStatus);
        } 
      };
      return ajax;
    }
    
    // Test status 201 and 204
    putEmptyFileStatus = 201;
    putFileStatus = 201;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    putEmptyFileStatus = 201;
    putFileStatus = 204;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    putEmptyFileStatus = 204;
    putFileStatus = 201;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    putEmptyFileStatus = 204;
    putFileStatus = 204;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
 
  /**
   * Test upload 14
   * 
   * Test upload with file 
   * - that excists on the server
   * - view should be updated and next action should start
   * - overwrite button is pressed
   * - unknown status on ajax upload
   */
  test("nl.sara.beehub.controller.initAction, upload", function() {
    expect(26);
    
    var putEmptyFileStatus = 0;
    var putFileStatus = 0;
    
    // Set up environment
    var items =  [
       // Test file
       { "name": "file1", "type":"type"},
       // Next file
       { "name": "file2", "type":"type" }
    ];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1", "Path should be "+currentDirectory+"/file1");
      deepEqual(info,"test", "Info should be test.");
    };
    
    nl.sara.webdav.Client = function(){
      this.head = function(path, callback, string){   
        switch(path)
        {
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          deepEqual(string, "", "Teststring should be empty.");
          callback(200);
          break;
        case currentDirectory+"/file2":
          ok(true, "Next action is started.");
          deepEqual(string, "", "Teststring should be empty.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      }; 
      this.put = function(destination, callback, string, type){
        deepEqual(destination, currentDirectory+"/file1", "Destination should be "+currentDirectory+"/file1");
        deepEqual(type, "type", "Type should be type.");
        deepEqual(string, "", "String should be empty.");
        callback(putEmptyFileStatus);
      };
      this.propfind = function(resourcepath, callback ,value ,properties){
        var data = getDataObject("file1", null);
        callback(207, data);
      };
      this.remove = function(destination){
        deepEqual(destination, currentDirectory+"/file1", "Resource path should be "+currentDirectory+"/file1");
      };
    };
      
    nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwrite, rename, cancel){
      deepEqual(resource.path,currentDirectory+"/file1", "Already exists should be called with "+currentDirectory+"/file1");
      overwrite();
    };
    
    nl.sara.beehub.view.addResource = function(resource){
      deepEqual(resource.path,"file1", "Path should be file1.");
    };
    
    nl.sara.webdav.Property = function(){
      // Do nothing
    };
    
    nl.sara.webdav.Client.getAjax = function(what, destination, callback, headers){
      deepEqual(what,"PUT","What should be PUT");
      deepEqual(destination,currentDirectory+"/file1","Destination should be "+currentDirectory+"/file1");
      
      var ajax = {
        upload : {
          addEventListener : function(){
            // not tested
          }
        },
        send : function(path){
          callback(1, "test");
        } 
      };
      return ajax;
    }
      
    // Test status 201 and 204
    putEmptyFileStatus = 201;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    putEmptyFileStatus = 204;
    nl.sara.beehub.controller.initAction(items, "upload");
    
    // Put back original functions
    backToOriginalEnvironment();
  });
})();