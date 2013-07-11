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

// If nl.sara.webdav.ClientResource is already defined, we have a namespace clash!
if (nl.sara.beehub.ClientResource !== undefined) {
  throw new nl.sara.webdav.Exception('Namespace nl.sara.webdav.ClientResource already taken, could not load JavaScript library for WebDAV connectivity.', nl.sara.webdav.Exception.NAMESPACE_TAKEN);
}

/**
 * @class Webdav Resource
 *
 * @param  {String}   name            The name of the resource
 * @param  {Boolean}  isDir           True when resource is a directory
 */
nl.sara.beehub.ClientResource = function(path) {
  this.path = path;
}

/**
 * Set type
 * 
 * @param {String} type Resource type
 */
nl.sara.beehub.ClientResource.prototype.setResourceType = function(type){
  this.type = type;
};

/**
 * Set displayname
 * 
 * @param {String} displayname Displayname of resource
 */
nl.sara.beehub.ClientResource.prototype.setDisplayName = function(displayname){
  this.displayname = displayname;
};

/**
 * Set owner
 * 
 * @param {String} owner Owner of resource
 */
nl.sara.beehub.ClientResource.prototype.setOwner = function(owner){
  this.owner = owner;
};

/**
 * Set lastmodified
 * 
 * @param {String} lastmodified Last modified date of resource
 */
nl.sara.beehub.ClientResource.prototype.setLastModified = function(lastmodified){
  this.lastmodified = lastmodified;
};

/**
 * Set size
 * Calculates size from contentlength
 * 
 * @param {String} contentlength Contentlength of resource
 */
nl.sara.beehub.ClientResource.prototype.setSize = function(contentlength){
  // Calculate size
  // TODO nog niet getest
  if (contentlength !== ""){
    var size = contentlength;
    if (size !== '' && size != 0) {
      var unit = null;
      units = array('B', 'KB', 'MB', 'GB', 'TB');
      for (var i = 0, c = count(units); i < c; i++) {
        if (size > 1024) {
          size = size / 1024;
        } else {
          unit = units[i];
          break;
        }
      }
      showsize = round(size, 0) + ' ' + unit;
    } else {
      showsize = '';
    }
    this.size = size;
  } else {
    this.size = contentlength;
  }
};