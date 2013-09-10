 /*
 * Initialize acl view
 * 
 */
nl.sara.beehub.view.acl.init = function() {
// ACL TAB ACTIONS/FUNCTIONS
$("#bh-dir-acl-table").tablesorter({
  // which columns are sortable
  headers: { 
    0 : { sorter: false },
    1 : { sorter: false },
    2 : { sorter: false},
    3: { sorter:false },
    4 : { sorter: false },
    5 : { sorter: false },
    6 : { sorter: false},
    7: { sorter:false }
  },
  widthFixed: false,
  // Fixed header on top of the table
  widgets : ['stickyHeaders'],
  widgetOptions : {
    // apply sticky header top below the top of the browser window
    stickyHeaders_offset : 186,
  }
});
// Niek
//  var aclcontents = new nl.sara.webdav.Acl(aclxmldocument.documentElement);
//  $.each(aclcontents.getAces(), function(index, ace){
//    var appendString='<tr class="bh-dir-aclrowclick">'
//    // Delete
//    appendString=appendString+'<td><i title="Delete" class="icon-remove bh-dir-acl-remove" style="cursor: pointer"></i></td>';
//    // Move up
//    appendString=appendString+'<td><i title="Move up" class="icon-arrow-up bh-dir-acl-up" style="cursor: pointer"></i></td>';
//    // Move down
//    appendString=appendString+'<td><i title="Move down" class="icon-arrow-down bh-dir-acl-down" style="cursor: pointer"></i></td>';
//
////    // check if the ace contains not supported entry's
////    if (ace.invertprincipal) {
////      record.set('notsupported', true);
////    };
//    // set values
//    // TODO change the way to check this
//    if (ace.principal.tagname != undefined) {
//      appendString=appendString+'<td id="DAV: "'+ace.principal.tagname+'>'+ace.principal.tagname+'</td>'
//    } else {
//      if(typeof ace.principal != 'string'){
//        switch (ace.principal) {
//          case nl.sara.webdav.Ace.ALL :
//            appendString=appendString+'<td id="DAV: all">[all]</td>';
//            break;
//          case nl.sara.webdav.Ace.UNAUTHENTICATED :
//            appendString=appenString+'<td id="DAV: unauthenticated">[unauthenticated]</td>';
//            break;
//          case nl.sara.webdav.Ace.AUTHENTICATED :
//            appendString=appendString+'<td id="DAV: authenticated">[unauthenticated]</td>';
//            break;
//          case nl.sara.webdav.Ace.SELF  :
//            appendString=appendString+'<td id="DAV: self">[self]</td>';
//            break;
//          default :
//            // This should never happen.
//        }
//      } else {
//        appendString=appendString+'<td id="'+ace.principal+'">'+ace.principal+'</td>'
//      };
//    }           
//    // TODO only DAV: privileges are supported
//    var privileges = [];
//      var supportedPrivileges =  ['all', 'read', 'write', 'read-acl', 'write-acl'];
//    $.each(ace.getPrivilegeNames('DAV:'), function(key,priv){
//      var supported = false;
//      // TODO probably nicer to make an object and ask value instead of read the list each time.
//      $.each(supportedPrivileges, function(support) {
//        if (support == priv) {
//          supported = true;
//        };
//      });
//      // remember this ace is not supported
////      if (!supported){
////        record.set('notsupported', true);
////      }
//      privileges['DAV: '+priv]='on';
//      
//    });
//    var privilegesString = "";
//    for (var i in privileges) {
//      var value2 = i.replace("DAV: ", "");
//      if (privilegesString === "") {
//        privilegesString = value2;
//      } else {
//        privilegesString = privilegesString + ", " + value2;
//      }
//    }
////    appendString=appendString+'<td>'+privilegesString+'</td>';
//    
//    appendString=appendString+'<td><center><input type="checkbox" class="bh-dir-acl-read"></center></td>';
//    appendString=appendString+'<td><center><input type="checkbox" class="bh-dir-acl-read"></center></td>';
//    appendString=appendString+'<td><center><input type="checkbox" class="bh-dir-acl-read"></center></td>';
//
//    if (ace.grantdeny == 1) {
//      appendString=appendString+'<td>grant</td>';
//    } else {
//      appendString=appendString+'<td>deny</td>';
//    }
//    appendString=appendString+'<td>'+(ace.inherited.length? ace.inherited : '')+'</td>'
//    appendString=appendString+'</tr>';
//    $('#bh-dir-aclcontent').append(appendString);
//  });
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
      $('.bh-dir-acl-add').hide();
      break;
    case 'show':
      $('.bh-dir-acl-add').show();
      break;
    default:
      // This should never happen
  };
};