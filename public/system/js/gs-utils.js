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
 * Mask view
 * 
 */
nl.sara.beehub.gs.view.utils.mask = function(mask) {
  if (mask) {
    $("#bh-dir-mask-transparant").show();
  } else {
    $("#bh-dir-mask-transparant").hide();
  };
};

nl.sara.beehub.gs.view.utils.STATUS_LAST_ADMIN_ALERT    = "You can't leave this group, you're the last administrator! Don't leave your herd without a shepherd, please appoint a new administrator before leaving them!" ;
nl.sara.beehub.gs.view.utils.STATUS_NOT_ALLOWED_ALERT   = 'You are not allowed to perform this action!';
nl.sara.beehub.gs.view.utils.STATUS_UNKNOWN_ERROR_ALERT = 'Something went wrong on the server. No changes were made.';