$(function (){
	/*
	 * Action when submit button in profile tab is clicked.
	 */
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
	    
	    var sponsor = new nl.sara.webdav.Property();
	    sponsor.namespace = 'http://beehub.nl/'; 
	    sponsor.tagname = 'sponsor';
	    sponsor.setValueAndRebuildXml($('#sponsor').val()); 
	    setProps.push(sponsor);
	    
	    var client = new nl.sara.webdav.Client();
	    client.proppatch(location.pathname, function(status, data) {
	    	//TODO check voor elke property
	    	if (status === 207) {
	    		alert("Your profile is changed!")
	    		$('#verify_password').val("");
	    		$('#verification_code').val("");
          location.reload();
	    	} else {
	    		alert("Something went wrong. Your profile is not updated!")
	    	}
	    }, setProps);
	   
	 }); // End of submit myprofile_form listener
	 
	var passwordListener = function(passwordConfirmationField){
		// clear error
		passwordConfirmationField.next().remove();
		passwordConfirmationField.parent().parent().removeClass('error');
		
		var showError = function(error){
			passwordConfirmationField.parent().parent().addClass('error');
			var error = $('<span class="help-inline">'+error+'</span>');
			passwordConfirmationField.parent().append(error);
		}
		
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
	
		/*
	* Action when the password field will change
	*/
	$('#password').change(function () {
		// clear error
		$(this).next().remove();
		$(this).parent().parent().removeClass('error');
	});
	
	/*
	* Action when the change password button is clicked
	*/
	$('#change-password').submit(function (e) {
		 e.preventDefault();
		 var client = new nl.sara.webdav.Client();
		    client.post(location.pathname, function(status, data) {
		    	if (status===200) {
		    		// TODO check if user is logged on with SURFconext.
		    		alert("Your password is changed now!");
		    		$('#change-password').each (function(){
		    			  this.reset();
		    		});
		    	}
		    	if (status===403) {
		    		$("#password").parent().parent().addClass('error');
					var error = $('<span class="help-inline">Wrong password.</span>');
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
		 var client = new nl.sara.webdav.Client();
		    client.post(location.pathname, function(status, data) {
		    	if (status===200) {
			    	location.reload();
		    	}
		    	if (status===403) {
		    		alert("Wrong verification code or password mismatch!")
		    	} else {
		    		alert("Something went wrong. Your email is not verified.!")
		    	};
		    }, $("#verify_email").serialize());
	});
});