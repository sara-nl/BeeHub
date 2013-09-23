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
    // Down (if possible)
    if (getIndexLastProtected()+1 === getIndexFirstInherited()) {
      row.push('<td></td>');
    } else {
      row.push('<td><i title="Move down" class="icon-arrow-down bh-dir-acl-down" style="cursor: pointer"></i></td>');
    }
    // Delete
    row.push('<td><i title="Delete" class="icon-remove bh-dir-acl-remove" style="cursor: pointer"></i></td>');
    
    row.push('</tr>');
    return row.join("");
  };
  
  /**
   * Returns index of the last protected rule
   * 
   * @return {Integer} index Index of last protected rule
   */
  getIndexLastProtected = function(){
    // Get protected items. length -1 is index
    return $('.bh-dir-acl-protected').length-1;
  }
  
  /**
   * Returns index of the last protected rule
   * 
   * @return {Integer} index Index of last protected rule
   */
  getIndexFirstInherited = function(){
    // Count of all items
    var all = $('.bh-dir-acl-contents > tr').length;
    // Count of all inherited items
    var allInherited = $('.bh-dir-acl-inherited').length;
    // Index
    var index = all - allInherited;
    return index;
  }
  
  /*
   * Add ace to Acl view
   * 
   * Public function
   * 
   * @param {nl.sara.beehub.ClientAce} ace Ace object
   */
  nl.sara.beehub.view.acl.addAce = function(ace){
    var row = createRow(ace);
    var index = getIndexLastProtected();
    $('.bh-dir-acl-contents > tr:eq('+index+')').after(row);
    $(".bh-dir-acl-contents").trigger("update");
    // Set handlers again
//    setRowHandlers();
  };
})();