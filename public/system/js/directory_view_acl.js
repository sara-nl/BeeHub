/**
 * Copyright ©2013 SURFsara bv, The Netherlands
 *
 * This file is part of the beehub client
 *
 * beehub client is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * beehub-client is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with beehub.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */

"use strict";

 /*
 * Initialize acl view
 * 
 */
(function() {
  var permissions = {
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
  };

  // Used for showing the mask after delete, up or down
  var timeout = 500;
  
  var aclView = "";
  var resourcePath = "";
  
  nl.sara.beehub.view.acl.init = function() {
    // Set view
    nl.sara.beehub.view.acl.setView("directory", nl.sara.beehub.controller.getPath());
    // ACL TAB ACTIONS/FUNCTIONS
    nl.sara.beehub.view.acl.setTableSorter(nl.sara.beehub.view.acl.getAclView().find('.bh-dir-acl-contents').find("#bh-dir-acl-table"));
    // Add rule handler
    $('.bh-dir-acl-add').click(nl.sara.beehub.controller.addAclRule);
    // Add handler on row
    var rows = nl.sara.beehub.view.acl.getAclView().find('.bh-dir-acl-contents').find('.bh-dir-acl-row');
    setRowHandlers(rows); 
  };
  
  /*
   * Set view
   *  
   */
  nl.sara.beehub.view.acl.setView = function(view, path){
    aclView = view;
    resourcePath = path;
  };
  
  /*
   * Return View path
   */
  nl.sara.beehub.view.acl.getViewPath = function(){
    return resourcePath;    
  };
  
  /**
   * Return active acl table
   *  
   */
  nl.sara.beehub.view.acl.getView = function(){
    return aclView;
  };
  
  /**
   * Return acl view DOM
   *  
   */
  nl.sara.beehub.view.acl.getAclView = function(){
    return $('#bh-dir-acl-'+aclView+'-acl');
  };
  
  /**
   * Return acl add button DOM
   *  
   */
  nl.sara.beehub.view.acl.getAddAclButton = function(){
    return $('#bh-dir-acl-'+aclView+'-button');
  };
  
  /**
   * Return acl form DOM
   *  
   */
  nl.sara.beehub.view.acl.getFormView = function(){
    return $('#bh-dir-acl-'+aclView+'-form');
  };
  
  /*
   * Action for all buttons in the fixed view on the top of the acl table
   * 
   * @param DOM Object table 
   * 
   */
  nl.sara.beehub.view.acl.setTableSorter = function(table){
    // Use table sorter for sticky headers (not scrolling
    // table header)
    table.tablesorter({
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
        stickyHeaders_offset : 186
      }
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
      var info = $(row).find('.bh-dir-acl-comment').attr('name');
      if (info !== 'protected' && info !== 'inherited') {
        var rowObject = $( row );
        rowObject.find('.bh-dir-acl-change-permissions').unbind( 'click' ).click(handle_permissions_click);
        rowObject.find('.bh-dir-acl-permissions-select').unbind( 'change' ).change(function(){
          var row = $(this).closest('tr');
          var oldVal = $(row).find(".bh-dir-acl-permissions").html();
          var val = $(row).find(".bh-dir-acl-table-permissions option:selected").val();
          nl.sara.beehub.view.acl.showChangePermissions(row, false);
          nl.sara.beehub.view.acl.changePermissions(row, val);
          nl.sara.beehub.controller.changePermissions(row, oldVal);
        });
        // Blur handler
        rowObject.find('.bh-dir-acl-table-permissions').unbind( 'blur' ).blur(function(){
          var row = $(this).closest('tr');
          nl.sara.beehub.view.acl.showChangePermissions(row, false);
        });
        // Up icon
        rowObject.find('.bh-dir-acl-icon-up').unbind( 'click' ).click(handle_up_click);
        
        // Down icon
        rowObject.find('.bh-dir-acl-icon-down').unbind( 'click' ).click(handle_down_click);
        
        // Delete icon
        rowObject.find('.bh-dir-acl-icon-remove').unbind( 'click' ).click(handle_remove_click);
      };
    });
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
    var invert =  (ace['invert'] ? 'Everybody except ' : '');
    var row = [];
    var info = ((ace['protected'] || ace['inherited'] ) ? 'info' : '' );
    row.push('<tr class="bh-dir-acl-row '+info+'">');
    // Principal
    var show = '';
    switch(ace.principal)
    {
      case 'DAV: owner':
        show = '<span style="font-weight: bold">'+invert+'Owner</span>';
        break;
      case 'DAV: all':
        show = '<span style="font-weight: bold">'+invert+'Everybody</span>';
        break;
      case 'DAV: authenticated':
        show = '<span style="font-weight: bold">'+invert+'All BeeHub users</span>';
        break;
      case 'DAV: unauthenticated':
        show = '<span style="font-weight: bold">'+invert+'All unauthenticated users</span>';
        break;
      case 'DAV: self':
        show = '<span style="font-weight: bold">'+invert+'This resource itself</span>';
        break;
      default:
        var display = nl.sara.beehub.controller.getDisplayName(ace.principal);
        if (display !== ''){
          show = invert + nl.sara.beehub.controller.htmlEscape( display );
        } else {
          show = '<span style="font-weight: bold">'+invert+'Unrecognized principal!</span>';
        }
    };
    // Groups icon unless it's a single user
    var icon = '<i class="icon-user"></i><i class="icon-user"></i>';
    if (ace.principal.indexOf(nl.sara.beehub.users_path) !== -1) {
      icon = '<i class="icon-user"></i>';
    }
    row.push('<td class="bh-dir-acl-principal" name="' + nl.sara.beehub.controller.htmlEscape( ace.principal ) + '" data-invert="' + nl.sara.beehub.controller.htmlEscape( ace['invert'] ) + '" data-toggle="tooltip" title="' + nl.sara.beehub.controller.htmlEscape( ace.principal ) + '" ></i><b>'+show+'</b> ('+icon+')</td>');
    
    // Permissions
    var aceClass= '';
    var tooltip = '';
    
    var dropdown = '<td class="bh-dir-acl-permissions-select" hidden>\
                      <select class="bh-dir-acl-table-permissions">';
    dropdown += '<option '+(ace.permissions === 'allow read'?"selected":"")+' value="allow read">'+permissions['allow read'].dropdown+'</option>';
    dropdown += '<option '+(ace.permissions === 'allow read, write'?"selected":"")+' value="allow read, write">'+permissions['allow read, write'].dropdown+'</option>';
    dropdown += '<option '+(ace.permissions === 'allow read, write, change acl'?"selected":"")+' value="allow read, write, change acl">\
                    '+permissions['allow read, write, change acl'].dropdown+'</option>';
    dropdown += '<option '+(ace.permissions === 'deny read, write, change acl'?"selected":"")+' value="deny read, write, change acl">\
                    '+permissions['deny read, write, change acl'].dropdown+'</option>';
    dropdown += '<option '+(ace.permissions === 'deny write, change acl'?"selected":"")+' value="deny write, change acl">'+permissions['deny write, change acl'].dropdown+'</option>';
    dropdown += '<option '+(ace.permissions === 'deny change acl'?"selected":"")+' value="deny change acl">'+permissions['deny change acl'].dropdown+'</option>';
    dropdown += '</select></td>';
    row.push(dropdown);
    if (permissions[ace.permissions] !== undefined) {
      row.push('<td class="bh-dir-acl-permissions bh-dir-acl-change-permissions '+permissions[ace.permissions].class+'" style="cursor: pointer" data-toggle="tooltip" title="'+permissions[ace.permissions].title+'" ><span class="presentation">'+ace.permissions+'</span>');
    } else {
      var aceClass = 'bh-dir-acl-allow';
      if (ace.permissions.indexOf('deny') !== -1) {
        aceClass = 'bh-dir-acl-deny';
      }
      row.push('<td class="bh-dir-acl-permissions bh-dir-acl-change-permissions '+aceClass+'" style="cursor: pointer" data-toggle="tooltip" title="'+ace.privileges+'" ><span class="presentation">'+ace.permissions+'</span>');
    }
    if (ace.permissions.indexOf('unknown') !== -1) {
      row.push('<span class="original" hidden="hidden">'+ace.privileges+'</span>');
    };
    row.push('</td>');
    
    var info = '';
    var message = '';
    var aceClass = '';

    if ( ace['protected'] ) {
      info = 'protected';
      message = 'protected, no changes are possible';
      aceClass ='bh-dir-acl-protected';
    } else if ( (ace['inherited'] !== undefined) ) {
      info = 'inherited';
      message = 'inherited from: <a href="' + ace['inherited'] + '">' + ace['inherited'] + '</a>';
      aceClass ='bh-dir-acl-inherited';
    }

    // Comment, not changable by user
    row.push('</td><td class="bh-dir-acl-comment '+aceClass+'" name="'+info+'" >'+message+'</td>');
    // Up
    row.push('<td class="bh-dir-acl-up"></td>');
    // Down 
    row.push('<td class="bh-dir-acl-down"></td>');
    // Delete 
    if ( ace['protected'] || ace['inherited'] ) {
      row.push('<td></td>');
    } else {
      row.push('<td><i title="Delete" class="icon-remove bh-dir-acl-icon-remove" style="cursor: pointer"></i></td>');
    }
    row.push('</tr>');
    return row.join("");
  };
  
  /**
   * Checks if up or down is possible and show arrows
   *  
   */
  var setUpDownButtons = function(){
    $.each(nl.sara.beehub.view.acl.getAclView().find('.bh-dir-acl-contents').find('tr'), function(index, row){
      var info = $(row).find('.bh-dir-acl-comment').attr('name');
      if (info !== 'protected' && info !== 'inherited') {
        // Check up button
        if ( index -1 !== nl.sara.beehub.view.acl.getIndexLastProtected() ) {
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
    return nl.sara.beehub.view.acl.getAclView().find('.bh-dir-acl-contents').find('.bh-dir-acl-protected').length-1;
  };
  
  /**
   * Returns index of the last protected rule
   * 
   * @return {Integer} index Index of last protected rule
   */
  var getIndexFirstInherited = function(){
    // Count of all items
    var all = nl.sara.beehub.view.acl.getAclView().find('.bh-dir-acl-contents').find('tr').length;
    // Count of all inherited items
    var allInherited = nl.sara.beehub.view.acl.getAclView().find('.bh-dir-acl-contents').find('.bh-dir-acl-inherited').length;
    // Index
    var index = all - allInherited;
    return index;
  };
  
  /**
   * Create acl from acl table in acl view
   * 
   * @return {nl.sara.webdav.Acl} Acl
   */
  nl.sara.beehub.view.acl.getAcl = function() { 
    var acl = new nl.sara.webdav.Acl();
    // put each item acl table in the created webdav acl
    $.each(nl.sara.beehub.view.acl.getAclView().find('.bh-dir-acl-contents').find('tr'), function(index, row){
      var principal = $(row).find('.bh-dir-acl-principal').attr('name');
      var permissions = $(row).find('.bh-dir-acl-permissions span.presentation').text();
      var info = $(row).find('.bh-dir-acl-comment').attr('name');
      var invert = $(row).find('.bh-dir-acl-principal').attr('data-invert'); 
      
      if (info !== 'protected' && info !== 'inherited') {
        // create ace according the webdavlib specifications
        var ace = new nl.sara.webdav.Ace();
        
        if (invert === "true") {
          ace.invertprincipal=invert;
        };
        
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
  
  /**
   * Set acl on resource on or of
   * 
   * @param {Boolean}   ownACL        true or false
   * @param {String}    resourcePath  Path of resource
   * 
   */
  nl.sara.beehub.view.acl.setCustomAclOnResource = function(ownACL, resourcePath){
    // If the resource has it's own ACE, set the view appropriately
    var resourceDiv = $( 'tr[id="' + resourcePath + '"]' );
    var exclamation = resourceDiv.find( '.bh-resource-specific-acl' );
    if ( ownACL && ( exclamation.html() === "" )) {
      exclamation.replaceWith( '<td title="Resource specific ACL set!" class="bh-resource-specific-acl"><i class="icon-star-empty"></i></td>' );
    } else if ( ! ownACL ) {
      exclamation.replaceWith( '<td class="bh-resource-specific-acl"></td>');
    }
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
    var table = nl.sara.beehub.view.acl.getAclView().find('.bh-dir-acl-contents');
    if (index === -1) {
      table.append(row);
    } else {
      table.find('tr:eq('+index+')').after(row);
    }
    table.trigger("update");

    setUpDownButtons();
    // Set handlers again
    setRowHandlers([row]);
  };
  
  // DIALOG ACL VIEW
  nl.sara.beehub.view.acl.setAddAclRuleDialogClickHandler = function(addFunction){
    nl.sara.beehub.view.acl.getAddAclButton().click(function(){
      addFunction( nl.sara.beehub.view.dialog.getFormAce() );
    });
  };
  
  /*
   * Create html for acl view in dialpg
   *  
   */
  nl.sara.beehub.view.acl.createDialogViewHtml = function(resource){
    var html = nl.sara.beehub.view.acl.createHtmlAclForm();
    html+= '<button class="btn btn-small" id="bh-dir-acl-resource-button" title="Add rule"\
      data-toggle="tooltip" style="display: inline-block;">\
       <i class="icon-plus"></i> Add rule\
      </button><br><br>';
    html += '<div id="bh-dir-acl-resource-acl">';
    html += '<table id="bh-dir-acl-table" class="table table-striped table-hover table-condensed">\
        <thead class="bh-dir-acl-table-header">\
          <tr>\
  <!--           Principal -->\
            <th>Principal</th>\
  <!--           Permissions -->\
            <th>Permissions</th>\
  <!--          Hidden dropdown column -->\
            <th hidden></th>\
  <!--           Comments -->\
            <th>Comment</th>\
  <!--          Move up -->\
            <th class="bh-dir-small-column"></th>\
  <!--           Move down -->\
            <th class="bh-dir-small-column"></th>\
  <!--           Delete row -->\
            <th class="bh-dir-small-column"></th>\
          </tr>\
        </thead>\
        <tbody class="bh-dir-acl-contents" name="'+resource+'">\
      </tbody></table>';
    html += '</div>';
    return html;
  };
  

  
//  /**
//   * Return active acl table 
//   *  
//   */
//  nl.sara.beehub.view.acl.nl.sara.beehub.view.acl.getAclView = function(){
//    return aclView;
//  }
  
  
  
  /**
   * Delete row at certain index
   * 
   * Public function
   * 
   * @param {Integer} index Index of row to delete
   */
  nl.sara.beehub.view.acl.deleteRowIndex = function(index){
    nl.sara.beehub.view.acl.getAclView().find('.bh-dir-acl-contents').find('tr:eq('+index+')').remove();
    nl.sara.beehub.view.acl.getAclView().find('.bh-dir-acl-contents').trigger("update");
    setUpDownButtons();
  };
  
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
    style="cursor: pointer; display: table-cell;"><span class="presentation">' + nl.sara.beehub.controller.htmlEscape( val ) + '</span></td>';
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
  };
  
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
  
  /**
   * Create html for acl form
   * 
   * @return {String} html
   * 
   */
  nl.sara.beehub.view.acl.createHtmlAclForm = function() {
    return '\
    <div id="bh-dir-acl-'+aclView+'-form">\
        <table>\
        <tr>\
          <td class="bh-dir-acl-table-label"><label><b>Principal</b></label></td>\
          <td><label class="radio"><input type="radio" class="bh-dir-view-acl-optionRadio bh-dir-acl-add-radio1" name="bh-dir-view-acl-optionRadio" value="authenticated" unchecked>All BeeHub users</label></td>\
        </tr>\
        <tr>\
          <td class="bh-dir-acl-table-label"></td>\
          <td><label class="radio"><input type="radio" class="bh-dir-view-acl-optionRadio bh-dir-acl-add-radio2" name="bh-dir-view-acl-optionRadio" value="all" unchecked>Everybody</label></td>\
        </tr>\
        <tr>\
          <td class="bh-dir-acl-table-label"></td>\
          <td>\
            <div class="radio">\
              <input type="radio" class="bh-dir-view-acl-optionRadio bh-dir-acl-add-radio3" name="bh-dir-view-acl-optionRadio" value="user_or_group" checked>\
              <input class="bh-dir-acl-table-search" type="text"  value="" placeholder="Search user or group...">\
            </div></td>\
        </tr>\
        <tr>\
          <td class="bh-dir-acl-table-label"><label><b>Permisions</b></label></td>\
          <td><select class="bh-dir-acl-table-permisions">\
            <option value="allow read">allow read</option>\
            <option value="allow read, write">allow read, write</option>\
            <option value="allow read, write, change acl">allow read, write, change acl</option>\
            <option value="deny read, write, change acl">deny read, write, change acl</option>\
            <option value="deny write, change acl">deny write, change acl</option>\
            <option value="deny change acl">deny change acl</option>\
          </select></td>\
        </tr>\
      </table>\
    </div>\
    ';
  };
  
})();
