$(function() {
	// TODO function are almost the same as groups.js functions.
	// better to merge this
	var userNameListener = function(userNameField){
	 var showError = function(error){
		 userNameField.parent().parent().addClass('error');
		 var error = $('<span class="help-inline">'+error+'</span>');
		 userNameField.parent().append(error);
	 }
	 
	 // TODO make tooltip with field specifications
	 // This is included in bootstrap with patern
	 
	 // clear error
	 userNameField.next().remove();
	 userNameField.parent().parent().removeClass('error');

	// Seperate regular expressions to make the errors more specific.
	// value starts with a-zA-Z0-9, else return
	 if (!RegExp('^[a-zA-Z0-9]{1}.*$').test(userNameField.val())) {
		 showError('First character must be a alphanumeric character.');
		 return false;
	 }
	// value only contain a-zA-Z0-9_-., else return
	 if (!RegExp('^[a-zA-Z0-9]{1}[a-zA-Z0-9_\\-\\.]*$').test(userNameField.val())) {
		 showError('This field can contain aphanumeric characters, numbers, "-", "_" and ".".');
		 return false;
	 }
	// value contain 1-255 characters, else return
	 if (!RegExp('^[a-zA-Z0-9]{1}[a-zA-Z0-9_\\-\\.]{0,255}$').test(userNameField.val())) {
		showError('This field can contain maximal 255 characters.');
		return false;
	 }
	 if (nl.sara.beehub.principals.users[userNameField.val()] !== undefined) {
		 showError('This username is already in use.');
		 return false;
	 };
	 
	 return true;
	};
	
	/*
	* Action when the usersname field will change
	*/
	$('#username').change(function () {
		 userNameListener($(this));
	})
	
	var passwordListener = function(passwordConfirmationField){
		// clear error
		passwordConfirmationField.next().remove();
		passwordConfirmationField.parent().parent().removeClass('error');
		
		var showError = function(error){
			passwordConfirmationField.parent().parent().addClass('error');
			var error = $('<span class="help-inline">'+error+'</span>');
			passwordConfirmationField.parent().append(error);
		}
		
		if(passwordConfirmationField.val() !== $('#username_password').val()) {
			showError('Password mismatch.');
			return false;
		};
		return true;
	};

	/*
	* Action when the usersname field will change
	*/
	$('#username_password_confirmation').change(function () {
		 passwordListener($(this));
	})
	
	/*
	* Action when the Create user button is clicked
	*/
	$('#createuserform').submit(function (e) {
		// check if username does not already exists.
		 if (!userNameListener($('#username'))) {
			 e.preventDefault();
		 };
		console.log(userNameListener($('#username_password_confirmation')));
		// check if username does not already exists.
		 if (!passwordListener($('#username_password_confirmation'))) {
			 e.preventDefault();
		 }
	});
});