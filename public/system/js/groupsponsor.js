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

if (nl.sara.beehub.gs === undefined) {
  /** @namespace Group and sponsor */
  nl.sara.beehub.gs = {};
}

if (nl.sara.beehub.gs.view === undefined) {
  /** @namespace Group and sponsor */
  nl.sara.beehub.gs.view = {};
}

// After load
$(function () {
  new nl.sara.beehub.gs.Controller("groupsponsor");
});
