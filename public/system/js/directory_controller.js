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
 * Extract properties from webdav response to resource object
 * 
 * @param Object {data}     Webdav propfind response
 * 
 * @return resource object
 */
nl.sara.beehub.controller.extractPropsFromPropfindRequest = function(data){
  var path = data.getResponseNames()[0];
  var resource = new nl.sara.beehub.ClientResource(path);
  // Get resourcetype
  if (data.getResponse(path).getProperty('DAV:','resourcetype') !== undefined) {
    var resourcetypeProp = data.getResponse(path).getProperty('DAV:','resourcetype');
    if ((resourcetypeProp.xmlvalue.length == 1)
        &&(resourcetypeProp.xmlvalue.item(0).namespaceURI=='DAV:')) 
    { 
      resource.setType(nl.sara.webdav.Ie.getLocalName(resourcetypeProp.xmlvalue.item(0)));
    } 
  };
  if (data.getResponse(path).getProperty('DAV:','displayname') !== undefined) {
    var displaynameProp = data.getResponse(path).getProperty('DAV:','displayname');
    if ((displaynameProp.xmlvalue.length == 1)
        &&(displaynameProp.namespace=='DAV:')) 
    { 
      resource.setDisplayName(displaynameProp.getParsedValue());
    }
  };
  if (data.getResponse(path).getProperty('DAV:','owner') !== undefined) {
    var ownerProp = data.getResponse(path).getProperty('DAV:','owner');
    // TODO request displayname
    if ((ownerProp.xmlvalue.length == 1)
        &&(ownerProp.xmlvalue.item(0).namespaceURI=='DAV:')) 
    { 
      resource.setOwner(ownerProp.xmlvalue.item(0).textContent);
    }
  };
  if (data.getResponse(path).getProperty('DAV:','getlastmodified') !== undefined) {
    var getlastmodifiedProp = data.getResponse(path).getProperty('DAV:','getlastmodified');
    if (getlastmodifiedProp.xmlvalue.length == 1)
      // TODO uitzoeken nameSpaceURI
//    if ((getlastmodifiedProp.xmlvalue.length == 1)
//        &&(getlastmodifiedProp.xmlvalue.item(0).namespaceURI=='DAV:')) 
    { 
      resource.setLastModified(getlastmodifiedProp.xmlvalue[0].textContent);
    }
  };

  if (data.getResponse(path).getProperty('DAV:','getcontentlength') !== undefined) {
    var getcontentlengthProp = data.getResponse(path).getProperty('DAV:','getcontentlength');
    if (getcontentlengthProp.xmlvalue.length == 1)
      // TODO uitzoeken nameSpaceURI
//    if ((getcontentlengthProp.xmlvalue.length == 1)
//        &&(getcontentlengthProp.xmlvalue.item(0).namespaceURI=='DAV:')) 
    { 
      resource.setContentLength(getcontentlengthProp.xmlvalue[0].textContent);
    }
  };
  
  return resource;
};

/*
 * Collect resource details from server and call callback function 
 * after ajax call is finished
 * 
 * String   {resourcepath} Resource path
 * Function {callback}     Callback function
 */
nl.sara.beehub.controller.getResourcePropsFromServer = function(resourcepath, callback){
  // Collect resource details
  var webdav = new nl.sara.webdav.Client();
  
  var resourcetypeProp = new nl.sara.webdav.Property();
  resourcetypeProp.tagname = 'resourcetype';
  resourcetypeProp.namespace='DAV:';
  
  var displaynameProp = new nl.sara.webdav.Property();
  displaynameProp.tagname = 'displayname';
  displaynameProp.namespace='DAV:';
  
  var ownerProp = new nl.sara.webdav.Property();
  ownerProp.tagname = 'owner';
  ownerProp.namespace='DAV:';
  
  var getlastmodifiedProp = new nl.sara.webdav.Property();
  getlastmodifiedProp.tagname = 'getlastmodified';
  getlastmodifiedProp.namespace='DAV:';
  
  var getcontentlengthProp = new nl.sara.webdav.Property();
  getcontentlengthProp.tagname = 'getcontentlength';
  getcontentlengthProp.namespace='DAV:';
  
  var properties = [resourcetypeProp, displaynameProp, ownerProp, getlastmodifiedProp, getcontentlengthProp];
  
  function createCallback(){
    return function(status, data) {
      // Callback
      if (status != 207) {
        nl.sara.beehub.view.dialog.showError("Unknown error.");
        return;
      };
      var resource = nl.sara.beehub.controller.extractPropsFromPropfindRequest(data);
      callback(resource);
    };
  };
  webdav.propfind(resourcepath, createCallback() ,1,properties);
};

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
    return function(status, path) {
      if (status === 201) {
        nl.sara.beehub.controller.getResourcePropsFromServer(path, nl.sara.beehub.view.addResource);
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

/**
 * Rename an object
 * 
 * @param string fileNameOrg
 * @param string fileNameNew
 * 
 */
nl.sara.beehub.controller.renameResource = function(resource, fileNameNew, overwriteMode){
  var webdav = new nl.sara.webdav.Client();
  
  function createCallback(resource, fileNameNew) {
    return function(status) {
      if (status === 412) {
        nl.sara.beehub.view.dialog.showOverwriteDialog(resource, fileNameNew);
      } 
      if (status === 201 || status === 204) {
        resource = nl.sara.beehub.view.content.getUnknownResourceValues(resource);

        var newPath = resource.path.replace(/[^\/]*$/,fileNameNew);
        
        var resourceNew = new nl.sara.beehub.ClientResource(newPath);
        resourceNew.setDisplayName(fileNameNew);
        resourceNew.setType(resource.type);
        resourceNew.setContentLength(resource.contentlength);
        resourceNew.setLastModified(resource.lastmodified);
        resourceNew.setOwner(resource.owner);
        nl.sara.beehub.view.updateResource(resource, resourceNew);
      }
    }
  };
   
  webdav.move(resource.path,createCallback(resource, fileNameNew), nl.sara.beehub.controller.path +fileNameNew,  overwriteMode);
};

/**
 * Create actionConfig object and show delete dialog
 * 
 * @param {Array} resources Array with resources
 * 
 */
nl.sara.beehub.controller.deleteResources = function(resources){
  var actionConfig = {};
  actionConfig.counter = 0;
  actionConfig.action = "delete";
  nl.sara.beehub.view.dialog.showResourcesDialog(resources, actionConfig, function(){
    nl.sara.beehub.controller.actionResources(resources, actionConfig);
  });
};

/**
 * Create actionConfig object and show delete dialog
 * 
 * @param {Array} resources Array with resources
 * 
 */
nl.sara.beehub.controller.copyResources = function(resources){
  var actionConfig = {};
  actionConfig.counter = 0;
  actionConfig.action = "copy";
  actionConfig.overwrite = nl.sara.webdav.Client.FAIL_ON_OVERWRITE;
  actionConfig.renameNumber = 1;
  actionConfig.forceRename = false;

  nl.sara.beehub.view.tree.setOnActivate(function(path){
    actionConfig.destinationPath = path;
    nl.sara.beehub.view.dialog.showResourcesDialog(resources, actionConfig, function() {
      nl.sara.beehub.controller.actionResources(resources, actionConfig);
    });
  });
  
  // show tree
  nl.sara.beehub.view.tree.showTree();
};

/*
 * Create callback for webdav request.
 * 
 * @param {Object} actionConfig Configuration settings
 *         
 */
nl.sara.beehub.controller.createActionCallback = function(resources, actionConfig){  
  var config = {}
  switch(actionConfig.action)
  {
  //copy settings
  case "copy":
    config.action = function(){
    };
    break;
  // move settings 
  case "move":
    break;
  // delete settings
  case "delete":
    config.action = function(){
      nl.sara.beehub.view.deleteResource(resources[actionConfig.counter]);
    };
    break;
  default:
    // This should never happen
  }

  function resourceExist(){
    // When filename already exist make new filename and send request again
    if (actionConfig.forceRename) {
      var newResources = [resources[actionConfig.counter]];
      var newActionConfig = {};
      newActionConfig.counter = 0;
      newActionConfig.action = actionConfig.action;
      newActionConfig.destinationPath = actionConfig.destinationPath;
      newActionConfig.destinationName = resources[actionConfig.counter].displayname+"_"+actionConfig.action+"_"+actionConfig.renameNumber;
      newActionConfig.overwrite = nl.sara.webdav.Client.FAIL_ON_OVERWRITE;
      newActionConfig.renameNumber = actionConfig.renameNumber + 1;
      newActionConfig.forceRename = true;
      nl.sara.beehub.controller.actionResources(newResources, newActionConfig);
    // or ask user what to do
    } else {
      function overwrite(overwriteResources, overwriteActionConfig){
        return function(){
          nl.sara.beehub.controller.actionResources(overwriteResources, overwriteActionConfig);
        };
      };
      
      function rename(renameResources, renameActionConfig){
        return function() {
          nl.sara.beehub.controller.actionResources(renameResources, renameActionConfig);
        };
      };
      
      function cancel(cancelResources) {
        return function() {
          nl.sara.beehub.view.dialog.updateResourceInfo(cancelResource, "Canceled");
        }
      };
      
      var overwriteResources = [resources[actionConfig.counter]];
      var overwriteActionConfig = {};
      overwriteActionConfig.counter = 0;
      overwriteActionConfig.action = actionConfig.action;
      overwriteActionConfig.destinationPath = actionConfig.destinationPath;
      overwriteActionConfig.destinationName = actionConfig.destinationName;
      overwriteActionConfig.overwrite = nl.sara.webdav.Client.SILENT_OVERWRITE;
      overwriteActionConfig.renameNumber = actionConfig.renameNumber;
      overwriteActionConfig.forceRename = false;
      
      var renameResources = [resources[actionConfig.counter]];
      var renameActionConfig = {};
      renameActionConfig.counter = 0;
      renameActionConfig.action = actionConfig.action;
      renameActionConfig.destinationPath = actionConfig.destinationPath;
      renameActionConfig.destinationName = resources[actionConfig.counter].displayname+"_"+actionConfig.action+"_"+actionConfig.renameNumber;
      renameActionConfig.overwrite = nl.sara.webdav.Client.FAIL_ON_OVERWRITE;
      renameActionConfig.renameNumber = actionConfig.renameNumber+1;
      renameActionConfig.forceRename = true;
      
      var cancelResource = resources[actionConfig.counter];
 
      nl.sara.beehub.view.dialog.setAlreadyExist(resources[actionConfig.counter], overwrite(overwriteResources, overwriteActionConfig), rename(renameResources, renameActionConfig), cancel(cancelResource));
    }
  };
  
  return function(status) {
    switch(status)
    {
    //Succeeded
    case 201:
      nl.sara.beehub.view.dialog.updateResourceInfo(resources[actionConfig.counter],"Done");
      config.action();
      break;
    // Succeeded
    case 204:
      nl.sara.beehub.view.dialog.updateResourceInfo(resources[actionConfig.counter],"Done");
      config.action();
      break;
    // Forbidden
    case 403:
      nl.sara.beehub.view.dialog.updateResourceInfo(resources[actionConfig.counter],"Forbidden");
      break;
    case 512:
      nl.sara.beehub.view.dialog.updateResourceInfo(resources[actionConfig.counter],"Copy to parent resource is not possible.");
      break;
    // Already exist
    case 412:
      resourceExist();
      break;
    default:
      nl.sara.beehub.view.dialog.updateResourceInfo(resources[actionConfig.counter],"Unknown error");
    }
  
    // Next item of the array
    if (resources[actionConfig.counter+1] !== undefined) {
      if ((actionConfig.action === "copy") || (actionConfig.action === "move")){
        actionConfig.destinationName = resources[actionConfig.counter+1].displayname;
      }
      actionConfig.counter = actionConfig.counter + 1;
      nl.sara.beehub.controller.actionResources(resources, actionConfig);
    } else {
    // Or ready
      nl.sara.beehub.view.dialog.setDialogReady(function(){
         nl.sara.beehub.view.clearAllViews(); 
      });
    }
    nl.sara.beehub.view.dialog.scrollTo(actionConfig.counter*35);
  }
};

/**
* Copy, move or delete an resource from an array and when not finished call this function again
* with the next item of the array
* 
* @param {Array}    resources           Array with resources
* @param {Object}   actionConfig        Object with settings:
*  {Integer}  counter             Position of array with resources
*  {String}   action              Action: copy, move, delete
*  {String}   destinationDir      Destination directory to copy or move to
*  {String}   destinationDir      Destination directory to copy or move to
*  {Integer}  overwrite           Way to send webdav request
*  {Integer}  renameNumber        Used to rename resources
*  {Boolean}  forceRename         Used to rename resources without confirm of user
* 
*/
nl.sara.beehub.controller.actionResources = function(resources, actionConfig){
 // create webdav client object
 var webdav = new nl.sara.webdav.Client();
 
 // create an object with action specific settings
 var config = {};
 switch(actionConfig.action)
 {
 // copy settings
 case "copy":
   config.action = function(){
     if (actionConfig.destinationName === undefined) {
       actionConfig.destinationName = resources[actionConfig.counter].displayname;
     }; 
     webdav.copy(resources[actionConfig.counter].path,nl.sara.beehub.controller.createActionCallback(resources, actionConfig), actionConfig.destinationPath + actionConfig.destinationName, actionConfig.overwrite);
   };
   break;
 // move settings 
 case "move":
   break;
 // delete settings
 case "delete":
   config.action = function(){
     webdav.remove(resources[actionConfig.counter].path,nl.sara.beehub.controller.createActionCallback(resources,actionConfig));
   };
   break;
 default:
   // This should never happen
 }
 config.action();
};