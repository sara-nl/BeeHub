/*
 * Copyright ©2013 SARA bv, The Netherlands
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
 */

/** 
 * Beehub Client dialogs
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 */

(function(){
  /*
   * Clear dialog
   * 
   * Public function
   * 
   */
  nl.sara.beehub.view.dialog.clearView = function(){
    // Close dialog
    $('#bh-dir-dialog').dialog("close");
  };
  
  /*
   * Show dialog with error
   * 
   * Public function
   * 
   * @param {String} error The error to show
   */
  nl.sara.beehub.view.dialog.showError = function(error) {
    $('#bh-dir-dialog').html(error);
    $('#bh-dir-dialog').dialog({
      resizable: false,
      title: " Error!",
      dialogClass: "custom_dialog",
      modal: true,
      maxHeight: 400,
      closeOnEscape: false,
      dialogClass: "custom-dialog",
      buttons: [{
        text: "Ok",
        click: function() {
          $(this).dialog("close");
        }
      }]
    });
  };
  
  /*
   * Show acl dialog
   * 
   * Public function
   * 
   * @param {String} resource The resource to show
   */
  nl.sara.beehub.view.dialog.showAcl = function(aceObjects) {
//    console.log("nu");
//    console.log(aceObjects);
//    var html = createHtmlAclView(aces);
    var html = '';
    $('#bh-dir-dialog').html(html);
    $('#bh-dir-dialog').dialog({
      resizable: false,
      title: " ACL",
      dialogClass: "custom_dialog",
      modal: true,
      minWidth:800,
      maxHeight: 400,
      closeOnEscape: false,
      dialogClass: "custom-dialog",
      buttons: [{
        text: "Ok",
        click: function() {
          $(this).dialog("close");
        }
      }]
    });
  };

  
  /*
   * Show dialog with ready buttons
   * 
   * Public function
   * 
   * @param function actionFunction Function to call when ready is clicked
   * 
   */
  nl.sara.beehub.view.dialog.setDialogReady = function(actionFunction){
    $('#bh-dir-dialog-button').button({label:"Ready"});
    $('#bh-dir-dialog-button').button("enable");
    $('#bh-dir-dialog-button').removeClass("btn-danger");
    $('#bh-dir-cancel-dialog-button').hide();
    $('#bh-dir-dialog-button').unbind('click').click(function(){
      $("#bh-dir-dialog").dialog("close");
      actionFunction();
    });
  };
  
  /*
  * Show progress bar
  * 
  * Public function
  * 
  * @param Resource resource Resource to show progress from
  * @param Integer  progress Progress of action
  */
  nl.sara.beehub.view.dialog.showProgressBar = function(resource, progress){
    $("tr[id='dialog_tr_"+resource.path+"']").find('.info').html("<div class='progress progress-success progress-striped'><div class='bar' style='width: "+progress+"%;'>"+progress+"%</div></div>");
  };
  
  /*
  * Update info column in dialog
  * 
  * Public function
  * 
  * @param Resource resource Resource to update
  * @param String   info     Information for dialog
  */
  nl.sara.beehub.view.dialog.updateResourceInfo = function(resource, info){
    $("tr[id='dialog_tr_"+resource.path+"']").find('.info').html("<b>"+info+"</b>");
  };
  
  /*
  * Set overwrite, rename and cancel buttons;
  * 
  * Public function
  * 
  * @param {Object} resource Resource
  * @param {Function} overwriteFunction Overwrite handler
  * @param {Function} renameFunction    Rename handler
  * @param {Function} cancelFunction    Cancel handler.
  */
  nl.sara.beehub.view.dialog.setAlreadyExist = function(resource, overwriteFunction, renameFunction, cancelFunction){
    var overwriteButton = '<button class="btn btn-danger overwritebutton">Overwrite</button>';
    var renameButton = '<button class="btn btn-success renamebutton">Rename</button>';
    var cancelButton = '<button class="btn btn-success cancelbutton">Cancel</button>';
    
    $("tr[id='dialog_tr_"+resource.path+"']").find('.info').html("Item exist on server!<br/>"+renameButton+" "+overwriteButton+" "+cancelButton);
    
    // Overwrite click handler
    $("tr[id='dialog_tr_"+resource.path+"']").find('.overwritebutton').click(function(){
      overwriteFunction();
    });
    
    // Cancel click handler
    $("tr[id='dialog_tr_"+resource.path+"']").find('.cancelbutton').click(function(){
      cancelFunction();
    });
    
    // Rename click handler
    $("tr[id='dialog_tr_"+resource.path+"']").find('.renamebutton').click(function(){   
      renameFunction();
    });
  };
  
  /*
  * Scroll to position in dialog
  * 
  * Public function
  * 
  * @param Integer number Position to scroll to
  */
  nl.sara.beehub.view.dialog.scrollTo = function(number){
    $("#bh-dir-dialog").scrollTop(number);
  };
  
   
  /*
   * Show dialog with resources to copy, move, upload or delete
   * 
   * Public function
   * 
   * @param function actionFunction Action handler
   */
  nl.sara.beehub.view.dialog.showResourcesDialog = function(actionFunction){
    var config = {}; 
    // Set text and labels
    switch(nl.sara.beehub.controller.actionAction)
    {
    case "copy": 
      config.title = "Copy to "+nl.sara.beehub.controller.actionDestination;
      config.buttonLabel = "Copy items...";
      config.buttonText = "Copy";
      break;
    case "move":
      config.title = "Move to "+nl.sara.beehub.controller.actionDestination;
      config.buttonLabel = "Moving items...";
      config.buttonText = "Move";
      break;
    case "delete":
      config.title = "Delete";
      config.buttonLabel = "Deleting...";
      config.buttonText = "Delete";
      break;
    case "upload":
      config.title = "Upload";
      config.buttonLabel = "Uploading...";
      config.buttonText = "Upload";
      break;
    default:
      // This should never happen
    }
    
    // Put all resources in dialog
    $("#bh-dir-dialog").html("");
    var appendString='';
    appendString = appendString + '<table class="table"><tbody>';
    $.each(nl.sara.beehub.controller.actionResources, function(i, item){
       appendString = appendString + '<tr id="dialog_tr_'+nl.sara.beehub.controller.htmlEscape(item.path)+'"><td>'+nl.sara.beehub.controller.htmlEscape(item.displayname)+'</td><td width="60%" class="info"></td></tr>';
    });
    appendString = appendString +'</tbody></table>';
    $("#bh-dir-dialog").append(appendString);
    
    // Show dialog
    $("#bh-dir-dialog").dialog({
      modal: true,
      maxHeight: 400,
      title: config.title,
      closeOnEscape: false,
      dialogClass: "custom-dialog",
      resizable: false,
      width: 460,
      buttons: [{
        text: "Cancel",
        id: 'bh-dir-cancel-dialog-button',
        click: function() {
          $(this).dialog("close");
          nl.sara.beehub.controller.clearAllViews();
        }
      }, {
        text: config.buttonText,
        id: 'bh-dir-dialog-button',
        click: function() {
          $('#bh-dir-dialog-button').button({label:config.buttonLabel});
          $('#bh-dir-dialog-button').button("disable");
          actionFunction();
        }
      }]
    });
    $("#bh-dir-dialog-button").addClass("btn-danger");
  };
  
  /*
   * Show overwrite buttons in dialog
   * 
   * Public function
   * 
   * @param Object    resource            Resource to update
   * @param String    fileNew             filename of the original file name
   * @param Function  overwriteFunction   Overwrite handler 
   */
  nl.sara.beehub.view.dialog.showOverwriteDialog = function(resource, fileNew, overwriteFunction) {
    var overwriteButton='<button id="bh-dir-rename-overwrite-button" class="btn btn-danger">Overwrite</button>';
    var cancelButton='<button id="bh-dir-rename-cancel-button" class="btn btn-success">Cancel</button>';
    $("#bh-dir-dialog").html('<h5><b><i>'+nl.sara.beehub.controller.htmlEscape(fileNew)+'</b></i> already exist in the current directory!</h5><br><center>'+overwriteButton+' '+cancelButton)+'</center>';
    $("#bh-dir-dialog").dialog({
         modal: true,
         title: "Warning"
          });
    $("#bh-dir-rename-overwrite-button").click(overwriteFunction);
    $("#bh-dir-rename-cancel-button").click(function(){
      $("tr[id='"+resource.path+"']").find(".bh-dir-rename-td").find(':input').val(resource.displayname);
      $("#bh-dir-dialog").dialog("close");
    });
  };
  
  /*
   * Close the dialog
   * 
   * Public function
   * 
   */
  nl.sara.beehub.view.dialog.closeDialog = function() {
    $("#bh-dir-dialog").dialog("close");
  };  
  
  // ACL
  /**
   * Create html for acl form
   * 
   * @return {String} html
   * 
   */
  createHtmlAclForm = function() {
    return '\
        <table>\
        <tr>\
          <td class="bh-dir-acl-table-label"><label><b>Principal</b></label></td>\
          <td><label class="radio"><input type="radio" name="bh-dir-view-acl-optionRadio" id="bh-dir-acl-add-radio1" value="authenticated" unchecked>All BeeHub users</label></td>\
        </tr>\
        <tr>\
          <td class="bh-dir-acl-table-label"></td>\
          <td><label class="radio"><input type="radio" name="bh-dir-view-acl-optionRadio" id="bh-dir-acl-add-radio2" value="all" unchecked>Everybody</label></td>\
        </tr>\
        <tr>\
          <td class="bh-dir-acl-table-label"></td>\
          <td>\
            <div class="radio">\
              <input type="radio" name="bh-dir-view-acl-optionRadio" id="bh-dir-acl-add-radio3" value="user_or_group" checked>\
              <input id="bh-dir-acl-table-autocomplete" class="bh-dir-acl-table-search" type="text"  value="" placeholder="Search user or group...">\
            </div></td>\
        </tr>\
        <tr>\
          <td class="bh-dir-acl-table-label"><label><b>Permisions</b></label></td>\
          <td><select class="bh-dir-acl-table-permisions">\
            <option value="allow read">allow read (read)</option>\
            <option value="allow write">allow write (read, write)</option>\
            <option value="allow manage">allow manage (read, write, change acl)</option>\
            <option value="deny read">deny read (read, write, change acl)</option>\
            <option value="deny write">deny write (write, change acl)</option>\
            <option value="deny manage">deny manage (change acl)</option>\
          </select></td>\
        </tr>\
      </table>\
    ';
  };
  
  /**
   * Create html for acl view in dialpg
   *  
   */
  createHtmlAclView = function(aces){
//    console.log(aces);
    var html = '<div id="bh-dir-view-acldialog" class="tab-pane fade">\
      <table id="bh-dir-acldialog-table" class="table table-striped table-hover table-condensed">\
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
        <tbody class="bh-dir-acl-contents" name="<?= DAV::xmlescape( DAV::unslashify($member->path) ) ?>">';
//  <!--       Niek -->\
//        <?php\
//        $acl = $this->user_prop_acl();\
//        $acl_length = count( $acl );\
//        for ( $key = 0; $key < $acl_length; $key++ ) :\
//          $ace = $acl[ $key ];\
//        \
//        if  ( $ace->protected  || $ace->inherited ) {\
//          $class = "info";\
//        } else {\
//          $class = "";\
//        };\
//          ?>\
//          <tr class="bh-dir-acl-row <?= $class ?>">
//  <!--          Principal -->
//  <?php
//    // Determine how to show the principal
//    switch ( $ace->principal ) {
//      case 'DAV: owner':
//        $displayname = '<em>Owner</em>';
//        break;
//      case DAVACL::PRINCIPAL_ALL:
//        $displayname = '<em>Everybody</em>';
//        break;
//      case DAVACL::PRINCIPAL_AUTHENTICATED:
//        $displayname = '<em>All BeeHub users</em>';
//        break;
//      case DAVACL::PRINCIPAL_UNAUTHENTICATED:
//        $displayname = '<em>All unauthenticated users</em>';
//        break;
//      case DAVACL::PRINCIPAL_SELF:
//        $displayname = '<em>This resource itself</em>';
//        break;
//      default:
//        $principal = DAV::$REGISTRY->resource( $ace->principal );
//        if ( $principal instanceof DAVACL_Principal ) {
//          $displayname = DAV::xmlescape($principal->user_prop( DAV::PROP_DISPLAYNAME ));
//        }else{
//          $displayname = '<em>Unrecognized principal!</em>';
//        }
//      break;
//    }
//      $icon= '<i class="icon-user"></i><i class="icon-user"></i>';
//      if ((strpos($ace->principal, BeeHub::USERS_PATH) !== false) || ($ace->principal == 'DAV: owner' )) {
//        $icon= '<i class="icon-user"></i>';
//      }
//  ?>
//            <td class="bh-dir-acl-principal" name="<?= DAV::xmlescape($ace->principal) ?>" data-toggle="tooltip"
//            title="<?= DAV::xmlescape($ace->principal)?>" ><b><?= ( $ace->invert ? 'Everybody except ' : '' ) . $displayname ?> </b>(<?= $icon?>)</td>
//            
//  <?php 
//    // make permissions string
//    $tooltip="";
//    $class="";
//    $permissions=""; 
//    if ( $ace->deny) {
//      $permissions="deny ";
//      $class="bh-dir-acl-deny";
//      if ( ( count( $ace->privileges ) === 1 ) && in_array( DAVACL::PRIV_WRITE_ACL, $ace->privileges ) ) {
//        $permissions .= "manage";
//        $tooltip="deny change acl";
//      } elseif ( ( count( $ace->privileges ) === 2 ) && in_array( DAVACL::PRIV_WRITE, $ace->privileges ) && in_array( DAVACL::PRIV_WRITE_ACL, $ace->privileges) ) {
//        $permissions .= "write";
//        $tooltip="deny write, change acl";
//      } elseif ( ( count( $ace->privileges ) === 3 ) && in_array( DAVACL::PRIV_READ, $ace->privileges ) && in_array( DAVACL::PRIV_WRITE, $ace->privileges ) && in_array( DAVACL::PRIV_WRITE_ACL, $ace->privileges) ) {
//        $permissions .= "read";
//        $tooltip="deny read, write, change acl";
//      } elseif ( in_array( DAVACL::PRIV_ALL, $ace->privileges ) ) {
//        $permissions .= "read";
//        $tooltip="deny read, write, change acl";
//      } else {
//        $permissions .= "unknown privilege (combination)";
//        $tooltip="deny " . implode( '; ', $ace->privileges );
//      }
//    } else { 
//      $permissions="allow ";
//      $class="bh-dir-acl-allow";
//      if ( ( count( $ace->privileges ) === 1 ) && in_array( DAVACL::PRIV_READ, $ace->privileges ) ) {
//        $permissions .= "read";
//        $tooltip="allow read";
//      } elseif ( ( count( $ace->privileges ) === 2 ) && in_array( DAVACL::PRIV_WRITE, $ace->privileges ) && in_array( DAVACL::PRIV_READ, $ace->privileges) ) {
//        $permissions .= "write";
//        $tooltip="allow read, write";
//      } elseif ( ( count( $ace->privileges ) === 3 ) && in_array( DAVACL::PRIV_WRITE_ACL, $ace->privileges ) && in_array( DAVACL::PRIV_WRITE, $ace->privileges ) && in_array( DAVACL::PRIV_READ, $ace->privileges ) ) {
//        $permissions .= "manage";
//        $tooltip="allow read, write, change acl";
//      } elseif ( in_array( DAVACL::PRIV_ALL, $ace->privileges ) ) {
//        $permissions .= "manage";
//        $tooltip="allow read, write, change acl";
//      } else {
//        $permissions .= "unknown privilege (combination)";
//        $tooltip="allow " . implode( '; ', $ace->privileges );
//      }
//    };
//
//    $changePermissionsClass = "bh-dir-acl-change-permissions";
//    $style= 'style="cursor: pointer"';
//    if  ( $ace->protected  || $ace->inherited ) {
//      $changePermissionsClass = "";
//      $style= "";
//    }
//  ?>
//    <td class="bh-dir-acl-permissions-select" hidden>
//      <select class="bh-dir-acl-table-permissions">
//        <option value="allow read" <?= ( $permissions === 'allow read' ) ? 'selected="selected"' : '' ?> >allow read (read)</option>
//        <option value="allow write" <?= ( $permissions === 'allow write' ) ? 'selected="selected"' : '' ?> >allow write (read, write)</option>
//        <option value="allow manage" <?= ( $permissions === 'allow manage' ) ? 'selected="selected"' : '' ?> >allow manage (read, write, change acl)</option>
//        <option value="deny read" <?= ( $permissions === 'deny read' ) ? 'selected="selected"' : '' ?> >deny read (read, write, change acl)</option>
//        <option value="deny write" <?= ( $permissions === 'deny write' ) ? 'selected="selected"' : '' ?> >deny write (write, change acl)</option>
//        <option value="deny manage" <?= ( $permissions === 'deny manage' ) ? 'selected="selected"' : '' ?> >deny manage (change acl)</option>
//      </select>
//    </td>
//    <td class="bh-dir-acl-permissions <?= $changePermissionsClass ?> <?= $class?>" <?= $style ?> data-toggle="tooltip" title="<?= $tooltip?>">
//      <span class="presentation"><?= $permissions ?></span>
//      <?php if ( strpos( $permissions, 'unknown' ) !== false ) : ?>
//        <span class="original" hidden="hidden"><?= implode( ' ', $ace->privileges ) ?></span>
//      <?php endif; ?>
//    </td>
//  <!--          Info -->
//  <?php
//    $info = '';
//    $message = '';
//    $class='';
//    if ( $ace->protected ) {
//      $info = 'protected';
//      $message = 'protected, no changes are possible';
//      $class ='bh-dir-acl-protected';
//    } elseif ( ! is_null( $ace->inherited ) ) {
//      $info = 'inherited';
//      $message = 'inherited from: <a href="' . $ace->inherited . '">' . $ace->inherited . '</a>';
//      $class ='bh-dir-acl-inherited';
//    }
//  ?>
//            <td class="bh-dir-acl-comment <?= $class  ?>" name="<?= $info  ?>" ><?= $message ?></td>
//  <!--        When ace is not protected, inherited and previous ace exists and is not protected  -->
//          <?php if ( ! $ace->protected &&
//                    ( is_null( $ace->inherited ) ) &&
//                    ($key !== 0) &&
//                    ( ! $acl[$key-1]->protected ) )
//           :?>
//  <!--          Move up -->
//            <td class="bh-dir-acl-up"><i title="Move up" class="icon-chevron-up bh-dir-acl-icon-up" style="cursor: pointer"></i></td>
//          <?php else : ?>
//  <!--          No move up possible -->
//            <td class="bh-dir-acl-up"></td>
//          <?php endif; ?>
//          <!--        When ace is not protected, inherited and next ace exists and is not inherited  -->
//          <?php if ( ! $ace->protected &&
//                    ( is_null( $ace->inherited ) ) &&
//                    ($key !== $acl_length - 1) &&
//                     is_null( $acl[$key+1]->inherited ) )
//           :?>
//  <!--          Move down -->
//            <td class="bh-dir-acl-down"><i title="Move down" class="icon-chevron-down bh-dir-acl-icon-down" style="cursor: pointer"></i></td>
//          <?php else : ?>
//  <!--          No move down possible -->
//            <td class="bh-dir-acl-down"></td>
//          <?php endif; ?>
//          <?php if ( $ace->protected || ! is_null( $ace->inherited ) ) :?>
//  <!--        no delete possible -->
//          <td></td>
//          <?php else : ?>
//  <!--          Delete icon -->
//            <td><i title="Delete" class="icon-remove bh-dir-acl-icon-remove" style="cursor: pointer"></i></td>
//          <?php endif; ?>
//          </tr>  
//       <?php
//          endfor;
//       ?>   
//        </tbody>
//      </table>
//    </div>
//    <!-- End Acl tab -->
//  </div>
//  <!-- End tab div -->
    return html;
  };
  
  /**
   * Initialize autocomplete for searching users and groups
   */
  setupAutoComplete = function(){
    var searchList = [];
    
    $.each(nl.sara.beehub.principals.groups, function (groupname, displayname) {
      searchList.push({
         "label"        : displayname+' ('+groupname+') ',
         "name"         : nl.sara.beehub.groups_path+groupname,
         "displayname"  : displayname,
         "icon"         : '<i class="icon-user"></i><i class="icon-user"></i>'
      });
    });
    
    $.each(nl.sara.beehub.principals.users, function (username, displayname) {
      searchList.push({
         "label"        : displayname+' ('+username+') ',
         "name"         : nl.sara.beehub.users_path+username,
         "displayname"  : displayname,
         "icon"         : '<i class="icon-user"></i>'
      });
    });

    $( "#bh-dir-acl-table-autocomplete" ).autocomplete({
      source:searchList,
      select: function( event, ui ) {
        $("#bh-dir-acl-table-autocomplete").val(ui.item.label);
        $("#bh-dir-acl-table-autocomplete").attr('name' ,ui.item.name);
        $("#bh-dir-aclform-add-button").button('enable');
        return false;
      },
      change: function (event, ui) {
        if(!ui.item){
            //http://api.jqueryui.com/autocomplete/#event-change -
            // The item selected from the menu, if any. Otherwise the property is null
            //so clear the item for force selection
            $("#bh-dir-aclform-add-button").button('disable');
            $("#bh-dir-acl-table-autocomplete").val("");
        }

      }
    }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
      return $( "<li></li>" )
        .data( "ui-autocomplete-item", item )
        .append( "<a><strong>" +item.icon +"  "+ nl.sara.beehub.controller.htmlEscape(item.label) + "</strong></a>" )
        .appendTo( ul );
    };  
  };
  
  /**
   * Add radio buttons handlers in Add acl rule form
   * 
   */
  setAddRadioButtons = function(){
    $("#bh-dir-acl-add-radio1").click(function(){
      $("#bh-dir-acl-table-autocomplete").attr("disabled",true);
      $("#bh-dir-acl-table-autocomplete").val("");
      $("#bh-dir-aclform-add-button").button('enable');

    });
    $("#bh-dir-acl-add-radio2").click(function(){
      $("#bh-dir-acl-table-autocomplete").attr("disabled",true);
      $("#bh-dir-acl-table-autocomplete").val("");
      $("#bh-dir-aclform-add-button").button('enable');
    });
    $("#bh-dir-acl-add-radio3").click(function(){
      $("#bh-dir-acl-table-autocomplete").attr("disabled",false);
      $("#bh-dir-aclform-add-button").button('disable');
    });
  };
  
  /**
   * Get value from the Acl add rule form
   * 
   * @return {Object} Principal and permissions
   */
  getFormAce= function(){
    var principal = '';
    switch($('input[name = "bh-dir-view-acl-optionRadio"]:checked').val())
    {
    // all
    case "all":
      principal="DAV: all";
      break;
    // Everybody
    case "authenticated":
      principal="DAV: authenticated";
      break;
    // User or group
    case "user_or_group":
      principal=$("#bh-dir-acl-table-autocomplete").attr('name');
      break;
    default:
      // This should never happen
    }
    var ace = {
        "principal": principal,
        "permissions": $(".bh-dir-acl-table-permisions option:selected").val(),
    };
    
    return ace;
  }
  
  /*
   * Show add rule dialog
   * 
   * Public function
   * 
   * @param {String} error The error to show
   */
  nl.sara.beehub.view.dialog.showAddRuleDialog = function(addFunction) {
    // createForm
    $('#bh-dir-dialog').html(createHtmlAclForm());

    // auto complete for searching users and groups
    setupAutoComplete();
 
    // radiobutton handlers
    setAddRadioButtons();
        
    $('#bh-dir-dialog').dialog({
      title: " Add acl rule",
      modal: true,
      maxHeight: 400,
      closeOnEscape: false,
      dialogClass: "custom-dialog",
      resizable: false,
      width: 370,
      buttons: [{
        text: "Cancel",
        click: function() {
          $(this).dialog("close");
        }
      },{
        text: "Add rule",
        id: "bh-dir-aclform-add-button",
        click: function() {
          addFunction(getFormAce());
        }
      }]
    });
    $("#bh-dir-aclform-add-button").button('disable');
  };
})();