  /*
 * Copyright ©2014 SURFsara bv, The Netherlands
 *
 * This file is part of the BeeHub webclient.
 *
 * BeeHub webclient is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * BeeHub webclient is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with BeeHub webclient.  If not, see <http://www.gnu.org/licenses/>.
 */
"use strict";

(function(){
  var aclTest = [{
    principal         :     '/system/groups/foo',
    invertprincipal   :     false,
    isprotected       :     false,
    grantdeny         :     2,
    privileges        :     ["write","write-acl"]
   },{
    principal         :     nl.sara.webdav.Ace.AUTHENTICATED,
    invertprincipal   :     true,
    isprotected       :     false,
    grantdeny         :     1,
    privileges        :     ["read"]
   },{
    principal         :     nl.sara.webdav.Ace.ALL,
    invertprincipal   :     false,
    isprotected       :     false,
    grantdeny         :     1,
    privileges        :     ["read"]
   },{
     principal         :     'owner',
     invertprincipal   :     false,
     isprotected       :     false,
     grantdeny         :     1,
     privileges        :     ["read"]
   },{
     principal         :     nl.sara.webdav.Ace.AUTHENTICATED,
     invertprincipal   :     false,
     isprotected       :     false,
     grantdeny         :     1,
     privileges        :     ["write"]
    }
  ];
  
  var currentDirectory =      "/foo/client_tests/";
  var addButton =             '.bh-dir-acl-add';
  var directoryView =         '#bh-dir-acl-directory-acl';
  var addAclButton=           '#bh-dir-acl-resource-button';
  var addAclDirButton=        '#bh-dir-acl-addrule-button';
  var aclFormView=            '#bh-dir-acl-directory-form';
  var indexLastProtected=     0;
  var aclColumns =            7;

  var aclContents =           '.bh-dir-acl-contents';
  var aclTable =              '.bh-dir-acl-table';
  var aclTableHeader =        '.bh-dir-acl-table-header';
  var aclRow =                '.bh-dir-acl-row';
  var aclComment =            '.bh-dir-acl-comment';
  var aclChangePermissions =  '.bh-dir-acl-change-permissions';
  var aclAllow =              '.bh-dir-acl-allow';
  var aclDeny =               '.bh-dir-acl-deny';
  var aclPermissionsField =   '.bh-dir-acl-permissions';
  var aclPermissionsSelect =  '.bh-dir-acl-permissions-select';
  var aclPermissions =        '.bh-dir-acl-table-permissions';
  var aclUpIcon =             '.bh-dir-acl-icon-up';
  var aclDownIcon =           '.bh-dir-acl-icon-down';
  var aclRemoveIcon =         '.bh-dir-acl-icon-remove';
  var aclPrincipal =          '.bh-dir-acl-principal';
  
  var optionRadio =           '.bh-dir-view-acl-optionRadio';
  var searchTable =           '.bh-dir-acl-table-search';
  var tablePermissions =      '.bh-dir-acl-table-permisions';

  module("acl view",{
    setup: function(){
      // Call init function
      nl.sara.beehub.view.acl.init();
    }, 
    teardown: function() {
      // clean up after each test
      backToOriginalEnvironment();
    }
  });
  
  var rememberChangePermissions = nl.sara.beehub.view.acl.changePermissions;
  var rememberChangePermissionsController = nl.sara.beehub.controller.changePermissions;
  var rememberShowChangePermissions = nl.sara.beehub.view.acl.showChangePermissions;
  var rememberMaskView =  nl.sara.beehub.controller.maskView;
  var rememberUpAclRule = nl.sara.beehub.view.acl.moveUpAclRule;
  var rememberMoveUpAclRuleController = nl.sara.beehub.controller.moveUpAclRule;
  var rememberDownAclRule = nl.sara.beehub.view.acl.moveDownAclRule;
  var rememberMoveDownAclRuleController = nl.sara.beehub.controller.moveDownAclRule;
  var rememberDeleteRowIndex = nl.sara.beehub.view.acl.deleteRowIndex;
  var rememberDeleteAclRuleController = nl.sara.beehub.controller.deleteAclRule;
  var rememberSetTableSort = nl.sara.beehub.view.acl.setTableSorter;
  var rememberAddAclRule = nl.sara.beehub.controller.addAclRule;

  var backToOriginalEnvironment = function(){
    nl.sara.beehub.view.acl.changePermissions = rememberChangePermissions;
    nl.sara.beehub.controller.changePermissions = rememberChangePermissionsController;
    nl.sara.beehub.view.acl.showChangePermissions = rememberShowChangePermissions;
    nl.sara.beehub.controller.maskView = rememberMaskView;
    nl.sara.beehub.view.acl.moveUpAclRule = rememberUpAclRule;
    nl.sara.beehub.controller.moveUpAclRule = rememberMoveUpAclRuleController;
    nl.sara.beehub.view.acl.moveDownAclRule = rememberDownAclRule;
    nl.sara.beehub.controller.moveDownAclRule = rememberMoveDownAclRuleController;
    nl.sara.beehub.view.acl.deleteRowIndex = rememberDeleteRowIndex;
    nl.sara.beehub.controller.deleteAclRule = rememberDeleteAclRuleController;
    nl.sara.beehub.view.acl.setTableSorter = rememberSetTableSort;
    nl.sara.beehub.controller.addAclRule = rememberAddAclRule;
  };
  
  /**
   * Check if row handlers are set
   * 
   * @param {Array} rows  Array with rows to check
   */
  var checkSetRowHandlers = function(rows){
    // Remember values
    var permissions = "";
    var permissionsOld = "";
    var aclPermissionsFieldShow = true;
    var aclPermissionsSelectShow = false;
    var rowIndex = 0;

    // Rewrite functions
    nl.sara.beehub.view.acl.changePermissions = function(row, val){
      deepEqual(val, permissions, "Permissions should be "+permissions);
    };
    
    nl.sara.beehub.controller.changePermissions = function(row, oldVal) {
      deepEqual(oldVal, permissionsOld, "Old permissions should be "+permissionsOld);
    };
    
    nl.sara.beehub.view.acl.showChangePermissions = function(row, show) {
      if (show) {
        $(row).find(aclPermissionsField).hide();
        $(row).find(aclPermissionsSelect).show();
        deepEqual($(row).find(aclPermissionsField).is(':hidden'), aclPermissionsFieldShow, 'Permissions field should be hidden');
        deepEqual($(row).find(aclPermissionsSelect).is(':hidden'), aclPermissionsSelectShow, 'Permissions select should be shown');
      } else {
        $(row).find(aclPermissionsField).show();
        $(row).find(aclPermissionsSelect).hide();
        deepEqual($(row).find(aclPermissionsField).is(':hidden'), aclPermissionsFieldShow, 'Permissions field should be shown');
        deepEqual($(row).find(aclPermissionsSelect).is(':hidden'), aclPermissionsSelectShow, 'Permissions select should be hidden');
      }
    };
    
    nl.sara.beehub.controller.maskView = function(value, bool) {
      // Do nothing
    };
    
    nl.sara.beehub.view.acl.moveUpAclRule = function(row){
      if ((row !== undefined) && ($(row).index() === rowIndex)) {
        ok(true, "Move up acl is called with the right row object");
      } else {
        ok(false, "Move up acl is called with no row or the wrong row index");
      }
    };
    
    nl.sara.beehub.controller.moveUpAclRule = function(row, t){
      if ((row !== undefined) && (t !== undefined) && ($(row).index() === rowIndex)) {
        ok(true, "Move up acl controller is called with the right row object");
      } else {
        ok(false, "Move up acl controller is called but row object or time object is wrong or undefined");
      }
    };
    
    nl.sara.beehub.view.acl.moveDownAclRule = function(row){
      if ((row !== undefined) && ($(row).index() === rowIndex)) {
        ok(true, "Move down acl is called with the right row object");
      } else {
        ok(false, "Move down acl is called with no row or the wrong row index");
      }
    };
    
    nl.sara.beehub.controller.moveDownAclRule = function(row, t){
      if ((row !== undefined) && (t !== undefined) && ($(row).index() === rowIndex)) {
        ok(true, "Move down acl controller is called with the right row object");
      } else {
        ok(false, "Move down acl controller is called but row object or time object is wrong or undefined");
      }
    };
    
    nl.sara.beehub.view.acl.deleteRowIndex = function(index){
      if ((index !== undefined) && (index === rowIndex)){
        ok(true, "Delete row is called with the right index");
      } else {
        ok(false, "Delete row is called but index is undefined or wrong");
      }
    };
    
    nl.sara.beehub.controller.deleteAclRule = function(row, index, t){
      if ((index !== undefined) && (index === rowIndex) && (t !== undefined)){
        ok(true, "Delete row controller is called with the right index");
      } else {
        ok(false, "Delete row controller is called but index is undefined or wrong");
      }
    };
    
    $.each(rows, function(key, row) {
      var info = $(row).find(aclComment).attr('data-value');
      if (info !== 'protected' && info !== 'inherited') {
        rowIndex = $(row).index();
        // Change permissions handler
        aclPermissionsFieldShow = true;
        aclPermissionsSelectShow = false;
        $(row).find(aclChangePermissions).click();
               
        // Check change handler
        permissionsOld = $(row).find(aclPermissionsField).text().trim();
        aclPermissionsFieldShow = false;
        aclPermissionsSelectShow = true;
        if ($(row).find(aclPermissions).prop("selectedIndex") !== 0) {
          permissions = "allow read";
          $(row).find(aclPermissions).prop("selectedIndex",0);
          $(row).find(aclPermissions).change();
        } else {
          permissions = "allow read, write";
          $(row).find(aclPermissions).prop("selectedIndex",1);
          $(row).find(aclPermissions).change();
        }; 
        // Check blur handler
        aclPermissionsFieldShow = true;
        aclPermissionsSelectShow = false;
        $(row).find(aclChangePermissions).click();
        aclPermissionsFieldShow = false;
        aclPermissionsSelectShow = true;
        $(row).find(aclPermissions).blur();
        
        // Check up icon handler
        $(row).find(aclUpIcon).click();
        
        // Check down icon handler
        $(row).find(aclDownIcon).click();
//        
        // Check delete icon handler
        $(row).find(aclRemoveIcon).click();
      };
    });
  };
  
  /**
   * Test a row that is created
   * 
   * @param {DOM object}  row     Row to test
   * @param {object}      ace     ace used to create the row
   * @param {boolean}     up      True if the up button should be visible, false otherwise
   * @param {boolean}     down    True if the down button should be visible, false otherwise
   * @param {boolean}     remove  True if the remove button should be visible, false otherwise
   * @returns  {void}
   */
  var testCreatedRow = function(row, ace, up, down, remove){
    var foundAce = nl.sara.beehub.view.acl.getAceFromDOMRow( row );
    deepEqual( foundAce, ace, 'ACE in the DOM should be the same as the one provided' );

    // Hidden select
    var select = true;
    if (row.find(aclPermissionsSelect) === undefined){
      select = false;
    }
    deepEqual(select, true, "Hidden select field is created");

    // Comment
    var comment = row.find(aclComment).attr('data-value');
    var iconDown = row.find(aclDownIcon);
    if ( ace.isprotected ){
      deepEqual(comment, "protected", "Info should be protected");
    } else if ( ace.inherited !== false ) {
      deepEqual(comment, "inherited", "Info should be inherited");
    } else {
      deepEqual(comment, "", "Info should be empty string");
    }
    // Icon up
    if (row.find(aclUpIcon).html() !== undefined){
      deepEqual(true, up, "Up icon should be visible.");
    } else {
      deepEqual(false, up, "Up icon should not be visible.");
    };
    
    // Icon down
    if (row.find(aclDownIcon).html() !== undefined){
      deepEqual(true, down, "Down icon should be visible.");
    } else {
      deepEqual(false, down, "Down icon should not be visible.");
    };
    
    // Icon delete
    if (row.find(aclRemoveIcon).html() !== undefined){
      deepEqual(true, remove, "Delete icon should be visible.");
    } else {
      deepEqual(false, remove, "Delete icon should not be visible.");
    };
  };
  
  /**
   * Check if Up/Down buttons are shown or hidden
   * 
   * @param {Array} rows   Rows to check
   */
  var checkSetUpDownButtons = function(rows){
    $.each(rows, function(index,row) {
      var info = $(row).find('.bh-dir-acl-comment').attr('data-value');
      var upLength = $(row).find('.bh-dir-acl-up').find('.bh-dir-acl-icon-up').length;
      var downLength = $(row).find('.bh-dir-acl-down').find('.bh-dir-acl-icon-down').length;

      if (info !== 'protected' && info !== 'inherited') {
        var prevProtected = $(row).prev().find('.bh-dir-acl-comment').attr('data-value');
        var nextInherited = $(row).next().find('.bh-dir-acl-comment').attr('data-value');
        
        // Check up button
        if ( prevProtected === "protected" ) {
          deepEqual(upLength, 0, "Up icon should be not visible now.");
        } else {
          deepEqual(upLength, 1, "Up icon should be visible now.");
        };
        
        // Check up button
        if ( nextInherited === "inherited" ) {
          deepEqual(downLength, 0, "Down icon should be not visible now.");
        } else {
          deepEqual(downLength, 1, "Down icon should be visible now.");
        };
      } else {
        // No up or down
        deepEqual(upLength, 0, "Up icon should be not visible now.");
        deepEqual(downLength, 0, "Down icon should be not visible now.");
      }
    });
  };
  
  /**
   * Test if view is set
   */
  test( 'nl.sara.beehub.view.acl.init: Set view', function() {
    expect( 3 );  
    
    nl.sara.beehub.view.acl.setTableSorter = function(input){
      deepEqual(input.length,1,"Table sorter is called");
    };
    
    // Call init function
    nl.sara.beehub.view.acl.init();
    
    // View is set
    deepEqual(nl.sara.beehub.view.acl.getViewPath(), currentDirectory, "View path should be "+currentDirectory );
    deepEqual(nl.sara.beehub.view.acl.getView(), "directory", "View should be directory");
    
  });
  
  /**
   * Test if add button handler is set
   */
  test( 'nl.sara.beehub.view.acl.init: Set view add button', function() {
    expect( 1 );  
 
    nl.sara.beehub.controller.addAclRule = function(){
      ok(true, "Add acl button click handler is set");
    };
    
    // Call init function
    nl.sara.beehub.view.acl.init();
    
    $(addButton).click(); 
  });
  
  /**
   * Test if row handlers are set
   */
  test( 'nl.sara.beehub.view.acl.init: Set view row handlers', function() {
    expect( 76 ); 
    var rows = nl.sara.beehub.view.acl.getAclView().find(aclContents).find(aclRow);
    checkSetRowHandlers(rows);
  });
  
  /**
   * Test setView, getViewPath and getView
   */
  test( 'nl.sara.beehub.view.acl.setView, getViewPath, getView', function() {
    expect( 2 ); 
    // Remember old value
    var view = nl.sara.beehub.view.acl.getView();
    var path = nl.sara.beehub.view.acl.getViewPath();
    
    nl.sara.beehub.view.acl.setView("test","/testpath");
    // View is set
    deepEqual(nl.sara.beehub.view.acl.getViewPath(), "/testpath", "View path should be /testpath" );
    deepEqual(nl.sara.beehub.view.acl.getView(), "test", "View should be test");
  
    // Back to original
    nl.sara.beehub.view.acl.setView(view, path);
  });
  
  /**
   * Test getAclView
   */
  test( 'nl.sara.beehub.view.acl.getAclView', function() {
    expect( 1 ); 
    deepEqual(nl.sara.beehub.view.acl.getAclView().attr("id"), directoryView.replace("#",""), "Acl view id should be "+directoryView.replace("#","") );
  });
  
  /**
   * Test getAddAclButton
   */
  test( 'nl.sara.beehub.view.acl.getAddAclButton', function() {
    expect( 1 ); 
    nl.sara.beehub.view.acl.setView("resource");
    $('#qunit-fixture').append('<button style="display: inline-block;" data-toggle="tooltip" title="Add rule" id="bh-dir-acl-resource-button" class="btn btn-small" disabled="">       <i class="icon-plus"></i> Add rule      </button>');
    deepEqual(nl.sara.beehub.view.acl.getAddAclButton().attr("id"), addAclButton.replace("#",""), "Add acl button id should be "+addAclButton.replace("#","") );
  });
  
  /**
   * Test getFormView
   */
  test( 'nl.sara.beehub.view.acl.getFormView', function() {
    expect( 1 ); 
    $('#qunit-fixture').append('<div id="'+aclFormView.replace("#","")+'"></div>');
    deepEqual(nl.sara.beehub.view.acl.getFormView().attr("id"), aclFormView.replace("#",""), "Form view id should be "+aclFormView.replace("#","") );
  });
  
  /**
   * Test allFixedButtons
   */
  test( 'nl.sara.beehub.view.acl.allFixedButtons', function() {
    expect( 2 ); 
    nl.sara.beehub.view.acl.allFixedButtons('hide');
    // Test if button is hidden
    deepEqual($(addAclDirButton).is(':hidden'), true, 'Add acl button should be hidden');
    nl.sara.beehub.view.acl.allFixedButtons('show');
    // Test if button is shown
    deepEqual($(addAclButton).is(':hidden'), false, 'Add acl button should be shown');

  });
  
  /**
   * Test addRow and createRow
   */
  test( 'nl.sara.beehub.view.acl.addRow,createRow', function() {
    expect( 24 );

    // Prepare some privileges
    var readPrivilege = new nl.sara.webdav.Privilege();
    readPrivilege.namespace = 'DAV:';
    readPrivilege.tagname = 'read';
    var writePrivilege = new nl.sara.webdav.Privilege();
    writePrivilege.namespace = 'DAV:';
    writePrivilege.tagname = 'write';
    var writeAclPrivilege = new nl.sara.webdav.Privilege();
    writeAclPrivilege.namespace = 'DAV:';
    writeAclPrivilege.tagname = 'write-acl';
    
    // Test createRow after last protected
    var ace = new nl.sara.webdav.Ace();
    ace.principal = 'test_principal';
    ace.invertprincipal = true;
    ace.grantdeny = nl.sara.webdav.Ace.GRANT;
    ace.addPrivilege( readPrivilege );
    ace.addPrivilege( writePrivilege );
    var createdRow = nl.sara.beehub.view.acl.createRow(ace);
    nl.sara.beehub.view.acl.addRow(createdRow, nl.sara.beehub.view.acl.getIndexLastProtected());
    var table = nl.sara.beehub.view.acl.getAclView().find(aclContents);
    var row = table.find('tr').find('td').filter("[data-value='test_principal']").parent();
    testCreatedRow(row, ace, false, true, true);
    
    // Test createRow with inherited ace
    var ace2 = new nl.sara.webdav.Ace();
    ace2.principal = 'test2_principal';
    ace2.invertprincipal = false;
    ace2.inherited = '/';
    ace2.grantdeny = nl.sara.webdav.Ace.GRANT;
    ace2.addPrivilege( readPrivilege );
    
    var createdRow2 = nl.sara.beehub.view.acl.createRow(ace2);
    var index = nl.sara.beehub.view.acl.getAclView().find(aclContents).find('tr').length;
    nl.sara.beehub.view.acl.addRow(createdRow2, index-1);
    var table2 = nl.sara.beehub.view.acl.getAclView().find(aclContents);
    var row2 = table2.find('tr').find('td').filter("[data-value='test2_principal']").parent();
    testCreatedRow(row2, ace2, false, false, false);
    
    // Test createRow with protected ace
    var ace3 = new nl.sara.webdav.Ace();
    ace3.principal = 'test3_principal';
    ace3.invertprincipal = false;
    ace3.isprotected = true;
    ace3.grantdeny = nl.sara.webdav.Ace.GRANT;
    ace3.addPrivilege( readPrivilege );
    
    // Test create row with up and down icons
    var createdRow3 = nl.sara.beehub.view.acl.createRow(ace3);
    nl.sara.beehub.view.acl.addRow(createdRow3, 0);
    var table3 = nl.sara.beehub.view.acl.getAclView().find(aclContents);
    var row3 = table3.find('tr').find('td').filter("[data-value='test3_principal']").parent();
    testCreatedRow(row3, ace3, false, false, false);

    var ace4 = new nl.sara.webdav.Ace();
    ace4.principal = 'test4_principal';
    ace4.grantdeny = nl.sara.webdav.Ace.DENY;
    ace4.addPrivilege( readPrivilege );
    ace4.addPrivilege( writePrivilege );
    ace4.addPrivilege( writeAclPrivilege );
    
    var createdRow4 = nl.sara.beehub.view.acl.createRow(ace4);
    nl.sara.beehub.view.acl.addRow(createdRow4, nl.sara.beehub.view.acl.getIndexLastProtected()+1);
    var table4 = nl.sara.beehub.view.acl.getAclView().find(aclContents);
    var row4 = table4.find('tr').find('td').filter("[data-value='test4_principal']").parent();
    testCreatedRow(row4, ace4, true, true, true);
  });
  
  /**
   * Test getIndexLastProtected
   */
  test('nl.sara.beehub.view.acl.getIndexLastProtected', function(){
    expect( 1 );
    var index = nl.sara.beehub.view.acl.getIndexLastProtected();
    
    // Test lat protected value
    deepEqual(index, indexLastProtected, 'Index last protected should be '+indexLastProtected);
  });
  
  /**
   * Test getAcl
   */
  test('nl.sara.beehub.view.acl.getAcl', function(){
    expect(25);   
    var acl = nl.sara.beehub.view.acl.getAcl();
    
    // Test values of all ace's
    for ( var key in acl.getAces() ) {
      var ace = acl.getAces()[key];
      var aceTest = aclTest[key];
      if (ace.principal.tagname !== undefined) {
        deepEqual(ace.principal.tagname, aceTest.principal, "Principal should be "+aceTest.principal);
      } else {
        deepEqual(ace.principal, aceTest.principal, "Principal should be "+aceTest.principal);
      }
      deepEqual(ace.invertprincipal, aceTest.invertprincipal, "Invert principal should be "+aceTest.invertprincipal);
      deepEqual(ace.isprotected, aceTest.isprotected, "Protected should be "+aceTest.isprotected);
      deepEqual(ace.grantdeny, aceTest.grantdeny, "Granddeny should be "+aceTest.granddeny);
      deepEqual(ace.getPrivilegeNames('DAV:'), aceTest.privileges, "Privileges should be "+aceTest.privileges);
    }
  });
  
  /**
   * Test setAddAclRuleDialogClickHandler
   */
  test('nl.sara.beehub.view.acl.setAddAclRuleDialogClickHandler', function(){
    expect(2);
    nl.sara.beehub.view.acl.setView("resource", currentDirectory);
    
    // Test if this function is called.
    var testFunction = function(ace){
      deepEqual( ace.grantdeny, nl.sara.webdav.Ace.GRANT, "Permissions should be granted." );
      deepEqual( ace.getPrivilegeNames( 'DAV:' ), [ 'read' ], "Privileges should be read." );
    };
    $('#qunit-fixture').append(nl.sara.beehub.view.acl.createDialogViewHtml());
    nl.sara.beehub.view.acl.setAddAclRuleDialogClickHandler(testFunction);
    nl.sara.beehub.view.acl.getAddAclButton().click();
  });
  
  /**
   * Test createHtmlAclForm
   */
  test('nl.sara.beehub.view.acl.createHtmlAclForm', function(){
    expect(11);
    
    // Create environment
    nl.sara.beehub.view.acl.setView("resource", currentDirectory);
    $('#qunit-fixture').append(nl.sara.beehub.view.acl.createDialogViewHtml(currentDirectory));
    var aclForm = nl.sara.beehub.view.acl.getFormView();
    
    // Test if radio buttons are available
    var values = ["authenticated", "all", "user_or_group"];
    aclForm.find('input[name = "'+optionRadio.replace(".","")+'"]').each(function(key, value){
      deepEqual($(value).val(), values[key], "Radio value should be "+values[key]);
    });
    
    // Test default checked value
    deepEqual(aclForm.find('input[name = "'+optionRadio.replace(".","")+'"]:checked').val(), "user_or_group","Selected value should be user_or_group");
    // Search field available
    deepEqual(aclForm.find(searchTable).val(),"", "Search field exists.");
    
    // Test if all options are available
    var options = ["allow read", "allow read, write", "allow read, write, change acl", "deny read, write, change acl", "deny write, change acl", "deny change acl"];
    aclForm.find(tablePermissions).find('option').each(function(key,option){
      deepEqual($(option).val(), options[key], "Option should be "+options[key]);
    });
    
  });
  
  /**
   * Test createDialogViewHtml
   */
  test('nl.sara.beehub.view.acl.createDialogViewHtml', function(){
    expect(2);
    
    // Set up environment
    nl.sara.beehub.view.acl.setView("resource", currentDirectory);
    $('#qunit-fixture').append(nl.sara.beehub.view.acl.createDialogViewHtml(currentDirectory));
    
    var count = nl.sara.beehub.view.acl.getAclView().find(aclTable).find(aclTableHeader).find('th').length;
    
    // Test total columns
    deepEqual(count, aclColumns, "Total columns should be "+aclColumns);
    // Find body and test name attribute of the body
    deepEqual(nl.sara.beehub.view.acl.getAclView().find(aclContents).attr('data-value'), currentDirectory, "Name attribute should be "+currentDirectory);
  });
  
  /**
   * Test deleteRowIndex
   */
  test('nl.sara.beehub.view.acl.deleteRowIndex', function(){
    expect(77);
    
    var name = nl.sara.beehub.view.acl.getAclView().find(aclContents).find(aclPrincipal).eq(1).attr('data-value');
    var result = nl.sara.beehub.view.acl.getAclView().find(aclContents).find('td[data-value = "'+name+'"]').attr('data-value');
    var length = nl.sara.beehub.view.acl.getAclView().find(aclContents).find('tr').length;
    // Check if row to delete exists
    deepEqual(result, name, "Name should be "+name);
    
    // Delete row
    nl.sara.beehub.view.acl.deleteRowIndex(1);
    
    var result2 = nl.sara.beehub.view.acl.getAclView().find(aclContents).eq(1).find('td[name = "'+name+'"]').attr('data-value');
    var length2 = nl.sara.beehub.view.acl.getAclView().find(aclContents).find('tr').length;
    var newLength = length -1;
    // Test if row is undefined now
    deepEqual(result2, undefined, "Name should be undefined");
    // Test is acl length is 1 shorter now
    deepEqual(length2, newLength, "Length of acl should be "+newLength);
    
    var rows = nl.sara.beehub.view.acl.getAclView().find(aclContents).find(aclRow);
    checkSetUpDownButtons(rows);
    checkSetRowHandlers(rows);
  });
  
  /**
   * Test moveDownAclRule
   */
  test('nl.sara.beehub.view.acl.moveDownAclRule' , function() {
    expect(93);
    
    var index = nl.sara.beehub.view.acl.getIndexLastProtected() + 1;
    var row = nl.sara.beehub.view.acl.getAclView().find(aclContents).find(aclRow).eq(index);
    var name = row.find(aclPrincipal).attr('data-value');
    
    nl.sara.beehub.view.acl.moveDownAclRule(row);
    
    // index +1 should be the same row now
    var rowNew = nl.sara.beehub.view.acl.getAclView().find(aclContents).find(aclRow).eq(index+1);
    var nameNew = rowNew.find(aclPrincipal).attr('data-value');
    deepEqual(nameNew, name, "Name should be "+name);
    
    var rows = nl.sara.beehub.view.acl.getAclView().find(aclContents).find(aclRow);
    checkSetUpDownButtons(rows);
    checkSetRowHandlers(rows);
  });
  
  /**
   * Test moveUpAclRule
   */
  test('nl.sara.beehub.view.acl.moveUpAclRule' , function() {
    expect(93);
    
    // Get first row that can move up
    var index = nl.sara.beehub.view.acl.getIndexLastProtected() + 2;
    var row = nl.sara.beehub.view.acl.getAclView().find(aclContents).find(aclRow).eq(index);
    var name = row.find(aclPrincipal).attr('data-value');
    
    nl.sara.beehub.view.acl.moveUpAclRule(row);
    
    // index +1 should be the same row now
    var rowNew = nl.sara.beehub.view.acl.getAclView().find(aclContents).find(aclRow).eq(index-1);
    var nameNew = rowNew.find(aclPrincipal).attr('data-value');
    deepEqual(nameNew, name, "Name should be "+name);
    
    var rows = nl.sara.beehub.view.acl.getAclView().find(aclContents).find(aclRow);
    checkSetUpDownButtons(rows);
    checkSetRowHandlers(rows);
  });
  
  /**
   * changePermissions
   */
  test('nl.sara.beehub.view.acl.changePermissions', function(){
    expect(6);
    
    var index = nl.sara.beehub.view.acl.getIndexLastProtected() + 1;
    var row = nl.sara.beehub.view.acl.getAclView().find(aclContents).find(aclRow).eq(index);
    
    // Test Allow read permissions
    var permissions = "allow read";
    nl.sara.beehub.view.acl.changePermissions(row, permissions);
    
    var allow = row.find(aclAllow).length;
    var title = row.find(aclChangePermissions).attr('title');
    var show = row.find('.presentation').html();
    deepEqual(allow, 1, "Class should be "+aclAllow);
    deepEqual(title, permissions, "Title should be "+permissions);
    deepEqual(show, permissions, "Presentation should be "+permissions);
    
    // Test Deny write, change acl permissions
    var permissions2 = "deny write, change acl";
    nl.sara.beehub.view.acl.changePermissions(row, permissions2);
    
    var deny = row.find(aclDeny).length;
    var title2 = row.find(aclChangePermissions).attr('title');
    var show2 = row.find('.presentation').html();
    deepEqual(deny, 1, "Class should be "+aclDeny);
    deepEqual(title2, permissions2, "Title should be "+permissions2);
    deepEqual(show2, permissions2, "Presentation should be "+permissions2);
  });
  
  /**
   * showChangePermissions
   */
  test('nl.sara.beehub.view.acl.showChangePermissions', function(){
    expect(6);
    
    var index = nl.sara.beehub.view.acl.getIndexLastProtected() + 1;
    var row = nl.sara.beehub.view.acl.getAclView().find(aclContents).find(aclRow).eq(index);
    
    deepEqual(row.find(aclPermissionsField).is(':hidden'), false, 'Permissions field should be shown');
    deepEqual(row.find(aclPermissionsSelect).is(':hidden'), true, 'Permissions select should be hidden');
   
    nl.sara.beehub.view.acl.showChangePermissions(row, true);
   
    deepEqual(row.find(aclPermissionsField).is(':hidden'), true, 'Permissions field should be hidden');
    deepEqual(row.find(aclPermissionsSelect).is(':hidden'), false, 'Permissions select should be shown');
    
    nl.sara.beehub.view.acl.showChangePermissions(row, false);
   
    deepEqual(row.find(aclPermissionsField).is(':hidden'), false, 'Permissions field should be shown');
    deepEqual(row.find(aclPermissionsSelect).is(':hidden'), true, 'Permissions select should be hidden');
  });
})();
// End of file