/**
 * Beehub Client View
 *
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
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */

"use strict";

/**
 * Initialize all views
 * 
 */
nl.sara.beehub.view.init = function() {
  // Init content view
  nl.sara.beehub.view.content.init();
  
  // Init tree view
  nl.sara.beehub.view.tree.init();
  
  // Init acl view
  nl.sara.beehub.view.acl.init();
  
  // Change tab listeners
  // Content tab
  $('a[href="#bh-dir-panel-contents"]').unbind('click').click(function(e){
    if ( $.cookie( "beehub-showtree" ) !== "false" ) {
      nl.sara.beehub.view.tree.showTree();
    }
    nl.sara.beehub.view.showFixedButtons('content');
  });
  // Acl tab
  $('a[href="#bh-dir-panel-acl"]').unbind('click').click(function(e){
    nl.sara.beehub.view.tree.closeTree();
    nl.sara.beehub.view.showFixedButtons('acl');
  });
  // Usage tab
  $('a[href="#bh-dir-panel-usage"]').unbind('click').click(function(e){
    nl.sara.beehub.view.tree.closeTree();
    nl.sara.beehub.view.showFixedButtons('usage');
    nl.sara.beehub.view.user_usage.createView();
  });
};

/*
 * Clear all views
 * 
 */
nl.sara.beehub.view.clearAllViews = function(){
  // Content
  nl.sara.beehub.view.content.clearView();
  // Tree
  nl.sara.beehub.view.tree.clearView();
  // Dialog
  nl.sara.beehub.view.dialog.clearView();
}; 

/*
 * Mask view, disable input
 * 
 * @param {String} type Mask type
 * @param Boolean mask true or false
 */
nl.sara.beehub.view.maskView = function(type , show){
  switch (type) {
    case  "mask":
      if (show) { 
        $("#bh-dir-mask").show();
      } else {
        $("#bh-dir-mask").hide();
      } 
      break;
    case "transparant":
      if (show) { 
        $("#bh-dir-mask-transparant").show();
      } else {
        $("#bh-dir-mask-transparant").hide();
      } 
      break;
    case "loading":
      if (show) { 
        $("#bh-dir-mask-loading").show();
      } else {
        $("#bh-dir-mask-loading").hide();
      } 
      break;
  } 
};

/*
 * Hide all masks
 * 
 */
nl.sara.beehub.view.hideMasks = function(){
  $("#bh-dir-mask").hide();
  $("#bh-dir-mask-transparant").hide();
  $("#bh-dir-mask-loading").hide();
};

/*
 * Show buttons that belong to a tab, acl or content
 * 
 * @param String action 'acl' or 'content'
 * 
 */
nl.sara.beehub.view.showFixedButtons = function(action){
  nl.sara.beehub.view.hideAllFixedButtons();
  switch(action)
  {
    case 'acl':
      nl.sara.beehub.view.acl.allFixedButtons('show');
      break;
    case 'content':
      nl.sara.beehub.view.content.allFixedButtons('show');
      break;
    case 'usage':
      break;
    default:
      // This should never happen
  };
};

/*
 * Hide all fixed buttons
 * 
 */
nl.sara.beehub.view.hideAllFixedButtons = function(){
  nl.sara.beehub.view.content.allFixedButtons('hide');
  nl.sara.beehub.view.acl.allFixedButtons('hide');
}

/*
 * Add resource to all views
 * 
 * @param {Object} resource Resource object
 */
nl.sara.beehub.view.addResource = function(resource){
  nl.sara.beehub.view.content.addResource(resource);
  if ( resource.type === 'collection' ) {
    nl.sara.beehub.view.tree.addPath( decodeURI( resource.path ) );
  }
};

/*
 * Update resource to all views
 * 
 * @param {Object} resource Resource object
 */
nl.sara.beehub.view.updateResource = function(resourceOrg, resourceNew){
  nl.sara.beehub.view.content.updateResource(resourceOrg, resourceNew);
  nl.sara.beehub.view.tree.updateResource( resourceOrg, resourceNew );
};

/*
 * Delete resource from all views
 * 
 * @param {Object} resource Resource object
 */
nl.sara.beehub.view.deleteResource = function(resource){
  nl.sara.beehub.view.content.deleteResource(resource);
  nl.sara.beehub.view.tree.removePath( decodeURI( resource.path ) );
};

/*
 * Set Custom acl on resource on or off
 * 
 * @param {Boolean} ownAcl True or false
 */
nl.sara.beehub.view.setCustomAclOnResource = function(ownACL, resourcePath){
  nl.sara.beehub.view.content.setCustomAclOnResource(ownACL, resourcePath);
};

