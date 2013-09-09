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
 * Beehub Client View
 * 
 * All views
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */

/*
 * Initialize all views
 * 
 */
nl.sara.beehub.view.init = function() {
  nl.sara.beehub.view.content.init();
  nl.sara.beehub.view.tree.init();
  nl.sara.beehub.view.acl.init();
}

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
 * @param Boolean mask true or false
 */
nl.sara.beehub.view.maskView = function(mask){
  if (mask) { 
    $("#bh-dir-all").show();
  } else {
    $("#bh-dir-all").hide();
  }  
}

/*
 * Show buttons that belong to a tab, acl or content
 * 
 * @param String action 'acl' or 'content'
 * 
 */
nl.sara.beehub.view.showFixedButtons = function(action){
  switch(action)
  {
    case 'acl':
      nl.sara.beehub.view.content.showFixedButtons('hide');
      nl.sara.beehub.view.acl.showFixedButtons('show');
      break;
    case 'content':
      nl.sara.beehub.view.acl.showFixedButtons('hide');
      nl.sara.beehub.view.content.showFixedButtons('show');
      break;
    default:
      // This should never happen
  };
};

/*
 * Add resource to all views
 * 
 * @param {Object} resource Resource object
 */
nl.sara.beehub.view.addResource = function(resource){
  nl.sara.beehub.view.content.addResource(resource);
};

/*
 * Update resource to all views
 * 
 * @param {Object} resource Resource object
 */
nl.sara.beehub.view.updateResource = function(resourceOrg, resourceNew){
  nl.sara.beehub.view.content.updateResource(resourceOrg, resourceNew);
};

/*
 * Delete resource from all views
 * 
 * @param {Object} resource Resource object
 */
nl.sara.beehub.view.deleteResource = function(resource){
  nl.sara.beehub.view.content.deleteResource(resource);
};

