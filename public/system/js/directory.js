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
 * along with js-webdav-client.  If not, see <http://www.gnu.org/licenses/>.
 */
"use strict"

/**
 * Beehub Client
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */
$(function() {
	// solving bug: https://github.com/twitter/bootstrap/issues/6094
	var btn = $.fn.button.noConflict() // reverts $.fn.button to jqueryui btn
	$.fn.btn = btn // assigns bootstrap button functionality to $.fn.btn
	
	// CONTENT TAB FUNCTIONS

	// Open selected handler: this can be a file or a directory
	$('.beehub-directory-openselected').click(function() {
		window.location.href=$(this).parent().parent('tr').attr("id");
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
	var uploadToServer = function(file, callback){
		var path = location.href;
		var fileName = file.name;
		// TODO eerst leeg bestand sturen om te controleren of het wel mag
		var headers = {
			'Content-Type': 'application/octet-stream'
			};
			var ajax = nl.sara.webdav.Client.getAjax( 
				"PUT",
		        path + fileName,
		        function(status) {
					if (status === 403) {
						// wijzig tabel dat forbidden
						$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileName+'"]').html('Forbidden <i class="icon-remove"></i>');
					}
					if (status === 201 || status === 204) {
						// wijzig tabel dat gelukt
						$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileName+'"]').html("<div class='progress'><div class='bar' style='width: 100%;'>100%</div></div>");
					} else {
						// wijzig tabel dat er iets fout gegaan is
						$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileName+'"]').html('Unknown error <i class="icon-remove"></i>');
					};
					callback();
		        },
		        headers 
		    );
			 
		    if (ajax.upload) {
		    	 // progress bar
		    	 ajax.upload.addEventListener("progress", function(event) {
		    		 var progress = parseInt(event.loaded / event.total * 100);
		    		 $("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileName+'"]').html("<div class='progress progress-success progress-striped'><div class='bar' style='width: "+progress+"%;'>"+progress+"%</div></div>");
		    	 }, false);
		    } else {
		    	$("#beehub-directory-upload-dialog").find('td[id="beehub-directory-'+fileName+'"]').html('Bezig... (ik kan geen voortgang laten zien in deze browser)');
		    }
			ajax.send(file);  
	};
	
	// Upload handlers
	$('#beehub-directory-upload').click(function() {
		$('#beehub-directory-upload-hidden').click();
	});
	
	// Upload dialog
	
	var counter;
	$('#beehub-directory-upload-hidden').change(function(){
		var counter;
		$("#beehub-directory-upload-dialog").html("");
		var upload_files = new Object();
		var files = $('#beehub-directory-upload-hidden')[0].files;
		var appendString=''
	 	appendString = appendString + '<table class="table"><tbody>';
		for (var i = 0; i < files.length; i++) {
			appendString = appendString + '<tr><td>'+files[i].name+'</td><td width="50%" id="beehub-directory-'+files[i].name+'"></td></tr>'
		};	
		appendString = appendString +'</tbody></table>';
		$("#beehub-directory-upload-dialog").append(appendString);

	    function handleUpload() {
	        counter++;
	        if ( counter < files.length ) {
	          uploadToServer( files[counter], handleUpload );
	        } else {
	        	$('#beehub-directory-close-upload-button').button("enable");
//	    		$("#beehub-directory-close-upload-button").attr("disabled", false).addClass("ui-state-enabled");
//	    		$("#beehub-directory-close-upload-button").click(function(){
//		        	window.location.reload();
//	    		})
//	        	$("#beehub-directory-close-upload-button").atrr("disabled", false);

	        }
	      } // End of handleUpload()
	    

		$("#beehub-directory-upload-dialog").dialog({
	    	modal: true,
	    	maxHeight: 300,
	    	closeOnEscape: false,
	    	dialogClass: "no-close",
	    	width: 500,
	    	buttons: [{
				text: "Ready",
				id: 'beehub-directory-close-upload-button',
				click: function() {
					window.location.reload();
				}
			}]
	    });
		// Start upload
	    $('#beehub-directory-close-upload-button').button("disable");
		counter = -1;
		handleUpload();
	});
	// END UPLOAD
	
	
	// New folder handler
	$('#beehub-directory-newfolder').click(function() {
		alert("deze knop werkt nog niet - newfolder"); 
	});
	
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