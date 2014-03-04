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
  module( "controller", {
    teardown: function() {
      // clean up after each test
      backToOriginalEnvironment();
    }
  });
  
  var currentDirectory = "/foo/client_tests";
  var parentDirectory = "/foo";
  var treeNode = $( "#bh-dir-tree ul.dynatree-container" );
  
  var rememberPrincipals =                           deepCopy(nl.sara.beehub.principals);
  var rememberClearAllViews =                       nl.sara.beehub.view.clearAllViews;
  var rememberClearDialogView =                     nl.sara.beehub.view.dialog.clearView;
  var rememberMaskView =                            nl.sara.beehub.view.maskView;
  var rememberInputDisable =                        nl.sara.beehub.view.inputDisable;
  var rememberShowError =                           nl.sara.beehub.view.showError;
  var rememberDislogShowError =                     nl.sara.beehub.view.dialog.showError;
  var rememberClient =                              nl.sara.webdav.Client;
  var rememberProperty =                            nl.sara.webdav.Property;
  var rememberCreateTreeNode =                      nl.sara.beehub.view.tree.createTreeNode;
  var rememberAddResource =                         nl.sara.beehub.view.addResource;
  var rememberTriggerRenameClick =                  nl.sara.beehub.view.content.triggerRenameClick;
  var rememberShowOverwriteDialog =                 nl.sara.beehub.view.dialog.showOverwriteDialog
  var rememberGetUnknownResourceValues =            nl.sara.beehub.view.content.getUnknownResourceValues
  var rememberUpdateResource =                      nl.sara.beehub.view.updateResource;
  var rememberDeleteResource =                      nl.sara.beehub.view.deleteResource;
  var rememberCloseDialog =                         nl.sara.beehub.view.dialog.closeDialog;
  var rememberCancelButton =                        nl.sara.beehub.view.tree.cancelButton;
  var rememberMaskView =                            nl.sara.beehub.view.maskView;
  var rememberNoMask =                              nl.sara.beehub.view.tree.noMask;
  var rememberSlideTrigger =                        nl.sara.beehub.view.tree.slideTrigger;   
  var rememberShowResourceDialog =                  nl.sara.beehub.view.dialog.showResourcesDialog;
  var rememberSetAlreadyExist =                     nl.sara.beehub.view.dialog.setAlreadyExist;
  var rememberResource =                            nl.sara.beehub.view.addResource;
  var rememberUpdateResource =                      nl.sara.beehub.view.dialog.updateResourceInfo;
  var rememberSetCopyMoveView =                     nl.sara.beehub.controller.setCopyMoveView
  var rememberClearView =                           nl.sara.beehub.view.tree.clearView;
  var rememberSetModal =                            nl.sara.beehub.view.tree.setModal;
  var rememberUsersPath =                           nl.sara.beehub.users_path;
  var rememberGroupsPath =                          nl.sara.beehub.groups_path;
  var rememberFailOnOverwrite =                     nl.sara.webdav.Client.FAIL_ON_OVERWRITE;
  var rememberSilelentOverwrite =                   nl.sara.webdav.Client.SILENT_OVERWRITE;
  var rememberGetAcl =                              nl.sara.beehub.view.acl.getAcl;
  var rememberGetViewPath =                         nl.sara.beehub.view.acl.getViewPath;
  var rememberSetView =                             nl.sara.beehub.view.acl.setView;
  var rememberShowAcl =                             nl.sara.beehub.view.dialog.showAcl;
  var rememberSetAddAclRuleDialogClickHandler =     nl.sara.beehub.view.acl.setAddAclRuleDialogClickHandler;
  var rememberSaveAclOnServer =                     nl.sara.beehub.controller.saveAclOnServer;
  var rememberAddRow =                              nl.sara.beehub.view.acl.addRow;
  var rememberDeleteRowIndex =                      nl.sara.beehub.view.acl.deleteRowIndex;
  var rememberShowAddRuleDialog =                   nl.sara.beehub.view.dialog.showAddRuleDialog;
  var rememberHideMasks =                           nl.sara.beehub.view.hideMasks;
  var rememberCreateRow =                           nl.sara.beehub.view.acl.createRow;
  var rememberChangePermissions =                   nl.sara.beehub.view.acl.changePermissions;
  var rememberMoveDownAclRule =                     nl.sara.beehub.view.acl.moveDownAclRule;
  var rememberMoveUpAclRule =                       nl.sara.beehub.view.acl.moveUpAclRule;
 
  
  var backToOriginalEnvironment = function(){
    nl.sara.beehub.view.clearAllViews =                     rememberClearAllViews;
    nl.sara.beehub.view.maskView =                          rememberMaskView;
    nl.sara.beehub.view.inputDisable =                      rememberInputDisable;
    nl.sara.beehub.view.showError =                         rememberShowError;
    nl.sara.beehub.view.dialog.showError =                  rememberDislogShowError;                   
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
    nl.sara.beehub.controller.setCopyMoveView =             rememberSetCopyMoveView;
    nl.sara.beehub.view.tree.setModal =                     rememberSetModal;
    nl.sara.beehub.view.tree.clearView =                    rememberClearView;
    nl.sara.beehub.users_path =                             rememberUsersPath;
    nl.sara.beehub.groups_path =                            rememberGroupsPath;
    nl.sara.webdav.Client.FAIL_ON_OVERWRITE =               rememberFailOnOverwrite;
    nl.sara.webdav.Client.SILENT_OVERWRITE =                rememberSilelentOverwrite;
    nl.sara.beehub.view.acl.getAcl =                        rememberGetAcl;
    nl.sara.beehub.view.acl.getViewPath =                   rememberGetViewPath;
    nl.sara.beehub.view.acl.setView =                       rememberSetView;
    nl.sara.beehub.view.dialog.showAcl =                    rememberShowAcl;
    nl.sara.beehub.view.acl.setAddAclRuleDialogClickHandler=rememberSetAddAclRuleDialogClickHandler;  
    nl.sara.beehub.controller.saveAclOnServer =             rememberSaveAclOnServer;                     ;
    nl.sara.beehub.view.acl.addRow =                        rememberAddRow;
    nl.sara.beehub.view.acl.deleteRowIndex =                rememberDeleteRowIndex;
    nl.sara.beehub.view.dialog.clearView =                  rememberClearDialogView;   
    nl.sara.beehub.view.dialog.showAddRuleDialog =          rememberShowAddRuleDialog;    
    nl.sara.beehub.view.hideMasks =                         rememberHideMasks;   
    nl.sara.beehub.view.acl.createRow =                     rememberCreateRow;   
    nl.sara.beehub.view.acl.changePermissions =             rememberChangePermissions;     
    nl.sara.beehub.view.acl.moveDownAclRule =               rememberMoveDownAclRule;   
    nl.sara.beehub.view.acl.moveUpAclRule =                 rememberMoveUpAclRule;  
    nl.sara.beehub.principals  =                            deepCopy(rememberPrincipals);
  }
  
  /**
   * Deepcopy an object
   */
  function deepCopy(p,c) {
    var c = c||{};
    for (var i in p) {
      if (typeof p[i] === 'object') {
        c[i] = (p[i].constructor === Array)?[]:{};
        deepCopy(p[i],c[i]);
      } else c[i] = p[i];}
    return c;
  }
  
  var getDataObject = function(path, testType, status, acl){
    var propertyObject = function(namespace, property){
      this.property = property;
      this.status = status;
    };
    
    propertyObject.prototype.getParsedValue = function(){
      if (this.property === "acl"){
        return {
          getAces: function(){
            return acl;
          }
        }
      }
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
  
  var getCopyMoveTestResources = function(dir){
    var resource1 = new nl.sara.beehub.ClientResource(dir+"/file1");
    var resource2 = new nl.sara.beehub.ClientResource(dir+"/file2"); 
    resource1.displayname = "file1";
    resource2.displayname = "file2";
    var items =  [resource1, resource2]; 
    
    return items
  };
  
  var setupCopyMoveTestEnvironment = function(status, secondStatus, existAction, thirdStatus){
    var inModal = true;
    var inView = true;
    var first412 = true;
    var second412 = true
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      ok(true, "UpdateResourceInfo is called with resource "+resource.path);
      ok(true, "UpdateResourceInfo is called with info "+info);
    };
    
    nl.sara.webdav.Client = function(){
      nl.sara.webdav.Client.FAIL_ON_OVERWRITE = 3;
      nl.sara.webdav.Client.SILENT_OVERWRITE = 5;
      
      this.copy = function(path, callback, destination, overwrite){
        switch(path)
        {
        case currentDirectory+"/directory/file1":
        case currentDirectory+"/file1":
        case parentDirectory+"/file1":
          ok(true, "Path is ok.");
          if (overwrite === 5) {
            callback(201);
            return;
          }
          // prevent recursion
          if (status === 412) {
            if (first412){
              first412 = false;
              callback(status);
            } else if (second412) {
              second412 = false
              callback(secondStatus);
            } else {
              callback(thirdStatus);
            }
          } else {
            callback(status);
          }
          break;
        case currentDirectory+"/directory/file2":
        case currentDirectory+"/file2":
        case parentDirectory+"/file2":
          ok(true, "Next action is started.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      };
      this.move = function(path, callback, destination, overwrite){
        switch(path)
        {
        case currentDirectory+"/directory/file1":
        case currentDirectory+"/file1":
        case parentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          if (overwrite === 5) {
            callback(201);
            return;
          }
          // prevent recursion
          if (status === 412) {
            if (first412){
              first412 = false;
              callback(status);
            } else if (second412) {
              second412 = false
              callback(secondStatus);
            } else {
              callback(thirdStatus);
            }
          } else {
            callback(status);
          }
          break;
        case currentDirectory+"/directory/file2":
        case currentDirectory+"/file2":
        case parentDirectory+"/file2":
          ok(true, "Next action is started.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      };
      this.propfind = function(resourcepath, callback ,value ,properties){
        var data = getDataObject("file1", null);
        callback(207, data);
      };
    };
    
    nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwrite, rename, cancel){
      deepEqual(resource.path,currentDirectory+"/directory/file1", "Already exists should be called with "+currentDirectory+"/file1");
      if (existAction === "cancel"){
        cancel();
        return;
      };
      if (existAction === "overwrite"){
        overwrite();
        return;
      };
      if (existAction === "rename"){
        rename();
        return;
      };
    };
    
    // Test view changes and go on
    nl.sara.beehub.controller.setCopyMoveView = function(value){
      deepEqual(value, inView, "setCopyMoveView should be called.");
      inView = false;
    };
    
    nl.sara.beehub.view.tree.setModal = function(value){
      // First time true, then false
      deepEqual(value, inModal, "Set modal is called with value false.");
      inModal = false;
    };
    
    nl.sara.beehub.view.tree.clearView = function(){
      // First time true, then false
      ok(true, "Clearview is called.");
    };
    
    nl.sara.beehub.view.addResource = function(resource){
      deepEqual(resource.path,"file1", "Add resource should be called with file1");
    };
    
    nl.sara.beehub.view.dialog.showError = function(error){
      deepEqual(error, "Moving an item to itself is not possible. Use rename icon for renaming the resource(s)."
          , "Error should be Moving an item to itself is not possible. Use rename icon for renaming the resource(s).");
    } 
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
  });
  
  /**
   * Test delete
   * 
   * Test delete a resource
   * - view should be updated and next action should start
   * - test with serveral status responses
   */
  test("nl.sara.beehub.controller.initAction, delete", function() {
    expect(18);
    
    var removeCallback = 0;
    var error = "";
    
    // Set up environment
    var items =  [new nl.sara.beehub.ClientResource(currentDirectory+"/file1"), 
                  new nl.sara.beehub.ClientResource(currentDirectory+"/file2")];
    
    nl.sara.beehub.view.dialog.showResourcesDialog =  function(actionFunction){
      actionFunction();
    };
    
    nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
      deepEqual(resource.path, currentDirectory+"/file1", "Path should be "+currentDirectory+"/file1");
      deepEqual(info, error, "Info should be "+error);
    };
    
    nl.sara.webdav.Client = function(){
      this.remove = function(path, callback){
        switch(path)
        {
        case currentDirectory+"/file1":
          // File excists
          ok(true, "Path is ok.");
          callback(removeCallback);
          break;
        case currentDirectory+"/file2":
          ok(true, "Next action is started.");
          break;
        default:
          ok(false, "This should not happen.");
        }
      };
    };
    
    nl.sara.beehub.view.dialog.showError = function(error){
      deepEqual(error, "Moving an item to itself is not possible. Use rename icon for renaming the resource(s)."
          , "Error should be Moving an item to itself is not possible. Use rename icon for renaming the resource(s).");
    }
    
    nl.sara.beehub.view.deleteResource = function(resource){
      deepEqual(resource.path, currentDirectory+"/file1", "Path should be "+currentDirectory+"/file1");
    };
    
    removeCallback = 403;
    error = "Forbidden";
    nl.sara.beehub.controller.initAction(items, "delete");
    
    removeCallback = 1;
    error = "Unknown error";
    nl.sara.beehub.controller.initAction(items, "delete");
    
    removeCallback = 201;
    error = "Done";
    nl.sara.beehub.controller.initAction(items, "delete");
    
    removeCallback = 204;
    error = "Done";
    nl.sara.beehub.controller.initAction(items, "delete");
  });
  
  /**
   * Test copy current dir
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 201
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy", function() {
    expect(14);
     
    setupCopyMoveTestEnvironment(201);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/']").click();
  });
  
  /**
   * Test copy child dir
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 201
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 1", function() {
    expect(14);
     
    setupCopyMoveTestEnvironment(201);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to same directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 204
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 2", function() {
    expect(14);
    
    setupCopyMoveTestEnvironment(204);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 204
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 3", function() {
    expect(14);
    
    setupCopyMoveTestEnvironment(204);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to same directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 403 - forbidden
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 4", function() {
    expect(13);
    
    setupCopyMoveTestEnvironment(403);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 403 - forbidden
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 5", function() {
    expect(13);
    
    setupCopyMoveTestEnvironment(403);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to parent directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 501 - parent
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 6", function() {
    expect(13);
    
    setupCopyMoveTestEnvironment(501);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(parentDirectory), "copy");
    // activate directory
    treeNode.find("a[href$='"+parentDirectory+"/']").click();
  });

  /**
   * Test copy to parent directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 512 - parent
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 7", function() {
    expect(13);
    
    setupCopyMoveTestEnvironment(512);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(parentDirectory), "copy");
    // activate directory
    treeNode.find("a[href$='"+parentDirectory+"/']").click();
  });
  
  /**
   * Test copy to same dir
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response unknown status
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 8", function() {
    expect(13);
    
    setupCopyMoveTestEnvironment(1);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/']").click();
  });

  /**
   * Test copy to child dir
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response unknown status
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 9", function() {
    expect(13);
    
    setupCopyMoveTestEnvironment(1);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to same dir
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - resource exists
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 10", function() {
    expect(17);
    
    setupCopyMoveTestEnvironment(412, 201);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/']").click();
  });
  
  /**
   * Test copy to child dir
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - resource exists
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 11", function() {
    expect(12);
    
    setupCopyMoveTestEnvironment(412, 201);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then cancel
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 12", function() {
    expect(14);
    
    setupCopyMoveTestEnvironment(412, 1, "cancel");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 201
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 13", function() {
    expect(16);
    
    setupCopyMoveTestEnvironment(412, 201, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rewrite with status response 201
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 14", function() {
    expect(16);
    
    setupCopyMoveTestEnvironment(412, 201, "overwrite");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 204
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 15", function() {
    expect(16);
    
    setupCopyMoveTestEnvironment(412, 204, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then overwrite with status response 204
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 16", function() {
    expect(16);
    
    setupCopyMoveTestEnvironment(412, 204, "overwrite");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
   
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 403
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 17", function() {
    expect(15);
    
    setupCopyMoveTestEnvironment(412, 403, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then overwrite with status response 403
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 18", function() {
    expect(16);
    
    setupCopyMoveTestEnvironment(412, 403, "overwrite");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 501
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 19", function() {
    expect(15);
    
    setupCopyMoveTestEnvironment(412, 501, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then overwrite with status response 501
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 20", function() {
    expect(16);
    
    setupCopyMoveTestEnvironment(412, 501, "overwrite");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 512
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 21", function() {
    expect(15);
    
    setupCopyMoveTestEnvironment(412, 512, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then overwrite with status response 512
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 22", function() {
    expect(16);
    
    setupCopyMoveTestEnvironment(412, 512, "overwrite");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 412
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 23", function() {
    expect(18);
    
    setupCopyMoveTestEnvironment(412, 412, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 412
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 24", function() {
    expect(15);
    
    setupCopyMoveTestEnvironment(412, 1, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test copy to child directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then overwrite with status response 412
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, copy 25", function() {
    expect(16);
    
    setupCopyMoveTestEnvironment(412, 1, "overwrite");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "copy");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  
  /**
   * Test move to same dir
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status is not used
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 1", function() {
    expect(6);
    
    setupCopyMoveTestEnvironment(1);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/']").click();
  });
  
  /**
   * Test move child dir
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 201
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 2", function() {
    expect(9);
     
    setupCopyMoveTestEnvironment(201);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move child dir
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 204
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 3", function() {
    expect(9);
     
    setupCopyMoveTestEnvironment(204);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move child dir
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 403 - forbidden
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 4", function() {
    expect(9);
     
    setupCopyMoveTestEnvironment(403);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });

  /**
   * Test move to parent directory
   * 
   * Test copy a resource
   * - view should be updated and next action should start
   * - status response 501 - parent
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 5", function() {
    expect(9);
    
    setupCopyMoveTestEnvironment(501);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(parentDirectory), "move");
    // activate directory
    treeNode.find("a[href$='"+parentDirectory+"/']").click();
  });

  /**
   * Test move to parent directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 512 - parent
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 6", function() {
    expect(9);
    
    setupCopyMoveTestEnvironment(512);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(parentDirectory), "move");
    // activate directory
    treeNode.find("a[href$='"+parentDirectory+"/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 7", function() {
    expect(8);
    
    setupCopyMoveTestEnvironment(412, 201);

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then cancel
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 8", function() {
    expect(10);
    
    setupCopyMoveTestEnvironment(412, 1, "cancel");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 201
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 9", function() {
    expect(11);
    
    setupCopyMoveTestEnvironment(412, 201, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rewrite with status response 201
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 10", function() {
    expect(11);
    
    setupCopyMoveTestEnvironment(412, 201, "overwrite");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 204
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 11", function() {
    expect(11);
    
    setupCopyMoveTestEnvironment(412, 204, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then overwrite with status response 204
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 12", function() {
    expect(11);
    
    setupCopyMoveTestEnvironment(412, 204, "overwrite");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
   
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 403
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 13", function() {
    expect(11);
    
    setupCopyMoveTestEnvironment(412, 403, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then overwrite with status response 403
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 14", function() {
    expect(11);
    
    setupCopyMoveTestEnvironment(412, 403, "overwrite");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 501
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 15", function() {
    expect(11);
    
    setupCopyMoveTestEnvironment(412, 501, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then overwrite with status response 501
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 16", function() {
    expect(11);
    
    setupCopyMoveTestEnvironment(412, 501, "overwrite");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 512
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 17", function() {
    expect(11);
    
    setupCopyMoveTestEnvironment(412, 512, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then overwrite with status response 512
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 18", function() {
    expect(11);
    
    setupCopyMoveTestEnvironment(412, 512, "overwrite");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 412
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 19", function() {
    expect(12);
    
    setupCopyMoveTestEnvironment(412, 412, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then rename with status response 412
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 20", function() {
    expect(11);
    
    setupCopyMoveTestEnvironment(412, 1, "rename");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test move to child directory
   * 
   * Test move a resource
   * - view should be updated and next action should start
   * - status response 412 - file exists- then overwrite with status response 412
   * 
   * - Note: renaming is not shown in add resource because the getDataObject function
   *   gives always the same values back
   */
  test("nl.sara.beehub.controller.initAction, move 21", function() {
    expect(11);
    
    setupCopyMoveTestEnvironment(412, 1, "overwrite");

    nl.sara.beehub.controller.initAction(getCopyMoveTestResources(currentDirectory+"/directory"), "move");
    // activate directory
    treeNode.find("a[href$='"+currentDirectory+"/directory/']").click();
  });
  
  /**
   * Test save acl on server
   * 
   */
  test("nl.sara.beehub.controller.saveAclOnServer", function(){
    expect(11);
    
    var status = 0;
    var testError = "";
    var addAclRuleDialog = false;
    
    nl.sara.beehub.view.acl.getAcl = function(){
      var acl = {"test": "acl"};
      return acl;
    };
    
    nl.sara.beehub.view.acl.getViewPath = function(){
      return "testPath";
    }
    
    nl.sara.webdav.Client = function(){
      this.acl = function(path, callback, acl){
        deepEqual(path,"testPath", "Path should be testPath");
        deepEqual(acl.test, "acl", "Acl test should be acl.");
        callback(status);
      }
    }
    
    var aclOk = function(){
      ok(true, "acl ok is called.");
    };
    
    var aclError = function(){
      ok(true, "acl Error is called.");
    };
    
    nl.sara.beehub.view.dialog.showError = function(error){
      deepEqual(error, testError, "Test error should be "+testError);
    } 
    
    status = 200;
    nl.sara.beehub.controller.saveAclOnServer(aclOk, aclError);
    
    status = 403;
    testError = 'You are not allowed to change the acl.';
    nl.sara.beehub.controller.saveAclOnServer(aclOk, aclError);

    status = 1;
    testError = 'Something went wrong on the server. No changes were made.';
    nl.sara.beehub.controller.saveAclOnServer(aclOk, aclError);

  });

  /**
   * Test getAclFromServer
   */
  test('nl.sara.beehub.controller.getAclFromServer', function(){
    expect(63);
    
    var errorTest = "";
    var testIndex = 0;
    var addAclRuleDialogTest = false;
    var closeDialogTest=false;
    var allInherited = true;

    var aclTestOnlyInherited = [{
      principal         :     '/system/groups/foo',
      principalString   :     '/system/groups/foo',
      invertprincipal   :     false,
      inherited         :     true,
      isprotected       :     false,
      grantdeny         :     2,
      permString        :     "deny write, change acl",
      getPrivilegeNames :     function(){return ["write","write-acl"]}
     },{
      principal         :     nl.sara.webdav.Ace.AUTHENTICATED,
      principalString   :     'DAV: authenticated',
      invertprincipal   :     true,
      isprotected       :     false,
      inherited         :     true,
      grantdeny         :     1,
      permString        :     "allow read",
      getPrivilegeNames :     function(){return ["read"]}
     },{
      principal         :     nl.sara.webdav.Ace.ALL,
      principalString   :     "DAV: all",
      invertprincipal   :     false,
      isprotected       :     false,
      inherited         :     true,
      grantdeny         :     1,
      permString        :     "allow read",
      getPrivilegeNames :     function(){return ["read"]}
     },{
      principal         :     {tagname: "owner"},
      principalString   :     'DAV: owner',
      invertprincipal   :     false,
      inherited         :     true,
      isprotected       :     false,
      grantdeny         :     1,
      permString        :     "allow read",
      getPrivilegeNames :     function(){return ["read"]}
     },{
      principal         :     nl.sara.webdav.Ace.SELF,
      principalString   :     'DAV: self',
      invertprincipal   :     false,
      isprotected       :     false,
      inherited         :     true,
      grantdeny         :     1,
      permString        :     "allow read",
      getPrivilegeNames :     function(){return ["read"]}
     },{
      principal         :     nl.sara.webdav.Ace.UNAUTHENTICATED,
      principalString   :     'DAV: unauthenticated',
      invertprincipal   :     false,
      isprotected       :     false,
      inherited         :     true,
      grantdeny         :     1,
      permString        :     "allow unknown privilege (combination)",
      getPrivilegeNames :     function(){return ["write"]}
      }
    ];
    
    var aclTest = [{
      principal         :     '/system/groups/foo',
      principalString   :     '/system/groups/foo',
      invertprincipal   :     false,
      inherited         :     false,
      isprotected       :     false,
      grantdeny         :     2,
      permString        :     "deny write, change acl",
      getPrivilegeNames :     function(){return ["write","write-acl"]}
     },{
      principal         :     nl.sara.webdav.Ace.AUTHENTICATED,
      principalString   :     'DAV: authenticated',
      invertprincipal   :     true,
      isprotected       :     false,
      inherited         :     false,
      grantdeny         :     1,
      permString        :     "allow read",
      getPrivilegeNames :     function(){return ["read"]}
     },{
      principal         :     nl.sara.webdav.Ace.ALL,
      principalString   :     "DAV: all",
      invertprincipal   :     false,
      isprotected       :     false,
      inherited         :     false,
      grantdeny         :     1,
      permString        :     "allow read",
      getPrivilegeNames :     function(){return ["read"]}
     },{
      principal         :     {tagname: "owner"},
      principalString   :     'DAV: owner',
      invertprincipal   :     false,
      inherited         :     false,
      isprotected       :     false,
      grantdeny         :     1,
      permString        :     "allow read",
      getPrivilegeNames :     function(){return ["read"]}
     },{
      principal         :     nl.sara.webdav.Ace.SELF,
      principalString   :     'DAV: self',
      invertprincipal   :     false,
      isprotected       :     false,
      inherited         :     false,
      grantdeny         :     1,
      permString        :     "allow read",
      getPrivilegeNames :     function(){return ["read"]}
     },{
      principal         :     nl.sara.webdav.Ace.UNAUTHENTICATED,
      principalString   :     'DAV: unauthenticated',
      invertprincipal   :     false,
      isprotected       :     false,
      inherited         :     false,
      grantdeny         :     1,
      permString        :     "allow unknown privilege (combination)",
      getPrivilegeNames :     function(){return ["write"]}
      }
    ];
    
    nl.sara.webdav.Property = function(){
      // Do nothing
    };
    
    nl.sara.webdav.Client = function(){
      this.propfind = function(resourcePath, callback ,value, properties){
        var data = {};
        if (closeDialogTest){
          data = getDataObject("/test", null, 200, aclTest);
          callback(207, data);
          
          allInherited = false;
          data = getDataObject("/test", null, 200, aclTestOnlyInherited);
          callback(207,data);
          
          // Do nothing
          callback(1,data);
          allInherited = false;
        } else {
         deepEqual(resourcePath, currentDirectory+'/test', "Resource path should be ");
         deepEqual(properties[0].tagname, "acl", "Tagname should be acl");
         deepEqual(properties[0].namespace, "DAV:", "Namespace should be DAV:");
         
         data = getDataObject("/test", null, 0);
         errorTest = 'Something went wrong at the server.';
         callback(207, data);
         
         data = getDataObject("/test", null, 403);
         errorTest = 'You are not allowed to view this acl.';
         callback(207, data);
         
         data = getDataObject("/test", null, 1);
         errorTest = 'Something went wrong at the server.';
         callback(207, data);
         
         data = getDataObject("/test", null, 200, aclTest);
         callback(207, data);
        }
      }
    };
    
    nl.sara.beehub.view.setCustomAclOnResource = function(ownACL, resourcePath){
      deepEqual(ownACL, allInherited, "OwnACL should be "+allInherited);
      deepEqual(resourcePath, currentDirectory+"/test", "Path should be "+currentDirectory+"/test");
    };
    
    nl.sara.beehub.view.dialog.showError = function(error){
      deepEqual(error, errorTest, "Error should be "+errorTest);
    }
    
    nl.sara.beehub.view.acl.setView = function(view, path){
      if (closeDialogTest){
        deepEqual(view, "directory", "View should be resource.");
        deepEqual(path, currentDirectory+"/", "Path should be "+currentDirectory+'/test'); 
      } else {
         deepEqual(view, "resource", "View should be resource.");
         deepEqual(path, currentDirectory+"/test", "Path should be "+currentDirectory+'/test'); 
      }
    }
    
    nl.sara.beehub.view.dialog.clearView = function(){
      ok(true,"Clear view is called.");
    }
    
    nl.sara.beehub.view.dialog.showAcl= function(html, resourcePath, closeDialogFunction){
      if (html !== undefined) {
        ok(true, "Html is not undefined.");
      } else {
        ok(false, "Html is undefined.");
      }
      deepEqual(resourcePath, currentDirectory+'/test', "Path should be "+currentDirectory+"/test");
        closeDialogTest = true;
        closeDialogFunction();
        closeDialogTest=false;
    }
    
    nl.sara.beehub.view.acl.setAddAclRuleDialogClickHandler = function(addAclRuleDialog){
      var userInput = {
          principal : "test",
          permissions : "read"
      }
      addAclRuleDialogTest = true;
      addAclRuleDialog(userInput);
      addAclRuleDialogTest = false;
    };
    
    nl.sara.beehub.view.acl.createRow = function(aceObject){
      if (addAclRuleDialogTest) {
        deepEqual(aceObject.principal,"test", "Principal should be test");
      } else {
       deepEqual(aceObject.principal,aclTest[testIndex].principalString, "Principal should be "+aclTest[testIndex].principalString);
       deepEqual(aceObject.invert,aclTest[testIndex].invertprincipal, "Invert should be "+aclTest[testIndex].invertprincipal);
       deepEqual(aceObject.protected,aclTest[testIndex].isprotected, "Protected should be "+aclTest[testIndex].isprotected);
       deepEqual(aceObject.invertprincipal,aclTest[testIndex].invertprincipal, "Invertprincipal should be "+aclTest[testIndex].invertprincipal);
       deepEqual(aceObject.permissions,aclTest[testIndex].permString, "Permissions string should be "+aclTest[testIndex].permString);
 
       testIndex++;
      }
      
      return "testRow";
    };
    
    nl.sara.beehub.view.acl.addRow = function(row, index){
      if (addAclRuleDialogTest) {
        deepEqual(index, nl.sara.beehub.view.acl.getIndexLastProtected(), "Index should be "+nl.sara.beehub.view.acl.getIndexLastProtected());
      } else {
        deepEqual(index, testIndex-2, "Index should be "+testIndex);
      }
      deepEqual(row, "testRow", "Row should be testRow.");
    };
    
    nl.sara.beehub.view.acl.deleteRowIndex = function(index){
      deepEqual(index, nl.sara.beehub.view.acl.getIndexLastProtected() + 1 , "Index should be "+nl.sara.beehub.view.acl.getIndexLastProtected() + 1);
    };
    
    nl.sara.beehub.controller.saveAclOnServer = function(okFunction, notOkFunction){
      notOkFunction();
    }
    
    nl.sara.beehub.controller.getAclFromServer(currentDirectory+'/test');
  });
  
  /**
   * Test nl.sara.beehub.controller.addAclRule
   */
  test("nl.sara.beehub.controller.addAclRule", function(){
    expect(5);
    
    nl.sara.beehub.view.dialog.showAddRuleDialog = function(actionFunction, html){
      if (html.length > 0){
        ok(true, "Html is created.");
      }
      var userInput = {
          principal : "testPricipal",
          permissions : "testPermissions"
      };
      actionFunction(userInput);
    }
    nl.sara.beehub.view.acl.createRow = function(ace){
      return "testRow";
    };
    
    nl.sara.beehub.view.acl.addRow = function(row, index){
      deepEqual(row, "testRow", "Row should be testRow.");
      deepEqual(index,0, "Index should be 0.");
    }
    
    nl.sara.beehub.controller.saveAclOnServer = function(okfunc, notokfunc){
      okfunc();
      notokfunc();
    };
    
    nl.sara.beehub.view.dialog.clearView = function(){
      ok(true, "Clear view is called.");
    } 
    
    nl.sara.beehub.view.acl.deleteRowIndex = function(index){
      deepEqual(index, 1, "Index should be 1.");
    }
    
    nl.sara.beehub.controller.addAclRule();
  })
  
  /**
   * Test changePermissions
   */
  test("nl.sara.beehub.controller.changePermissions", function(){
    expect(2);
    
    nl.sara.beehub.controller.saveAclOnServer = function(okfunc, notokfunc){
      okfunc();
      notokfunc();
    };
    
    nl.sara.beehub.view.acl.changePermissions = function(row, oldVal){
      deepEqual(row, "row", "Row should be row.");
      deepEqual(oldVal, "oldVal", "Oldval should be oldVal.");
    };
    
    nl.sara.beehub.controller.changePermissions("row", "oldVal");
  });
  
  /**
   * Test deleteAclRule
   */
  test("nl.sara.beehub.controller.deleteAclRule", function(){
    expect(4);
    
    var firstTest = true;
    
    var t = setTimeout(function(){ok(false, "This timeout should not be called.")},3000);
    
    nl.sara.beehub.controller.saveAclOnServer = function(okfunc, notokfunc){
      if (firstTest){
        okfunc();
      } else {
        notokfunc();
      }
    } 
    
    nl.sara.beehub.view.acl.addRow = function(row, index){
      deepEqual(row, "testRow", "Row should be testRow.");
      deepEqual(index, 0, "Index should be 0.");
    };
    
    nl.sara.beehub.view.hideMasks = function(){
      ok(true, "Hidemasks is called.");
    };
    
    nl.sara.beehub.controller.deleteAclRule("testRow", 1, t);
    
    firstTest = false;
    t = setTimeout(function(){ok(false, "This timeout should not be called.")},3000);
    nl.sara.beehub.controller.deleteAclRule("testRow", 1, t);
  })
  
  /**
   * Test moveDownAclRule
   */
  test("nl.sara.beehub.controller.moveDownAclRule", function(){
    expect(3);
    
    var firstTest = true;
    
    var t = setTimeout(function(){ok(false, "This timeout should not be called.")},3000);
    nl.sara.beehub.controller.saveAclOnServer = function(okfunc, notokfunc){
      if (firstTest){
        okfunc();
      } else {
        notokfunc();
      }
    };
    
    nl.sara.beehub.view.hideMasks = function(){
      ok(true,"Hidemask is called.");
    };
    
    nl.sara.beehub.view.acl.moveUpAclRule = function(row){
      deepEqual(row, "row", "Row should be row.");
    };
    
    // Test ok
    nl.sara.beehub.controller.moveDownAclRule("row", t);
    
    // Test not ok
    firstTest = false;
    t = setTimeout(function(){ok(false, "This timeout should not be called.")},3000);
    nl.sara.beehub.controller.moveDownAclRule("row", t);
  })
  
    /**
   * Test moveUpAclRule
   */
  test("nl.sara.beehub.controller.moveUpAclRule", function(){
    expect(3);
    
    var firstTest = true;
    
    var t = setTimeout(function(){ok(false, "This timeout should not be called.")},3000);
    nl.sara.beehub.controller.saveAclOnServer = function(okfunc, notokfunc){
      if (firstTest){
        okfunc();
      } else {
        notokfunc();
      }
    };
    
    nl.sara.beehub.view.hideMasks = function(){
      ok(true,"Hidemask is called.");
    };
    
    nl.sara.beehub.view.acl.moveDownAclRule = function(row){
      deepEqual(row, "row", "Row should be row.");
    };
    
    // Test ok
    nl.sara.beehub.controller.moveUpAclRule("row", t);
    
    // Test not ok
    firstTest = false;
    t = setTimeout(function(){ok(false, "This timeout should not be called.")},3000);
    nl.sara.beehub.controller.moveUpAclRule("row", t);
  })
})();