/**
 * Contains the systems tests to check whether the privileges are correctly enforced
 *
 * Copyright ©2014 SURFsara b.v., Amsterdam, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at <http://www.apache.org/licenses/LICENSE-2.0>
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
"use strict";

(function(){
  module("systemtests",{
    setup: function(){
      this.webdav = new nl.sara.webdav.Client();
      this.statusSuccessOK = 200;
      this.statusSuccessCreated = 201;
      this.statusSuccessNoContent = 204;
      this.statusSuccessMultistatus = 207;
      this.statusNotAllowed = 403;
      this.statusNotFound = 404;
      this.statusConflict = 409;
      this.statusNotImplemented = 501;
      this.newProperty = new nl.sara.webdav.Property();
      this.newProperty.namespace = 'http://tests.beehub.nl/';
      this.newProperty.tagname = 'testproperty';
      this.newProperty.setValueAndRebuildXml( 'test value' );
    },
    teardown: function(){
    }
  });
  
  function createRequestCallback(assert, result){
    return function(status){
      expect(1);
      assert.deepEqual(status, result, "Response status should be "+result);
      start();
    }
  };


  /**
   * Test GET requests
   */
  asyncTest( 'GET request /denyAll/allowRead', function(assert) { 
    //GET request to /denyAll/allowRead should be successful
    this.webdav.get('/denyAll/allowRead' , createRequestCallback(assert, this.statusSuccessOK));
  });
  asyncTest( 'GET request /denyAll/allowReadDir/resource', function(assert) { 
    //GET request to /denyAll/allowReadDir/resource should be successful
    this.webdav.get('/denyAll/allowReadDir/resource' , createRequestCallback(assert, this.statusSuccessOK));
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
    this.webdav.head('/denyAll/allowRead' , createRequestCallback(assert, this.statusSuccessOK));
  });
  asyncTest( 'HEAD request /denyAll/allowReadDir/resource', function(assert) {
    //HEAD request to /denyAll/allowReadDir/resource should be successful
    this.webdav.head('/denyAll/allowReadDir/resource' , createRequestCallback(assert, this.statusSuccessOK));
  });
  asyncTest( 'HEAD request /allowAll/denyRead', function(assert) {
    //HEAD request to /allowAll/denyRead should fail
    this.webdav.head('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotFound));
  });
  asyncTest( 'HEAD request /allowAll/denyReadDir/resource', function(assert) {
    //HEAD request to /allowAll/denyReadDir/resource should fail
    this.webdav.head('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotFound));
  });

  /**
   * Test POST request
   * 
   * Although required privileges are already defined, POST functionality is
   * currently not yet implemented. Therefore, most test should just check for
   * a Not Implemented response from the server.
   */
  asyncTest( 'POST request /denyAll/allowReadWrite', function(assert) { 
    //POST request to /denyAll/allowReadWrite should be successful
    this.webdav.post('/denyAll/allowReadWrite', createRequestCallback(assert, this.statusNotImplemented));
  });
  asyncTest( 'POST request /denyAll/allowReadWriteDir/resource', function(assert) { 
    //POST request to /denyAll/allowReadWriteDir/resource should be successful
    this.webdav.post('/denyAll/allowReadWriteDir/resource', createRequestCallback(assert, this.statusNotImplemented));
  });
  asyncTest( 'POST request /allowAll/denyRead', function(assert) { 
    //POST request to /allowAll/denyRead should fail
    this.webdav.post('/allowAll/denyRead', createRequestCallback(assert, this.statusNotFound));
  });
  asyncTest( 'POST request /allowAll/denyReadDir/', function(assert) { 
    //POST request to /allowAll/denyReadDir/resource should fail
    this.webdav.post('/allowAll/denyReadDir/resource', createRequestCallback(assert, this.statusNotFound));
  });
  asyncTest( 'POST request /allowAll/denyWrite', function(assert) { 
    //POST request to /allowAll/denyWrite should fail
    this.webdav.post('/allowAll/denyWrite', createRequestCallback(assert, this.statusNotImplemented));
  });
  asyncTest( 'POST request /allowAll/denyWriteDir/resource', function(assert) { 
    //POST request to /allowAll/denyWriteDir/resource should fail
    this.webdav.post('/allowAll/denyWriteDir/resource', createRequestCallback(assert, this.statusNotImplemented));
  });
  
  
  /**
   * Test PUT requests
   */
  asyncTest( 'PUT request /denyAll/allowReadWrite', function(assert) { 
    //PUT request to /denyAll/allowReadWrite should be successful
    this.webdav.put('/denyAll/allowReadWrite', createRequestCallback(assert, this.statusSuccessNoContent),"Test");
  });
  asyncTest( 'PUT request /denyAll/allowReadWriteDir/resource', function(assert) { 
    //PUT request to /denyAll/allowReadWriteDir/resource should be successful
    this.webdav.put('/denyAll/allowReadWriteDir/resource', createRequestCallback(assert, this.statusSuccessNoContent),"Test");
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
    this.webdav.put('/denyAll/allowReadWriteDir/newResource', createRequestCallback(assert, this.statusSuccessCreated),"Test");
  });
  asyncTest( 'PUT request /allowAll/denyReadDir/newResource', function(assert) { 
    //PUT request to /allowAll/denyReadDir/newResource should fail (does not exist)
    this.webdav.put('/allowAll/denyReadDir/newResource', createRequestCallback(assert, this.statusNotAllowed),"Test");
  });
  asyncTest( 'PUT request /allowAll/denyWriteDir/newResource', function(assert) { 
    //PUT request to /allowAll/denyWriteDir/newResource should be fail (does not exist)
    this.webdav.put('/allowAll/denyWriteDir/newResource', createRequestCallback(assert, this.statusNotAllowed),"Test");
  });


  /**
   * Test OPTIONS requests
   */
  asyncTest( 'OPTIONS request /denyAll/allowRead', function(assert) { 
    //OPTIONS request to /denyAll/allowRead should be successful
    this.webdav.options('/denyAll/allowRead' , createRequestCallback(assert, this.statusSuccessOK));
  });
  asyncTest( 'OPTIONS request /denyAll/allowReadDir/resource', function(assert) {
    //OPTIONS request to /denyAll/allowReadDir/resource should be successful
    this.webdav.options('/denyAll/allowReadDir/resource' , createRequestCallback(assert, this.statusSuccessOK));
  });
  asyncTest( 'OPTIONS request /allowAll/denyRead', function(assert) {
    //OPTIONS request to /allowAll/denyRead should fail
    this.webdav.options('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotFound));
  });
  asyncTest( 'OPTIONS request /allowAll/denyReadDir/resource', function(assert) {
    //OPTIONS request to /allowAll/denyReadDir/resource should fail
    this.webdav.options('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotFound));
  });


  /**
   * Test PROPFIND requests
   */
  asyncTest( 'PROPFIND request /denyAll/allowRead', function(assert) { 
    //PROPFIND request to /denyAll/allowRead should be successful
    this.webdav.propfind('/denyAll/allowRead' , createRequestCallback(assert, this.statusSuccessMultistatus));
  });
  asyncTest( 'PROPFIND request /denyAll/allowReadDir/resource', function(assert) {
    //PROPFIND request to /denyAll/allowReadDir/resource should be successful
    this.webdav.propfind('/denyAll/allowReadDir/resource' , createRequestCallback(assert, this.statusSuccessMultistatus));
  });
  asyncTest( 'PROPFIND request /allowAll/denyRead', function(assert) {
    //PROPFIND request to /allowAll/denyRead should fail
    this.webdav.propfind('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotFound));
  });
  asyncTest( 'PROPFIND request /allowAll/denyReadDir/resource', function(assert) {
    //PROPFIND request to /allowAll/denyReadDir/resource should fail
    this.webdav.propfind('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotFound));
  });


  /**
   * Test PROPPATCH requests
   */
  asyncTest( 'PROPPATCH request /denyAll/allowReadWrite', function(assert) { 
    //PROPPATCH request to /denyAll/allowReadWrite should be successful
    this.webdav.proppatch('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusSuccessOK), [ this.newProperty ] );
  });
  asyncTest( 'PROPPATCH request /denyAll/allowReadWriteDir/resource', function(assert) {
    //PROPPATCH request to /denyAll/allowReadWriteDir/resource should be successful
    this.webdav.proppatch('/denyAll/allowReadWriteDir/resource' , createRequestCallback(assert, this.statusSuccessOK), [ this.newProperty ]);
  });
  asyncTest( 'PROPPATCH request /allowAll/denyRead', function(assert) {
    //PROPPATCH request to /allowAll/denyRead should fail
    this.webdav.proppatch('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotFound), [ this.newProperty ]);
  });
  asyncTest( 'PROPPATCH request /allowAll/denyReadDir/resource', function(assert) {
    //PROPPATCH request to /allowAll/denyReadDir/resource should fail
    this.webdav.proppatch('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotFound), [ this.newProperty ]);
  });
  asyncTest( 'PROPPATCH request /allowAll/denyWrite', function(assert) {
    //PROPPATCH request to /allowAll/denyWrite should fail
    this.webdav.proppatch('/allowAll/denyWrite' , createRequestCallback(assert, this.statusNotAllowed), [ this.newProperty ]);
  });
  asyncTest( 'PROPPATCH request /allowAll/denyWriteDir/resource', function(assert) {
    //PROPPATCH request to /allowAll/denyWriteDir/resource should fail
    this.webdav.proppatch('/allowAll/denyWriteDir/resource' , createRequestCallback(assert, this.statusNotAllowed), [ this.newProperty ]);
  });


  /**
   * Test MKCOL request
   */
  asyncTest( 'MKCOL request /denyAll/allowReadWriteDir/newResource', function(assert) {
    //MKCOL request to /denyAll/allowReadWriteDir/newResource should successful
    this.webdav.mkcol('/denyAll/allowReadWriteDir/newResource' , createRequestCallback(assert, this.statusSuccessCreated));
  });
  asyncTest( 'MKCOL request /allowAll/denyReadDir/newResource', function(assert) {
    //MKCOL request to /allowAll/denyReadDir/newResource should fail
    this.webdav.mkcol('/allowAll/denyReadDir/newResource' , createRequestCallback(assert, this.statusConflict));
  });
  asyncTest( 'MKCOL request /allowAll/denyWriteDir/newResource', function(assert) {
    //MKCOL request to /allowAll/denyWriteDir/newResource should be fail
    this.webdav.mkcol('/allowAll/denyWriteDir/newResource' , createRequestCallback(assert, this.statusNotAllowed));
  });


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
//   //Change sponsor of /foo/file.txt (by Janine --> not owner) to sponsor_c should fail
// });

})();
//End of file


