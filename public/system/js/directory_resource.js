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
 * @class Webdav Resource
 *
 * @param  {String}   path            The path of the resource
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 * 
 */
nl.sara.beehub.ClientResource = function(path) {
  this.path = path;
}

/**
 * Set type
 * 
 * @param {String} type Resource type
 */
nl.sara.beehub.ClientResource.prototype.setType = function(type){
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
 * Set setcontentlength
 * 
 * @param {String} setcontentlength Length of the content
 */
nl.sara.beehub.ClientResource.prototype.setContentLength = function(contentlength){
  this.contentlength = contentlength;
};

