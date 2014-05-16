/**
 * Copyright ©2014 SURFsara bv, The Netherlands
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

"use strict";

/**
 * @class Groups and sponsors view
 * 
 * @author Laura Leistikow (laura.leistikow@surfsara.nl)
 * 
 */
nl.sara.beehub.gs.view.View = function(view, controller) { 
  this.controller = controller;
  
  if (view === "groupssponsors") {
    this.view = new nl.sara.beehub.gs.view.GroupsSponsorsView(controller, this);
    return;
  }
  if (view === "groupsponsor") {
    this.view = new nl.sara.beehub.gs.view.GroupSponsorView(controller, this);
    return;
  }
};

/**
 * Mask view
 * 
 */
nl.sara.beehub.gs.view.View.prototype.mask = function(mask){
  if (mask) {
    $("#bh-dir-mask-transparant").show();
  } else {
    $("#bh-dir-mask-transparant").hide();
  }
}


