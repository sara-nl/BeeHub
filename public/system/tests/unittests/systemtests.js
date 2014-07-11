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
   * 
   */
  function createMultistatusRequestCallback(assert, result, path){
    return function(status, data){
      if (status === 207) {
       expect(1);
       var response = data.getResponse(path);
       if (response !== undefined) {
        var namespaces = response.getNamespaceNames();
        var properties = response.getPropertyNames(namespaces[0]);
        var propStatus = response.getProperty(namespaces[0],properties[0]).status;
        assert.deepEqual(propStatus, result, "Multistatus response element's status should be "+result);
       } else {
         assert.ok(false, "Response is undefined");
       }
      } else {
        assert.deepEqual( status, 207, "Response header status should be 207");
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
    this.webdav.get('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotAllowed));
  }); 
  asyncTest( 'GET request /allowAll/denyReadDir/resource', function(assert) { 
    //GET request to /allowAll/denyReadDir/resource should fail
    this.webdav.get('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotAllowed));
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
    this.webdav.head('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotAllowed));
  });
  asyncTest( 'HEAD request /allowAll/denyReadDir/resource', function(assert) {
    //HEAD request to /allowAll/denyReadDir/resource should fail
    this.webdav.head('/allowAll/denyReadDir/resource' , createRequestCallback(assert, this.statusNotAllowed));
  });

  /**
   * Test POST request
   * 
   * Although required privileges are already defined, POST functionality is
   * currently not yet implemented. Therefore, most test should just check for
   * a Not Implemented response from the server.
   */
  asyncTest( 'POST request /denyAll/allowWrite', function(assert) { 
    //POST request to /denyAll/allowWrite should be successful
    this.webdav.post('/denyAll/allowWrite', createRequestCallback(assert, this.statusNotImplemented));
  });
  asyncTest( 'POST request /denyAll/allowWriteDir/resource', function(assert) { 
    //POST request to /denyAll/allowWriteDir/resource should be successful
    this.webdav.post('/denyAll/allowWriteDir/resource', createRequestCallback(assert, this.statusNotImplemented));
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
  asyncTest( 'PUT request /denyAll/allowWrite', function(assert) { 
    //PUT request to /denyAll/allowWrite should be successful
    this.webdav.put('/denyAll/allowWrite', createRequestCallback(assert, this.statusSuccessNoContent),"Test");
  });
  asyncTest( 'PUT request /denyAll/allowWriteDir/resource', function(assert) { 
    //PUT request to /denyAll/allowWriteDir/resource should be successful
    this.webdav.put('/denyAll/allowWriteDir/resource', createRequestCallback(assert, this.statusSuccessNoContent),"Test");
  });
  asyncTest( 'PUT request /allowAll/denyWrite', function(assert) { 
    //PUT request to /allowAll/denyWrite should fail
    this.webdav.put('/allowAll/denyWrite', createRequestCallback(assert, this.statusNotAllowed),"Test");
  });
  asyncTest( 'PUT request /allowAll/denyWriteDir/resource', function(assert) { 
    //PUT request to /allowAll/denyWriteDir/resource should fail
    this.webdav.put('/allowAll/denyWriteDir/resource', createRequestCallback(assert, this.statusNotAllowed),"Test");
  });
  asyncTest( 'PUT request /denyAll/allowWriteDir/newResource', function(assert) { 
    //PUT request to /denyAll/allowWriteDir/newResource should successful (does not exist)
    this.webdav.put('/denyAll/allowWriteDir/newResource', createRequestCallback(assert, this.statusSuccessCreated),"Test");
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
  asyncTest( 'OPTIONS request /denyAll/allowWrite', function(assert) {
    //OPTIONS request to /denyAll/allowWrite should be successful
    this.webdav.options('/denyAll/allowWrite' , createRequestCallback(assert, this.statusSuccessOK));
  });
  asyncTest( 'OPTIONS request /denyAll/allowWriteAcl', function(assert) {
    //OPTIONS request to /denyAll/allowWriteAcl should be successful
    this.webdav.options('/denyAll/allowWriteAcl' , createRequestCallback(assert, this.statusSuccessOK));
  });
  asyncTest( 'OPTIONS request /denyAll/', function(assert) {
    //OPTIONS request to /denyAll should fail
    this.webdav.options('/denyAll' , createRequestCallback(assert, this.statusNotFound));
  });


  /**
   * Test PROPFIND requests
   */
  asyncTest( 'PROPFIND request /denyAll/allowRead', function(assert) {
    //PROPFIND request to /denyAll/allowRead should be successful
    this.webdav.propfind('/denyAll/allowRead' , createMultistatusRequestCallback(assert, this.statusSuccessOK, '/denyAll/allowRead'));
  });
  asyncTest( 'PROPFIND request /denyAll/allowReadDir/resource', function(assert) {
    //PROPFIND request to /denyAll/allowReadDir/resource should be successful
    this.webdav.propfind('/denyAll/allowReadDir/resource' , createMultistatusRequestCallback(assert, this.statusSuccessOK, '/denyAll/allowReadDir/resource'));
  });
  asyncTest( 'PROPFIND request /allowAll/denyRead', function(assert) {
    //PROPFIND request to /allowAll/denyRead should fail
    this.webdav.propfind('/allowAll/denyRead' , createMultistatusRequestCallback(assert, this.statusNotAllowed, '/allowAll/denyRead' ) );
  });
  asyncTest( 'PROPFIND request /allowAll/denyReadDir/resource', function(assert) {
    //PROPFIND request to /allowAll/denyReadDir/resource should fail
    this.webdav.propfind('/allowAll/denyReadDir/resource' , createMultistatusRequestCallback(assert, this.statusNotAllowed, '/allowAll/denyReadDir/resource' ) );
  });


  /**
   * Test PROPPATCH requests
   */
  asyncTest( 'PROPPATCH request /denyAll/allowWrite', function(assert) {
    var newProperty = new nl.sara.webdav.Property();
    newProperty.namespace = 'http://tests.beehub.nl/';
    newProperty.tagname = 'testproperty';
    newProperty.setValueAndRebuildXml( 'test value' );
    //PROPPATCH request to /denyAll/allowWrite should be successful
    this.webdav.proppatch('/denyAll/allowWrite' , createMultistatusRequestCallback(assert, this.statusSuccessOK, '/denyAll/allowWrite'), [ newProperty ] );
  });
  asyncTest( 'PROPPATCH request /denyAll/allowWriteDir/resource', function(assert) {
    var newProperty = new nl.sara.webdav.Property();
    newProperty.namespace = 'http://tests.beehub.nl/';
    newProperty.tagname = 'testproperty';
    newProperty.setValueAndRebuildXml( 'test value' );
    //PROPPATCH request to /denyAll/allowWriteDir/resource should be successful
    this.webdav.proppatch('/denyAll/allowWriteDir/resource' , createMultistatusRequestCallback(assert, this.statusSuccessOK, '/denyAll/allowWriteDir/resource'), [ newProperty ]);
  });
  asyncTest( 'PROPPATCH request /allowAll/denyWrite', function(assert) {
    var newProperty = new nl.sara.webdav.Property();
    newProperty.namespace = 'http://tests.beehub.nl/';
    newProperty.tagname = 'testproperty';
    newProperty.setValueAndRebuildXml( 'test value' );
    //PROPPATCH request to /allowAll/denyWrite should fail
    this.webdav.proppatch('/allowAll/denyWrite' , createMultistatusRequestCallback(assert, this.statusNotAllowed, '/allowAll/denyWrite'), [ newProperty ]);
  });
  asyncTest( 'PROPPATCH request /allowAll/denyWriteDir/resource', function(assert) {
    var newProperty = new nl.sara.webdav.Property();
    newProperty.namespace = 'http://tests.beehub.nl/';
    newProperty.tagname = 'testproperty';
    newProperty.setValueAndRebuildXml( 'test value' );
    //PROPPATCH request to /allowAll/denyWriteDir/resource should fail
    this.webdav.proppatch('/allowAll/denyWriteDir/resource' , createMultistatusRequestCallback(assert, this.statusNotAllowed, '/allowAll/denyWriteDir/resource'), [ newProperty ]);
  });


  /**
   * Test MKCOL request
   */
  asyncTest( 'MKCOL request /denyAll/allowWriteDir/newResource', function(assert) {
    //MKCOL request to /denyAll/allowWriteDir/newResource should successful
    this.webdav.mkcol('/denyAll/allowWriteDir/newResource' , createRequestCallback(assert, this.statusSuccessCreated));
  });
  asyncTest( 'MKCOL request /allowAll/denyWriteDir/newResource', function(assert) {
    //MKCOL request to /allowAll/denyWriteDir/newResource should be fail
    this.webdav.mkcol('/allowAll/denyWriteDir/newResource' , createRequestCallback(assert, this.statusNotAllowed));
  });


  /**
   * Test DELETE request
   */
  asyncTest( 'DELETE request /denyAll/allowWrite', function(assert) { 
    //DELETE request to /denyAll/allowWrite should be successful
    this.webdav.remove('/denyAll/allowWrite' , createRequestCallback(assert, this.statusSuccessNoContent ) );
  });
  asyncTest( 'DELETE request /denyAll/allowWriteDir/resource', function(assert) {
    //DELETE request to /denyAll/allowWriteDir/resource should be successful
    this.webdav.remove('/denyAll/allowWriteDir/resource' , createRequestCallback(assert, this.statusSuccessNoContent ) );
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
  asyncTest( 'COPY request to /denyAll/allowRead to /denyAll/allowWriteDir/newResource', function(assert) { 
    //COPY request to /denyAll/allowRead to /denyAll/allowWriteDir/newResource should be successful
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusSuccessCreated ), '/denyAll/allowWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'COPY request to /allowAll/denyRead to /denyAll/allowWriteDir/newResource', function(assert) {
    //COPY request to /allowAll/denyRead to /denyAll/allowWriteDir/newResource should fail
    this.webdav.copy('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotAllowed ) , '/denyAll/allowWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'COPY request to /denyAll/allowRead to /allowAll/denyWriteDir/newResource', function(assert) {
    //COPY request to /denyAll/allowRead to /allowAll/denyWriteDir/newResource should fail
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusNotAllowed ), '/allowAll/denyWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'COPY request to /denyAll/allowRead to /denyAll/allowWriteWriteAcl', function(assert) {
    //COPY request to /denyAll/allowRead to /denyAll/allowWriteWriteAcl should be successful (overwrite)
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusSuccessNoContent ), '/denyAll/allowWriteWriteAcl', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });
  asyncTest( 'COPY request to /denyAll/allowRead to /allowAll/denyWrite', function(assert) {
    //COPY request to /denyAll/allowRead to /allowAll/denyWrite should fail (overwrite)
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusNotAllowed ), '/allowAll/denyWrite', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });
  asyncTest( 'COPY request to /denyAll/allowRead to /allowAll/denyWriteAcl', function(assert) {
    //COPY request to /denyAll/allowRead to /allowAll/denyWriteAcl should fail (overwrite)
    this.webdav.copy('/denyAll/allowRead' , createRequestCallback(assert, this.statusNotAllowed ), '/allowAll/denyWriteAcl', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });


  /**
   * Test MOVE request
   */
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /denyAll/allowWriteDir/newResource', function(assert) { 
    //MOVE request to /denyAll/allowReadWrite to /denyAll/allowWriteDir/newResource should be successful
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusSuccessCreated ), '/denyAll/allowWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'MOVE request to /allowAll/denyRead to /denyAll/allowWriteDir/newResource', function(assert) {
    //MOVE request to /allowAll/denyRead to /denyAll/allowWriteDir/newResource should fail
    this.webdav.move('/allowAll/denyRead' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'MOVE request to /allowAll/denyWrite to /denyAll/allowWriteDir/newResource', function(assert) {
    //MOVE request to /allowAll/denyWrite to /denyAll/allowWriteDir/newResource should fail
    this.webdav.move('/allowAll/denyWrite' , createRequestCallback(assert, this.statusNotAllowed ), '/denyAll/allowWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /allowAll/denyWriteDir/newResource', function(assert) {
    //MOVE request to /denyAll/allowReadWrite to /allowAll/denyWriteDir/newResource should fail
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusNotAllowed ), '/allowAll/denyWriteDir/newResource', nl.sara.webdav.Client.FAIL_ON_OVERWRITE );
  });
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /denyAll/allowWriteWriteAcl', function(assert) {
    //MOVE request to /denyAll/allowReadWrite to /denyAll/allowWriteWriteAcl should be successful (overwrite)
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusSuccessNoContent ), '/denyAll/allowWriteWriteAcl', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /allowAll/denyWrite', function(assert) {
    //MOVE request to /denyAll/allowReadWrite to /allowAll/denyWrite should fail (overwrite)
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusNotAllowed ), '/allowAll/denyWrite', nl.sara.webdav.Client.SILENT_OVERWRITE );
  });
  asyncTest( 'MOVE request to /denyAll/allowReadWrite to /allowAll/denyWriteAcl', function(assert) {
    //MOVE request to /denyAll/allowReadWrite to /allowAll/denyWriteAcl should fail (overwrite)
    this.webdav.move('/denyAll/allowReadWrite' , createRequestCallback(assert, this.statusNotAllowed ), '/allowAll/denyWriteAcl', nl.sara.webdav.Client.SILENT_OVERWRITE );
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

    asyncTest( 'LOCK request /denyAll/allowWrite', function(assert) {
      //LOCK request to /denyAll/allowWrite should be successful
      this.webdav.lock('/denyAll/allowWrite', createRequestCallback(assert, this.statusSuccessOK ), lockBody );
    });
    asyncTest( 'LOCK request /denyAll/allowWriteDir/', function(assert) {
      //LOCK request to /denyAll/allowWriteDir/ should be successful
      this.webdav.lock('/denyAll/allowWriteDir/', createRequestCallback(assert, this.statusSuccessOK ), lockBody, { 'Depth': '0' } );
    });
    asyncTest( 'LOCK request /allowAll/denyWrite', function(assert) {
      //LOCK request to /allowAll/denyWrite should fail
      this.webdav.lock('/allowAll/denyWrite', createRequestCallback(assert, this.statusNotAllowed ), lockBody );
    });
    asyncTest( 'LOCK request /allowAll/denyWriteDir/', function(assert) {
      //LOCK request to /allowAll/denyWriteDir/ should fail
      this.webdav.lock('/allowAll/denyWriteDir/', createRequestCallback(assert, this.statusNotAllowed ), lockBody, { 'Depth': '0' } );
    });
  })();


  //No need to test UNLOCK: UNLOCK is always allowed, as long as you have LOCKed the resource. This is already tested by Litmus I think.


  /**
   * Test ACL request
   */
  (function() {
    // Create an empty ACL to use for the ACL tests
    var emptyAcl = new nl.sara.webdav.Acl();
    
    asyncTest( 'ACL request /denyAll/allowWriteAcl', function(assert) { 
      //ACL request to /denyAll/allowWriteAcl should be successful
      this.webdav.acl('/denyAll/allowWriteAcl' , createRequestCallback(assert, this.statusSuccessOK ), emptyAcl );
    });
    asyncTest( 'ACL request /denyAll/allowWriteAclDir/resource', function(assert) {
      //ACL request to /denyAll/allowWriteAclDir/resource should be successful
      this.webdav.acl('/denyAll/allowWriteAclDir/resource' , createRequestCallback(assert, this.statusSuccessOK ), emptyAcl );
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
      this.webdav.report( '/denyAll/allowRead', createMultistatusRequestCallback( assert, this.statusSuccessOK, '/system/users/john'), reportBody );
    });
    asyncTest( 'REPORT request /denyAll/allowReadDir/resource', function(assert) { 
      //REPORT request to /denyAll/allowReadDir/resource should be successful
      this.webdav.report( '/denyAll/allowReadDir/resource', createMultistatusRequestCallback( assert, this.statusSuccessOK, '/system/users/john'), reportBody );
    });
    asyncTest( 'REPORT request /allowAll/denyRead', function(assert) { 
      //REPORT request to /allowAll/denyRead should fail
      this.webdav.report( '/allowAll/denyRead', createRequestCallback(assert, this.statusNotAllowed), reportBody );
    }); 
    asyncTest( 'REPORT request /allowAll/denyReadDir/resource', function(assert) { 
      //REPORT request to /allowAll/denyReadDir/resource should fail
      this.webdav.report( '/allowAll/denyReadDir/resource', createRequestCallback(assert, this.statusNotAllowed), reportBody );
    });
  })();


  /**
   * Test Become owner request
   */
  asyncTest( 'Become owner of /denyAll/allowWriteDir/allowRead', function(assert) {
    // Who should become owner?
    var newOwner = new nl.sara.webdav.Property();
    newOwner.namespace = 'DAV:';
    newOwner.tagname = 'owner';
    newOwner.setValueAndRebuildXml( '/system/users/john' );
    //Become owner of /denyAll/allowWriteDir/allowRead should be successful
    this.webdav.proppatch( '/denyAll/allowWriteDir/allowRead', createMultistatusRequestCallback(assert, this.statusSuccessOK, '/denyAll/allowWriteDir/allowRead'), [ newOwner ] );
  });
  asyncTest( 'Become owner of /allowAll/denyWriteDir/allowWrite', function(assert) {
    // Who should become owner?
    var newOwner = new nl.sara.webdav.Property();
    newOwner.namespace = 'DAV:';
    newOwner.tagname = 'owner';
    newOwner.setValueAndRebuildXml( '/system/users/john' );
    //Become owner of /allowAll/denyWriteDir/allowWrite should fail
    this.webdav.proppatch( '/allowAll/denyWriteDir/allowWrite', createMultistatusRequestCallback( assert, this.statusNotAllowed, '/allowAll/denyWriteDir/allowWrite' ), [ newOwner ] );
  });
  asyncTest( 'Become owner of /allowAll/denyRead', function(assert) {
    // Who should become owner?
    var newOwner = new nl.sara.webdav.Property();
    newOwner.namespace = 'DAV:';
    newOwner.tagname = 'owner';
    newOwner.setValueAndRebuildXml( '/system/users/john' );
    //Become owner of /allowAll/denyRead should fail
    this.webdav.proppatch( '/allowAll/denyRead', createMultistatusRequestCallback( assert, this.statusNotAllowed, '/allowAll/denyRead' ), [ newOwner ] );
  });
  asyncTest( 'Become owner of /allowAll/denyWrite', function(assert) {
    // Who should become owner?
    var newOwner = new nl.sara.webdav.Property();
    newOwner.namespace = 'DAV:';
    newOwner.tagname = 'owner';
    newOwner.setValueAndRebuildXml( '/system/users/john' );
    //Become owner of /allowAll/denyWrite should fail
    this.webdav.proppatch( '/allowAll/denyWrite', createMultistatusRequestCallback( assert, this.statusNotAllowed, '/allowAll/denyWrite' ), [ newOwner ] );
  });


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
      this.webdav.proppatch( '/foo/file.txt', createMultistatusRequestCallback( assert, this.statusSuccessOK, '/foo/file.txt' ), [ newSponsor ] );
    });
    asyncTest( 'Change sponsor of /foo/file.txt (by John --> owner) to sponsor_c', function(assert) { 
      //Change sponsor of /foo/file.txt (by John --> owner) to sponsor_c (does not sponsor John) should fail
      newSponsor.setValueAndRebuildXml( '/system/sponsors/sponsor_c' );
      this.webdav.proppatch( '/foo/file.txt', createMultistatusRequestCallback( assert, this.statusNotAllowed , '/foo/file.txt' ), [ newSponsor ] );
    });
    asyncTest( 'Change sponsor of /foo/file.txt (by Janine --> not owner) to sponsor_c', function(assert) { 
      //Change sponsor of /foo/file.txt (by Janine --> not owner) to sponsor_c should fail
      newSponsor.setValueAndRebuildXml( '/system/sponsors/sponsor_c' );
      var clientConfig = {
        'username' : 'janine',
        'password' : 'password_of_janine'
      };
      var webdav = new nl.sara.webdav.Client( clientConfig );
      webdav.proppatch( '/foo/file.txt', createMultistatusRequestCallback(assert, this.statusNotAllowed, '/foo/file.txt' ), [ newSponsor ] );
    });
  })();
// });

})();
//End of file


