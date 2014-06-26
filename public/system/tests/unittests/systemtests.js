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
    },
    teardown: function(){
    }
  });
  
  /**
   * Create callback for requests
   * 
   * @param {Object} assert Test
   * @param {String} result Expected result
   * 
   */
  function createRequestCallback(assert, result){
    return function(status, data){
      expect(1);
      assert.deepEqual(status, result, "Response status should be "+result);
      start();
    };
  };
  
  /**
   * Create callback for requests
   * 
   * @param {Object}  assert      Test
   * @param {String}  result      Expected result
   * @param {String}  path        Path of request
   * @param {Integer} multistatus Multistatus status
   * 
   */
  function createMultistatusRequestCallback(assert, result, path, multistatus){
    return function(status, data){
      if (status === multistatus) {
       expect(1);
       var response = data.getResponse(path);
       if (response !== undefined) {
        var namespaces = response.getNamespaceNames();
        var properties = response.getPropertyNames(namespaces[0]);
        var propStatus = response.getProperty(namespaces[0],properties[0]).status;
        assert.deepEqual(propStatus, result, "Response status should be "+result);
       } else {
         assert.ok(false, "Response is undefined");
       }
      } else {
        assert.ok(false, "Status should be 207");
      };
      start();
    };
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
    this.webdav.put('/allowAll/denyRead', createRequestCallback(assert, this.statusSuccessNoContent),"Test");
  });
  asyncTest( 'PUT request /allowAll/denyReadDir/resource', function(assert) { 
    //PUT request to /allowAll/denyReadDir/resource should fail
    this.webdav.put('/allowAll/denyReadDir/resource', createRequestCallback(assert, this.statusSuccessNoContent),"Test");
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
    this.webdav.propfind('/denyAll/allowRead' , createMultistatusRequestCallback(assert, this.statusSuccessOK, '/denyAll/allowRead', this.statusSuccessMultistatus));
  });
  asyncTest( 'PROPFIND request /denyAll/allowReadDir/resource', function(assert) {
    //PROPFIND request to /denyAll/allowReadDir/resource should be successful
    this.webdav.propfind('/denyAll/allowReadDir/resource' , createMultistatusRequestCallback(assert, this.statusSuccessOK, '/denyAll/allowReadDir/resource', this.statusSuccessMultistatus));
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
  (function() {
    var newProperty = new nl.sara.webdav.Property();
    newProperty.namespace = 'http://tests.beehub.nl/';
    newProperty.tagname = 'testproperty';
    newProperty.setValueAndRebuildXml( 'test value' );

    asyncTest( 'PROPPATCH request /denyAll/allowReadWrite', function(assert) { 
      //PROPPATCH request to /denyAll/allowReadWrite should be successful
      this.webdav.proppatch('/denyAll/allowReadWrite' , createMultistatusRequestCallback(assert, this.statusSuccessOK, '/denyAll/allowReadWrite', this.statusSuccessMultistatus), [ newProperty ] );
    });
    asyncTest( 'PROPPATCH request /denyAll/allowReadWriteDir/resource', function(assert) {
      //PROPPATCH request to /denyAll/allowReadWriteDir/resource should be successful
      this.webdav.proppatch('/denyAll/allowReadWriteDir/resource' , createMultistatusRequestCallback(assert, this.statusSuccessOK, '/denyAll/allowReadWriteDir/resource', this.statusSuccessMultistatus), [ newProperty ]);
    });
    asyncTest( 'PROPPATCH request /allowAll/denyRead', function(assert) {
      //PROPPATCH request to /allowAll/denyRead should fail
      this.webdav.proppatch('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotFound), [ newProperty ]);
    });
    asyncTest( 'PROPPATCH request /allowAll/denyReadDir/resource', function(assert) {
      //PROPPATCH request to /allowAll/denyReadDir/resource should fail
      this.webdav.proppatch('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotFound), [ newProperty ]);
    });
    asyncTest( 'PROPPATCH request /allowAll/denyWrite', function(assert) {
      //PROPPATCH request to /allowAll/denyWrite should fail
      this.webdav.proppatch('/allowAll/denyWrite' , createMultistatusRequestCallback(assert, this.statusNotAllowed, '/allowAll/denyWrite', this.statusSuccessMultistatus), [ newProperty ]);
    });
    asyncTest( 'PROPPATCH request /allowAll/denyWriteDir/resource', function(assert) {
      //PROPPATCH request to /allowAll/denyWriteDir/resource should fail
      this.webdav.proppatch('/allowAll/denyWriteDir/resource' , createMultistatusRequestCallback(assert, this.statusNotAllowed, '/allowAll/denyWriteDir/resource', this.statusSuccessMultistatus), [ newProperty ]);
    });
  })();


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


  /**
   * Test DELETE request
   */
  asyncTest( 'DELETE request /denyAll/allowReadWrite', function(assert) { 
    //DELETE request to /denyAll/allowReadWrite should be successful
    this.webdav.remove('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusSuccessNoContent ) );
  });
  asyncTest( 'DELETE request /denyAll/allowReadWriteDir/resource', function(assert) {
    //DELETE request to /denyAll/allowReadWriteDir/resource should be successful
    this.webdav.remove('/denyAll/allowReadWriteDir/resource' , createRequestCallback(assert, this.statusSuccessNoContent ) );
  });
  asyncTest( 'DELETE request /allowAll/denyRead', function(assert) {
    //DELETE request to /allowAll/denyRead should fail
    this.webdav.remove('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotFound ) );
  });
  asyncTest( 'DELETE request /allowAll/denyReadDir/resource', function(assert) {
    //DELETE request to /allowAll/denyReadDir/resource should fail
    this.webdav.remove('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotFound ) );
  });
  asyncTest( 'DELETE request /allowAll/denyWrite', function(assert) {
    //DELETE request to /allowAll/denyWrite should fail
    this.webdav.remove('/allowAll/denyWrite' , createRequestCallback(assert, this.statusNotAllowed ) );
  });
  asyncTest( 'DELETE request /allowAll/denyWriteDir/resource', function(assert) {
    //DELETE request to /allowAll/denyWriteDir/resource should fail
    this.webdav.remove('/allowAll/denyWriteDir/resource' , createRequestCallback(assert, this.statusNotAllowed ) );
  });


  /**
   * Test COPY request
   */
  asyncTest( 'COPY request to /denyAll/allowRead to /denyAll/allowReadWriteDir/newResource', function(assert) { 
    //COPY request to /denyAll/allowRead to /denyAll/allowReadWriteDir/newResource should be successful
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusSuccessCreated ), '/denyAll/allowReadWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'COPY request to /denyAll/allowReadDir/resource to /denyAll/allowReadWriteDir/newResource', function(assert) { 
    //COPY request to /denyAll/allowReadDir/resource to /denyAll/allowReadWriteDir/newResource should be successful
    this.webdav.copy('/denyAll/allowReadDir/resource' , createRequestCallback(assert, this.statusSuccessCreated ), '/denyAll/allowReadWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'COPY request to /denyAll/allowRead to /denyAll/allowReadWrite', function(assert) {
    //COPY request to /denyAll/allowRead to /denyAll/allowReadWrite should be successful (overwrite)
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusSuccessCreated ), '/denyAll/allowReadWrite', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });
  asyncTest( 'COPY request to /allowAll/denyRead to /denyAll/allowReadWriteDir/newResource', function(assert) {
    //COPY request to /allowAll/denyRead to /denyAll/allowReadWriteDir/newResource should fail
    this.webdav.copy('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotFound ), '/denyAll/allowReadWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'COPY request to /allowAll/denyReadDir/resource to /denyAll/allowReadWriteDir/newResource', function(assert) {
    //COPY request to /allowAll/denyReadDir/resource to /denyAll/allowReadWriteDir/newResource should fail
    this.webdav.copy('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotFound ), '/denyAll/allowReadWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'COPY request to /denyAll/allowRead to /denyAll/allowReadDir/newResource', function(assert) {
    //COPY request to /denyAll/allowRead to /denyAll/allowReadDir/newResource should fail
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowReadDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'COPY request to /denyAll/allowRead to /denyAll/allowWriteDir/newResource', function(assert) {
    //COPY request to /denyAll/allowRead to /denyAll/allowWriteDir/newResource should fail
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'COPY request to /denyAll/allowRead to /denyAll/allowRead2', function(assert) {
    //COPY request to /denyAll/allowRead to /denyAll/allowRead2 should fail (overwrite)
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowRead2', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });
  asyncTest( 'COPY request to /denyAll/allowRead to /denyAll/allowReadWriteDir/denyRead', function(assert) {
    //COPY request to /denyAll/allowRead to /denyAll/allowReadWriteDir/denyRead should fail (overwrite)
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowReadWriteDir/denyRead', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });
  asyncTest( 'COPY request to /denyAll/allowRead to /denyAll/allowWrite', function(assert) {
    //COPY request to /denyAll/allowRead to /denyAll/allowWrite should fail (overwrite)
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowWrite', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });
  asyncTest( 'COPY request to /denyAll/allowRead to /denyAll/allowReadWriteDir/denyWrite', function(assert) {
    //COPY request to /denyAll/allowRead to /denyAll/allowReadWriteDir/denyWrite should fail (overwrite)
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowReadWriteDir/denyWrite', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });


  /**
   * Test MOVE request
   */
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWriteDir/newResource', function(assert) { 
    //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWriteDir/newResource should be successful
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusSuccessCreated ), '/denyAll/allowReadWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'MOVE request to /denyAll/allowReadWriteDir/resource to /denyAll/allowReadWriteDir/newResource', function(assert) { 
    //MOVE request to /denyAll/allowReadWriteDir/resource to /denyAll/allowReadWriteDir/newResource should be successful
    this.webdav.move('/denyAll/allowReadWriteDir/resource' , createRequestCallback(assert, this.statusSuccessCreated ), '/denyAll/allowReadWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWrite2', function(assert) {
    //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWrite2 should be successful (overwrite)
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusSuccessCreated ), '/denyAll/allowReadWrite2', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });
  asyncTest( 'MOVE request to /allowAll/denyRead to /denyAll/allowReadWriteDir/newResource', function(assert) {
    //MOVE request to /allowAll/denyRead to /denyAll/allowReadWriteDir/newResource should fail
    this.webdav.move('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotFound ), '/denyAll/allowReadWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'MOVE request to /allowAll/denyReadDir/resource to /denyAll/allowReadWriteDir/newResource', function(assert) {
    //MOVE request to /allowAll/denyReadDir/resource to /denyAll/allowReadWriteDir/newResource should fail
    this.webdav.move('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotFound ), '/denyAll/allowReadWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'MOVE request to /allowAll/denyWrite to /denyAll/allowReadWriteDir/newResource', function(assert) {
    //MOVE request to /allowAll/denyWrite to /denyAll/allowReadWriteDir/newResource should fail
    this.webdav.move('/allowAll/denyWrite' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowReadWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'MOVE request to /allowAll/denyWriteDir/resource to /denyAll/allowReadWriteDir/newResource', function(assert) {
    //MOVE request to /allowAll/denyWriteDir/resource to /denyAll/allowReadWriteDir/newResource should fail
    this.webdav.move('/allowAll/denyWriteDir/resource' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowReadWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadDir/newResource', function(assert) {
    //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadDir/newResource should fail
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowReadDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /denyAll/allowWriteDir/newResource', function(assert) {
    //MOVE request to /denyAll/allowReadWrite to /denyAll/allowWriteDir/newResource should fail
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /denyAll/allowRead', function(assert) {
    //MOVE request to /denyAll/allowReadWrite to /denyAll/allowRead should fail (overwrite)
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowRead', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWriteDir/denyRead', function(assert) {
    //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWriteDir/denyRead should fail (overwrite)
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowReadWriteDir/denyRead', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /denyAll/allowWrite', function(assert) {
    //MOVE request to /denyAll/allowReadWrite to /denyAll/allowWrite should fail (overwrite)
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowWrite', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWriteDir/denyWrite', function(assert) {
    //MOVE request to /denyAll/allowReadWrite to /denyAll/allowReadWriteDir/denyWrite should fail (overwrite)
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowReadWriteDir/denyWrite', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });


  /**
   * Test LOCK request
   */
  (function(){
    // Create the body for the LOCK request
    var lockBody = document.implementation.createDocument("DAV:", "lockinfo", null);
    var exclusive = lockBody.createElementNS('DAV:', 'exclusive');
    var lockscope = lockBody.createElementNS('DAV:', 'lockscope');
    lockscope.appendChild( exclusive );
    var write = lockBody.createElementNS('DAV:', 'write');
    var locktype = lockBody.createElementNS('DAV:', 'locktype');
    locktype.appendChild( write );
    var href = lockBody.createElementNS('DAV:', 'href');
    href.appendChild( lockBody.createCDATASection( 'http://beehub.nl/lockowner' ) );
    var owner = lockBody.createElementNS('DAV:', 'owner');
    owner.appendChild( href );
    lockBody.documentElement.appendChild( lockscope );
    lockBody.documentElement.appendChild( locktype );
    lockBody.documentElement.appendChild( owner );
  
    asyncTest( 'LOCK request /denyAll/allowReadWrite', function(assert) { 
      //LOCK request to /denyAll/allowReadWrite should be successful
      this.webdav.lock('/denyAll/allowReadWrite', createRequestCallback(assert, this.statusSuccessOK ), lockBody );
    });
    asyncTest( 'LOCK request /denyAll/allowReadWriteDir/resource', function(assert) {
      //LOCK request to /denyAll/allowReadWriteDir/resource should be successful
      this.webdav.lock('/denyAll/allowReadWriteDir/resource', createRequestCallback(assert, this.statusSuccessOK ), lockBody );
    });
    asyncTest( 'LOCK request /allowAll/denyRead', function(assert) {
      //LOCK request to /allowAll/denyRead should fail
      this.webdav.lock('/allowAll/denyRead', createRequestCallback(assert, this.statusNotFound ), lockBody );
    });
    asyncTest( 'LOCK request /allowAll/denyReadDir/resource', function(assert) {
      //LOCK request to /allowAll/denyReadDir/resource should fail
      this.webdav.lock('/allowAll/denyReadDir/resource', createRequestCallback(assert, this.statusNotFound ), lockBody );
    });
    asyncTest( 'LOCK request /allowAll/denyWrite', function(assert) {
      //LOCK request to /allowAll/denyWrite should fail
      this.webdav.lock('/allowAll/denyWrite', createRequestCallback(assert, this.statusNotAllowed ), lockBody );
    });
    asyncTest( 'LOCK request /allowAll/denyWriteDir/resource', function(assert) {
      //LOCK request to /allowAll/denyWriteDir/resource should fail
      this.webdav.lock('/allowAll/denyWriteDir/resource', createRequestCallback(assert, this.statusNotAllowed ), lockBody );
    });
  })();


  //No need to test UNLOCK: UNLOCK is always allowed, as long as you have LOCKed the resource. This is already tested by Litmus I think.


  /**
   * Test ACL request
   */
  (function() {
    // Create an empty ACL to use for the ACL tests
    var emptyAcl = new nl.sara.webdav.Acl();
    
    asyncTest( 'ACL request /denyAll/allowReadWriteAcl', function(assert) { 
      //ACL request to /denyAll/allowReadWriteAcl should be successful
      this.webdav.acl('/denyAll/allowReadWriteAcl' , createRequestCallback(assert, this.statusSuccessOK ), emptyAcl );
    });
    asyncTest( 'ACL request /denyAll/allowReadWriteAclDir/resource', function(assert) {
      //ACL request to /denyAll/allowReadWriteAclDir/resource should be successful
      this.webdav.acl('/denyAll/allowReadWriteAclDir/resource' , createRequestCallback(assert, this.statusSuccessOK ), emptyAcl );
    });
    asyncTest( 'ACL request /allowAll/denyRead', function(assert) {
      //ACL request to /allowAll/denyRead should fail
      this.webdav.acl('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotFound ), emptyAcl );
    });
    asyncTest( 'ACL request /allowAll/denyReadDir/resource', function(assert) {
      //ACL request to /allowAll/denyReadDir/resource should fail
      this.webdav.acl('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotFound ), emptyAcl );
    });
    asyncTest( 'ACL request /allowAll/denyWriteAcl', function(assert) {
      //ACL request to /allowAll/denyWriteAcl should fail
      this.webdav.acl('/allowAll/denyWriteAcl' , createRequestCallback(assert, this.statusNotAllowed ), emptyAcl );
    });
    asyncTest( 'ACL request /allowAll/denyWriteAclDir/resource', function(assert) {
      //ACL request to /allowAll/denyWriteAclDir/resource should fail
      this.webdav.acl('/allowAll/denyWriteAclDir/resource' , createRequestCallback(assert, this.statusNotAllowed ), emptyAcl );
    });
  })();


  /**
   * Test REPORT request
   */
  (function() {
    // Create a body for the REPORT requests
    var reportBody = document.implementation.createDocument("DAV:", "acl-principal-prop-set", null);
    var displayname = reportBody.createElementNS('DAV:', 'displayname');
    var prop = reportBody.createElementNS('DAV:', 'prop');
    prop.appendChild( displayname );
    reportBody.documentElement.appendChild( prop );
    
    asyncTest( 'REPORT request /denyAll/allowRead', function(assert) { 
      //REPORT request to /denyAll/allowRead should be successful
      this.webdav.report( '/denyAll/allowRead', createMultistatusRequestCallback( assert, this.statusSuccessMultistatus, '/denyAll/allowRead', this.statusSuccessMultistatus), reportBody );
    });
    asyncTest( 'REPORT request /denyAll/allowReadDir/resource', function(assert) { 
      //REPORT request to /denyAll/allowReadDir/resource should be successful
      this.webdav.report( '/denyAll/allowReadDir/resource', createMultistatusRequestCallback( assert, this.statusSuccessOK, '/denyAll/allowReadDir/resource', this.statusSuccessMultistatus ), reportBody );
    });
    asyncTest( 'REPORT request /allowAll/denyRead', function(assert) { 
      //REPORT request to /allowAll/denyRead should fail
      this.webdav.report( '/allowAll/denyRead', createRequestCallback(assert, this.statusNotFound), reportBody );
    }); 
    asyncTest( 'REPORT request /allowAll/denyReadDir/resource', function(assert) { 
      //REPORT request to /allowAll/denyReadDir/resource should fail
      this.webdav.report( '/allowAll/denyReadDir/resource', createRequestCallback(assert, this.statusNotFound), reportBody );
    });
  })();


  /**
   * Test Become owner request
   */
  (function() {
    // Who should become owner?
    var newOwner = new nl.sara.webdav.Property();
    newOwner.namespace = 'DAV:';
    newOwner.tagname = 'owner';
    newOwner.setValueAndRebuildXml( '/system/users/janine' );
    
    asyncTest( 'Become owner of /denyAll/allowReadWriteDir/resource', function(assert) { 
      //Become owner of /denyAll/allowReadWriteDir/resource should be successful
      this.webdav.proppatch( '/denyAll/allowReadWriteDir/resource', createMultistatusRequestCallback( assert, this.statusSuccessOK, '/denyAll/allowReadWriteDir/resource', this.statusSuccessMultistatus ), [ newOwner ] );
    });
    asyncTest( 'Become owner of /denyAll/allowReadDir/allowWrite', function(assert) { 
      //Become owner of /denyAll/allowReadDir/allowWrite should fail
      this.webdav.proppatch( '/denyAll/allowReadDir/allowWrite', createMultistatusRequestCallback( assert, this.statusNotAllowed, '/denyAll/allowReadDir/allowWrite', this.statusSuccessMultistatus ), [ newOwner ] );
    });
    asyncTest( 'Become owner of /denyAll/allowWriteDir/allowRead', function(assert) { 
      //Become owner of /denyAll/allowWriteDir/allowRead should fail
      this.webdav.proppatch( '/denyAll/allowWriteDir/allowRead', createMultistatusRequestCallback(assert, this.statusNotAllowed, '/denyAll/allowWriteDir/allowRead', this.statusSuccessMultistatus), [ newOwner ] );
    });
    asyncTest( 'Become owner of /denyAll/allowReadWriteDir/denyRead', function(assert) { 
      //Become owner of /denyAll/allowReadWriteDir/denyRead should fail
      this.webdav.proppatch( '/denyAll/allowReadWriteDir/denyRead', createRequestCallback(assert, this.statusNotFound), [ newOwner ] );
    });
    asyncTest( 'Become owner of /denyAll/allowReadWriteDir/denyWrite', function(assert) { 
      //Become owner of /denyAll/allowReadWriteDir/denyWrite should fail
      this.webdav.proppatch( '/denyAll/allowReadWriteDir/denyWrite', createMultistatusRequestCallback(assert, this.statusNotAllowed, '/denyAll/allowReadWriteDir/denyWrite', this.statusSuccessMultistatus), [ newOwner ] );
    });
  })();


 /**
  * Test Change sponsor request
  */
  (function() {
    // Create an empty sponsor
    var newSponsor = new nl.sara.webdav.Property();
    newSponsor.namespace = 'http://beehub.nl/';
    newSponsor.tagname = 'sponsor';
    
    asyncTest( 'Change sponsor of /foo/file.txt (by John --> owner) to sponsor_b', function(assert) { 
      //Change sponsor of /foo/file.txt (by John --> owner) to sponsor_b should be successful
      newSponsor.setValueAndRebuildXml( '/system/sponsors/sponsor_b' );
      this.webdav.proppatch( '/foo/file.txt', createMultistatusRequestCallback( assert, this.statusSuccessOK, '/foo/file.txt' , this.statusSuccessMultistatus), [ newSponsor ] );
    });
    asyncTest( 'Change sponsor of /foo/file.txt (by John --> owner) to sponsor_c', function(assert) { 
      //Change sponsor of /foo/file.txt (by John --> owner) to sponsor_c (does not sponsor John) should fail
      newSponsor.setValueAndRebuildXml( '/system/sponsors/sponsor_c' );
      this.webdav.proppatch( '/foo/file.txt', createMultistatusRequestCallback( assert, this.statusNotAllowed , '/foo/file.txt', this.statusSuccessMultistatus), [ newSponsor ] );
    });
    asyncTest( 'Change sponsor of /foo/file.txt (by Janine --> not owner) to sponsor_c', function(assert) { 
      //Change sponsor of /foo/file.txt (by Janine --> not owner) to sponsor_c should fail
      newSponsor.setValueAndRebuildXml( '/system/sponsors/sponsor_c' );
      var clientConfig = {
        'username' : 'janine',
        'password' : 'password_of_janine'
      };
      var webdav = new nl.sara.webdav.Client( clientConfig );
      webdav.proppatch( '/foo/file.txt', createMultistatusRequestCallback(assert, this.statusNotAllowed, '/foo/file.txt', this.statusSuccessMultistatus), [ newSponsor ] );
    });
  })();
// });

})();
//End of file


