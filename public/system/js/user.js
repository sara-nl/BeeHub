$(function (){
  $('#change_password').change(function() {
    if ($(this).is(':checked')) {
      $('div.passwd').show("blind");
    }else{
      $('div.passwd').hide("blind");
    }
  }); // End of change password (checkbox) event listener

  $('.btn').click(function() {
    var setProps = new Array();
    if ($('#change_password').is(':checked')) {
      var passwordValue = $('input[name="password1"]').val();
      if (passwordValue != $('input[name="password2"]').val()) {
        alert('The two passwords are not identical!');
        return false;
      }
      var password = new nl.sara.webdav.Property();
      password.namespace = 'http://beehub.nl/';
      password.tagname = 'password';
      password.setValueAndRebuildXml(passwordValue);
      setProps.push(password);
    }
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
      alert(status);
    }, setProps);

    return false;
  }); // End of button click event listener
});