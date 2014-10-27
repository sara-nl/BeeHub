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
 * BeeHub Client Controller
 * 
 * The controller communicates with the webdav server and knows all views
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */

(function() {
  
  nl.sara.beehub.controller.STATUS_NOT_ALLOWED = 403;
  nl.sara.beehub.controller.ERROR_STATUS_NOT_ALLOWED = 'You are not allowed to perform this action!';
  nl.sara.beehub.controller.ERROR_UNKNOWN = 'Something went wrong on the server. No changes were made.';
  /*
   * Add slash to the end of the path
   */
  var path = location.pathname;
  if (!path.match(/\/$/)) {
    path=path+'/'; 
  };

  // The summary of the current pending actions
  var summary;

  // The file to perform an action upon
  var actionFiles;
  
  // Needed for copy, move, delete and upload
  var actionCounter = 0;
  
  nl.sara.beehub.controller.getPath = function(){
    return path;
  };
 
  /**
   * Escape html characters
   * 
   * Public function
   * 
   * @param  {String}  str  String to escape
   */
  nl.sara.beehub.controller.htmlEscape = function(str) {
    return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
  };
  
  /**
   * Go to page
   * 
   * Public function
   * 
   * @param {String} location
   */
  nl.sara.beehub.controller.goToPage = function(location) {
    window.location.href=location;
  };
  
  /**
   * Clear all views
   * 
   * Public function
   * 
   */
  nl.sara.beehub.controller.clearAllViews = function(){
    nl.sara.beehub.view.clearAllViews();
  };
  
  /**
   * Set mask on view
   * 
   */
  nl.sara.beehub.controller.maskView = function(type, boolean){
    nl.sara.beehub.view.maskView(type, boolean);
  };
  
  /**
   * Set input disable mask (transparant) on view
   * 
   */
  nl.sara.beehub.controller.inputDisable = function(boolean){
    nl.sara.beehub.view.inputDisable(boolean);
  };
  
  /**
   * Show error
   * 
   * @param {String} error Error to show
   */
  nl.sara.beehub.controller.showError = function(error){
    nl.sara.beehub.view.dialog.showError(error);
  };
  
  /**
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
    if (name.indexOf(nl.sara.beehub.sponsors_path) !== -1){
      var displayName = nl.sara.beehub.principals.sponsors[name.replace(nl.sara.beehub.sponsors_path,'')];
      return displayName;
    };
  };
  
  /**
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
    
    // Get sponsor
    if (data.getResponse(path).getProperty('http://beehub.nl/','sponsor').getParsedValue() !== null) {
      resource.sponsor = data.getResponse(path).getProperty('http://beehub.nl/','sponsor').getParsedValue();
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
  
  /**
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
    
    // Sponsor
    var sponsorProp = new nl.sara.webdav.Property();
    sponsorProp.tagname = 'sponsor';
    sponsorProp.namespace='http://beehub.nl/';
    
    // Last modified
    var getlastmodifiedProp = new nl.sara.webdav.Property();
    getlastmodifiedProp.tagname = 'getlastmodified';
    getlastmodifiedProp.namespace='DAV:';
    
    // Content length
    var getcontentlengthProp = new nl.sara.webdav.Property();
    getcontentlengthProp.tagname = 'getcontentlength';
    getcontentlengthProp.namespace='DAV:';
    
    // Put properties in array
    var properties = [resourcetypeProp, getcontenttypeProp, displaynameProp, ownerProp, sponsorProp, getlastmodifiedProp, getcontentlengthProp];
    
    /**
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
  
  /**
   * Get sponsors from server
   */
  nl.sara.beehub.controller.getSponsors = function(owner, path){  
    function callback(status, data){
      if (status !== 207) {
        nl.sara.beehub.view.content.errorGetSponsors(status);
      } else {
        var sponsors = [];
        // Get sponsor
        if (data.getResponse(owner).getProperty('http://beehub.nl/','sponsor-membership').getParsedValue() !== null) {
          sponsors = data.getResponse(owner).getProperty('http://beehub.nl/','sponsor-membership').getParsedValue();
        };
        
        var sponsorObjects = [];
        for (var i in sponsors) {
          var sponsor = {};
          sponsor.name = sponsors[i];
          sponsor.displayname = nl.sara.beehub.controller.getDisplayName(sponsors[i]);
          sponsorObjects.push(sponsor);
        }
        nl.sara.beehub.view.content.setSponsorDropdown(path, sponsorObjects);
      }
    }
  
    // Property request
    var webdav = new nl.sara.webdav.Client();
    var resourcetypeProp = new nl.sara.webdav.Property();
    resourcetypeProp.tagname = 'sponsor-membership';
    resourcetypeProp.namespace='http://beehub.nl/';
    var properties = [resourcetypeProp];
    webdav.propfind(owner, callback ,1,properties);
  };
  
  /**
   * Set sponsor on resource
   * 
   * @param {String} path       Path of resource
   * @param {Object} sponsor    Sponsor object with name and displayname
   */
  nl.sara.beehub.controller.setSponsor = function(owner, sponsor){
    function callback(status){
      if (status !== 207) {
        nl.sara.beehub.view.content.errorNewSponsor(status);
      } else {
        nl.sara.beehub.view.content.setNewSponsor(owner, sponsor);
      };
    };
    
    var webdav = new nl.sara.webdav.Client();
    var resourcetypeProp = new nl.sara.webdav.Property();
    resourcetypeProp.tagname = 'sponsor';
    resourcetypeProp.namespace='http://beehub.nl/';
    resourcetypeProp.setValueAndRebuildXml(sponsor.name);
    var properties = [resourcetypeProp];
    webdav.proppatch(owner, callback, properties);
  };
  
  /**
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
  
  /**
   * Create getTreeNode callback
   * 
   * Public function
   * 
   * @param {String}          url       Path
   * @param {DOM object}      parent    DOM object with parent of the node
   * @param {DOM object}      expander  DOM object
   * @param {Function}        callback  Callback function
   */
  nl.sara.beehub.controller.createGetTreeNodeCallback = function(url, parent, expander, callback){
   return function( status, data ) {
      // Callback
      if (status !== 207) {
        nl.sara.beehub.view.dialog.showError( 'Could not load the subdirectories.' );
        return;
      };
      nl.sara.beehub.view.tree.createTreeNode(data, url, parent, expander, callback);
    };
  };
  
  // CREATE NEW FOLDER
  /**
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
  
  /**
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
    webdav.move(resource.path, createRenameCallback(resource, fileNameNew, overwriteMode), encodeURI(path + fileNameNew),  overwriteMode);
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
        resourceNew.sponsor       = resource.sponsor;
    
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
  
  /**
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
  
  // COPY, MOVE, UPLOAD, DELETE
  /**
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
        resource.file = item;
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
        nl.sara.beehub.view.tree.setModal( false );
        nl.sara.beehub.view.tree.clearView();
        // Show dialog with all resources
        nl.sara.beehub.view.dialog.showResourcesDialog(function() {
          // Start action when ready is clicked
          startAction();
        });
      });
      // show tree
      nl.sara.beehub.view.tree.showTree();
      nl.sara.beehub.view.tree.setModal( true );
    } else {
      // show dialog with all resources
      nl.sara.beehub.view.dialog.showResourcesDialog(function() {
        // Start action when ready is clicked
        startAction();
      });
    };
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
      if (nl.sara.beehub.controller.actionDestination !== path) {
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Copy resource. This can take a while and no progress info is available. Please wait...");
        // start copy
        webdav.copy(resource.path, createActionCallback(resource, 0, false), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
      } else {
       resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_1";
       // start copy or move with new name
       // Update dialog info
       nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Copy resource. This can take a while and no progress info is available. Please wait...");
       // start copy request
       webdav.copy(resource.path, createActionCallback(resource, 1, false), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
      } 
       break;
    // move settings 
    case "move": 
      // destination
      var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname;
      if (nl.sara.beehub.controller.actionDestination !== path) {
        // start move
        webdav.move(resource.path,createActionCallback(resource, 0, false), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
      } else {
        nl.sara.beehub.view.dialog.showError("Moving an item to itself is not possible. Use rename icon for renaming the resource(s).");
      }
      break;
    // delete settings
    case "delete":
      // start delete
      webdav.remove(resource.path,createActionCallback(resource, 0, false));
      break;
    case "upload":
      // head request, notice: request and callback are not the same as the other actions. FAIL_ON_OVERWRITE is not implemented with uploading. 
      // Testing if file already exist must be done before start uploading the file
      webdav.head(resource.path, createUploadHeadCallback(resource, 0, false) ,"");
    default:
      // This should never happen
    }
  };
  
  /**
   * Return callback function for copy, move, delete requests
   * 
   * @params {Object} resource        Resource to create callback for
   * @params {Integer} renameCounter  Used to create new name when object already exist
   * @params {Boolean} single         Single or multiple resources
   */
  var createActionCallback = function(resource, renameCounter, single){
    return function(status){ 
      var webdav = new nl.sara.webdav.Client();
      switch(status)
      {
      //Succeeded
      case 201: 
      case 204:
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
        if ((0 === renameCounter)) {
          // Update summary
          summary.exist++;
          // Show button in dialog for user input
          setAlreadyExist(resource,1);
        // Not the first time means the user has choosen to rename but the new name also already exist on the server
        } else {
          // create new name
          renameCounter++;
          var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_"+renameCounter;
          // start copy or move with new name
          if (nl.sara.beehub.controller.actionAction === "copy") {
            // Update dialog info
            nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Copy resource. This can take a while and no progress info is available. Please wait...");
            // start copy request
            webdav.copy(resource.path, createActionCallback(resource, renameCounter, true), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
          };
          if (nl.sara.beehub.controller.actionAction === "move") {
            // start move request
            webdav.move(resource.path, createActionCallback(resource, renameCounter, true), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
          };
        }
        break;
        
      default:
        // Update summary
        summary.error++;
        // Update dialog info
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Unknown error");
      };
    
      if (!single) {
        // Scroll to next position
        nl.sara.beehub.view.dialog.scrollTo(actionCounter*35);
        // Start next action
        startNextAction();
      }
    };
  };
  
  /**
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
      nl.sara.beehub.view.dialog.setDialogReady(function(){
        nl.sara.beehub.view.clearAllViews(); 
      });
// TODO show action was succesfull and close dialog when no errors where found
//      var stop = false;
//      // Check if there were errors, overwrites or renames
//      $.each(summary, function(key,value) {
//        if (value !== 0) {
//          stop = true; 
//        }
//      });
//      // If there were errors, overwrites or renames, set dialog ready
//      if (stop) {
//        nl.sara.beehub.view.dialog.setDialogReady(function(){
//          nl.sara.beehub.view.clearAllViews(); 
//        });
//      // else ready and clear all views
//      } else {
//        nl.sara.beehub.view.clearAllViews(); 
//      }
    };
  };

  
  /**
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
      case "copy":
        // Flow of copy to same dir with automatically rename is different. This if statement solves this
        var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname;
        if (renameCounter > 0) {
          resourceDestination += "_" + renameCounter;
        }
        getResourcePropsFromServer(resourceDestination, function( resourceNew ) {
          if ( nl.sara.beehub.controller.actionAction === 'copy' ) {
            nl.sara.beehub.view.addResource( resourceNew );
          }else{
            resource.type = resourceNew.type;
            nl.sara.beehub.view.updateResource( resource, resourceNew );
          }
        }); 
        break;
      default:
        // This should never happen
    }
  };
  
  
  /**
   * Return callback function head request for uploading files to check if resource
   * exists on server
   * 
   * @param {Object}  resource      Resource to create callback for
   * @param {Integer} renameCounter Used to create new name when object already exist
   * @param {Boolean} single        Single or multiple resources
   * 
   */
  var createUploadHeadCallback = function(resource, renameCounter, single){
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
          webdav.head(destination, createUploadHeadCallback(resource, renameCounter, true));
        };
        
        if (!single) {
          // Start next
          startNextAction();
        };
        break;
      // File does not exist
      case 404:
        var destination = '';
        if (0 === renameCounter) {
          destination = resource.path;
        } else {
          destination = resource.path+"_"+renameCounter;
        }
        // Put empty file on server to check if upload is allowed. This prevent waiting for a long time (large files) 
        // while the upload is forbidden
        webdav.put(destination, createUploadEmptyFileCallback(resource, destination, renameCounter, false, single),"", resource.file.type);
        break;
      default:
        // Something went wrong, a new action should start
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,'Unknown error.');

        if (!single) {
          // Start next
          startNextAction();
        };
      };
    };
  };
  
  /**
   * Return callback function upload empty file request for uploading files to check if upload
   * is allowed
   * 
   * @param  Object   resource       Resource to upload
   * @param  String   destination    Upload destination
   * @param  Integer  renameCounter  Used to create new name when object already exist
   * @param  Boolean  overwrite      True when user has choosen overwrite when already exist
   * @param {Boolean} single         Single or multiple resources
   * 
   */
  var createUploadEmptyFileCallback = function(resource, destination, renameCounter, overwrite, single) {
    return function(status, responseText) {  
      switch(status)
      {
      // Ok, this means the upload is allowed
      case 201:
      case 204:
        // Upload file, this will overwrite the empty file
          var headers = {
            'Content-Type': resource.file.type
          };
          
          // To have more control (progressbar) the webdav library is not used here
          var client = new nl.sara.webdav.Client();
          var ajax = client.getAjax( 
          "PUT",
              destination,
              createUploadCallback(resource, destination, renameCounter, overwrite, single),
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
        
        if (!single) {
          startNextAction();
        };
        break;
      default:
        // Update summary
        summary.error++;
        // Unknown error
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,responseText);
        // Start next action
        
        if (!single) {
          startNextAction();
        };
      };
    };
  };
  
  /**
   * Return callback function upload file
   * 
   * @param  Object   resource       Resource to upload
   * @param  String   destination    Upload destination
   * @param  Integer  renameCounter  Used to create new name when object already exist
   * @param  Boolean  overwrite      True when user has choosen overwrite when already exist
   * @param  Boolean  single         Single or multiple resources
   * 
   */
  var createUploadCallback = function(resource, destination, renameCounter, overwrite, single){
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

      if (!single) {
        startNextAction();
     // Scroll to next position
        nl.sara.beehub.view.dialog.scrollTo(actionCounter*35);
      };
    };
  };
  
  
  /**
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
          // start copy with SILENT OVERWRITE and renameCounter=0
          webdav.copy(resource.path, createActionCallback(resource, 0, false), resourceDestination, nl.sara.webdav.Client.SILENT_OVERWRITE);
        };
        
        var rename = function() {
          // change destination name with renameCounter
          var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_1";
          // start copy with renameCounter=1
          webdav.copy(resource.path, createActionCallback(resource, 1, false), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
        };
        break;
      case "move": 
        var overwrite = function() {
          var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname;
          // start move with SILENT OVERWRITE and renameCounter=0
          webdav.move(resource.path, createActionCallback(resource, 0, false), resourceDestination, nl.sara.webdav.Client.SILENT_OVERWRITE);
        };
        
        var rename = function() {
          // change destination name with renameCounter
          var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_1";
          // start move with renameCounter=1
          webdav.move(resource.path, createActionCallback(resource, 1, false), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
        };  
        break;
      case "upload":
        var overwrite = function() {
          // start upload flow but skip head and set overwrite true and renameCounter=1
          webdav.put(resource.path, createUploadEmptyFileCallback(resource, resource.path, 1, true, true), "", resource.file.type);
        };
        
        var rename = function() {
          // change destination name
          var resourcePath = resource.path+"_1";
          // start head request with renameCounter=1
          webdav.head(resourcePath, createUploadHeadCallback(resource, 1, true) ,"");
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
  
  
  // ACL
  /**
   * Save acl on server
   * 
   */
  nl.sara.beehub.controller.saveAclOnServer = function(functionSaveAclOk, functionSaveAclError){
    var acl = nl.sara.beehub.view.acl.getAcl();
    // create webdav client object
    var webdav = new nl.sara.webdav.Client();
    // send acl request to the server
    webdav.acl(nl.sara.beehub.view.acl.getViewPath(),function(status,data){
      // Delete dialog
      if (status === 200){
        functionSaveAclOk();
        return;
      }
      // callback
      if (status === 403) {
        nl.sara.beehub.view.dialog.showError('You are not allowed to change the acl.');
        functionSaveAclError();
        return;
      } else {
        nl.sara.beehub.view.dialog.showError('Something went wrong on the server. No changes were made.');
        functionSaveAclError();
      };
    }, acl);
  };
  
  /**
   * Get acl from server
   * 
   */
  nl.sara.beehub.controller.getAclFromServer = function(resourcePath){
    var webdav = new nl.sara.webdav.Client();
    var aclProp = new nl.sara.webdav.Property();
    aclProp.tagname = 'acl';
    aclProp.namespace='DAV:';
    var properties = [aclProp];

    // send the request to the server
    webdav.propfind(resourcePath, createGetAclCallback(resourcePath) ,0,properties);
  };
  
  var addAclRuleDialog = function( ace ){
    if ( checkHomeFolderPrivileges( ace ) ) {
 
     // Add row in view
     var row = nl.sara.beehub.view.acl.createRow(ace);
     nl.sara.beehub.view.acl.addRow(row, nl.sara.beehub.view.acl.getIndexLastProtected());
     
     var functionSaveAclOk = function(){
       // Do Nothing;
     };
     
     var functionSaveAclError = function(){
       // Update view
       nl.sara.beehub.view.acl.deleteRowIndex(nl.sara.beehub.view.acl.getIndexLastProtected() + 1);
     };
     nl.sara.beehub.controller.saveAclOnServer(functionSaveAclOk, functionSaveAclError);
    }
  };
  
  var createGetAclCallback = function(resourcePath){
    return function(status, data) {
      // Callback
      // Something went wrong with status 207, stop then.
      if (status !== 207) {
        nl.sara.beehub.view.dialog.showError('Something went wrong at the server.');
        return;
      };
      // TODO return als privilege is niet bekend
      var value = data.getResponseNames()[0];
      var propstatus = data.getResponse(value).getProperty('DAV:','acl').status;
      // Status 403, forbidden, stop
      if (propstatus === 403) {
        nl.sara.beehub.view.dialog.showError('You are not allowed to view this acl.');
        return;
      };
      // Something went wrong with status 200, stop then.
      if (propstatus !== 200) {
        nl.sara.beehub.view.dialog.showError('Something went wrong at the server.');
        return;
      };
      if (data.getResponse(value).getProperty('DAV:','acl') !== undefined) {
        // Set acl view for dialog
        nl.sara.beehub.view.acl.setView("resource", resourcePath);
        
        var aclProp = data.getResponse(value).getProperty('DAV:','acl');
        
        var html = nl.sara.beehub.view.acl.createDialogViewHtml();
        
        nl.sara.beehub.view.dialog.showAcl(html, resourcePath, createCloseAclDialogFunction(resourcePath));
        
        nl.sara.beehub.view.acl.setAddAclRuleDialogClickHandler(addAclRuleDialog);
        
        var acl = aclProp.getParsedValue();
        var index = -1;
        for ( var key in acl.getAces() ) {
          var ace = acl.getAces()[key];
          // The protected property which grants everybody the 'DAV: unbind' privilege will be omitted from the list
          if ( ace.isprotected &&
              ( ace.principal === nl.sara.webdav.Ace.ALL ) &&
              ! ace.deny &&
              ( ace.getPrivilegeNames('DAV:').length === 1 ) &&
              ( ace.getPrivilegeNames('DAV:').indexOf('unbind') !== -1)
            )
          {
            continue;
          }
          var row = nl.sara.beehub.view.acl.createRow( ace );
          nl.sara.beehub.view.acl.addRow(row, index);
          index++;
        };
      };
    };
  };
  
  /**
   * Create ace
   */
  var createAceObject = function(ace){
    var aceObject = {};
    if (ace.principal.tagname !== undefined) {
      aceObject['principal'] =  "DAV: "+ ace.principal.tagname;
    } else {
      // Principal
      switch ( ace.principal ) {
        case nl.sara.webdav.Ace.ALL:
          aceObject['principal'] = 'DAV: all';
          break;
        case nl.sara.webdav.Ace.AUTHENTICATED:
          aceObject['principal'] = 'DAV: authenticated';
          break;
        case nl.sara.webdav.Ace.UNAUTHENTICATED:
          aceObject['principal'] = 'DAV: unauthenticated';
          break;
        case nl.sara.webdav.Ace.SELF:
          aceObject['principal'] = 'DAV: self';
          break;
        default:
          aceObject['principal'] = ace.principal;
        break;
      }
    }  
    aceObject['invert'] = ace.invertprincipal;

    aceObject['protected'] = ace.isprotected;
    
    if (ace.inherited) {
      aceObject['inherited'] = ace.inherited;
    };
    aceObject['invertprincipal'] = ace.invertprincipal;

    if (ace.getPrivilegeNames('DAV:').indexOf('unbind') !== -1) {
      aceObject['unbind'] = true;
    };
    
    // Make permissions string  
    if ( ace.grantdeny === 2 ) {
      aceObject['permissions'] = "deny ";
      if ( ( ace.getPrivilegeNames('DAV:').length === 1 ) && 
           ( ace.getPrivilegeNames('DAV:').indexOf('write-acl') !== -1) ) {
        aceObject['permissions'] += "change acl";
      } else if ( ( ace.getPrivilegeNames('DAV:').length === 2 ) && 
                 ( ace.getPrivilegeNames('DAV:').indexOf('write') !== -1 ) && 
                 ( ace.getPrivilegeNames('DAV:').indexOf('write-acl') !== -1  ) ) {
        aceObject['permissions'] += "write, change acl";
      } else if ( ( ( ace.getPrivilegeNames('DAV:').length === 3 ) && 
                   ( ace.getPrivilegeNames('DAV:').indexOf('read') !== -1 ) && 
                   ( ace.getPrivilegeNames('DAV:').indexOf('write') !== -1 ) && 
                   ( ace.getPrivilegeNames('DAV:').indexOf('write-acl') !== -1  ) ) || 
                 (ace.getPrivilegeNames('DAV:').indexOf('all') !== -1 ) ) {
        aceObject['permissions'] += "read, write, change acl";
      } else {
        aceObject['permissions'] += "unknown privilege (combination)";
        var array = [];
        for (var key in ace.getPrivilegeNames('DAV:')) {
          array.push("DAV: "+ace.getPrivilegeNames('DAV:')[key]);
        };
        aceObject['privileges'] = array.join(" ");
      }
    } else { 
      aceObject['permissions'] = "allow ";
      if ( ( ace.getPrivilegeNames('DAV:').length === 1 ) && 
           ( ace.getPrivilegeNames('DAV:').indexOf('read') !== -1 ) ) {
        aceObject['permissions'] += "read";
      } else if ( ( ace.getPrivilegeNames('DAV:').length === 2 ) && 
                 (ace.getPrivilegeNames('DAV:').indexOf('write') !== -1 ) && 
                  ( ace.getPrivilegeNames('DAV:').indexOf('read') !== -1 ) ) {
        aceObject['permissions'] += "read, write";
      } else if ( ( ( ace.getPrivilegeNames('DAV:').length === 3 ) && 
                   ( ace.getPrivilegeNames('DAV:').indexOf('write-acl') !== -1) && 
                   ( ace.getPrivilegeNames('DAV:').indexOf('write') !== -1 ) && 
                   ( ace.getPrivilegeNames('DAV:').indexOf('read') !== -1 ) ) || 
                 (ace.getPrivilegeNames('DAV:').indexOf('all') !== -1 ) ) {
        aceObject['permissions'] += "read, write, change acl";
      } else {
        aceObject['permissions'] += "unknown privilege (combination)";
        var array = [];
        for (var key in ace.getPrivilegeNames('DAV:')) {
          array.push("DAV: "+ace.getPrivilegeNames('DAV:')[key]);
        };
        aceObject['privileges'] = array.join(" ");
      }
    }
    return aceObject;
  };

  var createCloseAclDialogFunction = function(resourcePath) {
    return function(){
      // Check whether there is a resource specific ACL set
      var webdavClient = new nl.sara.webdav.Client();
      var aclProp = new nl.sara.webdav.Property();
      aclProp.namespace = 'DAV:';
      aclProp.tagname = 'acl';
      webdavClient.propfind( resourcePath, function( status, data ) {
        if ( status === 207 ) {
          
          var response = data.getResponse( resourcePath );
          if ( response === undefined ) {
            if ( resourcePath.substr( -1 ) === '/' ) {
              resourcePath = resourcePath.substr( 0, resourcePath.length -1 );
            }
            response = data.getResponse( resourcePath );
          }
          var aces = response.getProperty( 'DAV:', 'acl' ).getParsedValue().getAces();
          // Determine if there are non-inherited and non-protected ACE's
          var ownACL = false;
          for ( var counter in aces ) {
            var ace = aces[counter];
            if ( ( aces[ counter ].inherited === false ) &&
                 ( aces[ counter ].isprotected === false ) ) {
              ownACL = true;
              break;
            }
          }        
          nl.sara.beehub.view.setCustomAclOnResource(ownACL, resourcePath);    
        }
      }, 0, [ aclProp ] );
      
      // Set acl view for dialog
      nl.sara.beehub.view.acl.setView("directory", nl.sara.beehub.controller.getPath());
      nl.sara.beehub.view.dialog.clearView();
    };
  };


  /**
   * Warns the user before giving home folder privileges
   *
   * Checks whether you try to give 'the whole world' or 'all BeeHub users'
   * privileges on your home folder and warns you.
   *
   * @param    {nl.sara.webdav.Ace}  ace  The ACE to check
   * @returns  {Boolean}                  True if the user is warned but still wants to continue, or if no harmful privileges are being set.
   */
  function checkHomeFolderPrivileges( ace ) {
    if ( nl.sara.beehub.view.acl.getViewPath() !== nl.sara.beehub.view.getHomePath() ) {
      return true;
    }
    if (
      ( ace.grantdeny === nl.sara.webdav.Ace.GRANT ) &&
      (
        ( ace.principal === nl.sara.webdav.Ace.ALL ) ||
        ( ace.principal === nl.sara.webdav.Ace.AUTHENTICATED )
      )
    ) {
      return confirm( 'You are about to give a large group access to your home folder. Are you sure that this is what you want to do?' );
    }
    
    return true;
  }

  
  /**
   * Show add acl rule dialog.
   * 
   */
  nl.sara.beehub.controller.addAclRule = function(){
    nl.sara.beehub.view.dialog.showAddRuleDialog( function( ace ){
      if ( checkHomeFolderPrivileges( ace ) ) {
        // Add row in view
        var row = nl.sara.beehub.view.acl.createRow(ace);
        nl.sara.beehub.view.acl.addRow(row, nl.sara.beehub.view.acl.getIndexLastProtected());

        var functionSaveAclOk = function(){
          nl.sara.beehub.view.dialog.clearView();
        };

        var functionSaveAclError = function(){
          // Update view
          nl.sara.beehub.view.dialog.clearView();
          nl.sara.beehub.view.acl.deleteRowIndex(nl.sara.beehub.view.acl.getIndexLastProtected() + 1);
        };
        nl.sara.beehub.controller.saveAclOnServer(functionSaveAclOk, functionSaveAclError);
      }else{
        nl.sara.beehub.view.dialog.clearView();
      }
    }, nl.sara.beehub.view.acl.createHtmlAclForm("tab"));
  };
  
  /**
   * Change permissions of a row
   * 
   */
  nl.sara.beehub.controller.changePermissions = function(row, oldVal){
    if ( checkHomeFolderPrivileges( nl.sara.beehub.view.acl.getAceFromDOMRow( row ) ) ) {
      var functionSaveAclOk = function(){
        // Do nothing
      };

      var functionSaveAclError = function(){
        // Put back old value
        nl.sara.beehub.view.acl.changePermissions(row, oldVal);
        nl.sara.beehub.view.acl.showChangePermissions( row, false );
        // Do nothing
      };
      nl.sara.beehub.controller.saveAclOnServer(functionSaveAclOk, functionSaveAclError);
    }else{
      nl.sara.beehub.view.acl.changePermissions(row, oldVal);
      nl.sara.beehub.view.acl.showChangePermissions( row, false );
    }
  };
  
  /**
   * Delete acl rule
   * 
   */
  nl.sara.beehub.controller.deleteAclRule = function(row, index, t){
    var functionSaveAclOk = function(){
      clearTimeout(t);
      nl.sara.beehub.view.hideMasks();
    };
    
    var functionSaveAclError = function(){
      // Update view
      clearTimeout(t);
      nl.sara.beehub.view.hideMasks();
      nl.sara.beehub.view.acl.addRow(row, index -1);
    };
    nl.sara.beehub.controller.saveAclOnServer(functionSaveAclOk, functionSaveAclError);
  };
  
  /**
   * Move down acl rule
   * 
   */
  nl.sara.beehub.controller.moveDownAclRule = function(row, t){
    var functionSaveAclOk = function(){
      clearTimeout(t);
      nl.sara.beehub.view.hideMasks();
    };
    
    var functionSaveAclError = function(){
      // Update view
      clearTimeout(t);
      nl.sara.beehub.view.hideMasks();
      nl.sara.beehub.view.acl.moveUpAclRule(row);
    };
    nl.sara.beehub.controller.saveAclOnServer(functionSaveAclOk, functionSaveAclError);
  };
  
  /**
   * Move up acl rule
   * 
   */
  nl.sara.beehub.controller.moveUpAclRule = function(row,t){
    var functionSaveAclOk = function(){
      clearTimeout(t);
      nl.sara.beehub.view.hideMasks();
    };
    
    var functionSaveAclError = function(){
      // Update view
      clearTimeout(t);
      nl.sara.beehub.view.hideMasks();
      nl.sara.beehub.view.acl.moveDownAclRule(row);
    };
    nl.sara.beehub.controller.saveAclOnServer(functionSaveAclOk, functionSaveAclError);
  };
  
})();
