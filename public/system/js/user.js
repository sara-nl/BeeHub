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
	
	    var client = new nl.sara.webdav.Client();
	    client.proppatch(location.pathname, function(status, data) {
	    	//TODO check voor elke property
	    	if (status === 207) {
	    		alert("Your profile is changed!")
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
//		    		alert("Your password is changed now! When " +
//		    			"you are logged on with a SURFconext " +
//		    			"account you stay connected. Otherwise you need to login again with your new password.")
			    	location.reload();
		    	}
		    	if (status===403) {
		    		$("#password").parent().parent().addClass('error');
					var error = $('<span class="help-inline">Wrong password.</span>');
					$("#password").parent().append(error);
		    	};
		    }, $("#change-password").serialize());
	});
	
	//  $('#change_password').change(function() {
//   if ($(this).is(':checked')) {
//     $('div.passwd').show("blind");
//   }else{
//     $('div.passwd').hide("blind");
//   }
// }); // End of change password (checkbox) event listener
//  $('#save_button').click(function(event) {
//    event.preventDefault();
//
//    var setProps = new Array();
//    if ($('#change_password').is(':checked')) {
//      var passwordValue = $('input[name="password"]').val();
//      if (passwordValue != $('input[name="password2"]').val()) {
//        alert('The two passwords are not identical!');
//        return false;
//      }
//      var password = new nl.sara.webdav.Property();
//      password.namespace = 'http://beehub.nl/';
//      password.tagname = 'password';
//      password.setValueAndRebuildXml(passwordValue);
//      setProps.push(password);
//    }
//    var email = new nl.sara.webdav.Property();
//    email.namespace = 'http://beehub.nl/';
//    email.tagname = 'email';
//    email.setValueAndRebuildXml($('input[name="email"]').val());
//    setProps.push(email);
//    var displayname = new nl.sara.webdav.Property();
//    displayname.namespace = 'DAV:';
//    displayname.tagname = 'displayname';
//    displayname.setValueAndRebuildXml($('input[name="displayname"]').val());
//    setProps.push(displayname);
//
//    var delProps;
//    if ($('input[name="saml_unlink"]').is(':checked')) {
//      delProps = new Array();
//      var saml_unlink = new nl.sara.webdav.Property();
//      saml_unlink.namespace = 'http://beehub.nl/';
//      saml_unlink.tagname = 'surfconext-description';
//      delProps.push(saml_unlink);
//      saml_unlink = new nl.sara.webdav.Property();
//      saml_unlink.namespace = 'http://beehub.nl/';
//      saml_unlink.tagname = 'surfconext';
//      delProps.push(saml_unlink);
//    }
//
//    var client = new nl.sara.webdav.Client();
//    client.proppatch(location.pathname, function(status, data) {
//      alert(status);
//    }, setProps, delProps);
//  }); // End of button click event listener
});