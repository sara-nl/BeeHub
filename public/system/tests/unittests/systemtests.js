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
 * You should have received a copyof the GNU Lesser General Public License
 * along with BeeHub webclient.  If not, see <http://www.gnu.org/licenses/>.
 */
"use strict";

(function(){
  module("systemtests",{
    setup: function(){
      setupEnvironmentOnServer();
    },
    teardown: function(){
    }
  });
  
  
  // directory with acl deny read, deny write, deny write-acl
  var denyAll =                        '/denyAll/';     
  // resource with acl allow read, deny write, deny write-acl
  var denyAll_allowRead =              '/denyAll/allowRead';
  // resource with acl deny read, allow write, allow write-acl
  var denyAll_denyRead =               '/denyAll/denyRead';
  // resource with acl allow read, deny write, allow write-acl
  var denyAll_denyWrite =              '/denyAll/denyWrite';
  // resource with acl allow read, allow write, deny write-acl
  var denyAll_denyWriteAcl =           '/denyAll/denyWriteAcl';

  // directory with acl allow read, deny write, deny write-acl
  var allowRead =                      '/allowRead/';
  // resource with acl allow read, allow write, deny write-acl
  var allowRead_denyWriteAcl =         '/allowRead/denyWriteAcl';
  // resource with acl deny read, allow write, deny write-acl
  var allowRead_allowWrite =           '/allowRead/allowWrite';

  // directory with acl deny read, allow write, deny write-acl
  var allowWrite =                     '/allowWrite/';

  // directory with acl allow read, allow write, deny write-acl
  var denyWriteAcl =                   '/denyWriteAcl/';
  // resource with acl allow read, allow write, deny write-acl
  var denyWriteAcl_denyWriteAcl =      '/denyWriteAcl/denyWriteAcl';
  // resource with acl deny read, allow write, deny write-acl
  var denyWriteAcl_allowWrite =        '/denyWriteAcl/allowWrite';
  // resource with acl allow read, deny write, allow write-acl
  var denyWriteAcl_denyWrite =         '/denyWriteAcl/denyWrite';

  function setupEnvironmentOnServer(){
    
  };
  
  /**
   * Test GET request
   */
  test( 'GET request', function() { 
    expect(2);
    ok(false, "Not implemented");
    // GET request to /denyAll/allowRead should be successful
    // GET request to /denyAll/denyRead should fail
  });

  /**
   * Test HEAD request
   */
  test( 'HEAD request', function() { 
    expect(2);
    ok(false, "Not implemented");
    // HEAD request to /denyAll/allowRead should be successful
    // HEAD request to /denyAll/denyRead should fail
  });

  /**
   * Test POST request
   */
  test( 'POST request', function() { 
    expect(3);
    ok(false, "Not implemented");
    // POST request to /denyAll/allowRead should be successful
    // POST request to /denyAll/denyRead should fail
    // POST request to /denyAll/denyWrite should fail
  });
  
  /**
   * Test PUT request
   */
  test( 'PUT request', function() { 
    expect(6);
    ok(false, "Not implemented");
    // TODO deze wordt nog niet gebruikt
    // * Create directory /denyWriteAcl *
    // PUT request to /denyAll/allowRead should be successful
    // PUT request to /denyAll/denyRead should fail
    // PUT request to /denyAll/denyWrite should fail
    // PUT request to /denyAll/newResource should fail (does not exist)
    // PUT request to /allowRead/newResource should fail (does not exist)
    // PUT request to /allowWrite/newResource should be successful (does not exist)
  });

  /**
   * Test OPTIONS request
   */
  test( 'OPTIONS request', function() { 
    expect(2);
    ok(false, "Not implemented");
    // OPTIONS request to /denyAll/allowRead should be successful
    // OPTIONS request to /denyAll/denyRead should fail
  });
  
  /**
   * Test PROPFIND request
   */
  test( 'PROPFIND request', function() { 
    expect(2);
    ok(false, "Not implemented");
    // PROPFIND request to /denyAll/allowRead should be successful
    // PROPFIND request to /denyAll/denyRead should fail
  });
 
  /**
   * Test PROPPATCH request
   */
  test( 'PROPPATCH request', function() { 
    expect(3);
    ok(false, "Not implemented");
    // PROPPATCH request to /denyAll/denyWriteAcl should be successful
    // PROPPATCH request to /denyAll/denyRead should fail
    // PROPPATCH request to /denyAll/denyWrite should fail
  });

  /**
   * Test MKCOL request
   */
  test( 'MKCOL request', function() { 
    expect(5);
    ok(false, "Not implemented");
    // MKCOL request to /denyWriteAcl/denyWriteAcl should be successful
    // MKCOL request to /denyWriteAcl/allowWrite should fail
    // MKCOL request to /denyWriteAcl/allowRead should fail
    // MKCOL request to /allowRead/denyWriteAcl should fail 
    // MKCOL request to /allowWrite/denyWriteAcl should fail
  });
  
  /**
   * Test DELETE request
   */
  test( 'DELETE request', function() { 
    expect(3);
    ok(false, "Not implemented");
    // DELETE request to /denyAll/denyWriteAcl should be successful
    // DELETE request to /denyAll/denyRead should fail
    // DELETE request to /denyAll/denyWrite should fail
  });

  // *** ACTION DELETE request ***


  // *** ACTION COPY request ***
  // Nog testen, klopt niet helemaal
  // COPY request to /denyAll/allowRead to /denyWriteAcl/ should be successful
  // COPY request to /denyAll/denyRead to /denyWriteAcl/ should fail
  // COPY request to /denyAll/denyRead to /allowWrite/ should fail
  // COPY request to /denyAll/denyRead to /allowRead/ should fail
  // COPY request to /denyAll/allowRead to /allowRead/ should be successful(overwrite)
  // COPY request to /denyAll/denyRead to /allowRead/ should fail (overwrite)

  // *** ACTION MOVE request ***
  // MOVE request to /denyAll/denyWriteAcl to /allowWrite/ should be successful
  // MOVE request to /denyAll/denyRead to /allowWrite/ should fail
  // MOVE request to /denyAll/denyWrite to /allowWrite/ should fail
  // MOVE request to /allowWrite/denyWriteAcl to /denyWriteAcl/ should be successful(overwrite)
  // MOVE request to /denyWriteAcl/allowWrite to /denyWriteAcl/denyWrite should fail(overwrite)

  // *** ACTION LOCK request ***
  // LOCK request to /denyAll/allowRead should be successful
  // LOCK request to /denyAll/denyRead should fail

  // *** ACTION UNLOCK request ***
  // UNLOCK request to /denyAll/allowRead should be successful
  // UNLOCK request to /denyAll/denyRead should fail

  // *** ACTION ACL request ***
  // Change ACL request to /denyAll/denyWrite should be successful
  // Change ACL request to /denyAll/denyRead should fail
  // Change ACL request to /denyAll/denyWriteAcl should fail

  // *** ACTION REPORT request ***
  // REPORT request to /denyAll/allowRead should be successful
  // REPORT request to /denyAll/denyRead should fail

  // TODO Become owner, Change sponsor, wisbaarheid ofzo

})();
//End of file

