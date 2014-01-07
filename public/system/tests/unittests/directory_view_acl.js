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
  var getTable = function() {
    return '';
  };
  module("view acl")
  /**
   * Test home and up buttons click handlers
   */
  test( 'nl.sara.beehub.view.acl.init: Home and up buttons click handlers', function() {
    expect( 2 );
  
    // Home and up button
    $("#qunit-fixture").append('<button id="/home/testuser/" class="bh-dir-content-gohome"></button>');
    $("#qunit-fixture").append('<button id="/home/testuser/" class="bh-dir-content-up"></button>');
    
    // Rewrite controller goToPage
    var rememberGoToPage = nl.sara.beehub.controller.goToPage;
    nl.sara.beehub.controller.goToPage = function(location){
      deepEqual(location, "/home/testuser/", "Location should be /home/testuser" );
    };
    // Call init function
    nl.sara.beehub.view.content.init();
    
    // Call click handlers
    // Buttons
    $('.bh-dir-content-gohome').click();
    $('.bh-dir-content-up').click();
    
    // Original environment
    nl.sara.beehub.controller.goToPage = rememberGoToPage;
    nl.sara.beehub.view.content.init();
  });
})();
// End of file