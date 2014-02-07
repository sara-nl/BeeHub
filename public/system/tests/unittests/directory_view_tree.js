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
//  var currentDirectory =            "/foo/client_tests/";
//  var testFile =                    "/foo/client_tests/file2.txt";
  var treeNode = $( "#bh-dir-tree ul.dynatree-container" );
  
  var treeSlideTrigger = ".bh-dir-tree-slide-trigger"
  
  module("tree view",{
    setup: function(){
      // Call init function
      nl.sara.beehub.view.tree.init();
    }
  });
  
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
      })
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
        ok(false, "Not yet implemented, refactoring code is needed.");
      }

    }
  };
  
  /**
   * Test init
   */
  test( 'nl.sara.beehub.view.tree.init: Init', function() {
    expect(5);
    var rememberAttachEvents = nl.sara.beehub.view.tree.attachEvents;
    
    nl.sara.beehub.view.tree.attachEvents = function(treeNode) {
      deepEqual(treeNode, treeNode, "Treenode should be the same.");
    };
    
    var rememberCloseTree = nl.sara.beehub.view.tree.closeTree;
    nl.sara.beehub.view.tree.closeTree = function(){
      ok(true, "CloseTree is called.");
    }
    
    var rememberShowTree = nl.sara.beehub.view.tree.showTree;
    nl.sara.beehub.view.tree.showTree = function(){
      ok(true, "ShowTree is called.");
    }
    
    nl.sara.beehub.view.tree.init();
    
    // Test click handler
    $(treeSlideTrigger).removeClass( 'active' );
    $(treeSlideTrigger).click();
    deepEqual($.cookie( 'beehub-showtree' ), "true", "Show tree cookie should be true.");
    
    $(treeSlideTrigger).addClass( 'active' );
    $(treeSlideTrigger).click();
    deepEqual($.cookie( 'beehub-showtree' ), "false", "Show tree cookie should be false.");
    
    // Put back functions
    nl.sara.beehub.view.tree.attachEvents = rememberAttachEvents;
    nl.sara.beehub.view.tree.closeTree = rememberCloseTree;
    nl.sara.beehub.view.tree.showTree = rememberShowTree;
  });
   
  /**
   * Test setOnActivate, attachEvents
   */
  test('nl.sara.beehub.view.tree.setOnActivate, attachEvents', function(){
    expect(42);
    
    var testHref = "";
    
    var testActivateFunction = function(href){
      deepEqual(href, testHref, "Activate function should be called with href "+testHref);
    }
    
    nl.sara.beehub.view.tree.setOnActivate("Test header", testActivateFunction);
    
    nl.sara.beehub.view.tree.attachEvents(treeNode);
    
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
    })
  })
  
  /**
   * Test slideTrigger
   */
  test("nl.sara.beehub.view.tree.slideTrigger", function(){
    expect(5);
    
    nl.sara.beehub.view.tree.slideTrigger("show");
    deepEqual($(treeSlideTrigger).is(":hidden"), false, "Trigger should be shown");
    
    nl.sara.beehub.view.tree.slideTrigger("hide");
    deepEqual($(treeSlideTrigger).is(":hidden"), true, "Trigger should be hidden");
    
    nl.sara.beehub.view.tree.slideTrigger("show");
    deepEqual($(treeSlideTrigger).is(":hidden"), false, "Trigger should be shown");
    
    nl.sara.beehub.view.tree.slideTrigger("left");
    deepEqual($(treeSlideTrigger+" i").hasClass('icon-folder-open'), false, "icon-folder-open should be removed");
    deepEqual($(treeSlideTrigger+" i").hasClass('icon-folder-close'), true, "icon-folder-close should be added");
  })
})();
// End of file