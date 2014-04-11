/**
 * Contains system tests for the ACL functionality
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

// TODO: determine the url to the server
// TODO: prepare/reset server environment
// TODO: determine which two users to use
// TODO: how to make sure both users are member of the testgroup?
///**
// * Tests whether the correct attributes are set on group members
// */
//test( 'Group membership', function() {
//  // Prepare test values
//  var host = 'surfsara.nl';
//  var useHTTPS = true;
//  var port = 8080;
//  var client = new nl.sara.webdav.Client( host, useHTTPS, port );
//
//
//} );
if (nl === undefined) {
  /** @namespace */
  var nl = {};
}
if (nl.sara === undefined) {
  /** @namespace */
  nl.sara = {};
}
if (nl.sara.testutil === undefined) {
  /** @namespace The entire library is in this namespace. */
  nl.sara.testutil = {};
}


(function() {

  var tests = [];
  var running = false;

  nl.sara.testutil.SUCCESS = 1;
  nl.sara.testutil.FAILURE = 2;
  nl.sara.testutil.SKIPPED = 3;

  /**
   * Adds a test to the test queue.
   *
   * @param    {String}    description   The description of the test
   * @param    {Callback}  testFunction  The function that initiates the start. The function receives the test ID as the first parameter.
   * @returns  {void}
   */
  nl.sara.testutil.queueTest = function( description, testFunction ) {
    var testId = tests.length;
    tests[ testId ] = {
      'description': description,
      'test': testFunction
    }
    if ( ! running ) {
      running = true;
      window.setTimeout( function() { tests[ testId ].test( testId ); }, 500 );
    }
  }
  
  
  /**
   * Finishes a test. This allows the next test in the queue to start.
   *
   * @param    {Integer}    testId    The ID of the test
   * @param    {Boolean}    status    True if the test succeeded, false if it failed
   * @param    {String}     comments  Optional; Extra comments on the result.
   * @returns  {Void}
   */
  nl.sara.testutil.finishTest = function( testId, status, comments ) {
    if ( ( status !== nl.sara.testutil.SUCCESS ) && ( status !== nl.sara.testutil.FAILURE ) && ( status !== nl.sara.testutil.SKIPPED ) ) {
      status = nl.sara.testutil.FAILURE;
    }
    tests[ testId ].status = status;
    tests[ testId ].comments =  comments;
    
    // If the test failed, skip the other tests
    if ( status !== nl.sara.testutil.SUCCESS ) {
      running = false;
      for ( var counter = testId + 1; counter < tests.length; counter++ ) {
        tests[ counter ].status = nl.sara.testutil.SKIPPED;
      }
      nl.sara.testutil.showResults();
    }else{
      var nextTestId = testId + 1;
      if ( tests[ nextTestId ] !== undefined ) {
        tests[ nextTestId ].test( nextTestId );
      }else{
        running = false;
        nl.sara.testutil.showResults();
      }
    }
  }
  
  
  /**
   * Shows the results to the user/client
   *
   * @returns  {Void}
   */
  nl.sara.testutil.showResults = function() {
    for ( var key in tests ) {
      var test = tests[key];
      var resultDiv = window.document.createElement( 'div' );
      resultDiv.innerHTML = test.description + ': ' + ( test.status === nl.sara.testutil.SUCCESS ? 'OK' : ( test.status === nl.sara.testutil.SKIPPED ? 'Skip' : 'Failure' + ( test.comments !== undefined ? ': ' + test.comments : '' ) ) );
      window.document.body.appendChild( resultDiv );
    }
  }

} )();


// Home dir van user 1 wordt gebruikt en moet geen ACL er op hebben staan
var username1 = 'niek';
var password1 = 'test';
var username2 = 'laura';
var password2 = 'test';
// user 1 moet lid en administrator van deze groep zijn
var sharedGroup = 'tempgroupdinges';


/**
 * Returns a client that authenticates as user 1
 *
 * @returns  {@exp;nl@pro;sara@pro;webdav@call;Client}  The client instance
 */
function clientUser1() {
  var config = {
    'username': username1,
    'password': password1
  };
  return new nl.sara.webdav.Client( config );
}


/**
 * Returns a client that authenticates as user 2
 *
 * @returns  {@exp;nl@pro;sara@pro;webdav@call;Client}  The client instance
 */
function clientUser2() {
  var config = {
    'username': username2,
    'password': password2
  };
  return new nl.sara.webdav.Client( config ); 
}


/**
 * The base path in which to perform all operations for tests. This location should be empty and readable and writable by both users. It ends with a trailing slash.
 *
 * @returns  {String}  The path
 */
function basePath() {
  return '/home/' + username1 + '/';
}


// TODO: create test to check environment and set up final stuff?
nl.sara.testutil.queueTest( 'Deny access if directory owned by user 1', function( testId ) {
  var client1 = clientUser1();
  var client2 = clientUser2();

  // Make sure the testDir does not exist
  client1.remove( basePath() + 'testDir', function( status ) {
    if ( ( status < 200 ) || ( ( status > 299 ) && ( status !== 404 ) ) ) {
      nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to clear test directory ' + basePath() + 'testDir' + ' . Status code: ' + status );
      return;
    }

    // Create a directory for user 1
    client1.mkcol( basePath() + 'testDir', function( status ) {
      if ( ( status < 200 ) || ( status > 299 ) ) {
        nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to create directory. Status code: ' + status );
        return;
      }

      // Create a file in the directory for user 1
      client1.put( basePath() + 'testDir/file.txt', function( status ) {
        if ( ( status < 200 ) || ( status > 299 ) ) {
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to create file. Status code: ' + status );
          return;
        }

        // User 2 should now not have access to this file
        client2.get( basePath() + 'testDir/file.txt', function( status ) {
          if ( status !== 404 ) {
            nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 should not be able to access the file. Expected status code 404, received status code ' + status );
            return;
          }else{
            nl.sara.testutil.finishTest( testId, nl.sara.testutil.SUCCESS );
            return;
          }
        } );
      }, 'Lorem ipsum', 'text/plain; charset=UTF-8' );
    } );
  }, { 'Depth': 'infinity' } );
} );


nl.sara.testutil.queueTest( 'Test DAV:principal-collection-set and DAV:owner value', function( testId ) {
  var client = clientUser1();
  var ownerProp = new nl.sara.webdav.Property();
  ownerProp.namespace = 'DAV:';
  ownerProp.tagname = 'owner';
  var princColSetProp = new nl.sara.webdav.Property();
  princColSetProp.namespace = 'DAV:';
  princColSetProp.tagname = 'principal-collection-set';

  // Request the file properties
  client.propfind( basePath() + 'testDir/file.txt', function( status, multistatus ) {
    if ( status !== 207 ) {
      nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to get file properties. Expected status code 207, received status code: ' + status );
      return;
    }

    var response = multistatus.getResponse( basePath() + 'testDir/file.txt' );
    var receivedOwnerProp = response.getProperty( 'DAV:', 'owner' ).getParsedValue();
    var receivedPrincColSetProp = response.getProperty( 'DAV:', 'principal-collection-set' ).getParsedValue();

    // And check the response values
    if ( receivedOwnerProp !== '/system/users/niek' ) {
      nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Wrong owner set' );
      return;
    }
    if ( receivedPrincColSetProp.length !== 3 ) {
      nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Expected 3 principal collections, received ' + receivedPrincColSetProp.length );
      return;
    }
    for ( var key = 0; key < receivedPrincColSetProp.length; key++ ) {
      switch ( receivedPrincColSetProp[ key ] ) {
        case '/system/users/':
        case '/system/groups/':
        case '/system/sponsors/':
          break;
        default:
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unexpected principal collection returned: ' + receivedPrincColSetProp[ key ] );
        return;
      }
    }

    nl.sara.testutil.finishTest( testId, nl.sara.testutil.SUCCESS );
  }, 0, [ ownerProp, princColSetProp ] );
} );

/*
- User 1: create directory and file in directory, user 2 should not have access
- User 1: check that DAV:principal-collection-set and DAV:owner are set correctly
- User 1: give user 2 read privileges to file, user 2 should have read, but no more privileges (using DAV:current-user-privilege-set)
- User 1: give user 2 write privileges to file (second ACE), user 2 should have read and write privileges (using DAV:current-user-privilege-set)
- User 1: give user 2 only write-acl privileges to file, user 2 should have write-acl privileges (using DAV:current-user-privilege-set)
- User 1: give user 2 one 'deny write' ACE followed by an 'allow all', user 2 should now have all privileges (first match counts)
- User 1: give user 2 one 'allow all' ACE followed by a 'deny write', user 2 should now have read and write-acl privileges, but no write privileges
- User 2: put deny all privileges on file, user 1 should still have access (due to owner ACE)
- User 1: change ACL on directory; check that the file inherits the ACL correctly and whether DAV:inherited-acl-set is set correctly
- User 1: change privileges and check that the privilege table is implemented correctly
- User 1: give permissions to the group instead of to user 2 directly; check whether user 2 gets permissions (both through DAV:current-user-privilege-set and by really checking the ability to use the privilege)
*/

// End of file
