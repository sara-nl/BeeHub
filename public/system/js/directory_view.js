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

// TODO vast ergens uit te lezen
nl.sara.beehub.view.userspath = '/system/users/';
nl.sara.beehub.view.groupspath = '/system/groups/';
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
 * Mask view 
 */
nl.sara.beehub.view.maskView = function(mask){
  if (mask) { 
    $("#bh-dir-all").show();
  } else {
    $("#bh-dir-all").hide();
  }  
}
/*
 * Returns displayname from object
 * 
 * @param String {name} object
 * 
 * @return String Displayname
 */
nl.sara.beehub.view.getDisplayName = function(name){
  if (name === undefined) {
    return "";
  };
  if (name.indexOf(nl.sara.beehub.view.userspath) != -1){
//  if (name.contains(nl.sara.beehub.view.userspath)) {
    var displayName = nl.sara.beehub.principals.users[name.replace(nl.sara.beehub.view.userspath,'')];
    return displayName;
  };
  if (name.indexOf(nl.sara.beehub.view.groupsspath) != -1){
//  if (name.contains(nl.sara.beehub.view.groupspath)) {
    var displayName = nl.sara.beehub.principals.groups[name.replace(nl.sara.beehub.view.groupspath,'')];
    return displayName;
  };
}

/**
 * Convert number of bytes into human readable format
 *
 * @param integer bytes     Number of bytes to convert
 * @param integer precision Number of digits after the decimal separator
 * @return string
 */
nl.sara.beehub.view.bytesToSize = function(bytes, precision)
{  
    var kilobyte = 1024;
    var megabyte = kilobyte * 1024;
    var gigabyte = megabyte * 1024;
    var terabyte = gigabyte * 1024;
   
    if ((bytes >= 0) && (bytes < kilobyte)) {
        return bytes + ' B';
 
    } else if ((bytes >= kilobyte) && (bytes < megabyte)) {
        return (bytes / kilobyte).toFixed(precision) + ' KB';
 
    } else if ((bytes >= megabyte) && (bytes < gigabyte)) {
        return (bytes / megabyte).toFixed(precision) + ' MB';
 
    } else if ((bytes >= gigabyte) && (bytes < terabyte)) {
        return (bytes / gigabyte).toFixed(precision) + ' GB';
 
    } else if (bytes >= terabyte) {
        return (bytes / terabyte).toFixed(precision) + ' TB';
 
    } else {
        return bytes + ' B';
    }
}
//
//nl.sara.beehub.view.getSize = function(resource){
//   // Calculate size
//  if (resource.contentlength !== ""){
//   var size = resource.contentlength;
//   if (size !== '' && size != 0) {
//     var unit = null;
//     units = ['B', 'KB', 'MB', 'GB', 'TB'];
//     $.each(units,function(i,value) {
//       if (size > 1024) {
//         size = size / 1024;
//       } else {
//         unit = units[i];
//       }
//     }
//     showsize = round(size, 0) + ' ' + unit;
//   } else {
//     showsize = '';
//   }
//   size = size;
//  } else {
//   size = contentlength;
//  }
//  return size;
//}

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

