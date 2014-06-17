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
      this.webdav = new nl.sara.webdav.Client();
      this.statusOK_1 = 200;
      this.statusOK_2 = 201;
      this.statusOK_3 = 204;
      this.statusNotAllowed = 403;
      this.statusNotFound = 404;
    },
    teardown: function(){
    }
  });
  
  function createRequestCallback(assert, result){
    return function(status){
      expect(1);
      assert.deepEqual(status, result, "Request success should be "+result);
      start();
    }
  };
  
  /**
   * Test GET requests
   */
  asyncTest( 'GET request /denyAll/allowRead', function(assert) { 
    //GET request to /denyAll/allowRead should be successful
    this.webdav.get('/denyAll/allowRead' , createRequestCallback(assert, this.statusOK_1));
  });
  asyncTest( 'GET request /denyAll/allowReadDir/resource', function(assert) { 
    //GET request to /denyAll/allowReadDir/resource should be successful
    this.webdav.get('/denyAll/allowReadDir/resource' , createRequestCallback(assert, this.statusOK_1));
  });
  asyncTest( 'GET request /allowAll/denyRead', function(assert) { 
    //GET request to /allowAll/denyRead should fail
    this.webdav.get('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotFound));
  }); 
  asyncTest( 'GET request /allowAll/denyReadDir/resource', function(assert) { 
    //GET request to /allowAll/denyReadDir/resource should fail
    this.webdav.get('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotFound));
  });

  /**
   * Test HEAD requests
   */
  asyncTest( 'HEAD request /denyAll/allowRead', function(assert) { 
    //HEAD request to /denyAll/allowRead should be successful
    this.webdav.head('/denyAll/allowRead' , createRequestCallback(assert, this.statusOK_1));
  });
  asyncTest( 'HEAD request /denyAll/allowReadDir/resource', function(assert) {
    //HEAD request to /denyAll/allowReadDir/resource should be successful
    this.webdav.head('/denyAll/allowReadDir/resource' , createRequestCallback(assert, this.statusOK_1));
  });
  asyncTest( 'HEAD request /allowAll/denyRead', function(assert) {
    //HEAD request to /allowAll/denyRead should fail
    this.webdav.head('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotFound));
  });
  asyncTest( 'HEAD request /allowAll/denyReadDir/resource', function(assert) {
    //HEAD request to /allowAll/denyReadDir/resource should fail
    this.webdav.head('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotFound));
  });

//  /**
//   * Test POST request
//   */
//  //TODO deze wordt nog niet gebruikt
//  asyncTest( 'POST request /denyAll/allowReadWrite', function(assert) { 
//    expect(1);
//    //POST request to /denyAll/allowReadWrite should be successful
//    this.webdav.post('/denyAll/allowReadWrite', createRequestCallback(assert, true));
//  });
//  
//  asyncTest( 'POST request /denyAll/allowReadWriteDir/resource', function(assert) { 
//    expect(1);
//    //POST request to /denyAll/allowReadWriteDir/resource should be successful
//    this.webdav.post('/denyAll/allowReadWriteDir/resource', createRequestCallback(assert, true));
//  });
//  
//  asyncTest( 'POST request /allowAll/denyRead', function(assert) { 
//    expect(1);
//    //POST request to /allowAll/denyRead should fail
//    this.webdav.post('/allowAll/denyRead', createRequestCallback(assert, false));
//  });
//  
//  asyncTest( 'POST request /allowAll/denyReadDir/', function(assert) { 
//    expect(1);
//    //POST request to /allowAll/denyReadDir/resource should fail
//    this.webdav.post('/allowAll/denyReadDir/', createRequestCallback(assert, false));
//  });
//  
//  asyncTest( 'POST request /allowAll/denyWrite', function(assert) { 
//    expect(1);
//    //POST request to /allowAll/denyWrite should fail
//    this.webdav.post('/allowAll/denyWrite', createRequestCallback(assert, false));
//  });
//  
//  asyncTest( 'POST request /allowAll/denyWriteDir/resource', function(assert) { 
//    expect(1);
//    //POST request to /allowAll/denyWriteDir/resource should fail
//    this.webdav.post('/allowAll/denyWriteDir/resource', createRequestCallback(assert, false));
//  });
  
  
  /**
   * Test PUT requests
   */
  asyncTest( 'PUT request /denyAll/allowReadWrite', function(assert) { 
  //PUT request to /denyAll/allowReadWrite should be successful
    this.webdav.put('/denyAll/allowReadWrite', createRequestCallback(assert, this.statusOK_3),"Test");
  });
  asyncTest( 'PUT request /denyAll/allowReadWriteDir/resource', function(assert) { 
    //PUT request to /denyAll/allowReadWriteDir/resource should be successful
      this.webdav.put('/denyAll/allowReadWriteDir/resource', createRequestCallback(assert, this.statusOK_3),"Test");
  });
  asyncTest( 'PUT request /allowAll/denyRead', function(assert) { 
    //PUT request to /allowAll/denyRead should fail
      this.webdav.put('/allowAll/denyRead', createRequestCallback(assert, this.statusNotAllowed),"Test");
  });
  asyncTest( 'PUT request /allowAll/denyReadDir/resource', function(assert) { 
    //PUT request to /allowAll/denyReadDir/resource should fail
      this.webdav.put('/allowAll/denyReadDir/resource', createRequestCallback(assert, this.statusNotAllowed),"Test");
  });
  asyncTest( 'PUT request /allowAll/denyWrite', function(assert) { 
    //PUT request to /allowAll/denyWrite should fail
      this.webdav.put('/allowAll/denyWrite', createRequestCallback(assert, this.statusNotAllowed),"Test");
  });
  asyncTest( 'PUT request /allowAll/denyWriteDir/resource', function(assert) { 
    //PUT request to /allowAll/denyWriteDir/resource should fail
      this.webdav.put('/allowAll/denyWriteDir/resource', createRequestCallback(assert, this.statusNotAllowed),"Test");
  });
  asyncTest( 'PUT request /denyAll/allowReadWriteDir/newResource', function(assert) { 
    //PUT request to /denyAll/allowReadWriteDir/newResource should successful (does not exist)
      this.webdav.put('/denyAll/allowReadWriteDir/newResource', createRequestCallback(assert, this.statusOK_2),"Test");
  });
  asyncTest( 'PUT request /allowAll/denyReadDir/newResource', function(assert) { 
    //PUT request to /allowAll/denyReadDir/newResource should fail (does not exist)
      this.webdav.put('/allowAll/denyReadDir/newResource', createRequestCallback(assert, this.statusNotAllowed),"Test");
  });
  asyncTest( 'PUT request /allowAll/denyWriteDir/newResource', function(assert) { 
    //PUT request to /allowAll/denyWriteDir/newResource should be fail (does not exist)
      this.webdav.put('/allowAll/denyWriteDir/newResource', createRequestCallback(assert, this.statusNotAllowed),"Test");
  });

//  /**
//   * Test OPTIONS request
//   */
//  asyncTest( 'OPTIONS request', function() { 
//    expect(4);
//    ok(false, "Not implemented");
//  //*** ACTION OPTIONS request ***
//  //OPTIONS request to /denyAll/allowRead should be successful
//  //OPTIONS request to /denyAll/allowReadDir/resource should be successful
//  //OPTIONS request to /allowAll/denyRead should fail
//  //OPTIONS request to /allowAll/denyReadDir/resource should fail
//  });
//  
//  /**
//   * Test PROPFIND request
//   */
//  asyncTest( 'PROPFIND request', function() { 
//    expect(4);
//    ok(false, "Not implemented");
//  //*** ACTION PROPFIND request ***
//  //PROPFIND request to /denyAll/allowRead should be successful
//  //PROPFIND request to /denyAll/allowReadDir/resource should be successful
//  //PROPFIND request to /allowAll/denyRead should fail
//  //PROPFIND request to /allowAll/denyReadDir/resource should fail
//  });
// 
//  /**
//   * Test PROPPATCH request
//   */
//  asyncTest( 'PROPPATCH request', function() { 
//    expect(6);
//    ok(false, "Not implemented");
//  //*** ACTION PROPPATCH request ***
//  //PROPPATCH request to /denyAll/allowReadWrite should be successful
//  //PROPPATCH request to /denyAll/allowReadWriteDir/resource should be successful
//  //PROPPATCH request to /allowAll/denyRead should fail
//  //PROPPATCH request to /allowAll/denyReadDir/resource should fail
//  //PROPPATCH request to /allowAll/denyWrite should fail
//  //PROPPATCH request to /allowAll/denyWriteDir/resource should fail
//  });
//
//  /**
//   * Test MKCOL request
//   */
//  asyncTest( 'MKCOL request', function() { 
//    expect(5);
//    ok(false, "Not implemented");
//    // MKCOL request to /denyWriteAcl/denyWriteAcl should be successful
//    // MKCOL request to /denyWriteAcl/allowWrite should fail
//    // MKCOL request to /denyWriteAcl/allowRead should fail
//    // MKCOL request to /allowRead/denyWriteAcl should fail 
//    // MKCOL request to /allowWrite/denyWriteAcl should fail
//  });
//  
//  /**
//   * Test DELETE request
//   */
//  asyncTest( 'DELETE request', function() { 
//    expect(3);
//    ok(false, "Not implemented");
//  //*** ACTION DELETE request ***
//  //DELETE request to /denyAll/allowReadWrite should be successful
//  //DELETE request to /denyAll/allowReadWriteDir/resource should be successful
//  //DELETE request to /allowAll/denyRead should fail
//  //DELETE request to /allowAll/denyReadDir/resource should fail
//  //DELETE request to /allowAll/denyWrite should fail
//  //DELETE request to /allowAll/denyWriteDir/resource should fail
//  });
//
//  /**
//   * Test COPY request
//   */
//  asyncTest( 'COPY request', function() { 
//    expect(11);
//    ok(false, "Not implemented");
//   //*** ACTION COPY request ***
//   //COPY request to /denyAll/allowRead to /denyAll/allowReadWriteDir/newResource should be successful
//   //COPY request to /denyAll/allowReadDir/resource to /denyAll/allowReadWriteDir/newResource should be successful
//   //COPY request to /denyAll/allowRead to /denyAll/allowReadWrite should be successful (overwrite)
//   //COPY request to /allowAll/denyRead to /denyAll/allowReadWriteDir/newResource should fail
//   //COPY request to /allowAll/denyReadDir/resource to /denyAll/allowReadWriteDir/newResource should fail
//   //COPY request to /denyAll/allowRead to /denyAll/allowReadDir/newResource should fail
//   //COPY request to /denyAll/allowRead to /denyAll/allowWriteDir/newResource should fail
//   //COPY request to /denyAll/allowRead to /denyAll/allowRead should fail (overwrite)
//   //COPY request to /denyAll/allowRead to /denyAll/allowReadWriteDir/denyRead should fail (overwrite)
//   //COPY request to /denyAll/allowRead to /denyAll/allowWrite should fail (overwrite)
//   //COPY request to /denyAll/allowRead to /denyAll/allowReadWriteDir/denyWrite should fail (overwrite)
//  });
//
//   /**
//    * Test MOVE request
//    */
//   asyncTest( 'MOVE request', function() { 
//     expect(13);
//     ok(false, "Not implemented");
//     //*** ACTION MOVE request ***
//     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWriteDir/newResource should be successful
//     //MOVE request to /denyAll/allowReadWriteDir/resource to /denyAll/allowReadWriteDir/newResource should be successful
//     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWrite should be successful (overwrite)
//     //MOVE request to /allowAll/denyRead to /denyAll/allowReadWriteDir/newResource should fail
//     //MOVE request to /allowAll/denyReadDir/resource to /denyAll/allowReadWriteDir/newResource should fail
//     //MOVE request to /allowAll/denyWrite to /denyAll/allowReadWriteDir/newResource should fail
//     //MOVE request to /allowAll/denyWriteDir/resource to /denyAll/allowReadWriteDir/newResource should fail
//     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadDir/newResource should fail
//     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowWriteDir/newResource should fail
//     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowRead should fail (overwrite)
//     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWriteDir/denyRead should fail (overwrite)
//     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowWrite should fail (overwrite)
//     //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWriteDir/denyWrite should fail (overwrite)
//  });
//
//  /**
//   * Test LOCK request
//   */
//  asyncTest( 'LOCK request', function() { 
//    expect(6);
//    ok(false, "Not implemented");
//    //*** ACTION LOCK request ***
//    //LOCK request to /denyAll/allowReadWrite should be successful
//    //LOCK request to /denyAll/allowReadWrite/resource should be successful
//    //LOCK request to /allowAll/denyRead should fail
//    //LOCK request to /allowAll/denyReadDir/resource should fail
//    //LOCK request to /allowAll/denyWrite should fail
//    //LOCK request to /allowAll/denyWriteDir/resource should fail
//  });
//
//  /**
//   * Test UNLOCK request
//   */
//  asyncTest( 'UNLOCK request', function() { 
//    expect(3);
//    ok(false, "Not implemented");
//    //*** ACTION UNLOCK request ***
//    //No need to test UNLOCK: UNLOCK is always allowed, as long as you have LOCKed the resource. This is already tested by Litmus I think.
//  });
//
//  /**
//   * Test ACL request
//   */
//  asyncTest( 'ACL request', function() { 
//    expect(6);
//    ok(false, "Not implemented");
//    //*** ACTION ACL request ***
//    //ACL request to /denyAll/allowReadWriteAcl should be successful
//    //ACL request to /denyAll/allowReadWriteAclDir/resource should be successful
//    //ACL request to /allowAll/denyRead should fail
//    //ACL request to /allowAll/denyReadDir/resource should fail
//    //ACL request to /allowAll/denyWriteAcl should fail
//    //ACL request to /allowAll/denyWriteAclDir/resource should fail
//  });
//
//  /**
//   * Test REPORT request
//   */
//  asyncTest( 'REPORT request', function() { 
//    expect(4);
//    ok(false, "Not implemented");
//    //*** ACTION REPORT request ***
//    //TODO deze wordt misschien nog niet gebruikt?
//    //REPORT request to /denyAll/allowRead should be successful
//    //REPORT request to /denyAll/allowReadDir/resource should be successful
//    //REPORT request to /allowAll/denyRead should fail
//    //REPORT request to /allowAll/denyReadDir/resource should fail
//  });
//
//  /**
//   * Test Become owner request
//   */
//  asyncTest( 'Become owner request', function() { 
//    expect(5);
//    ok(false, "Not implemented");
//    //*** Become owner ***
//    //Become owner of /denyAll/allowReadWriteDir/resource should be successful
//    //Become owner of /denyAll/allowReadDir/allowWrite should fail
//    //Become owner of /denyAll/allowWriteDir/allowRead should fail
//    //Become owner of /denyAll/allowReadWriteDir/denyRead should fail
//    //Become owner of /denyAll/allowReadWriteDir/denyWrite should fail
// });
//
// /**
//  * Test Change sponsor request
//  */
// asyncTest( 'Change sponsor request', function() { 
//   expect(3);
//   ok(false, "Not implemented");
//   //*** Change sponsor ***
//   //Change sponsor of /foo/file.txt (by John --> owner) to sponsor_b should be successful
//   //Change sponsor of /foo/file.txt (by John) to sponsor_c (does not sponsor John) should fail
//   //Change sponsor of /foo/file.txt (by Jane --> not owner) to sponsor_c should fail
// });

})();
//End of file


