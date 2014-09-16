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
 *
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */

"use strict";

$(function (){
	/*
	 * Action when submit button in profile tab is clicked.
	 */
   var old_sponsor_value = $('#sponsor').val();
	 $('#myprofile_form').submit(function(event) {
		event.preventDefault();
	   
		var setProps = new Array();
		
		var email = new nl.sara.webdav.Property();
	    email.namespace = 'http://beehub.nl/';
	    email.tagname = 'email';
	    email.setValueAndRebuildXml($('input[name="email"]').val());
	    setProps.push(email);
	    
	    var displayname = new nl.sara.webdav.Property();
	    displayname.namespace = 'DAV:';
	    displayname.tagname = 'displayname';
	    displayname.setValueAndRebuildXml($('input[name="displayname"]').val());
	    setProps.push(displayname);
	    
      if ( $('#sponsor').val() !== old_sponsor_value ) {
  	    var sponsor = new nl.sara.webdav.Property();
  	    sponsor.namespace = 'http://beehub.nl/'; 
  	    sponsor.tagname = 'sponsor';
  	    sponsor.setValueAndRebuildXml($('#sponsor').val()); 
  	    setProps.push(sponsor);
      }
	    
	    var client = new nl.sara.webdav.Client();
	    client.proppatch(location.pathname, function(status, data) {
	    	//TODO check voor elke property
	    	if (status === 207) {
	    		alert( "Your profile is changed!" );
	    		$('#verify_password').val("");
	    		$('#verification_code').val("");
          location.reload();
	    	} else {
	    		alert( "Something went wrong. Your profile is not updated!" );
	    	}
	    }, setProps);
	   
	 }); // End of submit myprofile_form listener
	 
	var passwordListener = function(passwordConfirmationField){
		// clear error
		passwordConfirmationField.next().remove();
		passwordConfirmationField.parent().parent().removeClass('error');
		
		var showError = function(error){
			passwordConfirmationField.parent().parent().addClass('error');
			var errorSpan = $('<span class="help-inline"></span>');
      errorSpan.text( error );
			passwordConfirmationField.parent().append( errorSpan );
		};
		
		if(passwordConfirmationField.val() !== $('#new_password').val()) {
			showError('Password mismatch.');
			return false;
		};
		return true;
	};

	/*
	* Action when the password field will change
	*/
	$('#new_password2').change(function (e) {
		 passwordListener($(this));
	});
	
  /**
   * Action when the password field will change
   */
	$('#password').change(function () {
		// clear error
		$(this).next().remove();
		$(this).parent().parent().removeClass('error');
	});
	
  /**
   * Action when the change password button is clicked
   */
  $('#change-password').submit(function (e) {
    e.preventDefault();
    var client = new nl.sara.webdav.Client();
    $( 'input[name="POST_auth_code"]' ).val( nl.sara.beehub.postAuth );
    client.post(location.pathname, function(status, data) {
      nl.sara.beehub.retrieveNewPostAuth();
      if (status===200) {
        alert("Your password is changed now!");

        // TODO check if user is logged on with SURFconext.
        $('#change-password').each (function(){
          this.reset();
        });
      }else if (status===403) {
        $("#password").parent().parent().addClass('error');
        var error = $( '<span class="help-inline"></span>' );
        error.text( 'Wrong password.' );
        $("#password").parent().append(error);
      };
    }, $("#change-password").serialize());
  });
	
	/*
	* Action when the unlink button is clicked
	*/
	$('#unlink').click(function (event) {
	    var delProps;
	    if (confirm('Are you sure you want to unlink your SURFconext account?')) {
	      delProps = new Array();
	      
	      var saml_unlink = new nl.sara.webdav.Property();
	      saml_unlink.namespace = 'http://beehub.nl/';
	      saml_unlink.tagname = 'surfconext-description';
	      delProps.push(saml_unlink);
	      
	      saml_unlink = new nl.sara.webdav.Property();
	      saml_unlink.namespace = 'http://beehub.nl/';
	      saml_unlink.tagname = 'surfconext';
	      delProps.push(saml_unlink);
	      
	      var client = new nl.sara.webdav.Client();
		    client.proppatch(location.pathname, function(status, data) {
		    var notlinked = $('<br/> <h5>Your BeeHub account is not linked to a SURFconext account.</h5>'+
		    		'<p><a type="button" href="/system/saml_connect.php" class="btn btn-success">Link SURFconext account</a></p>');
		    $('#surfconext_linked').remove();
		    $('#surfconext').append(notlinked);
		    }, undefined ,delProps);
	    };
	});
	
	/*
	* Action when the verify email button is clicked
	*/
	$('#verify_email').submit(function (e) {
		 e.preventDefault();
   // Set the new POST authentication code
   $( 'input[name="POST_auth_code"]' ).val( nl.sara.beehub.postAuth );
   
		 var client = new nl.sara.webdav.Client();
		    client.post(location.pathname, function(status, data) {
		     nl.sara.beehub.retrieveNewPostAuth();
		    	if (status===200) {
			    	location.reload();
		    	}else if (status===403) {
		    		alert( "Wrong verification code or password mismatch!" );
		    	} else {
		    		alert( "Something went wrong. Your email is not verified.!" );
		    	};
		    }, $("#verify_email").serialize());
	});
});
