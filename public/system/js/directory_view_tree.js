/**
 * Copyright ©2013 SURFsara bv, The Netherlands
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
 * @author Niek Bosch (niek.bosch@surfsara.nl)
 */

(function(){

  var treeNode = $( "#bh-dir-tree ul.dynatree-container" );


  /*
   * Init tree
   * 
   * Public function
   */
  nl.sara.beehub.view.tree.init = function() {
    nl.sara.beehub.view.tree.attachEvents( treeNode );
    $(".bh-dir-tree-slide-trigger").unbind('click').click(handle_tree_slide_click);
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

  function treeExpandHandler( expander, callback ) {
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
      if ( callback !== undefined ) {
        callback();
      }
    }else{// if ( parent.hasClass( 'dynatree-has-children' ) ) {
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

        if ( callback !== undefined ) {
          callback();
        }
      }else{
        // If there is no list loaded yet; load one now!
        var url = expander.siblings( 'a' ).attr( 'href' );
        nl.sara.beehub.controller.getTreeNode( url, nl.sara.beehub.controller.createGetTreeNodeCallback(url, parent, expander));
      }
    }
  }
  
  nl.sara.beehub.view.tree.createTreeNode = function(data, url, parent, expander, callback){
    var childArray = [];
    var childCollections = {};
    for ( var pathindex in data.getResponseNames() ) {
      var path = data.getResponseNames()[pathindex];

      // We only want to add children and only if they are directories
      if ( ( url !== path) &&
           ( data.getResponse( path ).getProperty( 'DAV:','resourcetype' ) !== null ) &&
           ( nl.sara.webdav.codec.ResourcetypeCodec.COLLECTION === data.getResponse( path ).getProperty( 'DAV:','resourcetype' ).getParsedValue() )
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
      parent.addClass( 'dynatree-has-children' );
      expander.addClass( 'dynatree-expander' );
      expander.removeClass( 'dynatree-connector' );
      nl.sara.beehub.view.tree.attachEvents( parent );
      treeExpandHandler( expander, callback );
    }else{
      parent.removeClass( 'dynatree-has-children' );
      expander.removeClass( 'dynatree-expander' );
      expander.addClass( 'dynatree-connector' );
      expander.off( 'click' );

      if ( callback !== undefined ) {
        callback();
      }
    }
  };

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
      $("#bh-dir-tree-header").removeClass('bh-dir-nomask');
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
      $('#bh-dir-tree-cancel').unbind('click').click(function(){
        nl.sara.beehub.view.tree.setModal( false );
        nl.sara.beehub.controller.setCopyMoveView(false);
        nl.sara.beehub.view.tree.clearView();
      }); 
      $('#bh-dir-tree-cancel').show();
      return;
    };
    if (action === 'hide') {
      $('#bh-dir-tree-cancel').unbind('click');
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
    $('.bh-dir-tree-slide-trigger').removeClass("active");
    $('.bh-dir-tree-slide-trigger i').removeClass('icon-folder-open').addClass(' icon-folder-close');
    $(".bh-dir-tree-slide").slideUp('slow');
    $(".bh-dir-tree-header").hide();
  };
  
  /*
   * Show tree
   * 
   * Public function
   * 
   */
  nl.sara.beehub.view.tree.showTree = function(){
    $('.bh-dir-tree-slide-trigger').addClass("active");
    $('.bh-dir-tree-slide-trigger i').toggleClass('icon-folder-open icon-folder-close');
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
    if ( $.cookie( 'beehub-showtree' ) === "false" ) {
      nl.sara.beehub.view.tree.closeTree();
    }
  };
  
  /*
   * On click handler tree slide click
   * Open or close tree view
   */
  var handle_tree_slide_click = function() {
    if ( $( this ).hasClass( 'active' ) ) {
      $.cookie( "beehub-showtree", "false" , { path: '/' } );
      nl.sara.beehub.view.tree.closeTree();
    }else{
      $.cookie( "beehub-showtree", "true" , { path: '/' } );
      nl.sara.beehub.view.tree.showTree();
    }
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

    // Determine all parents
    var parents;
    var parentPath = path.substr( 1, path.substr( 0, path.length - 1 ).lastIndexOf( '/' ) - 1 );
    if ( parentPath !== '' ) {
      parents = parentPath.split( '/' );
    }else{
      parents = [];
    }

    // Open all parents and when that's done; add the directory
    expandRecursive( parents, '/', function() {
      addDirectory( path );
    } );
  };
  
  
  function expandRecursive( parents, expandedPath, callback ) {
    // If we have nothing to open anymore; call the callback!
    if ( parents.length === 0 ) {
      if ( callback !== undefined ) {
        callback();
      }
      return;
    }

    // Determine which path we want to extend now
    expandedPath += parents.shift() + '/';
    var parentLink = $( 'a[href="' + encodeURI( expandedPath ) + '"]', treeNode );
    if ( parentLink.length === 0 ) {
      throw "Unable to add directory to the tree: parent directory does not exist";
    }
    // It exists, let's expand this directory if it is not expanded already
    var parentSpan = parentLink.parent('span');
    if ( ! parentSpan.hasClass( 'dynatree-expanded' ) ) {
      var expander = $( '.dynatree-expander', parentSpan );
      if ( expander.length === 0 ) {
        expander = $( '.dynatree-connector', parentSpan );
      }
      treeExpandHandler( expander, function() {
        expandRecursive( parents, expandedPath, callback );
      } );
    }else{
      expandRecursive( parents, expandedPath, callback );
    }
  }


  function addDirectory( path ) {
    // Start with checking if the path doesn't exist yet (now all parents are expanded)
    if ( $( 'a[href="' + encodeURI( path ) + '"]', treeNode ).length > 0 ) {
      return;
    }

    var parentPath = path.substr( 0, path.substr( 0, path.length - 1 ).lastIndexOf( '/' ) + 1 );
    var parentLi;
    var parentSpan;
    var list;
    if ( parentPath !== '/' ) {
      parentSpan = $( 'a[href="' + encodeURI( parentPath ) + '"]', treeNode ).parent('span');
      parentLi = parentSpan.parent( 'li' );

      // Get the list
      list = parentLi.children( 'ul' );
      if ( list.length === 0 ) {
        list = $( '<ul></ul>' );
        parentLi.append( list );
      }
    }else{
      list = treeNode;
    }

    // Put the new directory in the right (sorted) place in the list
    var directoryToAdd = path.substring( parentPath.length, path.length - 1 );
    var listElements = $( 'li', list );
    var nextElement = undefined;
    listElements.each( function() {
      if ( ( nextElement === undefined ) && ( $( 'a', this ).text() > directoryToAdd ) ) {
        nextElement = $( this );
      }
    } );

    // Then insert the new element at the right location
    if ( nextElement === undefined ) {
      // It is the last element, so make the current last element not a 'last element' (huh? well, it works)
      if ( listElements.length > 0 ) {
        var lastElement = listElements.last();
        lastElement.removeClass( 'dynatree-lastsib' );
        var lastElementSpan = lastElement.children( 'span' );
        lastElementSpan.removeClass( 'dynatree-lastsib' );
        if ( lastElementSpan.hasClass( 'dynatree-exp-el' ) ) {
          lastElementSpan.removeClass( 'dynatree-exp-el' );
          lastElementSpan.addClass( 'dynatree-exp-e' );
        }else if ( lastElementSpan.hasClass( 'dynatree-exp-cl' ) ) {
          lastElementSpan.removeClass( 'dynatree-exp-cl' );
          lastElementSpan.addClass( 'dynatree-exp-c' );
        }else{
          lastElementSpan.removeClass( 'dynatree-exp-cdl' );
          lastElementSpan.addClass( 'dynatree-exp-cd' );
        }
      }else if ( parentPath !== '/' ) { // The parent didn't have any children before, but does have one now!
        var expander = $( '.dynatree-connector', parentSpan );
        expander.removeClass( 'dynatree-connector' );
        expander.addClass( 'dynatree-expander' );
        parentSpan.removeClass( 'dynatree-lazy' );
        parentSpan.removeClass( 'dynatree-exp-cl' );
        parentSpan.removeClass( 'dynatree-exp-cdl' );
        parentSpan.removeClass( 'dynatree-exp-c' );
        parentSpan.removeClass( 'dynatree-exp-cd' );
        parentSpan.removeClass( 'dynatree-exp-el' );
        parentSpan.removeClass( 'dynatree-exp-e' );
        parentSpan.removeClass( 'dynatree-ico-cf' );
        if ( parentSpan.hasClass( 'dynatree-lastsib' ) ) {
          parentSpan.addClass( 'dynatree-exp-el' );
        }else{
          parentSpan.addClass( 'dynatree-exp-e' );
        }
        parentSpan.addClass( 'dynatree-ico-ef' );
        parentSpan.addClass( 'dynatree-expanded' );
        parentSpan.addClass( 'dynatree-has-children' );
        nl.sara.beehub.view.tree.attachEvents( parentLi );
      }
      list.append( createTreeElement( encodeURI( path ), true ) );
    }else{
      nextElement.before( createTreeElement( encodeURI( path ), false ) );
    }
  }


  nl.sara.beehub.view.tree.removePath = function( path ) {
    // Normalize the path
    if ( path.substr( 0, 1 ) !== '/' ) {
      path = '/' + path;
    }
    if ( path.substr( -1 ) !== '/' ) {
      path += '/';
    }

    // Remove the list element representing the path to be deleted
    var pathLink = $( 'a[href="' + encodeURI( path ) + '"]', treeNode );
    if ( pathLink.length === 0 ) {
      // This path doesn't exist, so we're done without doing anything :)
      return;
    }
    var pathLi = pathLink.parent('span').parent( 'li' );
    var parentUl = pathLi.parent( 'ul' );
    pathLi.remove();

    // Then check if the parent is still correct
    var siblingsLi = $( 'li', parentUl );
    if ( ( siblingsLi.length === 0 ) && ( path !== '/' ) ) {
      // No more subdirectories for the parent, so make sure it uses the right icons
      var parentSpan = parentUl.siblings( 'span' );
      // Collapse the parent tree
      if ( parentSpan.hasClass( 'dynatree-expanded' ) ) {
        treeExpandHandler( $( '.dynatree-expander', parentSpan ) );
      }
      parentSpan.removeClass( 'dynatree-has-children' );
      var expander = $( '.dynatree-expander', parentSpan );
      expander.removeClass( 'dynatree-expander' );
      expander.addClass( 'dynatree-connector' );
      expander.off( 'click' );
    }else{
      // There still are some subdirectories, so let's just make sure the last one knows it is the last one
      var lastSibling = siblingsLi.last();
      lastSibling.addClass( 'dynatree-lastsib' );
      var elementSpan = $( '.dynatree-node', lastSibling );
      elementSpan.addClass( 'dynatree-lastsib' );
      if ( elementSpan.hasClass( 'dynatree-exp-cd' ) ) {
        elementSpan.removeClass( 'dynatree-exp-cd' );
        elementSpan.addClass( 'dynatree-exp-cdl' );
      }else if ( elementSpan.hasClass( 'dynatree-exp-c' ) ) {
        elementSpan.removeClass( 'dynatree-exp-c' );
        elementSpan.addClass( 'dynatree-exp-cl' );
      }else if ( elementSpan.hasClass( 'dynatree-exp-e' ) ) {
        elementSpan.removeClass( 'dynatree-exp-e' );
        elementSpan.addClass( 'dynatree-exp-el' );
      }
    }
  };
 

  /*
   * Update resource from content view
   * 
   * Public function
   * 
   * @param {Object} resourceOrg Original resource object
   * @param {Object} resourceOrg New resource object
   */
  nl.sara.beehub.view.tree.updateResource = function(resourceOrg, resourceNew){
    if ( resourceOrg.type === 'collection' ) {
      // delete current row
      nl.sara.beehub.view.tree.removePath( decodeURI( resourceOrg.path ) );
    }
    if ( resourceNew.type === 'collection' ) {
      // add new row
      nl.sara.beehub.view.tree.addPath( decodeURI( resourceNew.path ) );
    }
  };
   
  /**
   * Change when the directory tree is modal
   * 
   * @param {Boolean} modal  If set to true, the directory tree will be modal
   */
  nl.sara.beehub.view.tree.setModal = function ( modal ) {
    var treeHeader = $( '#bh-dir-tree-header' );
    var treeElements = treeHeader
            .add( 'a.bh-dir-tree-slide-trigger' )
            .add ( '#bh-dir-tree' );
    if ( modal ) {
      treeElements.addClass( 'ui-front' );
      treeHeader.before( '<div class="bh-tree-overlay ui-widget-overlay ui-front"></div>' );
    }else{
      $( 'div.bh-tree-overlay').remove();
      treeElements.removeClass( 'ui-front' );
    }
  };

})();
