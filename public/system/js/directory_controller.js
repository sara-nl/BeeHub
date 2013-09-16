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
 * Beehub Client Controller
 * 
 * The controller communicates with the webdav server and knows all views
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */


(function() {
  /*
   * Add slash to the end of the path
   */
  var path = location.pathname;
  if (!path.match(/\/$/)) {
    path=path+'/'; 
  } 
  // Needed for copy, move, delete and upload
  var actionCounter = 0;
  
  /*
   * Clear all views
   * 
   * Public function
   * 
   */
  nl.sara.beehub.controller.clearAllViews = function(){
    nl.sara.beehub.view.clearAllViews();
  };
  
  /*
   * Returns displayname from object
   * 
   * @param   {String}  name  object
   * 
   * @return  {String}        Displayname
   */
  nl.sara.beehub.controller.getDisplayName = function(name){
    if (name === undefined) {
      return "";
    };
    if (name.indexOf(nl.sara.beehub.users_path) !== -1){
      var displayName = nl.sara.beehub.principals.users[name.replace(nl.sara.beehub.users_path,'')];
      return displayName;
    };
    if (name.indexOf(nl.sara.beehub.groups_path) !== -1){
      var displayName = nl.sara.beehub.principals.groups[name.replace(nl.sara.beehub.groups_path,'')];
      return displayName;
    };
  };
  
  
  /**
   * Convert number of bytes into human readable format
   * 
   * Public function
   *
   * @param    {Integer}  bytes      Number of bytes to convert
   * @param    {Integer}  precision  Number of digits after the decimal separator
   * @returns  {String}
   */
  nl.sara.beehub.controller.bytesToSize = function(bytes, precision)
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
  };
  
  /*
   * Extract properties from webdav library response to resource object
   * 
   * @param   {nl.sara.webdav.Multistatus}     data  Webdav propfind response
   * 
   * @return  {nl.sara.beehub.ClientResource}        Resource object
   */
  var extractPropsFromPropfindRequest = function(data){
    var path = data.getResponseNames()[0];
    
    var resource = new nl.sara.beehub.ClientResource(path);
    
    // Get type
    if (data.getResponse(path).getProperty('DAV:','resourcetype').getParsedValue() !== null) {
      if (nl.sara.webdav.codec.ResourcetypeCodec.COLLECTION === data.getResponse(path).getProperty('DAV:','resourcetype').getParsedValue()) {
        resource.type = "collection";
      };
    } else if (data.getResponse(path).getProperty('DAV:','getcontenttype').getParsedValue() !== null) {
      resource.type = data.getResponse(path).getProperty('DAV:','getcontenttype').getParsedValue();
    }; 
    
    // Get displayname
    if (data.getResponse(path).getProperty('DAV:','displayname').getParsedValue() !== null) {
      resource.displayname = data.getResponse(path).getProperty('DAV:','displayname').getParsedValue();
    };
    // Get owner
    if (data.getResponse(path).getProperty('DAV:','owner').getParsedValue() !== null) {
      resource.owner = data.getResponse(path).getProperty('DAV:','owner').getParsedValue();
    };
    
    // Get last modified date
    if (data.getResponse(path).getProperty('DAV:','getlastmodified').getParsedValue() !== null) {
      resource.lastmodified = data.getResponse(path).getProperty('DAV:','getlastmodified').getParsedValue();
    };
    
    // Get content length
    if (data.getResponse(path).getProperty('DAV:','getcontentlength').getParsedValue() !== null) {
      resource.contentlength = data.getResponse(path).getProperty('DAV:','getcontentlength').getParsedValue();
    };
    return resource;
  };
  
  /*
   * Collect resource details from server and call callback function 
   * after ajax call is finished
   * 
   * @param {String}    resourcepath  Resource path
   * @param {Function}  callback      Callback function
   */
  var getResourcePropsFromServer = function(resourcepath, callback){ 
    // Create properties
    // Resource type
    var resourcetypeProp = new nl.sara.webdav.Property();
    resourcetypeProp.tagname = 'resourcetype';
    resourcetypeProp.namespace='DAV:';
    
    // Contenttype
    var getcontenttypeProp = new nl.sara.webdav.Property();
    getcontenttypeProp.tagname = 'getcontenttype';
    getcontenttypeProp.namespace='DAV:';
    
    // Displayname
    var displaynameProp = new nl.sara.webdav.Property();
    displaynameProp.tagname = 'displayname';
    displaynameProp.namespace='DAV:';
    
    // Owner
    var ownerProp = new nl.sara.webdav.Property();
    ownerProp.tagname = 'owner';
    ownerProp.namespace='DAV:';
    
    // Last modified
    var getlastmodifiedProp = new nl.sara.webdav.Property();
    getlastmodifiedProp.tagname = 'getlastmodified';
    getlastmodifiedProp.namespace='DAV:';
    
    // Content length
    var getcontentlengthProp = new nl.sara.webdav.Property();
    getcontentlengthProp.tagname = 'getcontentlength';
    getcontentlengthProp.namespace='DAV:';
    
    // Put properties in array
    var properties = [resourcetypeProp, getcontenttypeProp, displaynameProp, ownerProp, getlastmodifiedProp, getcontentlengthProp];
    
    /*
     * Create callback for property request
     */
    function createCallback(){
      return function(status, data) {
        if (status !== 207) {
          nl.sara.beehub.view.dialog.showError("Unknown error.");
          return;
        };
        // Put properties in a resource object
        var resource = extractPropsFromPropfindRequest(data);
        // Callback function
        callback(resource);
      };
    };
    
    // Property request
    var webdav = new nl.sara.webdav.Client();
    webdav.propfind(resourcepath, createCallback() ,1,properties);
  };
  
  /*
   * Get tree node from server
   * 
   * Public function
   * 
   * @param  {String}    path      Tree path
   * @param  {Function}  callback  Callback function
   */
  nl.sara.beehub.controller.getTreeNode = function(path, callback){
    var client = new nl.sara.webdav.Client();
    var resourcetypeProp = new nl.sara.webdav.Property();
    resourcetypeProp.tagname = 'resourcetype';
    resourcetypeProp.namespace='DAV:';
    var properties = [resourcetypeProp];
    client.propfind(path, callback ,1,properties);
  };
  
  // CREATE NEW FOLDER
  /*
   * Create new folder. When new foldername already exist add counter to the name
   * of the folder.
   * 
   * Public function
   * 
   * 
   */
  nl.sara.beehub.controller.createNewFolder = function(){
    // Init foldername and counter, used in callback
    var foldername = 'new_folder';
    var counter = 0;
    
    // Webdav request
    var webdav = new nl.sara.webdav.Client();
    webdav.mkcol(path+foldername, createNewFolderCallback(counter, foldername));
  };
  
  /*
   * Create callback for create new folder request
   * 
   * @param {Integer} counter     Used to make the new foldername
   * @param {String}  foldername  Initial foldername
   * 
   */
  var createNewFolderCallback = function(counter, foldername) {
    return function(status, callbackpath) {
      // Success
      if (status === 201) {
        // Reload tree
        // TODO update tree instead of reloading
        nl.sara.beehub.view.tree.reload();
        // Get properties of new directory from server and update view
        getResourcePropsFromServer(callbackpath, function(resource){
          // add resource to view
          nl.sara.beehub.view.addResource(resource);
          // trigger rename click so the user can immediately rename the folder to whatever name it should have
          nl.sara.beehub.view.content.triggerRenameClick(resource);
        });
        return;
      };
      // Folder already exist, change name and make new call
      if (status === 405){
        counter++;
        var webdav = new nl.sara.webdav.Client();
        webdav.mkcol(path+foldername+'_'+counter, createNewFolderCallback(counter, foldername));
        return;
      };
      // Forbidden
      if (status === 403) {
        nl.sara.beehub.view.dialog.showError("You are not allowed to create a new folder.");
      } else {
        nl.sara.beehub.view.dialog.showError("Unknown error.");
      }
    };
  };
  
  // RENAME
  /**
   * Rename an object.
   * 
   * Public function
   * 
   * @param  {Object}   resource       Resource to rename
   * @param  {String}   fileNameNew    New resource name
   * @param  {Integer}  overwriteMode  Fail on overwrite or force overwrite
   * 
   */
  nl.sara.beehub.controller.renameResource = function(resource, fileNameNew, overwriteMode){
    var webdav = new nl.sara.webdav.Client();
    webdav.move(resource.path, createRenameCallback(resource, fileNameNew, overwriteMode), path +fileNameNew,  overwriteMode);
  };
  
  /**
   * Rename callback.
   * 
   * Shows overwrite dialog when resource already exist. When rename is successfull,
   * update view.
   * 
   * @param  {Object}   resource       Resource to rename
   * @param  {String}   fileNameNew    New resource name
   * @param  {Integer}  overwriteMode  Fail on overwrite or overwrite
   * 
   */
  var createRenameCallback = function(resource, fileNameNew, overwriteMode) {
    return function(status) {
      // Resource already exist
      if (status === 412) {
        // show overwrite dialog
        nl.sara.beehub.view.dialog.showOverwriteDialog(resource, fileNameNew, function(){
          nl.sara.beehub.controller.renameResource(resource, fileNameNew,  nl.sara.webdav.Client.SILENT_OVERWRITE);
        });
      } 
      // Succeeded
      if (status === 201 || status === 204) {
        // get unknown values of original
        resource = nl.sara.beehub.view.content.getUnknownResourceValues(resource); 
        // new path
        var newPath = path+fileNameNew;
        // new resource
        var resourceNew = new nl.sara.beehub.ClientResource(newPath);
        resourceNew.displayname   = fileNameNew;
        resourceNew.type          = resource.type;
        resourceNew.contentlength = resource.contentlength;
        resourceNew.lastmodified  = resource.lastmodified;
        resourceNew.owner         = resource.owner;
    
        // Update view
        // When resource is renamed
        if (overwriteMode !== nl.sara.webdav.Client.SILENT_OVERWRITE) {
          nl.sara.beehub.view.updateResource(resource, resourceNew);
        // Or when resource is renamed and has overwritten another resource
        } else {
          // create resource that is overwritten
          var resourceOldOverwrite = new nl.sara.beehub.ClientResource(newPath);
          // delete orignal
          nl.sara.beehub.view.deleteResource(resource);
          // update the overwritten resource
          nl.sara.beehub.view.updateResource(resourceOldOverwrite, resourceNew);
          nl.sara.beehub.view.dialog.closeDialog();
        }
      }
    };
  };
  
  // COPY, MOVE, UPLOAD, DELETE
  /*
   * Initialize action Copy, Move, Upload or Delete
   * 
   * Public function
   * 
   * @param  {List}    items   The resource list
   * @param  {String}  action  Which action
   */
  nl.sara.beehub.controller.initAction = function(items, action){
    // actionCounter, used for rename resources when resource already exists
    actionCounter = 0;
    // actionAction, the action copy, move, upload or delete, public variable
    nl.sara.beehub.controller.actionAction = action;
    // summary, used to summary the action and to decide what to do in the callbacks
    summary = {
        error:      0,
        exist:      0,
        forbidden:  0
    };
    
    // When action is not upload items is an array of resources
    if (action !== "upload"){
      // actionResources, array with all resources for the action
      nl.sara.beehub.controller.actionResources = items;
    // When action is upload, items is an array with files
    } else {
      // actionResources, array with all resources for the action
      nl.sara.beehub.controller.actionResources = [];
      // actionFiles, maps resource to file
      actionFiles = {};
      // put items in actionFiles and actionResources
      $.each(items, function(i, item){
        var resource = new nl.sara.beehub.ClientResource(path + item.name);
        resource.displayname = item.name;
        nl.sara.beehub.controller.actionResources.push(resource);
        actionFiles[path + item.name] = item;
      });
    };
  
    if (action === "copy" || action === "move") {
      // Change view
      nl.sara.beehub.controller.setCopyMoveView(true);
      // Change select node handler in tree to get destination
      nl.sara.beehub.view.tree.setOnActivate("Select "+action+" destination", function(path){
        // actionDestination, destination of move or copy resources
        nl.sara.beehub.controller.actionDestination = path;
        // Original view
        nl.sara.beehub.controller.setCopyMoveView(false);
        // Original handlers
        nl.sara.beehub.view.tree.clearView();
        // Show dialog with all resources
        nl.sara.beehub.view.dialog.showResourcesDialog(function() {
          // Start action when ready is clicked
          startAction();
        });
      });
      // show tree
      nl.sara.beehub.view.tree.showTree();
    } else {
      // show dialog with all resources
      nl.sara.beehub.view.dialog.showResourcesDialog(function() {
        // Start action when ready is clicked
        startAction();
      });
    };
  };
  
  /*
   * Make view copy or move ready
   * 
   * Public function
   * 
   * @param Boolean view true or false
   * 
   */
  nl.sara.beehub.controller.setCopyMoveView = function(view){
    if (view) {
      // show cancel button
      nl.sara.beehub.view.tree.cancelButton('show');
      // mask all views
      nl.sara.beehub.view.maskView(true);
      // unmask tree
      nl.sara.beehub.view.tree.noMask(true);
      // hide slideTrigger
      nl.sara.beehub.view.tree.slideTrigger('left'); 
      nl.sara.beehub.view.tree.slideTrigger('hide'); 
    } else {
      // hide cancel button
      nl.sara.beehub.view.tree.cancelButton('hide');
      // unmask views
      nl.sara.beehub.view.maskView(false);
      nl.sara.beehub.view.tree.noMask(false);
      // show slide trigger
      nl.sara.beehub.view.tree.slideTrigger('show');
    }
  };
  
  /*
   * Start action Copy, Move, Upload or Delete for one resource
   * 
   */
  var startAction = function(){
    // Get resource to start
    var resource = nl.sara.beehub.controller.actionResources[actionCounter];
      
    // create webdav client object
    var webdav = new nl.sara.webdav.Client();
      
    switch(nl.sara.beehub.controller.actionAction)
    {
    // copy
    case "copy":
        // destination
        var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname;
        // update dialog
        // TODO - show progress with progress bar. Not yet possible it's one request, not like uploading files
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Copy resource. This can take a while and no progress info is available. Please wait...");
        // start copy
        webdav.copy(resource.path, createActionCallback(resource, 0), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
      break;
    // move settings 
    case "move": 
      // destination
      var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname;
      // start move
      webdav.move(resource.path,createActionCallback(resource, 0), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
      break;
    // delete settings
    case "delete":
      // start delete
      webdav.remove(resource.path,createActionCallback(resource, 0));
      break;
    case "upload":
      // head request, notice: request and callback are not the same as the other actions. FAIL_ON_OVERWRITE is not implemented with uploading. 
      // Testing if file already exist must be done before start uploading the file
      webdav.head(resource.path, createUploadHeadCallback(resource, 0) ,"");
    default:
      // This should never happen
    }
  };
  
  /*
   * Return callback function for copy, move, delete requests
   * 
   * @params {Object} resource Resource to create callback for
   * @params {Integer} renameCounter Used to create new name when object already exist
   */
  var createActionCallback = function(resource, renameCounter){
    return function(status){ 
      var webdav = new nl.sara.webdav.Client();
  
      switch(status)
      {
      //Succeeded
      case 201: 
      case 204:
        nl.sara.beehub.view.tree.reload();
        // Update dialog info
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Done");
        // Update view
        updateActionView(resource, renameCounter);
        break;
        
      // Forbidden
      case 403:
        // Update summary
        summary.forbidden++;
        // Update dialog info
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Forbidden");
        break;
      
        // Parent directory
      case 501:
      case 512:
        // Update summary
        summary.error++;
        // Update dialog info
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Copy to parent resource is not possible.");
        break;
        
      // Already exist
      case 412:
        // First time resource already exist on server
        if (0 === renameCounter) {
          // Update summary
          summary.exist++;
          // Show button in dialog for user input
          setAlreadyExist(resource,1);
        // Not the first time means the user has choosen to rename but the new name also already exist on the server
        } else {
          // create new name
          var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_"+renameCounter;
          renameCounter = renameCounter + 1;
          // start copy or move with new name
          if (nl.sara.beehub.controller.actionAction === "copy") {
            // Update dialog info
            nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Copy resource. This can take a while and no progress info is available. Please wait...");
            // start copy request
            webdav.copy(resource.path, createActionCallback(resource, renameCounter), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
          };
          if (nl.sara.beehub.controller.actionAction === "move") {
            // start move request
            webdav.move(resource.path, createActionCallback(resource, renameCounter), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
          };
        }
        break;
        
      default:
        // Update summary
        summary.error++;
        // Update dialog info
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Unknown error");
      };
      // When renameCounter = 0 the callback is not initiated by a one time rename action with the rename
      // button. So the next action from the actionResources array should start
      if (0 === renameCounter) {
        // Scroll to next position
        nl.sara.beehub.view.dialog.scrollTo(actionCounter*35);
        // Start next action
        startNextAction();
      }
    };
  };
  
  /*
   * Start next action or end actions
   * 
   */
  var startNextAction = function(){
    // less typework
    var counter = actionCounter;
    var resources = nl.sara.beehub.controller.actionResources;
    
    // Next resource if defined
    if (resources[counter + 1] !== undefined){
      actionCounter = counter + 1;
      // start action
      startAction();
    } else {
      var stop = false;
      // Check if there were errors, overwrites or renames
      $.each(summary, function(key,value) {
        if (value !== 0) {
          stop = true; 
        }
      });
      // If there were errors, overwrites or renames, set dialog ready
      if (stop) {
        nl.sara.beehub.view.dialog.setDialogReady(function(){
          nl.sara.beehub.view.clearAllViews(); 
        });
      // else ready and clear all views
      } else {
        nl.sara.beehub.view.clearAllViews(); 
      }
    };
  };
  
  
  /*
   * Update view after a succesfull action (copy, move, delete)
   * 
   * @params {Object} resource Resource to create callback for
   * @params {Integer} renameCounter Used to create new name when object already exist
   */
  var updateActionView = function(resource, renameCounter){
    switch(nl.sara.beehub.controller.actionAction)
    {
      // Update view
      case "delete":
        nl.sara.beehub.view.deleteResource(resource);
        break; 
      case "move":
        // Move to current directory (automatically with rename), update resource
        if (nl.sara.beehub.controller.actionDestination === path) {
          var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_"+renameCounter;
          getResourcePropsFromServer(resourceDestination, function(resource) {
            var orgResource = new nl.sara.beehub.ClientResource(resource.path);
            nl.sara.beehub.view.updateResource(orgResource,resource);
          }); 
        } else {
          // delete resource from current view
          nl.sara.beehub.view.deleteResource(resource);
        }
        break;
      case  "copy":
        // Move to current directory (automatically with rename), update resource
        if (nl.sara.beehub.controller.actionDestination === path) {
          var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_"+renameCounter;
          getResourcePropsFromServer(resourceDestination, function(resource) {
            nl.sara.beehub.view.addResource(resource);
          }); 
        };
        break;
      default:
        // This should never happen
    }
  };
  
  
  /*
   * Return callback function head request for uploading files to check if resource
   * exists on server
   * 
   * @param {Object}  resource      Resource to create callback for
   * @param {Integer} renameCounter Used to create new name when object already exist
   * 
   */
  var createUploadHeadCallback = function(resource, renameCounter){
    return function(status) {  
      var webdav = new nl.sara.webdav.Client();
      switch(status)
      {
      // File exists
      case 200:
        // When renameCounter = 0 it's the first time the file exist, user input about what to do is needed
        if (0 === renameCounter){
          // Update summary
          summary.exist++;
          // Show rename/overwrite/Cancel buttons
          setAlreadyExist(resource);
        // renameCounter is not 0, this means user decided to rename but the new name also already exists
        } else {
          // create new name with renameCounter
          renameCounter = renameCounter + 1;
          destination = resource.path+"_"+renameCounter;
          // start head request with new name
          webdav.head(destination, createUploadHeadCallback(resource, renameCounter));
        };
        // When renameCounter = 0 the callback is not initiated by a one time rename action with the rename
        // button. So the next action from the actionResources array should start
        if (0 === renameCounter) {
          // Start next
          startNextAction();
        };
        break;
      // File does not exist
      case 404:
        if (0 === renameCounter) {
          var destination = resource.path;
        } else {
          var destination = resource.path+"_"+renameCounter;
        }
        // Put empty file on server to check if upload is allowed. This prevent waiting for a long time (large files) 
        // while the upload is forbidden
        webdav.put(destination, createUploadEmptyFileCallback(resource, destination, renameCounter, false),"");
        break;
      default:
        // Something went wrong, a new action should start
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,'Unknown error.');
        // When renameCounter = 0 the callback is not initiated by a one time rename action with the rename
        // button. So the next action from the actionResources array should start
        if (0 === renameCounter) {
          // Start next
          startNextAction();
        };
      };
    };
  };
  
  /*
   * Return callback function upload empty file request for uploading files to check if upload
   * is allowed
   * 
   * @param  Object   resource       Resource to upload
   * @param  String   destination    Upload destination
   * @param  Integer  renameCounter  Used to create new name when object already exist
   * @param  Boolean  overwrite      True when user has choosen overwrite when already exist
   * 
   */
  var createUploadEmptyFileCallback = function(resource, destination, renameCounter, overwrite) {
    return function(status, responseText) {  
      switch(status)
      {
      // Ok, this means the upload is allowed
      case 201:
      case 204:
        // Upload file, this will overwrite the empty file
          var headers = {
            'Content-Type': 'application/octet-stream'
          };
          
          // To have more control (progressbar) the webdav library is not used here
          var ajax = nl.sara.webdav.Client.getAjax( 
          "PUT",
              destination,
              createUploadCallback(resource, destination, renameCounter, overwrite),
              headers 
          );
          
          if (ajax.upload) {
             // progress bar
             ajax.upload.addEventListener("progress", function(event) {
               var progress = parseInt(event.loaded / event.total * 100);
               nl.sara.beehub.view.dialog.showProgressBar(resource,progress);
             }, false);
          } else {
            // Show progress is not possible
            nl.sara.beehub.view.dialog.updateResourceInfo(resource,'Uploading, this can take a while, please wait...');
          };
          ajax.send(actionFiles[resource.path]);  
        break;
      // Forbidden
      case 403:
        // Update summary
        summary.forbidden++;
        // Update dialog info
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Forbidden");
        break;
      default:
        // Update summary
        summary.error++;
        // Unknown error
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,responseText);
        // Start next action
        // When renameCounter = 0 the callback is not initiated by a one time rename action with the rename
        // button. So the next action from the actionResources array should start
        if (0 === renameCounter) {
          startNextAction();
        };
      };
    };
  };
  
  /*
   * Return callback function upload file
   * 
   * @param  Object   resource       Resource to upload
   * @param  String   destination    Upload destination
   * @param  Integer  renameCounter  Used to create new name when object already exist
   * @param  Boolean  overwrite      True when user has choosen overwrite when already exist
   * 
   */
  var createUploadCallback = function(resource, destination, renameCounter, overwrite){
    return function(status, responseText) {
      switch(status)
      {
        // Ok
        case 201:
        case 204:
          // Upload file, this will overwrite the empty file
          nl.sara.beehub.view.dialog.showProgressBar(resource,100);
          // When the user choosed to overwrite, the original file must be removed from the view
          if (overwrite) {
            nl.sara.beehub.view.deleteResource(resource);
          };
          // Get the properties from the resource from the server and update view
          getResourcePropsFromServer(destination, nl.sara.beehub.view.addResource);
          break;
        default:
          // Update summary
          summary.error++;
          // Update dialog info
          nl.sara.beehub.view.dialog.updateResourceInfo(resource,responseText);
        // Delete the empty file
          var webdav = new nl.sara.webdav.Client();
          webdav.remove(destination);
      }
      // When renameCounter = 0 the callback is not initiated by a one time rename action with the rename
      // button. So the next action from the actionResources array should start
      if (0 === renameCounter) {
        startNextAction();
     // Scroll to next position
        nl.sara.beehub.view.dialog.scrollTo(actionCounter*35);
      };
    };
  };
  
  
  /*
  * Show rename, overwrite and cancel buttons and make handlers for this buttons
  * 
  * @param Object resource  Resource
  */
  var setAlreadyExist = function(resource){
    var webdav = new nl.sara.webdav.Client();
  
    switch(nl.sara.beehub.controller.actionAction)
    {
      // copy and move
      case "copy":
        var overwrite = function() {
          var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname;
          // start copy with SILENT OVERWRITE and renameCounter=1
          webdav.copy(resource.path, createActionCallback(resource, 1), resourceDestination, nl.sara.webdav.Client.SILENT_OVERWRITE);
        };
        
        var rename = function() {
          // change destination name with renameCounter
          var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_1";
          // start copy with renameCounter=1
          webdav.copy(resource.path, createActionCallback(resource, 1), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
        };
        break;
      case "move": 
        var overwrite = function() {
          var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname;
          // start move with SILENT OVERWRITE and renameCounter=1
          webdav.move(resource.path, createActionCallback(resource, 1), resourceDestination, nl.sara.webdav.Client.SILENT_OVERWRITE);
        };
        
        var rename = function() {
          // change destination name with renameCounter
          var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_1";
          // start move with renameCounter=1
          webdav.move(resource.path, createActionCallback(resource, 1), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
        };  
        break;
      case "upload":
        var overwrite = function() {
          // start upload flow but skip head and set overwrite true and renameCounter=1
          webdav.put(resource.path, createUploadEmptyFileCallback(resource, resource.path, 1, true), "");
        };
        
        var rename = function() {
          // change destination name
          var resourcePath = resource.path+"_1";
          // start head request with renameCounter=1
          webdav.head(resourcePath, createUploadHeadCallback(resource, 1) ,"");
        };
        break;
      default:
        // This should never happen
    } ;
    
    var cancel = function() {
      // Update dialog view when cancel button is clicked
      nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Canceled");
    };
    
    // Show the buttons in the dialog
    nl.sara.beehub.view.dialog.setAlreadyExist(resource, overwrite, rename, cancel);
  };
})();
