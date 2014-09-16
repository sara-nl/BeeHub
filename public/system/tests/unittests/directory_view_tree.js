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
  var foo =                     "/foo";
  var client_tests =            "/foo/client_tests";
  var directory =               "/foo/client_tests/directory";
  
//  var testFile =                    "/foo/client_tests/file2.txt";
  var treeNode = $( "#bh-dir-tree ul.dynatree-container" );
  
  var treeSlideTrigger = ".bh-dir-tree-slide-trigger"; 
  var treeHeader =        "#bh-dir-tree-header";
  var treeSlide =         ".bh-dir-tree-slide";
  var treeOverlay =       '.bh-tree-overlay';
  var treeHeaderClass =   ".bh-dir-tree-header";
  var tree =              "#bh-dir-tree";
  var treeCancelButton =  "#bh-dir-tree-cancel";
  
  module("tree view",{
    setup: function(){
      // Call init function
      nl.sara.beehub.view.tree.init();
    },
    teardown: function(){
      // clean up after each test
      backToOriginalEnvironment();
    }
  });
  
  var rememberGetTreeNode = nl.sara.beehub.controller.getTreeNode;
  var rememberAttachEvents = nl.sara.beehub.view.tree.attachEvents;
  var rememberShowTree = nl.sara.beehub.view.tree.showTree;
  var rememberSetModal = nl.sara.beehub.view.tree.setModal;
  var rememberSetCopyMoveView = nl.sara.beehub.controller.setCopyMoveView;
  var rememberClearView = nl.sara.beehub.view.tree.clearView;
  var rememberSlideUp = $.fn.slideUp; 
  var rememberSlideDown = $.fn.slideDown; 
  var rememberSetOnActivate = nl.sara.beehub.view.tree.setOnActivate;
  var rememberCloseTree = nl.sara.beehub.view.tree.closeTree;
  var rememberShowTreeCookie = $.cookie( 'beehub-showtree');
  var rememberremovePath = nl.sara.beehub.view.tree.removePath;
  var rememberAddPath = nl.sara.beehub.view.tree.addPath;

  var backToOriginalEnvironment = function(){
    nl.sara.beehub.controller.getTreeNode = rememberGetTreeNode; 
    nl.sara.beehub.view.tree.attachEvents = rememberAttachEvents;
    nl.sara.beehub.view.tree.closeTree = rememberCloseTree;
    nl.sara.beehub.view.tree.showTree = rememberShowTree;
    nl.sara.beehub.view.tree.setModal = rememberSetModal;
    nl.sara.beehub.controller.setCopyMoveView = rememberSetCopyMoveView;
    nl.sara.beehub.view.tree.clearView = rememberClearView;
    $.fn.slideUp = rememberSlideUp; 
    $.fn.slideDown = rememberSlideDown; 
    $.cookie( 'beehub-showtree', rememberShowTreeCookie , { path: '/' });
    nl.sara.beehub.view.tree.setOnActivate = rememberSetOnActivate;
    nl.sara.beehub.view.tree.removePath = rememberremovePath;
    nl.sara.beehub.view.tree.addPath = rememberAddPath;
  };
  
  /**
   * Test tree expand handler
   * 
   * @param {DOM object}    expander  Expander icon
   * @param {Boolean}       expanded  Expanded value before click
   */
  var testTreeExpandHandler = function(expander, expanded){      
    var parent = expander.parent();
    if (expanded){
      $(parent).siblings( 'ul' ).each(function(key, value){
        deepEqual($(value).attr('style'), "display: none;", "Ul should be hidden");
      });
      deepEqual($(parent).hasClass( 'dynatree-exp-el' ), false, "dynatree-exp-el should be removed");
      deepEqual($(parent).hasClass( 'dynatree-exp-e' ), false, "dynatree-exp-e should be removed");
      deepEqual($(parent).hasClass( 'dynatree-ico-ef' ), false, "dynatree-ico-ef should be removed");
      deepEqual($(parent).hasClass( 'dynatree-expanded' ), false, "dynatree-expanded should be removed");
      deepEqual($(parent).hasClass( 'dynatree-ico-cf' ), true, "dynatree-ico-cf should be added");
      if ( parent.hasClass( 'dynatree-lastsib' ) ) {
        deepEqual($(parent).hasClass( 'dynatree-exp-cl' ), true, "dynatree-exp-cl should be added");
      } else {
        deepEqual($(parent).hasClass( 'dynatree-exp-c' ), true, "dynatree-exp-c should be added");
      }
    } else {
      var list = $(parent).siblings( 'ul' );
      if ( list.length > 0 ) {
        $(parent).siblings( 'ul' ).each(function(key, value){
          deepEqual($(value).attr('style'), "display: block;", "Ul should be shown");
        });
        deepEqual($(parent).hasClass( 'dynatree-exp-cl' ), false, "dynatree-exp-cl should be removed");
        deepEqual($(parent).hasClass( 'dynatree-exp-cdl' ), false, "dynatree-exp-cdl should be removed");
        deepEqual($(parent).hasClass( 'dynatree-exp-c' ), false, "dynatree-exp-c should be removed");
        deepEqual($(parent).hasClass( 'dynatree-exp-cd' ), false, "dynatree-exp-cd should be removed");
        deepEqual($(parent).hasClass( 'dynatree-ico-cf' ), false, "dynatree-ico-cf should be removed");
        deepEqual($(parent).hasClass( 'dynatree-ico-ef' ), true, "dynatree-ico-ef should be added");
        deepEqual($(parent).hasClass( 'dynatree-expanded' ), true, "dynatree-expanded should be added");
        if ( parent.hasClass( 'dynatree-lastsib' ) ) {
          deepEqual($(parent).hasClass( 'dynatree-exp-el' ), true, "dynatree-exp-el should be added");
        } else {
          deepEqual($(parent).hasClass( 'dynatree-exp-e' ), true, "dynatree-exp-e should be added");
        }
      } else {
        // nl.sara.beehub.controller.getTreeNode should be called
      }
    };
  };
  
  /**
   * Test init
   */
  test( 'nl.sara.beehub.view.tree.init: Init', function() {
    expect(5);
    
    nl.sara.beehub.view.tree.attachEvents = function(treeNode) {
      deepEqual(treeNode, treeNode, "Treenode should be the same.");
    };
    
    nl.sara.beehub.view.tree.closeTree = function(){
      ok(true, "CloseTree is called.");
    };
    
    nl.sara.beehub.view.tree.showTree = function(){
      ok(true, "ShowTree is called.");
    };
    
    nl.sara.beehub.view.tree.init();
    
    // Test click handler
    $(treeSlideTrigger).removeClass( 'active' );
    $(treeSlideTrigger).click();
    deepEqual($.cookie( 'beehub-showtree' ), "true", "Show tree cookie should be true.");
    
    $(treeSlideTrigger).addClass( 'active' );
    $(treeSlideTrigger).click();
    deepEqual($.cookie( 'beehub-showtree' ), "false", "Show tree cookie should be false.");
  });
   
  /**
   * Test setOnActivate, attachEvents
   */
  test('nl.sara.beehub.view.tree.setOnActivate, attachEvents', function(){
    expect(45);
    
    var testHref = "";
    
    var testActivateFunction = function(href){
      deepEqual(href, testHref, "Activate function should be called with href "+testHref);
    };

    nl.sara.beehub.view.tree.setOnActivate("Test header", testActivateFunction);
    
    nl.sara.beehub.view.tree.attachEvents(treeNode);
    
    nl.sara.beehub.controller.getTreeNode = function(url, callback){
      ok(true, "Gettree node is called with "+url);
     };

    var expanders = $( '.dynatree-expander', treeNode );
    expanders.each(function(key, value){
      // Test click event on expanders

      var expanded = $(value).parent().hasClass( 'dynatree-expanded' );
      $(value).click();
      testTreeExpandHandler($(value), expanded);

      expanded = $(value).parent().hasClass( 'dynatree-expanded' );
      $(value).click();
      testTreeExpandHandler($(value), expanded);
    });
    

    var links = $( 'a', treeNode );
    links.each(function(key, value){
      // Test click event on links
      testHref = $(value).attr('href');
      value.click();
    });
  });
  
  /**
   * Test slideTrigger
   */
  test("nl.sara.beehub.view.tree.slideTrigger", function(){
    expect(5);
    
    nl.sara.beehub.view.tree.slideTrigger("show");
    deepEqual($(treeSlideTrigger).css('display') === "none", false, "Trigger should be shown");
    
    nl.sara.beehub.view.tree.slideTrigger("hide");
    deepEqual($(treeSlideTrigger).css('display') === "none", true, "Trigger should be hidden");
    
    nl.sara.beehub.view.tree.slideTrigger("show");
    deepEqual($(treeSlideTrigger).css('display') === "none", false, "Trigger should be shown");
    
    nl.sara.beehub.view.tree.slideTrigger("left");
    deepEqual($(treeSlideTrigger+" i").hasClass('icon-folder-open'), false, "icon-folder-open should be removed");
    deepEqual($(treeSlideTrigger+" i").hasClass('icon-folder-close'), true, "icon-folder-close should be added");
  });
  
  /**
   * Test noMask
   */
  test("nl.sara.beehub.view.tree.noMask", function(){
    expect(6);
    
    nl.sara.beehub.view.tree.noMask(true);
    deepEqual($(treeHeader).hasClass('bh-dir-nomask'), true, "bh-dir-nomask class should be set");
    deepEqual($(tree).hasClass('bh-dir-nomask'), true, "bh-dir-nomask class should be set");
    nl.sara.beehub.view.tree.noMask(false);
    deepEqual($(treeHeader).hasClass('bh-dir-nomask'), false, "bh-dir-nomask class should be unset");
    deepEqual($(tree).hasClass('bh-dir-nomask'), false, "bh-dir-nomask class should be unset");
    nl.sara.beehub.view.tree.noMask(true);
    deepEqual($(treeHeader).hasClass('bh-dir-nomask'), true, "bh-dir-nomask class should be set");
    deepEqual($(tree).hasClass('bh-dir-nomask'), true, "bh-dir-nomask class should be set");
  })
  
  /**
   * Test cancelButton
   */
  test("nl.sara.beehub.view.tree.cancelButton", function(){
    expect(9);
    
    // Setup environment
    nl.sara.beehub.view.tree.setModal = function(value){
      deepEqual(value, false, "Setmodel is called with value false.")
    };
    
    nl.sara.beehub.controller.setCopyMoveView = function(value){
      deepEqual(value, false, "CopyMoveView is called with value false.")
    };
    
    nl.sara.beehub.view.tree.clearView = function(){
      ok(true, "ClearView is called.");
    };
    
    nl.sara.beehub.view.tree.cancelButton("show");
    deepEqual($(treeCancelButton).is(":hidden"), false, "Cancel button should be shown.");
    $(treeCancelButton).click();
    
    nl.sara.beehub.view.tree.cancelButton("hide");
    deepEqual($(treeCancelButton).is(":hidden"), true, "Cancel button should be hidden.");
    // No functions should be called
    $(treeCancelButton).click();

    // Test again if show works
    nl.sara.beehub.view.tree.cancelButton("show");
    deepEqual($(treeCancelButton).is(":hidden"), false, "Cancel button should be shown.");
    $(treeCancelButton).click();
  });
  
  /**
   * Test closeTree, showTree
   */
  test('nl.sara.beehub.view.tree.closeTree, showTree', function(){
    expect(15);
    
    // Set up test environment
    //Overwrite slideUp
    $.fn.slideUp = function(input){
      deepEqual(input,"slow", "SlideUp is called with value slow")
    };
    
    //Overwrite slideDown
    $.fn.slideDown = function(input){
      deepEqual(input,"slow", "SlideDown is called with value slow")
    };
    
    nl.sara.beehub.view.tree.closeTree();
    deepEqual($(treeSlideTrigger).hasClass("active"), false, "No class active should be set.");
    deepEqual($(treeSlideTrigger+" i").hasClass("icon-folder-open"), false, "No class icon-folder-open should be set.");
    deepEqual($(treeSlideTrigger+" i").hasClass("icon-folder-close"), true, "Class icon-folder-close should be set.");
    deepEqual($(treeHeaderClass).is(":hidden"), true, "Header should be hidden");
    
    nl.sara.beehub.view.tree.showTree();
    deepEqual($(treeSlideTrigger).hasClass("active"), true, "Class active should be set.");
    deepEqual($(treeSlideTrigger+" i").hasClass("icon-folder-open"), true, "Class icon-folder-open should be set.");
    deepEqual($(treeSlideTrigger+" i").hasClass("icon-folder-close"), false, "No class icon-folder-close should be set.");
    deepEqual($(treeHeaderClass).is(":hidden"), false, "Header should be shown");

    nl.sara.beehub.view.tree.closeTree();
    deepEqual($(treeSlideTrigger).hasClass("active"), false, "No class active should be set.");
    deepEqual($(treeSlideTrigger+" i").hasClass("icon-folder-open"), false, "No class icon-folder-open should be set.");
    deepEqual($(treeSlideTrigger+" i").hasClass("icon-folder-close"), true, "Class icon-folder-close should be set.");
    deepEqual($(treeHeaderClass).is(":hidden"), true, "Header should be hidden");
  })
  
  /**
   * Test clearView
   */
  test("nl.sara.beehub.view.tree.clearView", function(){
    expect(3);
    
    // Set up test environment
    nl.sara.beehub.view.tree.setOnActivate = function(value){
      deepEqual(value, "Browse", "SetOnActivate should be called with value Browse");
    };
    
    nl.sara.beehub.view.tree.closeTree = function(){
      ok(true, "Close tree is called.");
    }
    
    $.cookie( 'beehub-showtree', "false" , { path: '/' });
    nl.sara.beehub.view.tree.clearView(); 
    
    $.cookie( 'beehub-showtree', "true" , { path: '/' });
    // Close tree should not be called
    nl.sara.beehub.view.tree.clearView();

  });
  
  /**
   * Test addPath
   */
  test("nl.sara.beehub.view.tree.addPath", function(){
    expect(3);
    var path = foo+"/";

    var newDir = $( 'a[href="' + encodeURI( path+'test1/' ) + '"]', treeNode );
    
    deepEqual(newDir.length, 0, "Path should nout be present.");
    
    nl.sara.beehub.view.tree.addPath(path+'test1/');
    
    newDir = $( 'a[href="' + encodeURI( path+'test1/' ) + '"]', treeNode );
    var parent = $( 'a[href="' + encodeURI( path ) + '"]', treeNode ).parent();
    
    deepEqual(newDir.length, 1, "Path should not be present.");
    deepEqual(parent.hasClass('dynatree-expanded'), true, "Class expanded should be set.");
    
    // Back to original environment
    nl.sara.beehub.view.tree.removePath(path+'test1');
  });
  
  /**
   * Test removePath
   */
  test("nl.sara.beehub.view.tree.removePath", function(){
    expect(2);
    
    var path = foo+"/";

    nl.sara.beehub.view.tree.addPath(path+'test1/');
    
    var newDir = $( 'a[href="' + encodeURI( path+'test1/' ) + '"]', treeNode );
    deepEqual(newDir.length, 1, "Path should not be present.");

    nl.sara.beehub.view.tree.removePath(path+'test1/');
    
    newDir = $( 'a[href="' + encodeURI( path+'test1/' ) + '"]', treeNode );
    deepEqual(newDir.length, 0, "Path should not be present.");
  })
  
  /**
   * Test updateResource
   */
  test("nl.sara.beehub.view.tree.updateResource", function(){
    expect(2);
    
    // Setup environment
    nl.sara.beehub.view.tree.removePath = function(path){
      deepEqual(path, "/olddir/", "RemovePath should be called with /olddir/.")
    };
    
    nl.sara.beehub.view.tree.addPath = function(path){
      deepEqual(path, "/newdir/", "AddPath should be called with /newdir/.")
    };
    
    var resourceOrg = new nl.sara.beehub.ClientResource("/olddir/");
    var resourceNew = new nl.sara.beehub.ClientResource("/newdir/");
    
    // Nothing should happen because type collection is not set
    nl.sara.beehub.view.tree.updateResource(resourceOrg, resourceNew);
    
    resourceOrg.type = "collection";
    resourceNew.type = "collection";
    
    nl.sara.beehub.view.tree.updateResource(resourceOrg, resourceNew);
  })
  
  /**
   * Test
   */
  test("nl.sara.beehub.view.tree.setModal", function(){
    expect(12);
    
    nl.sara.beehub.view.tree.setModal(true);
    deepEqual($(treeHeader).hasClass('ui-front'), true, "Class ui-front should be set.");
    deepEqual($('a'+treeSlideTrigger).hasClass('ui-front'), true, "Class ui-front should be set.");
    deepEqual($(tree).hasClass('ui-front'), true, "Class ui-front should be set.");
    deepEqual($( 'div'+treeOverlay).length, 1, "Div overlay should be present." );
    
    nl.sara.beehub.view.tree.setModal(false);
    deepEqual($(treeHeader).hasClass('ui-front'), false, "Class ui-front should be unset.");
    deepEqual($('a'+treeSlideTrigger).hasClass('ui-front'), false, "Class ui-front should be unset.");
    deepEqual($(tree).hasClass('ui-front'), false, "Class ui-front should be unset.");
    deepEqual($( 'div'+treeOverlay).length, 0, "Div overlay should be not present." );
    
    nl.sara.beehub.view.tree.setModal(true);
    deepEqual($(treeHeader).hasClass('ui-front'), true, "Class ui-front should be set.");
    deepEqual($('a'+treeSlideTrigger).hasClass('ui-front'), true, "Class ui-front should be set.");
    deepEqual($(tree).hasClass('ui-front'), true, "Class ui-front should be set.");
    deepEqual($( 'div'+treeOverlay).length, 1, "Div overlay should be present." );
  });
})();
// End of file