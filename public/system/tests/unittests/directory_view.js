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
 * You should have received a copyof the GNU Lesser General Public License
 * along with BeeHub webclient.  If not, see <http://www.gnu.org/licenses/>.
 */
"use strict";

(function(){
  
  var mask            = "#bh-dir-mask";
  var maskTransparant = "#bh-dir-mask-transparant";
  var maskLoading     = "#bh-dir-mask-loading";
  var aclPanel        = "#bh-dir-panel-acl";
  var contentPanel    = "#bh-dir-panel-contents";

  module("view");
  
  /**
   * Test init
   */
  test( 'nl.sara.beehub.view.init: Init', function() {
    expect(8);
    
    var rememberContentInit = nl.sara.beehub.view.content.init;
    nl.sara.beehub.view.content.init = function(){
      ok(true, "Function content init is called.");
    };
    
    var rememberTreeInit = nl.sara.beehub.view.tree.init;
    nl.sara.beehub.view.tree.init = function(){
      ok(true, "Function tree init is called.");
    };
    
    var rememberAclInit = nl.sara.beehub.view.acl.init;
    nl.sara.beehub.view.acl.init = function(){
      ok(true, "Function acl init is called.");
    };
    
    var rememberShowTree = nl.sara.beehub.view.tree.showTree;
    nl.sara.beehub.view.tree.showTree = function(){
      ok(true, "Function showTree is called.");
    };
    
    var rememberCloseTree = nl.sara.beehub.view.tree.closeTree;
    nl.sara.beehub.view.tree.closeTree = function(){
      ok(true, "Function closeTree is called.");
    };
    
    var rememberShowFixedButtons = nl.sara.beehub.view.showFixedButtons;
    nl.sara.beehub.view.showFixedButtons = function(value){
      deepEqual(value, fixedButton, "Function showFixedButton value should be "+fixedButton);
    };
    
    var rememberCookie = $.cookie( "beehub-showtree");
    
    // Init
    nl.sara.beehub.view.init();
    
    var fixedButton = "acl";
    $('a[href="'+aclPanel+'"]').click();
    
    $.cookie( "beehub-showtree" ,"true", { path: '/' } );
    fixedButton = "content";
    $('a[href="'+contentPanel+'"]').click();
    
    // Showtree should not be called
    $.cookie( "beehub-showtree" ,"false", { path: '/' } );
    $('a[href="'+contentPanel+'"]').click();
    
    // Back to original environment
    nl.sara.beehub.view.content.init = rememberContentInit;
    nl.sara.beehub.view.tree.init = rememberTreeInit;
    nl.sara.beehub.view.acl.init = rememberAclInit;
    nl.sara.beehub.view.tree.showTree = rememberShowTree;
    nl.sara.beehub.view.tree.closeTree = rememberCloseTree;
    nl.sara.beehub.view.showFixedButtons = rememberShowFixedButtons;
    $.cookie( "beehub-showtree" , rememberCookie , { path: '/' } );
  });
 
  /**
   * Test clearAllViews
   */
  test("nl.sara.beehub.view.clearAllViews", function(){
    expect(3);
    
    var rememberContentClearView = nl.sara.beehub.view.content.clearView;
    nl.sara.beehub.view.content.clearView = function(){
      ok(true, "Content clearView is called.");
    };
    
    var rememberTreeClearView = nl.sara.beehub.view.tree.clearView;
    nl.sara.beehub.view.tree.clearView = function(){
      ok(true, "Tree clearView is called.");
    };
    
    var rememberDialogClearView = nl.sara.beehub.view.dialog.clearView;
    nl.sara.beehub.view.dialog.clearView = function(){
      ok(true, "Dialog clearView is called.");
    };
    
    nl.sara.beehub.view.clearAllViews();
    
    nl.sara.beehub.view.content.clearView = rememberContentClearView;
    nl.sara.beehub.view.tree.clearView = rememberTreeClearView;
    nl.sara.beehub.view.dialog.clearView = rememberDialogClearView;
  });

  /**
   * Test maskView
   */
  test("nl.sara.beehub.view.maskView", function(){
    expect(6);
    
    nl.sara.beehub.view.maskView("mask", true);
    deepEqual($(mask).is(":hidden"), false, "Mask should be shown.");
    
    nl.sara.beehub.view.maskView("mask", false);
    deepEqual($(mask).is(":hidden"), true, "Mask should be hidden.");
  
    nl.sara.beehub.view.maskView("transparant", true);
    deepEqual($(maskTransparant).is(":hidden"), false, "Transparant mask should be shown.");

    nl.sara.beehub.view.maskView("transparant", false);
    deepEqual($(maskTransparant).is(":hidden"), true, "Transparant mask should be hidden.");

    nl.sara.beehub.view.maskView("loading", true);
    deepEqual($(maskLoading).is(":hidden"), false, "Loading mask should be shown.");

    nl.sara.beehub.view.maskView("loading", false);
    deepEqual($(maskLoading).is(":hidden"), true, "Loading mask should be hidden.");
  })
  
  /**
   * Test
   */
  test("nl.sara.beehub.view.hideMasks", function(){
    expect(6);
    
    nl.sara.beehub.view.maskView("mask", true);
    nl.sara.beehub.view.maskView("transparant", true);
    nl.sara.beehub.view.maskView("loading", true);
    
    deepEqual($(mask).is(":hidden"), false, "Mask should be shown.");
    deepEqual($(maskTransparant).is(":hidden"), false, "Transparant mask should be shown.");
    deepEqual($(maskLoading).is(":hidden"), false, "Loading mask should be shown.");

    nl.sara.beehub.view.hideMasks();
    
    deepEqual($(mask).is(":hidden"), true, "Mask should be hidden.");
    deepEqual($(maskTransparant).is(":hidden"), true, "Transparant mask should be hidden.");
    deepEqual($(maskLoading).is(":hidden"), true, "Loading mask should be hidden.");

  });

  /**
   * Test showFixedButtons
   */
  test("nl.sara.beehub.view.showFixedButtons", function(){
    expect(4);
    
    // Setup environment
    var rememberContentAllFixedButtons = nl.sara.beehub.view.content.allFixedButtons;
    nl.sara.beehub.view.content.allFixedButtons = function(value){
      if (view === "content"){
       deepEqual(value, "show", "AllFixedButtons should be called with value shown.");
      } else {
       deepEqual(value, "hide", "AllFixedButtons should be called with value hide.");
      }
    };
    
    var rememberAclAllFixedButtons = nl.sara.beehub.view.acl.allFixedButtons;
    nl.sara.beehub.view.acl.allFixedButtons = function(value){
      if (view === "content"){
        deepEqual(value, "hide", "AllFixedButtons should be called with value hide.");
       } else {
        deepEqual(value, "show", "AllFixedButtons should be called with value shown.");
       }
    };
    
    var view = "acl";
    nl.sara.beehub.view.showFixedButtons("acl");
    view = "content";
    nl.sara.beehub.view.showFixedButtons("content");
    
    // Back to original environment
    nl.sara.beehub.view.content.allFixedButtons = rememberContentAllFixedButtons;
    nl.sara.beehub.view.acl.allFixedButtons = rememberAclAllFixedButtons;
  });
  
  /**
   * Test
   */
  test("nl.sara.beehub.view.addResource", function(){
    expect(3);
    
    // Setup environment
    var rememberContentAddResource = nl.sara.beehub.view.content.addResource;
    nl.sara.beehub.view.content.addResource = function(resource) {
      deepEqual(resource.path, "/test/", "Addresource should be called with resource.path /test/.");
    };
    
    var rememberTreeAddPath = nl.sara.beehub.view.tree.addPath;
    nl.sara.beehub.view.tree.addPath = function(path){
      deepEqual(path, "/test/", "AddPath should be called with resource.path /test/.");
    };
    
    var resource1 = new nl.sara.beehub.ClientResource("/test/");
    nl.sara.beehub.view.addResource(resource1);
    
    var resource2 = new nl.sara.beehub.ClientResource("/test/");
    resource2.type = "collection";
    nl.sara.beehub.view.addResource(resource2);
    
    // Back to original environment
    nl.sara.beehub.view.content.addResource = rememberContentAddResource;
    nl.sara.beehub.view.tree.addPath = rememberTreeAddPath;
  });
  
  /**
   * Test updateResource
   */
  test("nl.sara.beehub.view.updateResource", function(){
    expect(4);
    
    // Setup environment
    var resource1 = new nl.sara.beehub.ClientResource("/test1/");
    var resource2 = new nl.sara.beehub.ClientResource("/test2/");
    
    var rememberContentUpdateResource = nl.sara.beehub.view.content.updateResource;
    nl.sara.beehub.view.content.updateResource = function(resourceOrg, resourceNew){
      deepEqual(resourceOrg.path, "/test1/", "Content Update resource should be called.");
      deepEqual(resourceNew.path, "/test2/", "Content Update resource should be called.")
    }

    var rememberTreeUpdateResource = nl.sara.beehub.view.tree.updateResource;
    nl.sara.beehub.view.tree.updateResource = function(resourceOrg, resourceNew){
      deepEqual(resourceOrg.path, "/test1/", "Tree Update resource should be called.");
      deepEqual(resourceNew.path, "/test2/", "Tree Update resource should be called.")
    }
    
    nl.sara.beehub.view.updateResource(resource1, resource2);
    
    // Back to original environment
    nl.sara.beehub.view.content.updateResource = rememberContentUpdateResource;
    nl.sara.beehub.view.tree.updateResource = rememberTreeUpdateResource;
  });
  
  /**
   * Test deleteResource
   */
  test("nl.sara.beehub.view.deleteResource", function(){
    expect(2);
    
    // Setup environment
    var resource = new nl.sara.beehub.ClientResource("/test/");
    
    var rememberContentDeleteResource = nl.sara.beehub.view.content.deleteResource;
    nl.sara.beehub.view.content.deleteResource = function(resource){
      deepEqual(resource.path, "/test/", "Content Delete resource should be called.");
    }

    var rememberTreeRemovePath = nl.sara.beehub.view.tree.removePath;
    nl.sara.beehub.view.tree.removePath = function(path){
      deepEqual(path, "/test/", "Remove path resource should be called.");
    }
    
    nl.sara.beehub.view.deleteResource(resource);
    
    // Back to original environment
    nl.sara.beehub.view.content.deleteResource = rememberContentDeleteResource;
    nl.sara.beehub.view.tree.removePath = rememberTreeRemovePath;
  });
  
  /**
   * Test
   */
  test("nl.sara.beehub.view.setCustomAclOnResource", function(){
    expect(1);
    
    var rememberSetCustomAclOnResource = nl.sara.beehub.view.content.setCustomAclOnResource;
    nl.sara.beehub.view.content.setCustomAclOnResource = function(value1, value2){
      ok(true, "SetCurtomAclOnResource is called");
    };
    
    nl.sara.beehub.view.setCustomAclOnResource("test","test");
    
    nl.sara.beehub.view.content.setCustomAclOnResource = rememberSetCustomAclOnResource;
  });
  
})();
// End of file