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

/** 
 * Beehub Client
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */
$(function() {
	
	var path = location.pathname;
	// add slash to the end of path
	if (!path.match(/\/$/)) {
		path=path+'/'; 
	} 
	
	// solving bug: https://github.com/twitter/bootstrap/issues/6094
	// conflict bootstrap and jquery
	var btn = $.fn.button.noConflict() // reverts $.fn.button to jqueryui btn
	$.fn.btn = btn // assigns bootstrap button functionality to $.fn.btn
	
	// CONTENTS TAB
	// Open selected handler: this can be a file or a directory
	$('.beehub-directory-openselected').click(function() {
		window.location.href=$(this).attr('name');
	});
	
	// Go to users homedirectory handler
	$('.beehub-directory-gohome').click(function() {
		window.location.href=$(this).attr("id");
	});
	
	// Go up one directory handler
	$('.beehub-directory-goup').click(function() {
		window.location.href=$(this).attr("id");
	});
	
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
		
		function callback2(file) {
	        return function(status) {
				// Forbidden
				if (status === 403) {
					$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+file.name+'"]').html("<div class='progress progress-danger progress-striped'><div class='bar' style='width: 100%;'>Forbidden</div></div>");
				}
				// Succeeded
				if (status === 201 || status === 204) {
					$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+file.name+'"]').html("<div class='progress progress-success progress-striped'><div class='bar' style='width: 100%;'>100%</div></div>");
				// Unknown error
				} else {
					$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+file.name+'"]').html("<div class='progress progress-danger progress-striped'><div class='bar' style='width: 100%;'>Unknown error</div></div>");
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
	    		 $("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+file.name+'"]').html("<div class='progress progress-success progress-striped'><div class='bar' style='width: "+progress+"%;'>"+progress+"%</div></div>");

	    	 
	    	 }, false);
	    } else {
	    	$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+file.name+'"]').html('Bezig... (ik kan geen voortgang laten zien in deze browser)');
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
		$("#beehub-directory-upload-dialog").find('button[id="beehub-directory-upload-overwrite-'+fileName+'"]').click(function(){
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
		$("#beehub-directory-upload-dialog").find('button[id="beehub-directory-upload-cancel-'+fileName+'"]').click(function(){
			$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileName+'"]').html("<div class='progress progress-danger progress-striped'><div class='bar' style='width: 100%;'>Canceled</div></div>");
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
		$("#beehub-directory-upload-dialog").find('button[id="beehub-directory-upload-rename-'+fileName+'"]').click(function(){
			// search fileName td and make input field
			var fileNameOrg= $("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileName+'"]').prev().html();
			var buttonsOrg = $("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileName+'"]').html();
			$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileName+'"]').prev().html("<input id='beehub-directory-upload-rename-input-"+fileName+"' value='"+fileName+"'></input>");
			// change buttons - cancel and upload
			var renameUploadButton = '<button id="beehub-directory-upload-rename-upload-'+fileNameOrg+'" name="'+fileNameOrg+'" class="btn btn-success">Upload</button>'
			var renameCancelButton = '<button id="beehub-directory-upload-rename-cancel-'+fileNameOrg+'" class="btn btn-danger">Cancel</button>'
			$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileName+'"]').html(renameUploadButton+" "+renameCancelButton);
			// handler cancel rename
			$("#beehub-directory-upload-dialog").find('button[id="beehub-directory-upload-rename-cancel-'+fileName+'"]').click(function(){
				$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileNameOrg+'"]').html(buttonsOrg);
				$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileNameOrg+'"]').prev().html(fileNameOrg);
				setOverwriteHandler(fileName, filesHash);
				setCancelHandler(fileName);
				setRenameHandler(fileName, fileNameOrg, filesHash);
			})
			// add handler upload rename
			$("#beehub-directory-upload-dialog").find('button[id="beehub-directory-upload-rename-upload-'+fileName+'"]').click(function(){
				var newName = $("#beehub-directory-upload-dialog").find('input[id="beehub-directory-upload-rename-input-'+fileName+'"]').val();
				if (newName !== fileName) {
					$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileNameOrg+'"]').prev().html(fileName+" <br/> <b>renamed to</b> <br/> "+newName);
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
		webdav.head(path + fileName, function(status, body, headers){
			// Fil does nog exist
			if (status === 404)  {
				uploadToServer( fileName, file, callback);
				return;
			};
			// File exist
			if (status === 200) {
				var overwriteButton = '<button id="beehub-directory-upload-overwrite-'+fileName+'" name="'+fileName+'" class="btn btn-danger">Overwrite</button>'
				var renameButton = '<button id="beehub-directory-upload-rename-'+fileName+'" class="btn btn-success">Rename</button>'
				var cancelButton = '<button id="beehub-directory-upload-cancel-'+fileName+'" name="'+fileName+'" class="btn btn-success">Cancel</button>'
				$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileName+'"]').html("File exist on server!<br/>"+renameButton+" "+overwriteButton+" "+cancelButton);
				setOverwriteHandler(fileName, filesHash);
				setCancelHandler(fileName);
				setRenameHandler(fileName, file.name, filesHash);
				if (callback !== null) {
					callback();
				}
			} 
		},"");
	};
	
	/**
	 * Upload handler
	 * 
	 */
    function handleUpload(files, filesHash) {
        counter++;
        if ( counter < files.length ) {
        	$("#beehub-directory-upload-dialog").scrollTop(counter*50);
        	checkFileName( files[counter].name, files[counter], function(){handleUpload(files, filesHash)}, filesHash );
        } else { 
        	$('#beehub-directory-close-upload-button').button("enable");
        	// Samenvatting upload
        }
      } // End of handleUpload()
    
    
	// Upload handlers
	$('#beehub-directory-upload').click(function() {
		// show local files and directories
		$('#beehub-directory-upload-hidden').click();
	});
	
	// Upload dialog
	var counter;
	$('#beehub-directory-upload-hidden').change(function(){
		$("#beehub-directory-upload-dialog").html("");
		var upload_files = new Object();
		var files = $('#beehub-directory-upload-hidden')[0].files;
		var filesHash = {};
		for (var i = 0; i < files.length; i++) {
			filesHash[files[i].name] = files[i];
		};
		var appendString=''
	 	appendString = appendString + '<table class="table"><tbody>';
		for (var i = 0; i < files.length; i++) {
			appendString = appendString + '<tr><td>'+files[i].name+'</td><td width="60%" id="beehub-directory-'+files[i].name+'"></td></tr>'
		};	
		appendString = appendString +'</tbody></table>';
		$("#beehub-directory-upload-dialog").append(appendString);

		$("#beehub-directory-upload-dialog").dialog({
	    	modal: true,
	    	height: 400,
	    	closeOnEscape: false,
	    	dialogClass: "no-close",
	    	width: 600,
	    	buttons: [{
				text: "Ready",
				id: 'beehub-directory-close-upload-button',
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
	
	
	// New folder handler
	$('#beehub-directory-newfolder').click(function() {
		alert("deze knop werkt nog niet - newfolder"); 
	});
	
	// Edit handler
	$('.beehub-directory-edit').click(function() {
		// Search nearest name field and hide
		$(this).closest("tr").find(".beehub-directory-name").hide();
		// Show form
		$(this).closest("tr").find(".beehub-directory-rename-td").show();
	});
	
	// RENAME
	/**
	 * Move an object
	 * 
	 * @param string fileNameOrg
	 * @param string fileNameNew
	 * 
	 */
	function moveObject(fileNameOrg, fileNameNew, overwriteMode){
		var webdav = new nl.sara.webdav.Client();
		function callback(fileOrg, fileNew) { // put here your variables
			return function(status) {
				if (status === 412) {
					if (confirm('File exists, overwrite?')) {
						moveItem(fileOrg, fileNew, nl.sara.webdav.Client.SILENT_OVERWRITE);
					} else {
						console.log("nee");
						// form weer naar list
					}
				} 
				if (status === 201 || status === 204) {
					window.location.reload();
				}
			}
		};
		webdav.move(path + fileNameOrg,callback(fileNameOrg,fileNameNew), path +fileNameNew,  overwriteMode);
	};
	
	// Rename handler
	$('.beehub-directory-rename-form').change(function(){
		moveObject($(this).attr('name'),$(this).val(), nl.sara.webdav.Client.FAIL_ON_OVERWRITE);
	})
	
//	$("#beehub-directory-contents-table").tablesorter();

	
	
	// ACL TAB ACTIONS/FUNCTIONS
	$("#beehub-directory-acl-table tbody").sortable();

	var aclcontents = new nl.sara.webdav.Acl(aclxmldocument.documentElement);
	$.each(aclcontents.getAces(), function(index, ace){
		var appendString='<tr class="beehub-directory-aclrowclick">'
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
		$('#beehub-directory-aclcontent').append(appendString);
	});
});