 /*
 * Initialize acl view
 * 
 */
nl.sara.beehub.view.acl.init = function() {
// ACL TAB ACTIONS/FUNCTIONS
$("#bh-dir-acl-table tbody").sortable();

  var aclcontents = new nl.sara.webdav.Acl(aclxmldocument.documentElement);
  $.each(aclcontents.getAces(), function(index, ace){
    var appendString='<tr class="bh-dir-aclrowclick">'
//    // check if the ace contains not supported entry's
//    if (ace.invertprincipal) {
//      record.set('notsupported', true);
//    };
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
//      if (!supported){
//        record.set('notsupported', true);
//      }
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
};

/*
 * Action for all buttons in the fixed view on the top of the acl table
 * 
 * @param String what 'hide' or 'show'
 * 
 */
nl.sara.beehub.view.acl.allFixedButtons = function(action){
  switch(action)
  {
    case 'hide':
      break;
    case 'show':
      break;
    default:
      // This should never happen
  };
};