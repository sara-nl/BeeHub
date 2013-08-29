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
 * Clear views
 * 
 */
nl.sara.beehub.controller.clearAllViews = function(){
  nl.sara.beehub.view.clearAllViews()
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
  // Get type
  if (data.getResponse(path).getProperty('DAV:','resourcetype') !== undefined) {
    var resourcetypeProp = data.getResponse(path).getProperty('DAV:','resourcetype');
    var getcontenttypeProp = data.getResponse(path).getProperty('DAV:','getcontenttype');
    if ((resourcetypeProp.xmlvalue.length === 1) &&(resourcetypeProp.xmlvalue.item(0).namespaceURI==='DAV:')){
        resource.setType(nl.sara.webdav.Ie.getLocalName(resourcetypeProp.xmlvalue.item(0)));
    } else {
      if ((getcontenttypeProp.xmlvalue.length === 1) && (getcontenttypeProp.namespace ==='DAV:')){
        resource.setType(getcontenttypeProp.getParsedValue());
      }
    }
  } 
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
 * @param String   {resourcepath} Resource path
 * @param Function {callback}     Callback function
 */
nl.sara.beehub.controller.getResourcePropsFromServer = function(resourcepath, callback){
  // Collect resource details
  var webdav = new nl.sara.webdav.Client();
  
  var resourcetypeProp = new nl.sara.webdav.Property();
  resourcetypeProp.tagname = 'resourcetype';
  resourcetypeProp.namespace='DAV:';
  
  var getcontenttypeProp = new nl.sara.webdav.Property();
  getcontenttypeProp.tagname = 'getcontenttype';
  getcontenttypeProp.namespace='DAV:';
  
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
  
  var properties = [resourcetypeProp, getcontenttypeProp, displaynameProp, ownerProp, getlastmodifiedProp, getcontentlengthProp];
  
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
        nl.sara.beehub.view.tree.reload();
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
 * Rename an object.
 * 
 * @param Object  resource      Resource to rename
 * @param String  fileNameNew   New resource name
 * @param Integer overwriteMode Fail on overwrite or overwrite
 * 
 */
nl.sara.beehub.controller.renameResource = function(resource, fileNameNew, overwriteMode){
  var webdav = new nl.sara.webdav.Client();
  webdav.move(resource.path, nl.sara.beehub.controller.createRenameCallback(resource, fileNameNew, overwriteMode), nl.sara.beehub.controller.path +fileNameNew,  overwriteMode);
};

/**
 * Rename callback.
 * 
 * Shows overwrite dialog when resource already exist. When rename is successfull,
 * update view.
 * 
 * @param Object  resource      Resource to rename
 * @param String  fileNameNew   New resource name
 * @param Integer overwriteMode Fail on overwrite or overwrite
 * 
 */
nl.sara.beehub.controller.createRenameCallback = function(resource, fileNameNew, overwriteMode) {
  return function(status) {
    // Resource already exist
    if (status === 412) {
      // show overwrite dialog
      nl.sara.beehub.view.dialog.showOverwriteDialog(resource, fileNameNew, function(){
        nl.sara.beehub.controller.renameResource(resource, fileNameNew,  nl.sara.webdav.Client.SILENT_OVERWRITE);
      });
    } 
    if (status === 201 || status === 204) {
      // get unknown values of original
      resource = nl.sara.beehub.view.content.getUnknownResourceValues(resource); 
      // new path
      var newPath = nl.sara.beehub.controller.path+fileNameNew;
      // new resource
      var resourceNew = new nl.sara.beehub.ClientResource(newPath);
      resourceNew.setDisplayName(fileNameNew);
      resourceNew.setType(resource.type);
      resourceNew.setContentLength(resource.contentlength);
      resourceNew.setLastModified(resource.lastmodified);
      resourceNew.setOwner(resource.owner);
      
      // 
      if (overwriteMode !== nl.sara.webdav.Client.SILENT_OVERWRITE) {
        nl.sara.beehub.view.updateResource(resource, resourceNew);
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
  }
}
 
/*
 * Initialize action Copy, Move, Upload or Delete
 * 
 * @param List items The resource list
 * @param String action Which action
 */
nl.sara.beehub.controller.initAction = function(items, action){
  nl.sara.beehub.controller.actionCounter = 0;
  nl.sara.beehub.controller.actionAction = action;
  
  if (action !== "upload"){
    nl.sara.beehub.controller.actionResources = items;
  } else {
    nl.sara.beehub.controller.actionResources = [];
    nl.sara.beehub.controller.actionFiles = {};
    $.each(items, function(i, item){
      var resource = new nl.sara.beehub.ClientResource(nl.sara.beehub.controller.path + item.name);
      resource.setDisplayName(item.name);
      nl.sara.beehub.controller.actionResources.push(resource);
      nl.sara.beehub.controller.actionFiles[nl.sara.beehub.controller.path + item.name] = item;
    });
  };

  if (action === "copy" || action === "move") {
    nl.sara.beehub.controller.setCopyMoveView(true);
    // Change select node handler in tree to get destination
    nl.sara.beehub.view.tree.setOnActivate("Select "+action+" destination", function(path){
      nl.sara.beehub.controller.actionDestination = path;
      nl.sara.beehub.controller.setCopyMoveView(false);
      nl.sara.beehub.view.tree.clearView();
      nl.sara.beehub.view.dialog.showResourcesDialog(function() {
        nl.sara.beehub.controller.startAction();
      });
    });
    // show tree
    nl.sara.beehub.view.tree.showTree();
  } else {
    nl.sara.beehub.view.dialog.showResourcesDialog(function() {
      nl.sara.beehub.controller.startAction();
    });
  };
};

/*
 * Make view copy or move ready
 */
nl.sara.beehub.controller.setCopyMoveView = function(view){
  if (view) {
    nl.sara.beehub.view.tree.cancelButton('show');
    nl.sara.beehub.view.maskView(true);
    nl.sara.beehub.view.tree.noMask(true);
    nl.sara.beehub.view.tree.slideTrigger('left'); 
    nl.sara.beehub.view.tree.slideTrigger('hide'); 
  } else {
    nl.sara.beehub.view.tree.cancelButton('hide');
    nl.sara.beehub.view.maskView(false);
    nl.sara.beehub.view.tree.noMask(false);
    nl.sara.beehub.view.tree.slideTrigger('show');
  }
}

/*
 * Start action Copy, Move, Upload or Delete
 */
nl.sara.beehub.controller.startAction = function(){
  var resource = nl.sara.beehub.controller.actionResources[nl.sara.beehub.controller.actionCounter];
  
  // create webdav client object
  var webdav = new nl.sara.webdav.Client();
  
  var resourcePath = nl.sara.beehub.controller.actionResources[nl.sara.beehub.controller.actionCounter].path;
  
  switch(nl.sara.beehub.controller.actionAction)
  {
  // copy
  case "copy":
      var resourceDestination = nl.sara.beehub.controller.actionDestination + nl.sara.beehub.controller.actionResources[nl.sara.beehub.controller.actionCounter].displayname;
      webdav.copy(resourcePath, nl.sara.beehub.controller.createActionCallback(resource, 0), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
    break;
  // move settings 
  case "move": 
    var resourceDestination = nl.sara.beehub.controller.actionDestination + nl.sara.beehub.controller.actionResources[nl.sara.beehub.controller.actionCounter].displayname;
    webdav.move(resourcePath,nl.sara.beehub.controller.createActionCallback(resource, 0), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
    break;
  // delete settings
  case "delete":
    webdav.remove(resourcePath,nl.sara.beehub.controller.createActionCallback(resource, 0));
    break;
  case "upload":
    webdav.head(resourcePath, nl.sara.beehub.controller.createUploadHeadCallback(resource, 0) ,"");
  default:
    // This should never happen
  }
};

/*
 * Start next action
 */
nl.sara.beehub.controller.startNextAction = function(){
  // less typework
  var counter = nl.sara.beehub.controller.actionCounter;
  var resources = nl.sara.beehub.controller.actionResources;
  
  // Next resource
  if (resources[counter + 1] !== undefined){
    nl.sara.beehub.controller.actionCounter = counter + 1;
    nl.sara.beehub.controller.startAction();
  } else {
    nl.sara.beehub.view.dialog.setDialogReady(function(){
      nl.sara.beehub.view.clearAllViews(); 
    });
  };
};


/*
 * Update view after succesfull action
 * 
 * @params {Object} resource Resource to create callback for
 * @params {Integer} renameCounter Used to create new name when object already exist
 */
nl.sara.beehub.controller.updateActionView = function(resource, renameCounter){
  switch(nl.sara.beehub.controller.actionAction)
  {
    // Update view
    case "delete":
      nl.sara.beehub.view.deleteResource(resource);
      break; 
    case "move":
      // Move to current directory (is automatically with rename)
      if (nl.sara.beehub.controller.actionDestination === nl.sara.beehub.controller.path) {
        var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_"+renameCounter;
        nl.sara.beehub.controller.getResourcePropsFromServer(resourceDestination, function(resource) {
          var orgResource = new nl.sara.beehub.ClientResource(resource.path);
          nl.sara.beehub.view.updateResource(orgResource,resource);
        }); 
      } else {
        nl.sara.beehub.view.deleteResource(resource);
      }
      break;
    case  "copy":
      // Move to current directory (is automatically with rename)
      if (nl.sara.beehub.controller.actionDestination === nl.sara.beehub.controller.path) {
        var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_"+renameCounter;
        nl.sara.beehub.controller.getResourcePropsFromServer(resourceDestination, function(resource) {
          nl.sara.beehub.view.addResource(resource);
        }); 
      };
      break;
    default:
      // This should never happen
  }
}

/*
 * Return callback funtion for copy, move, delete requests
 * 
 * @params {Object} resource Resource to create callback for
 * @params {Integer} renameCounter Used to create new name when object already exist
 */
nl.sara.beehub.controller.createActionCallback = function(resource, renameCounter){
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
      nl.sara.beehub.controller.updateActionView(resource, renameCounter);
      break;
      
    // Forbidden
    case 403:
      nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Forbidden");
      break;
    
      // Parent directory
    case 501:
    case 512:
      nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Copy to parent resource is not possible.");
      break;
      
    // Already exist
    case 412:
      if (0 === renameCounter) {
        nl.sara.beehub.controller.setAlreadyExist(resource,1);
      } else {
        var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_"+renameCounter;
        renameCounter = renameCounter + 1;
        if (nl.sara.beehub.controller.actionAction === "copy") {
          webdav.copy(resource.path, nl.sara.beehub.controller.createActionCallback(resource, renameCounter), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
        };
        if (nl.sara.beehub.controller.actionAction === "move") {
          webdav.move(resource.path, nl.sara.beehub.controller.createActionCallback(resource, renameCounter), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
        };
      }
      break;
      
    default:
      nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Unknown error");
    };
    if (0 === renameCounter) {
      // Scroll to next position
      nl.sara.beehub.view.dialog.scrollTo(nl.sara.beehub.controller.actionCounter*35);
      // Start next action
      nl.sara.beehub.controller.startNextAction();
    };
  }
};

/*
 * Return callback function head request for uploading files to check if resource
 * exists on server
 * 
 */
nl.sara.beehub.controller.createUploadHeadCallback = function(resource, renameCounter){
  return function(status) {  
    var webdav = new nl.sara.webdav.Client();
    switch(status)
    {
    // File exists
    case 200:
      if (0 === renameCounter){
        nl.sara.beehub.controller.setAlreadyExist(resource);
      } else {
        renameCounter = renameCounter + 1;
        destination = resource.path+"_"+renameCounter;
        webdav.head(destination, nl.sara.beehub.controller.createUploadHeadCallback(resource, renameCounter));
      };
      if (0 === renameCounter) {
        // Start next
        nl.sara.beehub.controller.startNextAction();
      };
      break;
    // File does not exist
    case 404:
      if (0 === renameCounter) {
        var destination = resource.path;
      } else {
        var destination = resource.path+"_"+renameCounter;
      }
      // Put empty file
      webdav.put(destination, nl.sara.beehub.controller.createUploadEmptyFileCallback(resource, destination, renameCounter, false),"");
      break;
    default:
      if (0 === renameCounter) {
        // Start next
        nl.sara.beehub.controller.startNextAction();
      };
    };
  };
};

/*
 * Return callback function upload empty file request for uploading files to check if upload
 * is allowed
 * 
 */
nl.sara.beehub.controller.createUploadEmptyFileCallback = function(resource, destination, renameCounter, overwrite) {
  return function(status, responseText) {  
    switch(status)
    {
    // Ok
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
            nl.sara.beehub.controller.createUploadCallback(resource, destination, renameCounter, overwrite),
            headers 
        );
        
        if (ajax.upload) {
           // progress bar
           ajax.upload.addEventListener("progress", function(event) {
             var progress = parseInt(event.loaded / event.total * 100);
             nl.sara.beehub.view.dialog.showProgressBar(resource,progress);
           }, false);
        } else {
          nl.sara.beehub.view.dialog.updateResourceInfo(resource,'Uploading...');
        };
        ajax.send(nl.sara.beehub.controller.actionFiles[resource.path]);  
      break;
    // Forbidden
    case 403:
      nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Forbidden");
      break;
    default:
      nl.sara.beehub.view.dialog.updateResourceInfo(resource,responseText);
      // Start next action
      if (0 === renameCounter) {
        nl.sara.beehub.controller.startNextAction();
      };
    };
  };
};

/*
 * Return callback function upload
 * 
 */
nl.sara.beehub.controller.createUploadCallback = function(resource, destination, renameCounter, overwrite){
  return function(status, responseText) {
    switch(status)
    {
      // Ok
      case 201:
      case 204:
        // Upload file, this will overwrite the empty file
        nl.sara.beehub.view.dialog.showProgressBar(resource,100);
        if (overwrite) {
          nl.sara.beehub.view.deleteResource(resource);
        };
        nl.sara.beehub.controller.getResourcePropsFromServer(destination, nl.sara.beehub.view.addResource);
        break;
      default:
        nl.sara.beehub.view.dialog.updateResourceInfo(resource,responseText);
      // Delete empty file
        var webdav = new nl.sara.webdav.Client();
        webdav.remove(destination);
    }
    if (0 === renameCounter) {
      nl.sara.beehub.controller.startNextAction();
   // Scroll to next position
      nl.sara.beehub.view.dialog.scrollTo(nl.sara.beehub.controller.actionCounter*35);
    };
  };
};


/*
* Show rename, overwrite and cancel buttons and make handlers for this button
*/
nl.sara.beehub.controller.setAlreadyExist = function(resource){
  var webdav = new nl.sara.webdav.Client();

  switch(nl.sara.beehub.controller.actionAction)
  {
    // copy and move
    case "copy":
      var overwrite = function() {
        var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname;
        webdav.copy(resource.path, nl.sara.beehub.controller.createActionCallback(resource, 1), resourceDestination, nl.sara.webdav.Client.SILENT_OVERWRITE);
      };
      
      var rename = function() {
        var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_1";
        webdav.copy(resource.path, nl.sara.beehub.controller.createActionCallback(resource, 1), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
      };
      break;
    case "move": 
      var overwrite = function() {
        var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname;
        webdav.move(resource.path, nl.sara.beehub.controller.createActionCallback(resource, 1), resourceDestination, nl.sara.webdav.Client.SILENT_OVERWRITE);
      };
      
      var rename = function() {
        var resourceDestination = nl.sara.beehub.controller.actionDestination + resource.displayname+"_1";
        webdav.move(resource.path, nl.sara.beehub.controller.createActionCallback(resource, 1), resourceDestination, nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
      };  
      break;
    case "upload":
      var overwrite = function() {
        webdav.put(resource.path, nl.sara.beehub.controller.createUploadEmptyFileCallback(resource, resource.path, 1, true), "");
      };
      
      var rename = function() {
        var resourcePath = resource.path+"_1";
        webdav.head(resourcePath, nl.sara.beehub.controller.createUploadHeadCallback(resource, 1) ,"");
      };
      break;
    default:
      // This should never happen
  } ;
  
  var cancel = function() {
    nl.sara.beehub.view.dialog.updateResourceInfo(resource,"Canceled");
  };
  
  nl.sara.beehub.view.dialog.setAlreadyExist(resource, overwrite, rename, cancel);
};

