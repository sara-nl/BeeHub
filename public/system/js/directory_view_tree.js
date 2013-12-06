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

"use strict";

/**
 * Beehub Client Tree
 * 
 * Directory tree
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */

(function(){

  var treeNode = $( "#bh-dir-tree" );


  /*
   * Init tree
   * 
   * Public function
   */
  nl.sara.beehub.view.tree.init = function() {
    nl.sara.beehub.view.tree.attachEvents( treeNode );
    $(".bh-dir-tree-slide-trigger").click(handle_tree_slide_click);
  };

  var directoryClickHandlerAlternative = null;

  function directoryClickHandler() {
    var link = $( this );

    // If there is an alter handler has been defined, use call that one
    if ( directoryClickHandlerAlternative !== null ) {
      directoryClickHandlerAlternative( link.attr( 'href' ) );
      return false;
    }

    // Else the default action will be called; follow the link!
  }

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
    if (activateFunction !== undefined) {
      directoryClickHandlerAlternative = activateFunction;
    }else{
      directoryClickHandlerAlternative = null;
    }
  };

  nl.sara.beehub.view.tree.attachEvents = function( list ) {
    var expanders = $( '.dynatree-expander', list );
    expanders.off( 'click' );
    expanders.on( 'click', function() { treeExpandHandler( $( this ) ); } );
    var links = $( 'a', list );
    links.off( 'click' );
    links.on( 'click', directoryClickHandler );
  };

  function treeExpandHandler( expander ) {
    var parent = expander.parent();
    if ( parent.hasClass( 'dynatree-expanded' ) ) {
      // The subtree is expanded, so we need to collapse it
      // Hide the sub list
      parent.siblings( 'ul' ).hide();

      //And change some classes so the right icons are shown
      parent.removeClass( 'dynatree-exp-el' );
      parent.removeClass( 'dynatree-exp-e' );
      parent.removeClass( 'dynatree-ico-ef' );
      parent.removeClass( 'dynatree-expanded' );
      if ( parent.hasClass( 'dynatree-lastsib' ) ) {
        parent.addClass( 'dynatree-exp-cl' );
      }else{
        parent.addClass( 'dynatree-exp-c' );
      }
      parent.addClass( 'dynatree-ico-cf' );
    }else if ( parent.hasClass( 'dynatree-has-children' ) ) {
      // The subtree is collapsed, so we need to expand it
      var list = parent.siblings( 'ul' );
      if ( list.length > 0 ) {
        // If there is already a list loaded; just use that one
        list.show();

        // Change some classes so the right icons are shown
        parent.removeClass( 'dynatree-exp-cl' );
        parent.removeClass( 'dynatree-exp-cdl' );
        parent.removeClass( 'dynatree-exp-c' );
        parent.removeClass( 'dynatree-exp-cd' );
        parent.removeClass( 'dynatree-ico-cf' );
        if ( parent.hasClass( 'dynatree-lastsib' ) ) {
          parent.addClass( 'dynatree-exp-el' );
        }else{
          parent.addClass( 'dynatree-exp-e' );
        }
        parent.addClass( 'dynatree-ico-ef' );
        parent.addClass( 'dynatree-expanded' );
      }else{
        // If there is no list loaded yet; load one now!
        var url = expander.siblings( 'a' ).attr( 'href' );
        nl.sara.beehub.controller.getTreeNode( url, function( status, data ) {
          // Callback
          if (status !== 207) {
            alert( 'Could not load the subdirectories' );
            return;
          };

          var childArray = [];
          var childCollections = {};
          for ( var pathindex in data.getResponseNames() ) {
            var path = data.getResponseNames()[pathindex];

            // We only want to add children and only if they are directories
            if ( ( url !== path) &&
                 ( data.getResponse(path).getProperty('DAV:','resourcetype') !== null ) &&
                 ( nl.sara.webdav.codec.ResourcetypeCodec.COLLECTION === data.getResponse(path).getProperty('DAV:','resourcetype').getParsedValue() )
               )
            {
              childArray.push( path.toLowerCase() );
              childCollections[ path.toLowerCase() ] = path;
            }
          }

          childArray.sort();
          var list = $( '<ul></ul>' );
          for ( var index in childArray ) {
            var path = childCollections[ childArray[ index ] ];
            var element = createTreeElement( path, ( parseInt( index ) === ( childArray.length - 1 ) ) );
            list.append( element );
          }

          // Once expanded, some attribute will never apply anymore
          parent.removeClass( 'dynatree-lazy' );
          if ( list.children().length > 0 ) {
            parent.after( list );
            treeExpandHandler( expander );
          }else{
            parent.removeClass( 'dynatree-has-children' );
            expander.removeClass( 'dynatree-expander' );
            expander.addClass( 'dynatree-connector' );
            expander.off( 'click' );
          }
        } );
      }
    }
  }

  function createTreeElement( path, last ) {
    var name = path;
    while (name.substring(name.length-1) === '/') {
      name = name.substr(0, name.length-1);
    };
    name = decodeURIComponent( name.substr( name.lastIndexOf( '/' ) + 1 ) );
    
    var element = $( '<li></li>' );
    if ( last)  {
      element.addClass( 'dynatree-lastsib' );
    }
    var elementSpan = $( '<span class="dynatree-node dynatree-folder dynatree-has-children dynatree-lazy dynatree-ico-cf"></span>' );
    if ( last ) {
      elementSpan.addClass( 'dynatree-exp-cdl' );
      elementSpan.addClass( 'dynatree-lastsib' );
    }else{
      elementSpan.addClass( 'dynatree-exp-cd' );
    }
    var expanderSpan = $( '<span class="dynatree-expander"></span>' );
    var iconSpan = $( '<span class="dynatree-icon"></span>' );
    var link = $( '<a class="dynatree-title"></a>' );
    link.attr( 'href', path );
    link.text( name );
    elementSpan.append( expanderSpan );
    elementSpan.append( iconSpan );
    elementSpan.append( link );
    element.append( elementSpan );
    nl.sara.beehub.view.tree.attachEvents( element );

    return element;
  }
  
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
    $.cookie("beehub-showtree", "false", { path: '/' });
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
    $.cookie("beehub-showtree", "true", { path: '/' });
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
    nl.sara.beehub.view.tree.setOnActivate( "Browse" );
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
    var obj = $(this);
    obj.toggleClass("active");
    $.cookie( "beehub-showtree", ( ( obj.hasClass( 'active' ) ) ? 'true' : 'false' ), { path: '/' } );
    $('.bh-dir-tree-slide-trigger i').toggleClass('icon-folder-open icon-folder-close');

    return false;
  };


  nl.sara.beehub.view.tree.addPath = function( path ){
    // Normalize the path
    if ( path.substr( 0, 1 ) !== '/' ) {
      path = '/' + path;
    }
    if ( path.substr( -1 ) !== '/' ) {
      path += '/';
    }

    // Determine which parent does exist in the tree
    var existingParent = path;
    while ( $( 'a[href="' + existingParent + '"]', treeNode ).length === 0 ) {
      existingParent = existingParent.substr( 0, existingParent.length - 1 );
      existingParent = existingParent.substr( 0, existingParent.lastIndexOf( '/' ) + 1 );
      if ( existingParent === '/' ) {
        break;
      }
    }
    if ( existingParent === path ) {
      // The new path is already known in the tree, so nothing to do!
      return;
    }

    // Then add all parents that don't exist
    var parentsToAdd = path.substr( existingParent.length ).split( '/' );
    var parentSpan = $( 'a[href="' + existingParent + '"]', treeNode ).parent( 'span' );
    var parent = parentSpan.parent( 'li' );
    if ( parentSpan.hasClass( 'dynatree-expanded' ) ) {
      $( '.dynatree-expander', parent ).click();
    }
    for ( var index in parentsToAdd ) {
      // First get the list
      var list = parent.children( 'ul' );
      var last = false;
      if ( list.length === 0 ) {
        list = $( '<ul></ul>' );
        parent.append( list );
        last = true;
      }else{
        // TODO put the new directory in the right (sorted) place in the list
      }

      // Then add the current parent and update 'existingParent' and 'parent' variables
      existingParent += parentsToAdd[ index ] + '/';
      parent = createTreeElement( existingParent, last );
      list.append( parent );
    }
  };

})();