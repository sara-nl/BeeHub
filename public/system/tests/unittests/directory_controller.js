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
  
    var rememberClearAllViews = nl.sara.beehub.view.clearAllViews;
    nl.sara.beehub.view.clearAllViews = function(){
      ok(true, "nl.sara.beehub.view.clearAllViews is called.");
    };
    
    nl.sara.beehub.controller.clearAllViews();
    
    nl.sara.beehub.view.clearAllViews = rememberClearAllViews;
  });
  
  /**
   * Test maskView
   */
  test("nl.sara.beehub.controller.maskView", function(){
    expect(6);
    
    var rememberMaskView = nl.sara.beehub.view.maskView;
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
    
    nl.sara.beehub.view.maskView = rememberMaskView;
  });
  
  /**
   * Test inputDisable
   */
  test("nl.sara.beehub.controller.inputDisable", function(){
    expect(2);
    
    var rememberInputDisable = nl.sara.beehub.view.inputDisable;
    nl.sara.beehub.view.inputDisable = function(value){
      deepEqual(value, testValue, "nl.sara.beehub.view.inputDisable should be called with value "+testValue);
    };
    
    var testValue = true;
    nl.sara.beehub.controller.inputDisable(testValue);
    testValue = false;
    nl.sara.beehub.controller.inputDisable(testValue);
    
    nl.sara.beehub.view.inputDisable = rememberInputDisable;
  });
  
  /**
   * Test showError
   */
  test("nl.sara.beehub.controller.showError", function(){
    expect(1);
    
    var rememberShowError = nl.sara.beehub.view.showError;
    nl.sara.beehub.view.dialog.showError = function(value){
      deepEqual(value, "test", "nl.sara.beehub.view.showError should be called with value test.");
    };
    
    nl.sara.beehub.controller.showError("test");
  
    nl.sara.beehub.view.showError = rememberShowError;
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
  
//  nl.sara.beehub.controller.getTreeNode = function(path, callback){
//    var client = new nl.sara.webdav.Client();
//    var resourcetypeProp = new nl.sara.webdav.Property();
//    resourcetypeProp.tagname = 'resourcetype';
//    resourcetypeProp.namespace='DAV:';
//    var properties = [resourcetypeProp];
//    client.propfind(path, callback ,1,properties);
//  };
  
  /**
   * Test getTreeNode
   */
  test("nl.sara.beehub.controller.getTreeNode", function(){
    expect(5);
    
    var callback = function(){
      ok(true, "Callback function is called.");
    };
    
    var rememberClient = nl.sara.webdav.Client;
    nl.sara.webdav.Client = function(){
      this.propfind = function(path, callback ,val ,properties){
        deepEqual(path, "/test", "Propfind should be called with path value "+path);
        deepEqual(val, 1, "Propfind should be called with val value 1");
        deepEqual(properties[0].tagname, 'resourcetype', "Prop tagname should be resourcetype");
        deepEqual(properties[0].namespace, 'DAV:', "Prop namespace should be DAV:");
        callback();
      };
    };
    
    var rememberProperty = nl.sara.webdav.Property;
    nl.sara.webdav.Property = function(){
      // Do nothing
    };
    
    nl.sara.beehub.controller.getTreeNode("/test", callback);
    
    nl.sara.webdav.Client = rememberClient;
    nl.sara.webdav.Property = rememberProperty;
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
    
    var rememberShowError = nl.sara.beehub.view.dialog.showError;
    nl.sara.beehub.view.dialog.showError = function(error){
      deepEqual(error,'Could not load the subdirectories.', "Show error should be called with value testError.");
    };
    
    var rememberCreateTreeNode = nl.sara.beehub.view.tree.createTreeNode;
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
    
    // Back to original environment
    nl.sara.beehub.view.dialog.showError = rememberShowError;
    nl.sara.beehub.view.tree.createTreeNode = rememberCreateTreeNode;
  });
   

  
  /**
   * Test
   */
  test("nl.sara.beehub.controller.createNewFolder", function(){
    expect(1);
    
    // Set up environment
    var tested405 = false;
    var pathName = currentDirectory+"/new_folder";
    
    var rememberClient = nl.sara.webdav.Client;
    nl.sara.webdav.Client = function(){
      this.mkcol = function(path, callback) {
        deepEqual(path, pathName, "Mkkol is called with path "+currentDirectory+"/new_folder");
        callback(201,"/test");
        if (!tested405) {
          tested405 = true;
          pathName = currentDirectory+"/new_folder_1";
          callback(405,"/test");
        }
        callback(403,"/test");
//        // Unknown return value
        callback(1,"/test");
      };
      
      this.propfind = function(resourcepath, callback ,val , properties){
        deepEqual(resourcepath, "/test", "Resource path should be test");
        deepEqual(val, 1, "Val should be 1.");
        deepEqual(properties.length, 6, "Properties");
        deepEqual(properties[0].tagname, "resourcetype", "Tagname should be resourcetype");
        deepEqual(properties[0].namespace, "DAV:", "Namespace should be DAV:");
        deepEqual(properties[1].tagname, "getcontenttype", "Tagname should be");
        deepEqual(properties[1].namespace, "DAV:", "Namespace should be DAV:");
        deepEqual(properties[2].tagname, "displayname", "Tagname should be");
        deepEqual(properties[2].namespace, "DAV:", "Namespace should be DAV:");
        deepEqual(properties[3].tagname, "owner", "Tagname should be");
        deepEqual(properties[3].namespace, "DAV:", "Namespace should be DAV:");
        deepEqual(properties[4].tagname, "getlastmodified", "Tagname should be");
        deepEqual(properties[4].namespace, "DAV:", "Namespace should be DAV:");
        deepEqual(properties[5].tagname, "getcontentlength", "Tagname should be");
        deepEqual(properties[5].namespace, "DAV:", "Namespace should be DAV:");
       
        var propertyObject = function(namespace, property){
          this.property = property;
        };
        
        propertyObject.prototype.getParsedValue = function(){
          if ((this.property === "resourcetype") {}|| (this.property === "getcontenttype")){
            console.log("in "+this.property);

            return testType;
          } else {
            console.log("in2 "+this.property);

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
          return ["testPath"];
        };
        dataObject.prototype.getResponse = function(){
          return new responseObject;
        };
        
        var data = new dataObject;
        
        var testType = nl.sara.webdav.codec.ResourcetypeCodec.COLLECTION;
        callback(207,data);
        callback(1,data);
        testType = "notcollection"
        callback(207,data);
      };
    };
  
    var rememberAddResource = nl.sara.beehub.view.addResource;
    nl.sara.beehub.view.addResource = function(resource){
      ok(false, "Not yet implmented.");
    }
    
    var rememberTriggerRenameClick = nl.sara.beehub.view.content.triggerRenameClick;
    nl.sara.beehub.view.content.triggerRenameClick = function(resource){
      ok(false, "Not yet implmented.");
    }
    
    var rememberShowError = nl.sara.beehub.view.dialog.showError;
    nl.sara.beehub.view.dialog.showError = function(error){
      ok(false, "Not yet implmented.");
    }
    
    var rememberProperty = nl.sara.webdav.Property;
    nl.sara.webdav.Property = function(){
      // Do nothing
    };
    
    nl.sara.beehub.controller.createNewFolder();
    
    // Back to original environment
    nl.sara.webdav.Client = rememberClient;
    nl.sara.beehub.view.addResource = rememberAddResource;
    nl.sara.beehub.view.content.triggerRenameClick = rememberTriggerRenameClick;
    nl.sara.beehub.view.dialog.showError = rememberShowError;
    nl.sara.webdav.Property = rememberProperty;
  });
  
  ///**
  // * Test addSlash
  // */
  //test( 'addSlash', function() {
  //  deepEqual( nl.sara.beehub.controller.addSlash("/home/laura"), "/home/laura/", "Add slash to /home/laura" );
  //  deepEqual( nl.sara.beehub.controller.addSlash("/home/laura/"), "/home/laura/", "Add no slash to /home/laura/" );
  //} );
  //

  //

  //
  //
  ////test('extractPropsFromPropfindRequest', function(){
  ////  //TODO 
  ////  // Hiervoor moet ik data object na kunnen maken
  ////});
  //
  ///**
  // * Test getTreeNode
  // * 
  // */
  //asyncTest('getTreeNode', function(){
  //  var path="/home/laura/folder/";
  //  var callback = function(status, data){
  //    start();
  //    deepEqual( status, "testStatus", 'getTreeNode should send request to server' );
  //  }
  //  var client = new nl.sara.webdav.Client();
  //  var resourcetypeProp = new nl.sara.webdav.Property();
  //  resourcetypeProp.tagname = 'resourcetype';
  //  resourcetypeProp.namespace='DAV:';
  //  var properties = [resourcetypeProp];
  //  var config = {
  //      "path":       "/home/laura/folder/",
  //      "client" :    client,
  //      "properties": properties,
  //      "callback"  : callback
  //  }
  //  // Prepare to mock AJAX
  //  var server = new MockHttpServer( function ( request ) {
  //    // Prepare a response
  //    request.receive( "testStatus" );
  //  } );
  //  server.start();
  //  
  //  nl.sara.beehub.controller.getTreeNode(config); 
  // 
  //  // End mocking of AJAX
  //  server.stop();
  //});
  //
  ///**
  // * Test getTreeNode
  // * 
  // */
  //asyncTest('createNewFolder', function(){ 
  //  var path="/home/laura/folder/";
  //  var callback = function(status, data){
  //    start();
  ////    var testfunc = controller.createNewFolderCallback(0,"new_folder").bind(controller)(201,"test");
  //    var testfunc = nl.sara.beehub.controller.createNewFolderCallback(0,"new_folder");
  //    deepEqual(testfunc,"Test");
  ////    deepEqual( status, "testStatus", 'createNewFolder should send request to server' );
  //  }
  //  var client = new nl.sara.webdav.Client();
  //
  //  var config = {
  //      "path":       "/home/laura/folder/",
  //      "client" :    client,
  //      "foldername"  : "new_folder",
  //      "callback"    : callback
  //  }
  //  // Prepare to mock AJAX
  //  var server = new MockHttpServer( function ( request ) {
  //    // Prepare a response
  //    request.receive( "testStatus" );
  //  } );
  //  server.start();
  //  nl.sara.beehub.controller.createNewFolder(config); 
  //  // End mocking of AJAX
  //  server.stop();
  //});
  //// End of file
})();