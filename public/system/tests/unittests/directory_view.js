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
  
})();
// End of file