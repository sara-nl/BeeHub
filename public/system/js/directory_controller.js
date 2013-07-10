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

/*
 * Add slash to the end of the path
 */
nl.sara.beehub.controller.path = location.pathname;
if (!nl.sara.beehub.controller.path.match(/\/$/)) {
  nl.sara.beehub.controller.path=nl.sara.beehub.controller.path+'/'; 
} 

/*
 * Create new folder. When new foldername already exist add counter to the name
 * of the folder
 */
nl.sara.beehub.controller.createNewFolder = function(){
  var webdav = new nl.sara.webdav.Client();
  var foldername = 'new_folder';
  var counter = 0;

  /*
   * Create callback for webdav request
   */
  function createCallback() {
    return function(status) {
      if (status === 201) {
        // TODO zonder reload
        window.location.reload();
        return;
      };
      // Folder already exist
      if (status === 405){
        counter++;
        webdav.mkcol(nl.sara.beehub.controller.path+foldername+'_'+counter,createCallback());
        return;
      };
      // Forbidden
      if (status === 403) {
        nl.sara.beehub.view.dialog.showError("You are not allowed to create a new folder.");
      } else {
        nl.sara.beehub.view.dialog.showError("Unknown error.");
      }
    }
  };
  // Webdav request
  webdav.mkcol(nl.sara.beehub.controller.path+foldername,createCallback());
}  
