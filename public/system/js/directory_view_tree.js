/*
 * Copyright ©2013 SARA bv, The Netherlands
 *
 * This file is part of the beehub client
 *
 * beehub client is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * beehub-client is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with beehub.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Beehub Client Tree
 * 
 * Directory tree
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */

(function(){
  /*
   * Init tree
   * 
   * Public function
   */
  nl.sara.beehub.view.tree.init = function() {
    // Directory tree in tree panel
    $("#bh-dir-tree").dynatree({
      onActivate: function(node) {
        // First close tree to prevent you see a load error in the treeview
        nl.sara.beehub.view.tree.closeTree();
        // Load node
        window.location.href = node.data.id;
      },
      persist: false,
      // from beehub_directory.php
      children: treecontents,
      // collapse
      onLazyRead: function(node){
        nl.sara.beehub.controller.getTreeNode(node.data.id, getTreeNodeCallback(node));
      },
      debugLevel: 0
    });
    // Decided to implement this later
//     Tree slide handler
//    $(".bh-dir-tree-slide-trigger").hover(function() {
//      console.log($(this).data('timeout'));
//      clearTimeout($(this).data('timeout'));
//      handle_tree_slide_click();
//  }, function() {
//      var t = setTimeout(function() {
//        handle_tree_slide_click();
//      }, 60000);
//      $(this).data('timeout', t);
//  });
//    $(".bh-dir-tree-slide-trigger").hover(handle_tree_slide_click, function(){
////      No action;
//    });
    $(".bh-dir-tree-slide-trigger").click(handle_tree_slide_click);
    if ($.cookie("beehub-showtree") !== "done") {
      $.cookie("beehub-showtree", "done", { path: '/' });
      $(".bh-dir-tree-slide").toggle();
      $(".bh-dir-tree-header").toggle();
      $(this).toggleClass("active");
      $('.bh-dir-tree-slide-trigger i').toggleClass('icon-folder-open icon-folder-close');
      setTimeout(function(){$(".bh-dir-tree-slide-trigger").trigger('click');},1000);
    };
  };
  
  /*
   * Return callback function for getTreeNode
   * 
   * @param Object  node  Node from tree
   * 
   */
  var getTreeNodeCallback = function(node){
    return function(status, data) {
      // Callback
      if (status !== 207) {
        // Server returned an error condition: set node status accordingly
        node.setLazyNodeStatus(DTNodeStatus_Error, {
          tooltip: data.faultDetails,
          info: data.faultString
        });
      };
      var res = [];
      $.each(data.getResponseNames(), function(pathindex){
        var path = data.getResponseNames()[pathindex];
        
        // put response in array
        if (node.data.id !== path) {
          if ((data.getResponse(path).getProperty('DAV:','resourcetype') !== null) && 
              (nl.sara.webdav.codec.ResourcetypeCodec.COLLECTION === data.getResponse(path).getProperty('DAV:','resourcetype').getParsedValue())){
            var name = path;
            while (name.substring(name.length-1) === '/') {
              name = name.substr(0, name.length-1);
            };
            name = decodeURIComponent(name.substr(name.lastIndexOf('/')+1));
            res.push({
              'title': nl.sara.beehub.controller.htmlEscape(name),
              'id' : nl.sara.beehub.controller.htmlEscape(path),
              'isFolder': 'true',
              'isLazy' : 'true'
            });
          };
        };
      });
      // PWS status OK
      node.setLazyNodeStatus(DTNodeStatus_Ok);
      node.addChild(res);
      // end callback
    };
  };
  
  /*
   * Action slide trigger
   * 
   * Public function
   * 
   * @param String action hide, show or icon left
   */
  nl.sara.beehub.view.tree.slideTrigger = function(action){
    switch(action)
    {
    case "show":
      $(".bh-dir-tree-slide-trigger").show();
      break;
    case "hide":
      $(".bh-dir-tree-slide-trigger").hide();
      break;
    case "left":
      $('.bh-dir-tree-slide-trigger i').removeClass('icon-folder-open');
      $('.bh-dir-tree-slide-trigger i').addClass('icon-folder-close');
      break;
    default:
      // This should never happen
    }
  };
  
  /*
   * Overrule mask, show only tree view
   * 
   * Public function
   * 
   * @param Boolean nomask true or false
   * 
   */
  nl.sara.beehub.view.tree.noMask = function(nomask){
    if (nomask) {
      $("#bh-dir-tree-header").addClass('bh-dir-nomask');
      $("#bh-dir-tree").addClass('bh-dir-nomask');
    } else {
      $(".bh-dir-tree-header").removeClass('bh-dir-nomask');
      $("#bh-dir-tree").removeClass('bh-dir-nomask');
    }
  };
  
  /*
   * Show or hide cancel button and set click handler
   * 
   * Public function
   * 
   * @param String action show or hide
   */
  nl.sara.beehub.view.tree.cancelButton = function(action){
    if (action === 'show') {
      $('#bh-dir-tree-cancel').click(function(){
        nl.sara.beehub.controller.setCopyMoveView(false);
        nl.sara.beehub.view.tree.clearView();
      }); 
      $('#bh-dir-tree-cancel').show();
      return;
    };
    if (action === 'hide') {
      $('#bh-dir-tree-cancel').hide();
      return;
    }
  };
  
  /*
   * Close tree
   * 
   * Public function
   * 
   */
  nl.sara.beehub.view.tree.closeTree = function(){
    $(".bh-dir-tree-slide").hide();
    $(".bh-dir-tree-header").hide();
  };
  
  /*
   * Show tree
   * 
   * Public function
   * 
   */
  nl.sara.beehub.view.tree.showTree = function(){
    $(".bh-dir-tree-slide").slideDown('slow');
    $(".bh-dir-tree-header").show();
  };
  
  /*
   * Clear tree view
   * 
   * Public function
   * 
   */
  nl.sara.beehub.view.tree.clearView = function(){
    // original onactivate
    nl.sara.beehub.view.tree.setOnActivate("Browse", function(node){
      // Close tree otherwise a load error is shown
      nl.sara.beehub.view.tree.closeTree();
      window.location.href = node;
    });
    // close tree
    nl.sara.beehub.view.tree.slideTrigger('left');
    nl.sara.beehub.view.tree.closeTree();
  };
  
  /*
   * On click handler tree slide click
   * Open or close tree view
   */
  var handle_tree_slide_click = function() {
    $(".bh-dir-tree-slide").slideToggle("slow");
    $(".bh-dir-tree-header").toggle();
    $(this).toggleClass("active");
    $('.bh-dir-tree-slide-trigger i').toggleClass('icon-folder-open icon-folder-close');
    return false;
  };
  
  /*
   * Reload tree 
   * 
   * Public function
   * 
   */
  nl.sara.beehub.view.tree.reload = function(){
    $("#bh-dir-tree").dynatree("getTree").reload();
  };
  
  /*
   * Set onActivate
   * 
   * Public function
   *  
   * @param String    header              Header to show
   * @param Function  activationFunction  Activate function
   * 
   */
  nl.sara.beehub.view.tree.setOnActivate = function(header, activateFunction){
    $(".bh-dir-tree-header").html(header);
    $("#bh-dir-tree").dynatree({
      onActivate: function(node) {
        if (activateFunction !== undefined) {
          activateFunction(node.data.id);
        }
      }
    });
    nl.sara.beehub.view.tree.reload();
  };
})();