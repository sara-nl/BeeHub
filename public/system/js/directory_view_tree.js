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

nl.sara.beehub.view.tree.init = function() {
  // Directory tree in tree panel
  $("#bh-dir-tree").dynatree({
    onActivate: function(node) {
      // A DynaTreeNode object is passed to the activation handler
      // Note: we also get this event, if persistence is on, and the page is reloaded.
//      alert("You activated " + node.data.title);
//      window.location.reload();
    },
    persist: false,
    children: treecontents,
    onLazyRead: function(node){
      var client = new nl.sara.webdav.Client();
      var resourcetypeProp = new nl.sara.webdav.Property();
      resourcetypeProp.tagname = 'resourcetype';
      resourcetypeProp.namespace='DAV:';
      var properties = [resourcetypeProp];
      client.propfind(node.data.id, function(status, data) {
        // Callback
        if (status != 207) {
          // Server returned an error condition: set node status accordingly
          node.setLazyNodeStatus(DTNodeStatus_Error, {
            tooltip: data.faultDetails,
            info: data.faultString
          });
        };
        var res = [];
        $.each(data.getResponseNames(), function(pathindex){
          var path = data.getResponseNames()[pathindex];
          
          if (node.data.id !== path) {
            if (data.getResponse(path).getProperty('DAV:','resourcetype') !== undefined) {
              var resourcetypeProp = data.getResponse(path).getProperty('DAV:','resourcetype');
              if ((resourcetypeProp.xmlvalue.length == 1)
                  &&(nl.sara.webdav.Ie.getLocalName(resourcetypeProp.xmlvalue.item(0))=='collection')
                  &&(resourcetypeProp.xmlvalue.item(0).namespaceURI=='DAV:')) 
              {
                var name = path;
                while (name.substring(name.length-1) == '/') {
                  name = name.substr(0, name.length-1);
                }
                name = decodeURIComponent(name.substr(name.lastIndexOf('/')+1));
                res.push({
                  'title': name,
                  'id' : path,
                  'isFolder': 'true',
                  'isLazy' : 'true'
                });
              }
            }
          };
        });
        // PWS status OK
        node.setLazyNodeStatus(DTNodeStatus_Ok);
        node.addChild(res);
        // end callback
      },1,properties);
    }
  });
  
  // Tree slide handler
  $(".bh-dir-tree-slide-trigger").hover(nl.sara.beehub.view.content.handle_tree_slide_click, function(){
   // No action;
  });
  $(".bh-dir-tree-slide-trigger").click(nl.sara.beehub.view.content.handle_tree_slide_click);
};

//  $("#bh-dir-tree").dynatree({
//    onActivate: function(node) {
//        // A DynaTreeNode object is passed to the activation handler
//        // Note: we also get this event, if persistence is on, and the page is reloaded.
//        alert("You activated " + node.data.title);
//    }
//  });

/*
 * On click handler tree slide click
 * Open or close tree view
 */
nl.sara.beehub.view.content.handle_tree_slide_click = function() {
  $(".bh-dir-tree-slide").toggle("slow");
  $(this).toggleClass("active");
  $('.bh-dir-tree-slide-trigger i').toggleClass('icon-chevron-left icon-chevron-right');
  return false;
}

/*
 * Set tree in copy mode
 *  
 * @param {Array} Resources Array with resources
 * 
 */
nl.sara.beehub.view.tree.setTreeCopyMode = function(resources){
  // show dialog with items to copy and target directory
  $("#bh-dir-tree").dynatree({
    onActivate: function(node) {
      // A DynaTreeNode object is passed to the activation handler
      // Note: we also get this event, if persistence is on, and the page is reloaded.
      var destination = {};
      destination.path = node.data.id;
      nl.sara.beehub.view.dialog.showResourcesDialog(resources, "copy", destination);
    }
  });
};
