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
    },
    teardown: function(){
    }
  });
  
  /**
   * Test GET request
   */
  test( 'GET request', function() { 
    expect(4);
    ok(false, "Not implemented");
  //*** ACTION GET request ***
  //GET request to /denyAll/allowRead should be successful
  //GET request to /denyAll/allowReadDir/resource should be successful
  //GET request to /allowAll/denyRead should fail
  //GET request to /allowAll/denyReadDir/resource should fail
  });

  /**
   * Test HEAD request
   */
  test( 'HEAD request', function() { 
    expect(4);
    ok(false, "Not implemented");
  //*** ACTION HEAD request ***
  //HEAD request to /denyAll/allowRead should be successful
  //HEAD request to /denyAll/allowReadDir/resource should be successful
  //HEAD request to /allowAll/denyRead should fail
  //HEAD request to /allowAll/denyReadDir/resource should fail
  });

  /**
   * Test POST request
   */
  test( 'POST request', function() { 
    expect(6);
    ok(false, "Not implemented");
  //*** ACTION POST request ***
  //TODO deze wordt nog niet gebruikt
  //POST request to /denyAll/allowReadWrite should be successful
  //POST request to /denyAll/allowReadWriteDir/resource should be successful
  //POST request to /allowAll/denyRead should fail
  //POST request to /allowAll/denyReadDir/resource should fail
  //POST request to /allowAll/denyWrite should fail
  //POST request to /allowAll/denyWriteDir/resource should fail
  });
  
  /**
   * Test PUT request
   */
  test( 'PUT request', function() { 
    expect(9);
    ok(false, "Not implemented");
  //*** ACTION PUT request ***
  //PUT request to /denyAll/allowReadWrite should be successful
  //PUT request to /denyAll/allowReadWriteDir/resource should be successful
  //PUT request to /allowAll/denyRead should fail
  //PUT request to /allowAll/denyReadDir/resource should fail
  //PUT request to /allowAll/denyWrite should fail
  //PUT request to /allowAll/denyWriteDir/resource should fail
  //PUT request to /denyAll/allowReadWriteDir/newResource should successful (does not exist)
  //PUT request to /allowAll/denyReadDir/newResource should fail (does not exist)
  //PUT request to /allowAll/denyWriteDir/newResource should be fail (does not exist)
  });

  /**
   * Test OPTIONS request
   */
  test( 'OPTIONS request', function() { 
    expect(4);
    ok(false, "Not implemented");
  //*** ACTION OPTIONS request ***
  //OPTIONS request to /denyAll/allowRead should be successful
  //OPTIONS request to /denyAll/allowReadDir/resource should be successful
  //OPTIONS request to /allowAll/denyRead should fail
  //OPTIONS request to /allowAll/denyReadDir/resource should fail
  });
  
  /**
   * Test PROPFIND request
   */
  test( 'PROPFIND request', function() { 
    expect(4);
    ok(false, "Not implemented");
  //*** ACTION PROPFIND request ***
  //PROPFIND request to /denyAll/allowRead should be successful
  //PROPFIND request to /denyAll/allowReadDir/resource should be successful
  //PROPFIND request to /allowAll/denyRead should fail
  //PROPFIND request to /allowAll/denyReadDir/resource should fail
  });
 
  /**
   * Test PROPPATCH request
   */
  test( 'PROPPATCH request', function() { 
    expect(6);
    ok(false, "Not implemented");
  //*** ACTION PROPPATCH request ***
  //PROPPATCH request to /denyAll/allowReadWrite should be successful
  //PROPPATCH request to /denyAll/allowReadWriteDir/resource should be successful
  //PROPPATCH request to /allowAll/denyRead should fail
  //PROPPATCH request to /allowAll/denyReadDir/resource should fail
  //PROPPATCH request to /allowAll/denyWrite should fail
  //PROPPATCH request to /allowAll/denyWriteDir/resource should fail
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
  //*** ACTION DELETE request ***
  //DELETE request to /denyAll/allowReadWrite should be successful
  //DELETE request to /denyAll/allowReadWriteDir/resource should be successful
  //DELETE request to /allowAll/denyRead should fail
  //DELETE request to /allowAll/denyReadDir/resource should fail
  //DELETE request to /allowAll/denyWrite should fail
  //DELETE request to /allowAll/denyWriteDir/resource should fail
  });

  /**
   * Test COPY request
   */
  test( 'COPY request', function() { 
    expect(11);
    ok(false, "Not implemented");
   //*** ACTION COPY request ***
   //COPY request to /denyAll/allowRead to /denyAll/allowReadWriteDir/newResource should be successful
   //COPY request to /denyAll/allowReadDir/resource to /denyAll/allowReadWriteDir/newResource should be successful
   //COPY request to /denyAll/allowRead to /denyAll/allowReadWrite should be successful (overwrite)
   //COPY request to /allowAll/denyRead to /denyAll/allowReadWriteDir/newResource should fail
   //COPY request to /allowAll/denyReadDir/resource to /denyAll/allowReadWriteDir/newResource should fail
   //COPY request to /denyAll/allowRead to /denyAll/allowReadDir/newResource should fail
   //COPY request to /denyAll/allowRead to /denyAll/allowWriteDir/newResource should fail
   //COPY request to /denyAll/allowRead to /denyAll/allowRead should fail (overwrite)
   //COPY request to /denyAll/allowRead to /denyAll/allowReadWriteDir/denyRead should fail (overwrite)
   //COPY request to /denyAll/allowRead to /denyAll/allowWrite should fail (overwrite)
   //COPY request to /denyAll/allowRead to /denyAll/allowReadWriteDir/denyWrite should fail (overwrite)
  });

   /**
    * Test MOVE request
    */
   test( 'MOVE request', function() { 
     expect(13);
     ok(false, "Not implemented");
     //*** ACTION MOVE request ***
     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWriteDir/newResource should be successful
     //MOVE request to /denyAll/allowReadWriteDir/resource to /denyAll/allowReadWriteDir/newResource should be successful
     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWrite should be successful (overwrite)
     //MOVE request to /allowAll/denyRead to /denyAll/allowReadWriteDir/newResource should fail
     //MOVE request to /allowAll/denyReadDir/resource to /denyAll/allowReadWriteDir/newResource should fail
     //MOVE request to /allowAll/denyWrite to /denyAll/allowReadWriteDir/newResource should fail
     //MOVE request to /allowAll/denyWriteDir/resource to /denyAll/allowReadWriteDir/newResource should fail
     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadDir/newResource should fail
     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowWriteDir/newResource should fail
     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowRead should fail (overwrite)
     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWriteDir/denyRead should fail (overwrite)
     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowWrite should fail (overwrite)
     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWriteDir/denyWrite should fail (overwrite)
  });

  /**
   * Test LOCK request
   */
  test( 'LOCK request', function() { 
    expect(6);
    ok(false, "Not implemented");
    //*** ACTION LOCK request ***
    //LOCK request to /denyAll/allowReadWrite should be successful
    //LOCK request to /denyAll/allowReadWrite/resource should be successful
    //LOCK request to /allowAll/denyRead should fail
    //LOCK request to /allowAll/denyReadDir/resource should fail
    //LOCK request to /allowAll/denyWrite should fail
    //LOCK request to /allowAll/denyWriteDir/resource should fail
  });

  /**
   * Test UNLOCK request
   */
  test( 'UNLOCK request', function() { 
    expect(3);
    ok(false, "Not implemented");
    //*** ACTION UNLOCK request ***
    //No need to test UNLOCK: UNLOCK is always allowed, as long as you have LOCKed the resource. This is already tested by Litmus I think.
  });

  /**
   * Test ACL request
   */
  test( 'ACL request', function() { 
    expect(6);
    ok(false, "Not implemented");
    //*** ACTION ACL request ***
    //ACL request to /denyAll/allowReadWriteAcl should be successful
    //ACL request to /denyAll/allowReadWriteAclDir/resource should be successful
    //ACL request to /allowAll/denyRead should fail
    //ACL request to /allowAll/denyReadDir/resource should fail
    //ACL request to /allowAll/denyWriteAcl should fail
    //ACL request to /allowAll/denyWriteAclDir/resource should fail
  });

  /**
   * Test REPORT request
   */
  test( 'REPORT request', function() { 
    expect(4);
    ok(false, "Not implemented");
    //*** ACTION REPORT request ***
    //TODO deze wordt misschien nog niet gebruikt?
    //REPORT request to /denyAll/allowRead should be successful
    //REPORT request to /denyAll/allowReadDir/resource should be successful
    //REPORT request to /allowAll/denyRead should fail
    //REPORT request to /allowAll/denyReadDir/resource should fail
  });

  /**
   * Test Become owner request
   */
  test( 'Become owner request', function() { 
    expect(5);
    ok(false, "Not implemented");
    //*** Become owner ***
    //Become owner of /denyAll/allowReadWriteDir/resource should be successful
    //Become owner of /denyAll/allowReadDir/allowWrite should fail
    //Become owner of /denyAll/allowWriteDir/allowRead should fail
    //Become owner of /denyAll/allowReadWriteDir/denyRead should fail
    //Become owner of /denyAll/allowReadWriteDir/denyWrite should fail
 });

 /**
  * Test Change sponsor request
  */
 test( 'Change sponsor request', function() { 
   expect(3);
   ok(false, "Not implemented");
   //*** Change sponsor ***
   //Change sponsor of /foo/file.txt (by John --> owner) to sponsor_b should be successful
   //Change sponsor of /foo/file.txt (by John) to sponsor_c (does not sponsor John) should fail
   //Change sponsor of /foo/file.txt (by Jane --> not owner) to sponsor_c should fail
 });

})();
//End of file


