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
  // Usage tab
  $('a[href="#bh-dir-panel-usage"]').unbind('click').click(function(e){
    nl.sara.beehub.view.tree.closeTree();
    nl.sara.beehub.view.showFixedButtons('usage');
    nl.sara.beehub.view.user_usage.createView();
  });
  
  nl.sara.beehub.view.user_usage.createView = function(){
   $("#bh-dir-acl-directory-usage").html("");
   var width = 960,
   height = 700,
   radius = Math.min(width, height) / 2,
   color = d3.scale.category20c();
 
 var svg = d3.select("#bh-dir-acl-directory-usage").append("svg")
   .attr("width", width)
   .attr("height", height)
   .append("g")
   .attr("transform", "translate(" + width / 2 + "," + height * .52 + ")");
 
 var partition = d3.layout.partition()
   .sort(null)
   .size([2 * Math.PI, radius * radius])
   .value(function(d) { return 1; });
 
 var arc = d3.svg.arc()
   .startAngle(function(d) { return d.x; })
   .endAngle(function(d) { return d.x + d.dx; })
   .innerRadius(function(d) { return Math.sqrt(d.y); })
   .outerRadius(function(d) { return Math.sqrt(d.y + d.dy); });
 
 d3.json("/system/flare.json", function(error, root) {
   var path = svg.datum(root).selectAll("path")
     .data(partition.nodes)
     .enter().append("path")
     .attr("display", function(d) { return d.depth ? null : "none"; }) // hide inner ring
     .attr("d", arc)
     .style("stroke", "#fff")
     .style("fill", function(d) { return color((d.children ? d : d.parent).name); })
     .style("fill-rule", "evenodd")
     .each(stash);
   
   d3.selectAll("input").on("change", function change() {
     var value = this.value === "count"
         ? function() { return 1; }
         : function(d) { return d.size; };
   
     path
         .data(partition.value(value).nodes)
       .transition()
         .duration(1500)
         .attrTween("d", arcTween);
   });
 });
 
 //Stash the old values for transition.
 function stash(d) {
 d.x0 = d.x;
 d.dx0 = d.dx;
 }
 
 //Interpolate the arcs in data space.
 function arcTween(a) {
 var i = d3.interpolate({x: a.x0, dx: a.dx0}, a);
 return function(t) {
   var b = i(t);
   a.x0 = b.x;
   a.dx0 = b.dx;
   return arc(b);
 };
 }
 
 d3.select(self.frameElement).style("height", height + "px");
  }
})();
