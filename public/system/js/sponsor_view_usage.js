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
// Change tab listeners
  // Content tab
  $('a[href="#bh-gs-panel-usage"]').unbind('click').click(function(e){
    $("#bh-gs-panel-usage").html("");
        var width = 960,
        height = 500,
        radius = Math.min(width, height) / 2;
     
     var color = d3.scale.ordinal()
        .range(["#98abc5", "#8a89a6", "#7b6888", "#6b486b", "#a05d56", "#d0743c", "#ff8c00"]);
     
     var arc = d3.svg.arc()
        .outerRadius(radius - 10)
        .innerRadius(0);
     
     var pie = d3.layout.pie()
        .sort(null)
        .value(function(d) { return d.used; });
     
     var svg = d3.select("#bh-gs-panel-usage").append("svg")
        .attr("width", width)
        .attr("height", height)
      .append("g")
        .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");
     
     d3.csv("/system/data.csv", function(error, data) {
     
      data.forEach(function(d) {
        d.used = +d.used;
      });
     
      var g = svg.selectAll(".arc")
          .data(pie(data))
        .enter().append("g")
          .attr("class", "arc");
     
      g.append("path")
          .attr("d", arc)
          .style("fill", function(d) { return color(d.data.user); });
     
      g.append("text")
          .attr("transform", function(d) { return "translate(" + arc.centroid(d) + ")"; })
          .attr("dy", ".35em")
          .style("text-anchor", "middle")
          .text(function(d) { return d.data.user; });
     
     });
  });
  
})();
