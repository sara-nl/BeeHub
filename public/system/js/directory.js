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
"use strict"

// Create the namespace if that's not done yet
if (nl === undefined) {
  /** @namespace */
  var nl = {};
}
if (nl.sara === undefined) {
  /** @namespace */
  nl.sara = {};
}
if (nl.sara.beehub === undefined) {
  /** @namespace The entire client is in this namespace. */
  nl.sara.beehub = {};
}
if (nl.sara.beehub.view === undefined) {
  /** @namespace Holds all the view classes */
  nl.sara.beehub.view = {};
}
if (nl.sara.beehub.view.contents === undefined) {
  /** @namespace Holds all the view classes */
  nl.sara.beehub.view.contents = {};
}
if (nl.sara.beehub.view.tree === undefined) {
  /** @namespace Holds all the view classes */
  nl.sara.beehub.view.tree = {};
}
if (nl.sara.beehub.controller === undefined) {
  /** @namespace Holds all the view classes */
  nl.sara.beehub.controller = {};
}
if (nl.sara.beehub.view.dialog === undefined) {
  /** @namespace Holds all the view classes */
  nl.sara.beehub.view.dialog = {};
}
//
//if (nl.sara.beehub.Resource === undefined) {
//  /** @namespace Holds all the view classes */
//  nl.sara.beehub.Resource = {};
//}

///**
// * @class BeeHub client Resource
// *
// **/
////If nl.sara.webdav.Client is already defined, we have a namespace clash!
//if (nl.sara.webdav.view.Resource !== undefined) {
//  throw new nl.sara.webdav.Exception('Namespace nl.sara.beehub.view.Resource already taken, could not load client.', nl.sara.webdav.Exception.NAMESPACE_TAKEN);
//}
//var nl.sara.beehub.view.Resource = function() {}
//


/** 
 * Beehub Client
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */
  $(function() {
    
  // Directory tree in tree panel
  $("#bh-dir-tree").dynatree({
    onActivate: function(node) {
      // A DynaTreeNode object is passed to the activation handler
      // Note: we also get this event, if persistence is on, and the page is reloaded.
//      alert("You activated " + node.data.title);
//      window.location.reload();
    },
    persist: false,
    children: treecontents,
    onLazyRead: function(node){
      var client = new nl.sara.webdav.Client();
      var resourcetypeProp = new nl.sara.webdav.Property();
      resourcetypeProp.tagname = 'resourcetype';
      resourcetypeProp.namespace='DAV:';
      var properties = [resourcetypeProp];
      client.propfind(node.data.id, function(status, data) {
        // Callback
        if (status != 207) {
          // Server returned an error condition: set node status accordingly
          node.setLazyNodeStatus(DTNodeStatus_Error, {
            tooltip: data.faultDetails,
            info: data.faultString
          });
        };
        var res = [];
        $.each(data.getResponseNames(), function(pathindex){
          var path = data.getResponseNames()[pathindex];
          
          if (node.data.id !== path) {
            if (data.getResponse(path).getProperty('DAV:','resourcetype') !== undefined) {
              var resourcetypeProp = data.getResponse(path).getProperty('DAV:','resourcetype');
              if ((resourcetypeProp.xmlvalue.length == 1)
                  &&(nl.sara.webdav.Ie.getLocalName(resourcetypeProp.xmlvalue.item(0))=='collection')
                  &&(resourcetypeProp.xmlvalue.item(0).namespaceURI=='DAV:')) 
              {
                var name = path;
                while (name.substring(name.length-1) == '/') {
                  name = name.substr(0, name.length-1);
                }
                name = decodeURIComponent(name.substr(name.lastIndexOf('/')+1));
                res.push({
                  'title': name,
                  'id' : path,
                  'isFolder': 'true',
                  'isLazy' : 'true'
                });
              }
            }
          };
        });
        // PWS status OK
        node.setLazyNodeStatus(DTNodeStatus_Ok);
        node.addChild(res);
        // end callback
      },1,properties);
    }
  });
//  $("#bh-dir-tree").dynatree({
//    onActivate: function(node) {
//        // A DynaTreeNode object is passed to the activation handler
//        // Note: we also get this event, if persistence is on, and the page is reloaded.
//        alert("You activated " + node.data.title);
//    }
//  });

	// solving bug: https://github.com/twitter/bootstrap/issues/6094
	// conflict bootstrap and jquery
	var btn = $.fn.button.noConflict() // reverts $.fn.button to jqueryui btn
	$.fn.btn = btn // assigns bootstrap button functionality to $.fn.btn
	
	

	
	// UPLOAD
	/**
	 * Upload file to server
	 * 
	 * @param string fileName
	 * @param file file
	 * @param function callback
	 * 
	 */
	var uploadToServer = function(fileName, file, callback){
		var path = location.href;
		// add slash to the end of path
		if (!path.match(/\/$/)) {
			path=path+'/'; 
		} 
		// TODO eerst leeg bestand sturen om te controleren of het wel mag
		var headers = {
			'Content-Type': 'application/octet-stream'
		};
		
		// closure for variable file
		function callback2(file) {
	        return function(status) {
				// Forbidden
				if (status === 403) {
					$("#bh-dir-dialog").find('td[id="bh-dir-'+file.name+'"]').html("<div class='progress progress-danger progress-striped'><div class='bar' style='width: 100%;'>Forbidden</div></div>");
				//succeeded
				} else if (status === 201 || status === 204) {
					$("#bh-dir-dialog").find('td[id="bh-dir-'+file.name+'"]').html("<div class='progress progress-success progress-striped'><div class='bar' style='width: 100%;'>100%</div></div>");
				// Unknown error
				} else {
					$("#bh-dir-dialog").find('td[id="bh-dir-'+file.name+'"]').html("<div class='progress progress-danger progress-striped'><div class='bar' style='width: 100%;'>Unknown error</div></div>");
				};
				if (callback !== null) {
					callback();
				};
	        }
		}
		var ajax = nl.sara.webdav.Client.getAjax( 
			"PUT",
	        path + fileName,
            callback2(file),
	        headers 
	    );
		 
	    if (ajax.upload) {
	    	 // progress bar
	    	 ajax.upload.addEventListener("progress", function(event) {
	    		 var progress = parseInt(event.loaded / event.total * 100);
	    		 $("#bh-dir-dialog").find('td[id="bh-dir-'+file.name+'"]').html("<div class='progress progress-success progress-striped'><div class='bar' style='width: "+progress+"%;'>"+progress+"%</div></div>");

	    	 
	    	 }, false);
	    } else {
	    	$("#bh-dir-dialog").find('td[id="bh-dir-'+file.name+'"]').html('Bezig... (ik kan geen voortgang laten zien in deze browser)');
	    }
		ajax.send(file);  
	};
	
	/**
	 * Overwrite handler
	 * 
	 * @param string fileName
	 * @param object fileHash
	 * 
	 */
	function setOverwriteHandler(fileName, filesHash){
		$("#bh-dir-dialog").find('button[id="bh-dir-upload-overwrite-'+fileName+'"]').click(function(){
			uploadToServer(fileName, filesHash[fileName], null );
		})
	};
	
	/**
	 * Cancel handler
	 * 
	 * @param string fileName
	 * 
	 */
	function setCancelHandler(fileName) {
		$("#bh-dir-dialog").find('button[id="bh-dir-upload-cancel-'+fileName+'"]').click(function(){
			$("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').html("<div class='progress progress-danger progress-striped'><div class='bar' style='width: 100%;'>Canceled</div></div>");
		})
	}
	
	/**
	 * Rename handler
	 * 
	 * @param string fileName
	 * @param string fileNameOrg
	 * @param object filesHash
	 */
	function setRenameHandler(fileName, fileNameOrg, filesHash){
		$("#bh-dir-dialog").find('button[id="bh-dir-upload-rename-'+fileName+'"]').click(function(){
			// search fileName td and make input field
			var fileNameOrg= $("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').prev().html();
			var buttonsOrg = $("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').html();
			$("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').prev().html("<input id='bh-dir-upload-rename-input-"+fileName+"' value='"+fileName+"'></input>");
			// change buttons - cancel and upload
			var renameUploadButton = '<button id="bh-dir-upload-rename-upload-'+fileNameOrg+'" name="'+fileNameOrg+'" class="btn btn-success">Upload</button>'
			var renameCancelButton = '<button id="bh-dir-upload-rename-cancel-'+fileNameOrg+'" class="btn btn-danger">Cancel</button>'
			$("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').html(renameUploadButton+" "+renameCancelButton);
			// handler cancel rename
			$("#bh-dir-dialog").find('button[id="bh-dir-upload-rename-cancel-'+fileName+'"]').click(function(){
				$("#bh-dir-dialog").find('td[id="bh-dir-'+fileNameOrg+'"]').html(buttonsOrg);
				$("#bh-dir-dialog").find('td[id="bh-dir-'+fileNameOrg+'"]').prev().html(fileNameOrg);
				setOverwriteHandler(fileName, filesHash);
				setCancelHandler(fileName);
				setRenameHandler(fileName, fileNameOrg, filesHash);
			})
			// add handler upload rename
			$("#bh-dir-dialog").find('button[id="bh-dir-upload-rename-upload-'+fileName+'"]').click(function(){
				var newName = $("#bh-dir-dialog").find('input[id="bh-dir-upload-rename-input-'+fileName+'"]').val();
				if (newName !== fileName) {
					$("#bh-dir-dialog").find('td[id="bh-dir-'+fileNameOrg+'"]').prev().html(fileName+" <br/> <b>renamed to</b> <br/> "+newName);
				};
				checkFileName(newName, filesHash[fileName], null, filesHash);
			})
		})
	};
	
	/**
	 * Check file name 
	 * 
	 * Checks if the files already exists on the server. If not start upload, otherwise
	 * add buttons
	 * 
	 * @param string fileName
	 * @param file file
	 * @param function callback
	 * @param object filesHash
	 * 
	 */
	function checkFileName(fileName, file, callback, filesHash){
		var webdav = new nl.sara.webdav.Client();
		
		// closure for variables fileName, file, callback  
		function callback2(fileName,file,callback){
			return function(status, body, headers){
				// File does nog exist
				if (status === 404)  {
					uploadToServer( fileName, file, callback);
					return;
				};
				// File exist
				if (status === 200) {
					var overwriteButton = '<button id="bh-dir-upload-overwrite-'+fileName+'" name="'+fileName+'" class="btn btn-danger">Overwrite</button>'
					var renameButton = '<button id="bh-dir-upload-rename-'+fileName+'" class="btn btn-success">Rename</button>'
					var cancelButton = '<button id="bh-dir-upload-cancel-'+fileName+'" name="'+fileName+'" class="btn btn-success">Cancel</button>'
					$("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').html("File exist on server!<br/>"+renameButton+" "+overwriteButton+" "+cancelButton);
					setOverwriteHandler(fileName, filesHash);
					setCancelHandler(fileName);
					setRenameHandler(fileName, file.name, filesHash);
					if (callback !== null) {
						callback();
					}
				} 
			}
		}
		webdav.head(path + fileName, callback2(fileName,file,callback) ,"");
	};
	
	/**
	 * Upload handler
	 * 
	 */
    function handleUpload(files, filesHash) {
        counter++;
        if ( counter < files.length ) {
        	$("#bh-dir-dialog").scrollTop(counter*50);
        	checkFileName( files[counter].name, files[counter], function(){handleUpload(files, filesHash)}, filesHash );
        } else { 
        	$('#bh-dir-close-upload-button').button("enable");
        	// Samenvatting upload
        }
      } // End of handleUpload()
    
    
	// Upload handlers
	$('#bh-dir-upload').click(function() {
		// show local files and directories
		$('#bh-dir-upload-hidden').click();
	});
	
	// Upload dialog
	var counter;
	$('#bh-dir-upload-hidden').change(function(){
		$("#bh-dir-dialog").html("");
		var upload_files = new Object();
		var files = $('#bh-dir-upload-hidden')[0].files;
		var filesHash = {};
		for (var i = 0; i < files.length; i++) {
			filesHash[files[i].name] = files[i];
		};
		var appendString=''
	 	appendString = appendString + '<table class="table"><tbody>';
		for (var i = 0; i < files.length; i++) {
			appendString = appendString + '<tr><td>'+files[i].name+'</td><td width="60%" id="bh-dir-'+files[i].name+'"></td></tr>'
		};	
		appendString = appendString +'</tbody></table>';
		$("#bh-dir-dialog").append(appendString);

		$("#bh-dir-dialog").dialog({
	    	modal: true,
	    	maxHeight: 400,
	    	title: " Upload",
	    	closeOnEscape: false,
	    	dialogClass: "no-close",
	    	width: 600,
	    	buttons: [{
				text: "Ready",
				id: 'bh-dir-close-upload-button',
		    	disabled: true,
				click: function() {
					window.location.reload();
				}
			}]
	    });
		// Start upload
		counter = -1;
		handleUpload(files, filesHash);
	});
	// END UPLOAD

	

	
//	$("#bh-dir-contents-table").tablesorter();

	
	// DELETE
	 /**
   * Delete an object from an array and when not finished call this function again
   * with the next item of the array
   * 
   * @param array deleteArray
   * @param integer counter
   * 
   */
	function deleteItem(deleteArray, counter){
    var webdav = new nl.sara.webdav.Client();
    
    function callback(deleteArray, counter) {
      return function(status) {
        if (deleteArray[counter+1] != undefined) {
          deleteItem(deleteArray,counter+1);
        } else {
          $('#bh-dir-delete-button').button({label:"Ready"});
          $('#bh-dir-delete-button').button("enable");
          $('#bh-dir-delete-button').removeClass("btn-danger");
          $('#bh-dir-cancel-delete-button').hide();
          $('#bh-dir-delete-button').unbind('click').click(function(){
            window.location.reload();
          })
        }
        $("#bh-dir-dialog").scrollTop(counter*35);
        if (status === 403) {
          $("#bh-dir-dialog").find('td[id="bh-dir-delete-'+deleteArray[counter].value+'"]').html("<b>Forbidden</b>");
          return;
        }
        if (status == 204) {
          $("#bh-dir-dialog").find('td[id="bh-dir-delete-'+deleteArray[counter].value+'"]').html("<b>Deleted</b>");
        } else {
          $("#bh-dir-dialog").find('td[id="bh-dir-delete-'+deleteArray[counter].value+'"]').html("<b>Unknown error</b>");
        }
 
      }
    };
     
    webdav.remove(path + deleteArray[counter].value,callback(deleteArray, counter));
	}
	 /**
   * Overwrite Copy/Move/Delete handler
   * 
   * @param string fileName
   * @param object fileHash
   * 
   */
  function setActionOverwriteHandler(actionConfig){
    $("#bh-dir-dialog").find('button[id="bh-dir-action-overwrite-'+actionConfig.contents[actionConfig.counter].value+'"]').click(function(){
      actionConfig.overwrite = nl.sara.webdav.Client.SILENT_OVERWRITE;
      actionConfig.single = true;
      actionItem(actionConfig);
    })
  };
  
  /**
   * Cancel Copy/Move/Delete handler
   * 
   * @param string fileName
   * 
   */
  function setActionCancelHandler(actionConfig) {
    $("#bh-dir-dialog").find('button[id="bh-dir-action-cancel-'+actionConfig.contents[actionConfig.counter].value+'"]').click(function(){
      $("#bh-dir-dialog").find('td[id="bh-dir-action-'+actionConfig.contents[actionConfig.counter].value+'"]').html("<div class='progress progress-danger progress-striped'><div class='bar' style='width: 100%;'>Canceled</div></div>");
    })
  }
  
  /**
   * Rename Copy/Move/Delete handler
   * 
   * @param string fileName
   * @param string fileNameOrg
   * @param object filesHash
   */
  function setActionRenameHandler(actionConfig){
    var fileName = actionConfig.contents[actionConfig.counter].value;
    $("#bh-dir-dialog").find('button[id="bh-dir-upload-rename-'+fileName+'"]').click(function(){
      // search fileName td and make input field
      var fileNameOrg= $("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').prev().html();
      actionConfig.filenameOrg = fileNameOrg;
      var buttonsOrg = $("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').html();
      $("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').prev().html("<input id='bh-dir-upload-rename-input-"+fileName+"' value='"+fileName+"'></input>");
      // change buttons - cancel and upload
      var renameUploadButton = '<button id="bh-dir-upload-rename-upload-'+fileNameOrg+'" name="'+fileNameOrg+'" class="btn btn-success">Upload</button>'
      var renameCancelButton = '<button id="bh-dir-upload-rename-cancel-'+fileNameOrg+'" class="btn btn-danger">Cancel</button>'
      $("#bh-dir-dialog").find('td[id="bh-dir-'+fileName+'"]').html(renameUploadButton+" "+renameCancelButton);
      // handler cancel rename
      $("#bh-dir-dialog").find('button[id="bh-dir-upload-rename-cancel-'+fileName+'"]').click(function(){
        $("#bh-dir-dialog").find('td[id="bh-dir-'+fileNameOrg+'"]').html(buttonsOrg);
        $("#bh-dir-dialog").find('td[id="bh-dir-'+fileNameOrg+'"]').prev().html(fileNameOrg);
        setActionOverwriteHandler(actionConfig);
        setActionCancelHandler(actionConfig);
        setActionRenameHandler(actionConfig);
      })
      // add handler upload rename
      $("#bh-dir-dialog").find('button[id="bh-dir-upload-rename-upload-'+fileName+'"]').click(function(){
        var newName = $("#bh-dir-dialog").find('input[id="bh-dir-upload-rename-input-'+fileName+'"]').val();
        if (newName !== fileName) {
          $("#bh-dir-dialog").find('td[id="bh-dir-'+fileNameOrg+'"]').prev().html(fileName+" <br/> <b>renamed to</b> <br/> "+newName);
        };
        checkFileName(newName, filesHash[fileName], null, filesHash);
      })
    })
  };
	 // DELETE, COPY, MOVE selected items
  /**
  * Delete, Copy, Move an object from an array and when not finished call this function again
  * with the next item of the array
  * 
  * @param array contentArray
  * @param integer actionConfig.counter
  * 
  */
 function actionItem(actionConfig){
   var webdav = new nl.sara.webdav.Client();
   console.log("in action item");
   console.log(actionConfig);
   function callback(actionConfig) {
     console.log("callback");
     console.log(actionConfig);
     return function(status) {
       console.log("in return");
       console.log(actionConfig);
       if (!actionConfig.single) {
    
         if (actionConfig.contents[actionConfig.counter] != undefined) {
         
           actionItem(actionConfig);
         } else {
           $('#bh-dir-action-button').button({label:"Ready"});
           $('#bh-dir-action-button').button({label:"Ready"});
           $('#bh-dir-action-button').button("enable");
           $('#bh-dir-action-button').removeClass("btn-danger");
           $('#bh-dir-cancel-action-button').hide();
           $('#bh-dir-action-button').unbind('click').click(function(){
             window.location.reload();
           })
         }
         $("#bh-dir-dialog").scrollTop(actionConfig.counter*35);
       };
       if (status === 403) {
         $("#bh-dir-dialog").find('td[id="bh-dir-action-'+actionConfig.contents[actionConfig.counter].value+'"]').html("<b>Forbidden</b>");
         return;
       }
       console.log(status);
       console.log("in not undefined");

       console.log(actionConfig);
       if ((status === 201) || (status === 204)) {
         $("#bh-dir-dialog").find('td[id="bh-dir-action-'+actionConfig.contents[actionConfig.counter].value+'"]').html("<b>Done</b>");
       } else if (status === 412) {
         var overwriteButton = '<button id="bh-dir-action-overwrite-'+actionConfig.contents[actionConfig.counter].value+'" name="'+actionConfig.contents[actionConfig.counter].value+'" class="btn btn-danger">Overwrite</button>'
         var renameButton = '<button id="bh-dir-action-rename-'+actionConfig.contents[actionConfig.counter].value+'" class="btn btn-success">Rename</button>'
         var cancelButton = '<button id="bh-dir-action-cancel-'+actionConfig.contents[actionConfig.counter].value+'" name="'+actionConfig.contents[actionConfig.counter].value+'" class="btn btn-success">Cancel</button>'

         $("#bh-dir-dialog").find('td[id="bh-dir-action-'+actionConfig.contents[actionConfig.counter].value+'"]').html("Item exist on server!<br/>"+renameButton+" "+overwriteButton+" "+cancelButton);
         setActionOverwriteHandler(actionConfig);
         setActionCancelHandler(actionConfig);
         setActionRenameHandler(actionConfig);
       } else {
         $("#bh-dir-dialog").find('td[id="bh-dir-action-'+actionConfig.contents[actionConfig.counter].value+'"]').html("<b>Unknown error</b>");
       }
     }
   };
   
   if (actionConfig.action === "delete") { 
     webdav.remove(path + actionConfig.contents[actionConfig.counter].value,callback(actionConfig.contents, actionConfig.counter));
   } else if (actionConfig.action === "copy") { 
     console.log('nu voor copy');
     console.log(actionConfig);
     webdav.copy(path + actionConfig.contents[actionConfig.counter].value,callback(actionConfig),actionConfig.copyTo+actionConfig.contents[actionConfig.counter].value, actionConfig.overwrite);
   }; 
   actionConfig.counter = actionConfig.counter +1;
 }
	
	// Delete handler
	$('#bh-dir-delete').click(function(e){
    $("#bh-dir-dialog").html("");
    var appendString='';
    appendString = appendString + '<table class="table"><tbody>';
    var deleteArray=[];
    $('.bh-dir-checkbox:checked').each(function(){
      appendString = appendString + '<tr><td>'+$(this).val()+'</td><td width="20%" id="bh-dir-delete-'+$(this).val()+'"></td></tr>'
    });
    appendString = appendString +'</tbody></table>';
    $("#bh-dir-dialog").append(appendString);
    $("#bh-dir-dialog").dialog({
      modal: true,
      maxHeight: 400,
      title: "Delete",
      closeOnEscape: false,
      dialogClass: "no-close",
      minWidth: 400,
      buttons: [{
        text: "Cancel",
        id: 'bh-dir-cancel-delete-button',
        click: function() {
         $(this).dialog("close");
        }
      }, {
        text: "Delete",
        id: 'bh-dir-delete-button',
        click: function() {
          $('#bh-dir-delete-button').button({label:"Deleting..."});
          $('#bh-dir-delete-button').button("disable");
          deleteItem($('.bh-dir-checkbox:checked'),0);
        }
      }]
    });
    $("#bh-dir-delete-button").addClass("btn-danger");
	})
	
	// COPY
	// Copy handler
	$('#bh-dir-copy').click(function(e) {
	  // change click listener in tree
	      // show dialog with items to copy and target directory
      $("#bh-dir-tree").dynatree({
      onActivate: function(node) {
          // A DynaTreeNode object is passed to the activation handler
          // Note: we also get this event, if persistence is on, and the page is reloaded.
          $("#bh-dir-dialog").html("");
          var appendString='';
          appendString = appendString + '<table class="table"><tbody>';
          var deleteArray=[];
          $('.bh-dir-checkbox:checked').each(function(){
            appendString = appendString + '<tr><td>'+$(this).val()+'</td><td width="60%" id="bh-dir-action-'+$(this).val()+'"></td></tr>'
          });
          appendString = appendString +'</tbody></table>';
          $("#bh-dir-dialog").append(appendString);
          $("#bh-dir-dialog").dialog({
            modal: true,
            maxHeight: 400,
            title: "Copy to "+node.data.id,
            closeOnEscape: false,
            dialogClass: "no-close",
            minWidth: 450,
            buttons: [{
              text: "Cancel",
              id: 'bh-dir-cancel-action-button',
              click: function() {
               $(this).dialog("close");
               $(".bh-dir-tree-slide-trigger").trigger('click');
              }
            }, {
              text: "Copy",
              id: 'bh-dir-action-button',
              click: function() {
                $('#bh-dir-action-button').button({label:"Copy items..."});
                $('#bh-dir-action-button').button("disable");
//                var actionConfig = new Object();
                var actionConfig = {
                  contents : $('.bh-dir-checkbox:checked'),
                  counter : 0,
                  action : 'copy',
                  copyTo : node.data.id,
                  overwrite : nl.sara.webdav.Client.FAIL_ON_OVERWRITE,
                  single : false,
                };
                actionItem(actionConfig);
              }
            }]
          });
          $("#bh-dir-action-button").addClass("btn-danger");
      }
    });
	  // show tree
	  $(".bh-dir-tree-slide-trigger").trigger('click');
	  // blur on click somewhere else
	});
	
	 // TREE
	 // Tree slide handler
   $(".bh-dir-tree-slide-trigger").hover(function(){
    $(".bh-dir-tree-slide").toggle("slow");
    $(this).toggleClass("active");
    $('.bh-dir-tree-slide-trigger i').toggleClass('icon-chevron-left icon-chevron-right');
    return false;
  }, function(){
    // No action;
  });
	$(".bh-dir-tree-slide-trigger").click(function(){
	   $(".bh-dir-tree-slide").toggle("slow");
	    $(this).toggleClass("active");
	    $('.bh-dir-tree-slide-trigger i').toggleClass('icon-chevron-left icon-chevron-right');
	    return false;
	});
	
	// ACL TAB ACTIONS/FUNCTIONS
	$("#bh-dir-acl-table tbody").sortable();

	var aclcontents = new nl.sara.webdav.Acl(aclxmldocument.documentElement);
	$.each(aclcontents.getAces(), function(index, ace){
		var appendString='<tr class="bh-dir-aclrowclick">'
//		// check if the ace contains not supported entry's
//		if (ace.invertprincipal) {
//			record.set('notsupported', true);
//		};
		// set values
		// TODO change the way to check this
		if (ace.principal.tagname != undefined) {
			appendString=appendString+'<td id="DAV: "'+ace.principal.tagname+'>'+ace.principal.tagname+'</td>'
		} else {
			if(typeof ace.principal != 'string'){
				switch (ace.principal) {
					case nl.sara.webdav.Ace.ALL :
						appendString=appendString+'<td id="DAV: all">[all]</td>';
						break;
					case nl.sara.webdav.Ace.UNAUTHENTICATED :
						appendString=appenString+'<td id="DAV: unauthenticated">[unauthenticated]</td>';
						break;
					case nl.sara.webdav.Ace.AUTHENTICATED :
						appendString=appendString+'<td id="DAV: authenticated">[unauthenticated]</td>';
						break;
					case nl.sara.webdav.Ace.SELF  :
						appendString=appendString+'<td id="DAV: self">[self]</td>';
						break;
					default :
						// This should never happen.
				}
			} else {
				appendString=appendString+'<td id="'+ace.principal+'">'+ace.principal+'</td>'
			};
		}						
		// TODO only DAV: privileges are supported
		var privileges = [];
	    var supportedPrivileges =  ['all', 'read', 'write', 'read-acl', 'write-acl'];
		$.each(ace.getPrivilegeNames('DAV:'), function(key,priv){
			var supported = false;
			// TODO probably nicer to make an object and ask value instead of read the list each time.
			$.each(supportedPrivileges, function(support) {
				if (support == priv) {
					supported = true;
				};
			});
			// remember this ace is not supported
//			if (!supported){
//				record.set('notsupported', true);
//			}
			privileges['DAV: '+priv]='on';
			
		});
		var privilegesString = "";
		for (var i in privileges) {
			var value2 = i.replace("DAV: ", "");
			if (privilegesString === "") {
				privilegesString = value2;
			} else {
				privilegesString = privilegesString + ", " + value2;
			}
		}
		appendString=appendString+'<td>'+privilegesString+'</td>';
		if (ace.grantdeny == 1) {
			appendString=appendString+'<td>grant</td>';
		} else {
			appendString=appendString+'<td>deny</td>';
		}
		appendString=appendString+'<td>'+(ace.inherited.length? ace.inherited : '')+'</td>'
		appendString=appendString+'</tr>';
		$('#bh-dir-aclcontent').append(appendString);
	});
	
	nl.sara.beehub.view.init();
});