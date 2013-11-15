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

module("controller")

/**
 * Test htmlEscape
 */
test( 'htmlEscape', function() {
  var controller = new Controller();  
  deepEqual( controller.htmlEscape("&escape&"), "&amp;escape&amp;", "Escape &" );
  deepEqual( controller.htmlEscape('"escape"'), "&quot;escape&quot;", 'Escape "' );
  deepEqual( controller.htmlEscape("'escape'"), "&#39;escape&#39;", "Escape '" );
  deepEqual( controller.htmlEscape("<escape<"), "&lt;escape&lt;", "Escape <" );
  deepEqual( controller.htmlEscape(">escape>"), "&gt;escape&gt;", "Escape >" );
} );

/**
 * Test addSlash
 */
test( 'addSlash', function() {
  var controller = new Controller();  
  deepEqual( controller.addSlash("/home/laura"), "/home/laura/", "Add slash to /home/laura" );
  deepEqual( controller.addSlash("/home/laura/"), "/home/laura/", "Add no slash to /home/laura/" );
} );

/**
 * Test getDisplayName
 */
test( 'getDisplayName', function() {
  var controller = new Controller();  
  nl.sara.beehub.users_path = "/home/users/"
  var username = nl.sara.beehub.users_path + "laura"
  nl.sara.beehub.principals.users["laura"] = "Laura Leistikow";
  
  nl.sara.beehub.groups_path = "/home/groups/"
  var groupname = nl.sara.beehub.groups_path + "group"
  nl.sara.beehub.principals.groups["group"] = "Test group";
  
  var name = undefined;
  
  deepEqual( controller.getDisplayName(username), "Laura Leistikow", "User laura" );
  deepEqual( controller.getDisplayName(groupname), "Test group", "Group group" );
  deepEqual( controller.getDisplayName(name), "", "Name undefined" );

} );

/**
 * Test bytesToSize
 * 
 */
test('bytesToSize', function(){
  var controller = new Controller();  
  deepEqual( controller.bytesToSize(0, 2), "0 B", "0 bytes, precision 2");
  deepEqual( controller.bytesToSize(500, 2), "500 B", "500 bytes, precision 2");
  deepEqual( controller.bytesToSize(1500, 2), "1.46 KB", "1500 bytes, precision 2");
  deepEqual( controller.bytesToSize(15000000, 2), "14.31 MB", "15000000 bytes, precision 2");
  deepEqual( controller.bytesToSize(15000000000, 1), "14.0 GB", "15000000000 bytes, precision 1");
  deepEqual( controller.bytesToSize(15000000000000, 0), "14 TB", "15000000000000 bytes, precision 0");
});


//test('extractPropsFromPropfindRequest', function(){
//  //TODO 
//  // Hiervoor moet ik data object na kunnen maken
//});

/**
 * Test getTreeNode
 * 
 */
asyncTest('getTreeNode', function(){
  var controller = new Controller();  
  var path="/home/laura/folder/";
  var callback = function(status, data){
    start();
    deepEqual( status, "testStatus", 'getTreeNode should send request to server' );
  }
  var client = new nl.sara.webdav.Client();
  var resourcetypeProp = new nl.sara.webdav.Property();
  resourcetypeProp.tagname = 'resourcetype';
  resourcetypeProp.namespace='DAV:';
  var properties = [resourcetypeProp];
  var config = {
      "path":       "/home/laura/folder/",
      "client" :    client,
      "properties": properties,
      "callback"  : callback
  }
  // Prepare to mock AJAX
  var server = new MockHttpServer( function ( request ) {
    // Prepare a response
    request.receive( "testStatus" );
  } );
  server.start();
  
  controller.getTreeNode(config); 
 
  // End mocking of AJAX
  server.stop();
});

/**
 * Test getTreeNode
 * 
 */
asyncTest('createNewFolder', function(){ 
  var controller = new Controller();  
  var path="/home/laura/folder/";
  var callback = function(status, data){
    start();
//    var testfunc = controller.createNewFolderCallback(0,"new_folder").bind(controller)(201,"test");
    var testfunc = controller.createNewFolderCallback(0,"new_folder");
    deepEqual(testfunc,"Test");
//    deepEqual( status, "testStatus", 'createNewFolder should send request to server' );
  }
  var client = new nl.sara.webdav.Client();

  var config = {
      "path":       "/home/laura/folder/",
      "client" :    client,
      "foldername"  : "new_folder",
      "callback"    : callback
  }
  // Prepare to mock AJAX
  var server = new MockHttpServer( function ( request ) {
    // Prepare a response
    request.receive( "testStatus" );
  } );
  server.start();
  controller.createNewFolder(config); 
  // End mocking of AJAX
  server.stop();
});
// End of file