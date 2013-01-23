$(function (){
  $('.btn').click(function() {
    var setProps = new Array();
    var displayname = new nl.sara.webdav.Property();
    displayname.namespace = 'DAV:';
    displayname.tagname = 'displayname';
    displayname.setValueAndRebuildXml($('input[name="displayname"]').val());
    setProps.push(displayname);
    var description = new nl.sara.webdav.Property();
    description.namespace = 'http://beehub.nl/';
    description.tagname = 'description';
    description.setValueAndRebuildXml($('input[name="email"]').val());
    setProps.push(description);

    var client = new nl.sara.webdav.Client();
    client.proppatch(location.pathname, function(status, data) {
      alert(status);
    }, setProps);

    return false;
  }); // End of button click event listener
});