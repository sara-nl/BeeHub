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
    row.push('<td class="bh-dir-acl-principal" name="'+ace.principal+'" data-toggle="tooltip" title="'+ace.principal+'" ><b>'+show+'</b></td>');
    
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
    row.push('<td class="bh-dir-acl-comment" name=""></td>');
    // Up
    row.push('<td class="bh-dir-acl-up"></td>');
    // Down (if possible)
    if (getIndexLastProtected()+1 === getIndexFirstInherited()) {
      row.push('<td class="bh-dir-acl-down"></td>');
    } else {
      row.push('<td class="bh-dir-acl-down"><i title="Move down" class="icon-arrow-down bh-dir-acl-down" style="cursor: pointer"></i></td>');
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
   * @param {Object} ace  
   * 
   * @param {nl.sara.beehub.ClientAce} ace Ace object
   */
  nl.sara.beehub.view.acl.addAce = function(ace){
    var row = createRow(ace);
    var index = getIndexLastProtected();
    ace.index = index+1;
    var nextRow = $('.bh-dir-acl-contents > tr:eq('+index+1+')');
    if ($(nextRow).find('.bh-dir-acl-comment') !== 'inherited') {
      $(nextRow).find('.bh-dir-acl-up').html('<i title="Move up" class="icon-arrow-up bh-dir-acl-up" style="cursor: pointer"></i>');
    }
    $('.bh-dir-acl-contents > tr:eq('+index+')').after(row);
    $(".bh-dir-acl-contents").trigger("update");
    // Set handlers again
//    setRowHandlers();
  };
  
  /*
   * Add ace to Acl view
   * 
   * Public function
   * 
   * @param {nl.sara.beehub.ClientAce} ace Ace object
   */
  nl.sara.beehub.view.acl.deleteAce = function(ace){
    $('.bh-dir-acl-contents > tr:eq('+ace.index+')').remove();
    $(".bh-dir-acl-contents").trigger("update");
  }
  
  /**
   * Create acl from acl table in acl view
   * 
   * @return {nl.sara.webdav.Acl} Acl
   */
  nl.sara.beehub.view.acl.getAcl = function() {
    var acl = new nl.sara.webdav.Acl();
    // put each item acl table in the created webdav acl
    $.each($('.bh-dir-acl-contents > tr'), function(index, row){
      var principal = $(row).find('.bh-dir-acl-principal').attr('name');
      var permissions = $(row).find('.bh-dir-acl-permissions').attr('name');
      var info = $(row).find('.bh-dir-acl-comment').attr('name');
      
      if (info !== 'protected' && info !== 'inherited') {
        // create ace according the webdavlib specifications
        var ace = new nl.sara.webdav.Ace();
        // put all values from rec in ace
        switch ( principal ) {
          case 'all':
            ace.principal = nl.sara.webdav.Ace.ALL;
            break;
          case 'authenticated':
            ace.principal = nl.sara.webdav.Ace.AUTHENTICATED;
            break;
          case 'unauthenticated':
            ace.principal = nl.sara.webdav.Ace.UNAUTHENTICATED;
            break;
          case 'self':
            ace.principal = nl.sara.webdav.Ace.SELF;
            break;
          default:
            ace.principal = principal;
        };
        
        var readPriv = new nl.sara.webdav.Privilege();
        readPriv.namespace = "DAV:";
        readPriv.tagname= "read";
        
        var writePriv = new nl.sara.webdav.Privilege();
        writePriv.namespace = "DAV:";
        writePriv.tagname= "write";
        
        var readAclPriv = new nl.sara.webdav.Privilege();
        readAclPriv.namespace = "DAV:";
        readAclPriv.tagname= "read_acl";
        
        var writeAclPriv = new nl.sara.webdav.Privilege();
        readAclPriv.namespace = "DAV:";
        readAclPriv.tagname= "write_acl";
        
        switch( permissions )
        {
          case 'deny read':
            ace.grantdeny = nl.sara.webdav.Ace.DENY;
            ace.addPrivilege(readPriv);
            break;
          case 'deny write':
            ace.grantdeny = nl.sara.webdav.Ace.DENY;
            ace.addPrivilege(readPriv);
            ace.addPrivilege(writePriv);
            break;
          case 'deny manage':
            ace.grantdeny = nl.sara.webdav.Ace.DENY;
            ace.addPrivilege(readPriv);
            ace.addPrivilege(writePriv);
            ace.addPrivilege(readAclPriv);
            ace.addPrivilege(writeAclPriv);
            break;
          case 'allow read':
            ace.grantdeny = nl.sara.webdav.Ace.GRANT;
            ace.addPrivilege(readPriv);
            break;
          case 'allow write':
            ace.grantdeny = nl.sara.webdav.Ace.GRANT;
            ace.addPrivilege(readPriv);
            ace.addPrivilege(writePriv);
            break;
          case 'allow manage':
            ace.grantdeny = nl.sara.webdav.Ace.GRANT;
            ace.addPrivilege(readPriv);
            ace.addPrivilege(writePriv);
            ace.addPrivilege(readAclPriv);
            ace.addPrivilege(writeAclPriv);         
            break;
          default:
            // This should never happen  
        };
        acl.addAce(ace);
      };
    });
    return acl;
  };
})();