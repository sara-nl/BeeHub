 /*
 * Initialize acl view
 * 
 */
(function() {
  permissions = {
    'deny read, write, change acl': {
      'class'     : "bh-dir-acl-deny",
      'title'     : "deny read, write, change acl",
      'dropdown'  : "deny read, write, change acl"
    },
    'deny write, change acl': {
      'class'     : "bh-dir-acl-deny",
      'title'     : "deny write, change acl",
      'dropdown'  : "deny write, change acl"
    },
    'deny change acl': {
      'class'     : "bh-dir-acl-deny",
      'title'     : "deny change acl",
      'dropdown'  : "deny change acl"
    },
    'allow read': {
      'class'     : "bh-dir-acl-allow",
      'title'     : "allow read",
      'dropdown'  : "allow read"
    },
    'allow read, write': {
      'class'     : "bh-dir-acl-allow",
      'title'     : "allow read, write",
      'dropdown'  : "allow read, write"
    },
    'allow read, write, change acl': {
      'class'     : "bh-dir-acl-allow",
      'title'     : "allow read, write, change acl",
      'dropdown'  : "allow read, write, change acl"
    } 
  }

  // Used for showing the mask after delete, up or down
  var timeout = 500;
  
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
    // Add handler on row
    var rows = $('.bh-dir-acl-row');
    setRowHandlers(rows); 
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
  
  /**
   * Sets up, down, delete handlers
   * 
   * @param rows Array of row to set handlers for
   */
  var setRowHandlers = function(rows){   
    $.each(rows, function (key, row){
      // Permissions 
      $(row).find('.bh-dir-acl-change-permissions').unbind().click(handle_permissions_click);
      
      $(row).find('.bh-dir-acl-permissions-select').unbind().change(function(){
        var row = $(this).closest('tr');
        var oldVal = $(row).find(".bh-dir-acl-permissions").html();
        var val = $(row).find(".bh-dir-acl-table-permissions option:selected").val();
        nl.sara.beehub.view.acl.showChangePermissions(row, false);
        nl.sara.beehub.view.acl.changePermissions(row, val);
        nl.sara.beehub.controller.changePermissions(row, oldVal);
      });
      
      // Blur handler
      $(row).find('.bh-dir-acl-table-permissions').unbind().blur(function(){
        var row = $(this).closest('tr');
        nl.sara.beehub.view.acl.showChangePermissions(row, false);
      });
      
      // Up icon
      $(row).find('.bh-dir-acl-icon-up').unbind().click(handle_up_click);
      
      // Down icon
      $(row).find('.bh-dir-acl-icon-down').unbind().click(handle_down_click);
      
      // Delete icon
      $(row).find('.bh-dir-acl-icon-remove').unbind().click(handle_remove_click);
    }) 
  };
  
  /*
   * Create aclview row from ace object
   * 
   * Public function
   * 
   * @param {nl.sara.beehub.ClientAce} ace Ace object
   * 
   * @return String Row string 
   */
  nl.sara.beehub.view.acl.createRow = function(ace){
    var row = [];
    
    row.push('<tr>');
    // Principal
    var show = '';
    switch(ace.principal)
    {
      case 'DAV: all':
        show = '<em>Everybody</em>';
        break;
      case 'DAV: authenticated':
        show = '<em>All BeeHub users</em>'
        break;
      default:
        show = nl.sara.beehub.controller.getDisplayName(ace.principal)
    };
    // Groups icon unless it's a single user
    var icon = '<i class="icon-user"></i><i class="icon-user"></i>';
    if (ace.principal.indexOf(nl.sara.beehub.users_path) !== -1) {
      icon = '<i class="icon-user"></i>';
    }
    row.push('<td class="bh-dir-acl-principal" name="'+ace.principal+'" data-toggle="tooltip" title="'+ace.principal+'" ></i><b>'+show+'</b> ('+icon+')</td>');
    
    // Permissions
    var aceClass= '';
    var tooltip = '';

    var dropdown = '<td class="bh-dir-acl-permissions-select" hidden>\
                      <select class="bh-dir-acl-table-permissions">\
                        <option value="allow read">'+permissions['allow read'].dropdown+'</option>\
                        <option value="allow read, write">'+permissions['allow read, write'].dropdown+'</option>\
                        <option value="allow read, write, change acl">'+permissions['allow read, write, change acl'].dropdown+'</option>\
                        <option value="deny read, write, change acl">'+permissions['deny read, write, change acl'].dropdown+'</option>\
                        <option value="deny write, change acl">'+permissions['deny write, change acl'].dropdown+'</option>\
                        <option value="deny change acl">'+permissions['deny change acl'].dropdown+'</option>\
                      </select>\
                    </td>';
    row.push(dropdown);
    
    row.push('<td class="bh-dir-acl-permissions bh-dir-acl-change-permissions '+permissions[ace.permissions].class+'" style="cursor: pointer" data-toggle="tooltip" title="'+permissions[ace.permissions].title+'" ><span class="presentation">'+ace.permissions+'</span></td>');
    
    // Comment, not changable by user
    row.push('<td class="bh-dir-acl-comment" name=""></td>');
    // Up
    row.push('<td class="bh-dir-acl-up"></td>');
    // Down 
    row.push('<td class="bh-dir-acl-down"></td>');
    // Delete 
    row.push('<td><i title="Delete" class="icon-remove bh-dir-acl-icon-remove" style="cursor: pointer"></i></td>');
    
    row.push('</tr>');
    return row.join("");
  };
  
  /**
   * Checks if up or down is possible and show arrows
   *  
   */
  var setUpDownButtons = function(){  
    $.each($('.bh-dir-acl-contents > tr'), function(index, row){
      var info = $(row).find('.bh-dir-acl-comment').attr('name');
      if (info !== 'protected' && info !== 'inherited') {
        // Check up button
        if ( index - 1 !== nl.sara.beehub.view.acl.getIndexLastProtected() ) {
          $(row).find('.bh-dir-acl-up').html('<i title="Move up" class="icon-chevron-up bh-dir-acl-icon-up" style="cursor: pointer"></i>');
        } else {
          $(row).find('.bh-dir-acl-up').html('');
        }
        
        // Check down button
        if ( index + 1 !== getIndexFirstInherited() ) {
          $(row).find('.bh-dir-acl-down').html('<i title="Move down" class="icon-chevron-down bh-dir-acl-icon-down" style="cursor: pointer"></i>');
        } else {
          $(row).find('.bh-dir-acl-down').html('');
        }
      }
      setRowHandlers([row]);
    });
  };
  
  /**
   * Returns index of the last protected rule
   * 
   * Public function
   * 
   * @return {Integer} index Index of last protected rule
   */
  nl.sara.beehub.view.acl.getIndexLastProtected = function(){
    // Get protected items. length -1 is index
    return $('.bh-dir-acl-protected').length-1;
  }
  
  /**
   * Returns index of the last protected rule
   * 
   * @return {Integer} index Index of last protected rule
   */
  var getIndexFirstInherited = function(){
    // Count of all items
    var all = $('.bh-dir-acl-contents > tr').length;
    // Count of all inherited items
    var allInherited = $('.bh-dir-acl-inherited').length;
    // Index
    var index = all - allInherited;
    return index;
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
      var permissions = $(row).find('.bh-dir-acl-permissions span.presentation').text();
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
        
        var managePriv = new nl.sara.webdav.Privilege();
        managePriv.namespace = "DAV:";
        managePriv.tagname= "write-acl";

        var allPriv = new nl.sara.webdav.Privilege();
        allPriv.namespace = "DAV:";
        allPriv.tagname= "all";
        
        switch( permissions )
        {
          case 'deny read, write, change acl':
            ace.grantdeny = nl.sara.webdav.Ace.DENY;
            ace.addPrivilege(readPriv);
            ace.addPrivilege(writePriv);
            ace.addPrivilege(managePriv);
            break;
          case 'deny write, change acl':
            ace.grantdeny = nl.sara.webdav.Ace.DENY;
            ace.addPrivilege(writePriv);
            ace.addPrivilege(managePriv);
            break;
          case 'deny change acl':
            ace.grantdeny = nl.sara.webdav.Ace.DENY;
            ace.addPrivilege(managePriv);
            break;
          case 'deny all':
            ace.grantdeny = nl.sara.webdav.Ace.DENY;
            ace.addPrivilege(allPriv);
            break;
          case 'allow read':
            ace.grantdeny = nl.sara.webdav.Ace.GRANT;
            ace.addPrivilege(readPriv);
            break;
          case 'allow read, write':
            ace.grantdeny = nl.sara.webdav.Ace.GRANT;
            ace.addPrivilege(readPriv);
            ace.addPrivilege(writePriv);
            break;
          case 'allow read, write, change acl':
            ace.grantdeny = nl.sara.webdav.Ace.GRANT;
            ace.addPrivilege(readPriv);
            ace.addPrivilege(writePriv);
            ace.addPrivilege(managePriv);
            break;
          case 'allow all':
            ace.grantdeny = nl.sara.webdav.Ace.GRANT;
            ace.addPrivilege(allPriv);
            break;
          default:
            var originalPrivs = $(row).find('.bh-dir-acl-permissions span.original').text().split(' ');
            if ( ( originalPrivs.length === 0 ) || ( ( originalPrivs.length % 2 ) !== 0 ) ) {
              alert( 'There was an error identifying an unknown privilege. Please reload the page and make sure all your privileges are displayed correctly!' );
            } else {
              if ( RegExp('^allow ').test( permissions ) ) {
                ace.grantdeny = nl.sara.webdav.Ace.GRANT;
              } else {
                ace.grantdeny = nl.sara.webdav.Ace.DENY;
              }
              var privWordCounter = 0;
              while ( privWordCounter < originalPrivs.length ) {
                var newPriv = new nl.sara.webdav.Privilege();
                newPriv.namespace = originalPrivs[ privWordCounter++ ];
                newPriv.tagname = originalPrivs[ privWordCounter++ ];
                ace.addPrivilege( newPriv );
              }
            }
          break;
        };
        acl.addAce(ace);
      };
    });
    return acl;
  };
  
  /*
   * Add ace to Acl view
   * 
   * Public function
   * 
   * @param {DOM Object} row  
   * @param {Integer} index Index to prepend row after
   * 
   */
  nl.sara.beehub.view.acl.addRow = function(row, index){
    $('.bh-dir-acl-contents > tr:eq('+index+')').after(row);
    $('.bh-dir-acl-contents').trigger("update");

    setUpDownButtons();
    // Set handlers again
    setRowHandlers([row]);
  };
  
  /**
   * Delete row at certain index
   * 
   * Public function
   * 
   * @param {Integer} index Index of row to delete
   */
  nl.sara.beehub.view.acl.deleteRowIndex = function(index){
    $('.bh-dir-acl-contents > tr:eq('+index+')').remove();
    $('.bh-dir-acl-contents').trigger("update");
    setUpDownButtons();
  }
  
  /**
   * Move down acl rule
   * 
   * @param {DOM object} row  Row to move down
   */
  nl.sara.beehub.view.acl.moveDownAclRule = function(row) {
    row.insertAfter( row.next() );
    setUpDownButtons();
    // Set handlers again
    setRowHandlers([row]);
  };
  
  /**
   * Move up acl rule
   * 
   * @param {DOM object} row  Row to move up
   */
  nl.sara.beehub.view.acl.moveUpAclRule = function(row) {
    row.insertBefore( row.prev() );
    setUpDownButtons();
    // Set handlers again
    setRowHandlers([row]);
  };
  
  /**
   * Change permissions of row
   * 
   * @param {DOM object}  row Row to change permissions
   * @param {String}      val Permission value
   */
  nl.sara.beehub.view.acl.changePermissions = function(row, val){
    var td = '<td class="bh-dir-acl-permissions bh-dir-acl-change-permissions '+permissions[val].class+'"\
    title="'+permissions[val].title+'" data-toggle="tooltip" \
    style="cursor: pointer; display: table-cell;"><span class="presentation">'+val+'</span></td>';
    $(row).find(".bh-dir-acl-permissions").replaceWith(td);
  };
  
  nl.sara.beehub.view.acl.showChangePermissions = function(row, show){
    if (show) {
      $(row).find(".bh-dir-acl-permissions").hide();
      $(row).find(".bh-dir-acl-permissions-select").show();
    } else {
      $(row).find(".bh-dir-acl-permissions-select").hide();
      $(row).find(".bh-dir-acl-permissions").show();
    }
    setRowHandlers([row]);   
  }
  
  /**
   * Onclick handler permissions acl view
   */
  var handle_permissions_click = function() {
    var row = $(this).closest('tr');    
    nl.sara.beehub.view.acl.showChangePermissions(row, true);  
    $(row).find(".bh-dir-acl-table-permissions").focus();
  };
  
  /**
   * Onclick handler up icon acl view
   */
  var handle_up_click = function() {
     // mask view
     nl.sara.beehub.controller.maskView("transparant", true);
     var t=setTimeout(function(){nl.sara.beehub.controller.maskView("loading", true);},timeout);
     
     var row = $(this).closest('tr');
     nl.sara.beehub.view.acl.moveUpAclRule(row);
     
     // Add rule on server
     nl.sara.beehub.controller.moveUpAclRule(row, t);
  };
  
  /**
   * Onclick handler up icon acl view
   */
  var handle_down_click = function() {
    // mask view
    nl.sara.beehub.controller.maskView("transparant", true);
    var t=setTimeout(function(){nl.sara.beehub.controller.maskView("loading", true);},timeout);
    
    var row = $(this).closest('tr');
    nl.sara.beehub.view.acl.moveDownAclRule(row);
    
    // Add rule on server
    nl.sara.beehub.controller.moveDownAclRule(row, t);
  };
  
  /**
   * Onclick handler up icon acl view
   */
  var handle_remove_click = function() {
    // mask view
    nl.sara.beehub.controller.maskView("transparant", true);
    var t=setTimeout(function(){nl.sara.beehub.controller.maskView("loading", true);},timeout);
    var row = $(this).closest('tr');
    var index = row.index();
    nl.sara.beehub.view.acl.deleteRowIndex(index);
   
    // Add rule on server
    nl.sara.beehub.controller.deleteAclRule(row, index, t);
  };
})();
