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
  var currentDirectory =      "/foo/";
  var addButton =             '.bh-dir-acl-add';
  
  var aclContents =           '.bh-dir-acl-contents';
  var aclRow =                '.bh-dir-acl-row';
  var aclComment =            '.bh-dir-acl-comment';
  var aclChangePermissions =  '.bh-dir-acl-change-permissions';
  var aclPermissionsField =   '.bh-dir-acl-permissions';
  var aclPermissionsSelect =  '.bh-dir-acl-permissions-select';
  var aclPermissions =        '.bh-dir-acl-table-permissions';
  var aclUpIcon =             '.bh-dir-acl-icon-up';
  var aclDownIcon =           '.bh-dir-acl-icon-down';
  var aclRemoveIcon =         '.bh-dir-acl-icon-remove';

  module("view acl",{
    setup: function(){
      // Call init function
      nl.sara.beehub.view.acl.init();
    }
  });
  
  /**
   * Check if row handlers are set
   * 
   * @param {Array} rows  Array with rows to check
   */
  var checkSetRowHandlers = function(rows){
    var permissions = "";
    var permissionsOld = "";
    var aclPermissionsFieldShow = true;
    var aclPermissionsSelectShow = false;

    var rememberChangePermissions = nl.sara.beehub.view.acl.changePermissions;
    nl.sara.beehub.view.acl.changePermissions = function(row, val){
      deepEqual(val, permissions, "Permissions should be "+permissions);
    };
    
    var rememberChangePermissionsController = nl.sara.beehub.controller.changePermissions;
    nl.sara.beehub.controller.changePermissions = function(row, oldVal) {
      deepEqual(oldVal, permissionsOld, "Old permissions should be "+permissionsOld);
    }
    
    var rememberShowChangePermissions = nl.sara.beehub.view.acl.showChangePermissions;
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
    }
    
    var rememberMaskView =  nl.sara.beehub.controller.maskView;
    nl.sara.beehub.controller.maskView = function(value, bool) {
      ok(true, "Mask view is called");
    }
    
    var rememberUpAclRule = nl.sara.beehub.view.acl.moveUpAclRule;
    nl.sara.beehub.view.acl.moveUpAclRule = function(row){
      deepEqual("iets in row", "deze row", "");
    };
    
    var rememberMoveUpAclRuleController = nl.sara.beehub.controller.moveUpAclRule;
    nl.sara.beehub.controller.moveUpAclRule = function(row, t){
      deepEqual("iets","met iets","");
    }
    
    var rememberDownAclRule = nl.sara.beehub.view.acl.moveDownAclRule;
    nl.sara.beehub.view.acl.moveDownAclRule = function(row){
      deepEqual("iets in row", "deze row", "");
    };
    
    var rememberMoveDownAclRuleController = nl.sara.beehub.controller.moveDownAclRule;
    nl.sara.beehub.controller.moveDownAclRule = function(row, t){
      deepEqual("iets","met iets","");
    }
    
    var rememberDeleteRowIndex = nl.sara.beehub.view.acl.deleteRowIndex;
    nl.sara.beehub.view.acl.deleteRowIndex = function(index){
      deepEqual("iets","met iets","");
    };
    
    var rememberDeleteAclRuleController = nl.sara.beehub.controller.deleteAclRule;
    nl.sara.beehub.controller.deleteAclRule = function(row, index, t){
      deepEqual("iets","met iets","");
    };
    
    $.each(rows, function(key, row) {
      var info = $(row).find(aclComment).attr('name');
      if (info !== 'protected' && info !== 'inherited') {
        // Change permissions handler
        aclPermissionsFieldShow = true;
        aclPermissionsSelectShow = false;
        $(row).find(aclChangePermissions).click();
       
        // check focus
        deepEqual($(row).find(aclPermissions).is(':focus'), true, 'Mouse should be focused in select field');
        
        // Check change handler
        permissionsOld = $(row).find(aclPermissionsField).html();
        aclPermissionsFieldShow = false;
        aclPermissionsSelectShow = true;
        if ($(row).find(aclPermissions).prop("selectedIndex") !== 0) {
          permissions = "allow read";
          $(row).find(aclPermissions).val(0).change();
        } else {
          permissions = "allow read, write";
          $(row).find(aclPermissions).val(1).change();
        }; 
        
        // Check blur handler
        aclPermissionsFieldShow = true;
        aclPermissionsSelectShow = false;
        $(row).find(aclChangePermissions).click();
        aclPermissionsFieldShow = false;
        aclPermissionsSelectShow = true;
        $(row).find(aclPermissions).blur();
        
        // Check up icon handler
//        console.log($(row));
//        console.log($(row).find(aclUpIcon));
        $(row).find(aclUpIcon).click();
        
        // Check down icon handler
        $(row).find(aclDownIcon).click();
        
        // Check delete icon handler
        $(row).find(aclRemoveIcon).click();
      };
    });
    
    nl.sara.beehub.view.acl.changePermissions = rememberChangePermissions;
    nl.sara.beehub.controller.changePermissions = rememberChangePermissionsController;
    nl.sara.beehub.view.acl.showChangePermissions = rememberShowChangePermissions;
    nl.sara.beehub.controller.maskView = rememberMaskView;
    nl.sara.beehub.view.acl.moveUpAclRule = rememberUpAclRule;
    nl.sara.beehub.controller.moveUpAclRule = rememberMoveUpAclRuleController;
    nl.sara.beehub.view.acl.moveDownAclRule = rememberDownAclRule;
    nl.sara.beehub.controller.moveDownAclRule = rememberMoveDownAclRuleController;
  };
  
  /**
   * Test init function
   */
  test( 'nl.sara.beehub.view.acl.init: Set view', function() {
    expect( 15 );  
    
    var rememberSetTableSort = nl.sara.beehub.view.acl.setTableSorter;
    nl.sara.beehub.view.acl.setTableSorter = function(input){
      deepEqual(input.length,1,"Table sorter is called");
    }
    var rememberAddAclRule = nl.sara.beehub.controller.addAclRule;
    nl.sara.beehub.controller.addAclRule = function(){
      ok(true, "Add acl button click handler is set");
    };
    
    // Call init function
    nl.sara.beehub.view.acl.init();
    
    // View is set
    deepEqual(nl.sara.beehub.view.acl.getViewPath(), currentDirectory, "View path should be "+currentDirectory );
    deepEqual(nl.sara.beehub.view.acl.getView(), "directory", "View should be directory");

    $(addButton).click();
    
    var rows = nl.sara.beehub.view.acl.getAclView().find(aclContents).find(aclRow);
    checkSetRowHandlers(rows);
    
    nl.sara.beehub.view.acl.setTableSorter = rememberSetTableSort;
    nl.sara.beehub.controller.addAclRule = rememberAddAclRule;
  });
})();
// End of file