 /*
 * Initialize acl view
 * 
 */
(function() {
  nl.sara.beehub.view.acl.init = function() {
    // ACL TAB ACTIONS/FUNCTIONS
    // Use table sorter for sticky headers (not scrolling
    // table header)
    $("#bh-dir-acl-table").tablesorter({
      // which columns are sortable
      headers: { 
        0 : { sorter: false },
        1 : { sorter: false },
        2 : { sorter: false},
        3:  { sorter:false },
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
    // Add rule handler
    $('.bh-dir-acl-add').click(nl.sara.beehub.controller.addAclRule);
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
  
  /*
   * Create aclview row from ace object
   * 
   * @param {nl.sara.beehub.ClientAce} ace Ace object
   * 
   * @return String Row string 
   */
  var createRow = function(ace){
    var row = [];
    
    row.push('<tr>');
    // Principal
    var show = '';
    switch(ace.principal)
    {
      case 'all':
        show = '<em>Everybody</em>'
        break;
      case 'authenticated':
        show = '<em>All BeeHub users</em>'
        break;
      default:
        show = nl.sara.beehub.controller.getDisplayName(ace.principal);
    };
    row.push('<td name="'+ace.principal+'" data-toggle="tooltip" title="'+ace.principal+'" ><b>'+show+'</b></td>');
    
    // Permissions
    var aceClass= '';
    var tooltip = '';
    switch(ace.permissions)
    {
      case 'deny read':
        aceClass="bh-dir-acl-deny";
        tooltip="deny read, write, change acl";
        break;
      case 'deny write':
        aceClass="bh-dir-acl-deny";
        tooltip="deny write, change acl";
        break;
      case 'deny manage':
        aceClass="bh-dir-acl-deny";
        tooltip="deny change acl";
        break;
      case 'allow read':
        aceClass="bh-dir-acl-allow";
        tooltip="allow read";
        break;
      case 'allow write':
        aceClass="bh-dir-acl-allow";
        tooltip="allow read, write";
        break;
      case 'allow manage':
        aceClass="bh-dir-acl-allow";
        tooltip="allow read, write, change acl";
        break;
      default:
        // This should never happen  
    };
    row.push('<td class="bh-dir-acl-permissions '+aceClass+'" data-toggle="tooltip" title="'+tooltip+'" name="'+ace.permissions+'">'+ace.permissions+'</td>');
    
    // Comment, not changable by user
    row.push('<td></td>');
    // Up
    row.push('<td></td>');
    // Down
    row.push('<td></td>');
    // Delete
    row.push('<td></td>');
    
    row.push('</tr>');
    return row.join("");
  };
  
  /*
   * Add ace to Acl view
   * 
   * Public function
   * 
   * @param {nl.sara.beehub.ClientAce} ace Ace object
   */
  nl.sara.beehub.view.acl.addAce = function(ace){
    var row = createRow(ace);
    // Find position
    var index = 0;
    // First is allways protected, owner
    for (var i=1; i < $('.bh-dir-acl-contents > tr').length; i++) {
      var isProtected = false;
      if ($('.bh-dir-acl-contents > tr:eq('+i+')').find(".bh-dir-acl-comment").attr('name') === 'protected'){
        isProtected = true;
      }
      if (!isProtected) {
        index = i-1;
        break;
      };
      if (i === $('.bh-dir-acl-contents > tr').length -1){
        index = i;
        break;
      }
    };
    $('.bh-dir-acl-contents > tr:eq('+index+')').after(row);
    $(".bh-dir-acl-contents").trigger("update");
    // Set handlers again
//    setRowHandlers();
  };
})();