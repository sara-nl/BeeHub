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
// TODO: create test to check environment and set up final stuff?
// TODO:
/*
- User 1: change privileges and check that the privilege table is implemented correctly
*/


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
    };
    if ( ! running ) {
      running = true;
      window.setTimeout( function() { tests[ testId ].test( testId ); }, 500 );
    }
  };
  
  
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
  };
  
  
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
  };

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


// User 1: create directory and file in directory, user 2 should not have access
nl.sara.testutil.queueTest( 'Deny access to user 2 if directory owned by user 1', function( testId ) {
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


// User 1: check that DAV:principal-collection-set and DAV:owner are set correctly
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


// User 1: give permissions to the group instead of to user 2 directly; check whether user 2 gets permissions (both through DAV:current-user-privilege-set and by really checking the ability to use the privilege)
nl.sara.testutil.queueTest( 'Grant the group all access to file of user 1', function( testId ) {
  // Give user 2 read and write privileges to file
  var allPrivilege = new nl.sara.webdav.Privilege();
  allPrivilege.namespace = 'DAV:';
  allPrivilege.tagname = 'all';
  var allAce = new nl.sara.webdav.Ace();
  allAce.addPrivilege( allPrivilege );
  allAce.grantdeny = nl.sara.webdav.Ace.GRANT;
  allAce.invertprincipal = false;
  allAce.principal = '/system/groups/' + sharedGroup;
  var acl = new nl.sara.webdav.Acl();
  acl.addAce( allAce, 0 );
  var client1 = clientUser1();
  client1.acl( basePath() + 'testDir/file.txt', function( status ){
    if ( status !== 200 ) {
      nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to set ACL' );
      return;
    }
    
    // User 2 should have read and write acl, but no write privileges
    // Test this using DAV:current-user-privilege-set)
    var client2 = clientUser2();
    var prop = new nl.sara.webdav.Property();
    prop.namespace = 'DAV:';
    prop.tagname = 'current-user-privilege-set';
    client2.propfind( basePath() + 'testDir/file.txt', function( status, data ) {
      if ( status !== 207 ) {
        nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to read properties' );
        return;
      }
      
      var response = data.getResponse( basePath() + 'testDir/file.txt' );
      var prop = response.getProperty( 'DAV:', 'current-user-privilege-set' );
      var privs = prop.getParsedValue();
      for ( var key in privs ) {
        if (
          (
            ( privs[ key ].namespace !== 'DAV:' ) ||
            (
              ( privs[ key ].tagname !== 'unbind' ) &&
              ( privs[ key ].tagname !== 'write-acl' ) &&
              ( privs[ key ].tagname !== 'write-content' ) &&
              ( privs[ key ].tagname !== 'read-acl' ) &&
              ( privs[ key ].tagname !== 'read-current-user-privilege-set' )
            )
          ) &&
          (
            ( privs[ key ].namespace !== 'http://beehub.nl/' ) ||
            ( privs[ key ].tagname !== 'read-content' )
          )
        ){
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unexpected privileges returned: ' + privs[ key ].namespace + ' ' + privs[ key ].tagname );
          return;
        }
      }

      // And test this by trying requests GET and ACL should succeed and PUT should be forbidden
      client2.get( basePath() + 'testDir/file.txt', function( status, data ) {
        if ( ( status !== 200 ) || ( data !== 'Lorem ipsum' ) ) {
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to correctly retrieve the file' );
          return;
        }
        
        client2.put( basePath() + 'testDir/file.txt', function( status ) {
          if ( status !== 204 ) {
            nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 should get a 204 (no content) return code when attempting to write to the file. Instead received this code: ' + status );
            return;
          }

          var emptyAcl = new nl.sara.webdav.Acl();
          client2.acl( basePath() + 'testDir/file.txt', function( status ) {
            if ( status !== 200 ) {
              nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 is unable to set ACL' );
              return;
            }

            nl.sara.testutil.finishTest( testId, nl.sara.testutil.SUCCESS );
            return;

          }, emptyAcl );
        }, 'Lorem ipsum' );
      } );
      
    }, 0, [ prop ] );

  }, acl );
} );


// User 1: give user 2 read privileges to file, user 2 should have read, but no more privileges
nl.sara.testutil.queueTest( 'Grant user 2 read access to file of user 1', function( testId ) {
  // Give user 2 read privileges to file
  var privilege = new nl.sara.webdav.Privilege();
  privilege.namespace = 'DAV:';
  privilege.tagname = 'read';
  var ace = new nl.sara.webdav.Ace();
  ace.addPrivilege( privilege );
  ace.grantdeny = nl.sara.webdav.Ace.GRANT;
  ace.invertprincipal = false;
  ace.principal = '/system/users/' + username2;
  var acl = new nl.sara.webdav.Acl();
  acl.addAce( ace, 0 );
  var client1 = clientUser1();
  client1.acl( basePath() + 'testDir/file.txt', function( status ){
    if ( status !== 200 ) {
      nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to set ACL' );
      return;
    }
    
    // User 2 should have read, but no more privileges
    // Test this using DAV:current-user-privilege-set)
    var client2 = clientUser2();
    var prop = new nl.sara.webdav.Property();
    prop.namespace = 'DAV:';
    prop.tagname = 'current-user-privilege-set';
    client2.propfind( basePath() + 'testDir/file.txt', function( status, data ) {
      if ( status !== 207 ) {
        nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to read properties' );
        return;
      }
      
      var response = data.getResponse( basePath() + 'testDir/file.txt' );
      var prop = response.getProperty( 'DAV:', 'current-user-privilege-set' );
      var privs = prop.getParsedValue();
      for ( var key in privs ) {
        if (
          (
            ( privs[ key ].namespace !== 'DAV:' ) ||
            (
              ( privs[ key ].tagname !== 'unbind' ) &&
              ( privs[ key ].tagname !== 'read-acl' ) &&
              ( privs[ key ].tagname !== 'read-current-user-privilege-set' )
            )
          ) &&
          (
            ( privs[ key ].namespace !== 'http://beehub.nl/' ) ||
            ( privs[ key ].tagname !== 'read-content' )
          )
        ){
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unexpected privileges returned: ' + privs[ key ].namespace + ' ' + privs[ key ].tagname );
          return;
        }
      }

      // And test this by trying requests GET (should succeed) and PUT and ACL (should be forbidden)
      client2.get( basePath() + 'testDir/file.txt', function( status, data ) {
        if ( ( status !== 200 ) || ( data !== 'Lorem ipsum' ) ) {
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to correctly retrieve the file' );
          return;
        }
        
        client2.put( basePath() + 'testDir/file.txt', function( status ) {
          if ( status !== 403 ) {
            nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 should get a 403 (forbidden) return code when attempting to write to the file. Instead received this code: ' + status );
            return;
          }

          client2.acl( basePath() + 'testDir/file.txt', function( status ) {
            if ( status !== 403 ) {
              nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 should get a 403 (forbidden) return code when attempting to change the acl of the file. Instead received this code: ' + status );
              return;
            }

            nl.sara.testutil.finishTest( testId, nl.sara.testutil.SUCCESS );
            return;

          }, acl );
        }, 'Lorem ipsum' );
      } );
      
    }, 0, [ prop ] );

  }, acl );
} );


// User 1: give user 2 write privileges to file (second ACE), user 2 should have read and write privileges (using DAV:current-user-privilege-set)
nl.sara.testutil.queueTest( 'Grant user 2 read and write access to file of user 1', function( testId ) {
  // Give user 2 read and write privileges to file
  var readPrivilege = new nl.sara.webdav.Privilege();
  readPrivilege.namespace = 'DAV:';
  readPrivilege.tagname = 'read';
  var writePrivilege = new nl.sara.webdav.Privilege();
  writePrivilege.namespace = 'DAV:';
  writePrivilege.tagname = 'write';
  var ace = new nl.sara.webdav.Ace();
  ace.addPrivilege( readPrivilege );
  ace.addPrivilege( writePrivilege );
  ace.grantdeny = nl.sara.webdav.Ace.GRANT;
  ace.invertprincipal = false;
  ace.principal = '/system/users/' + username2;
  var acl = new nl.sara.webdav.Acl();
  acl.addAce( ace, 0 );
  var client1 = clientUser1();
  client1.acl( basePath() + 'testDir/file.txt', function( status ){
    if ( status !== 200 ) {
      nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to set ACL' );
      return;
    }
    
    // User 2 should have read and write privileges
    // Test this using DAV:current-user-privilege-set)
    var client2 = clientUser2();
    var prop = new nl.sara.webdav.Property();
    prop.namespace = 'DAV:';
    prop.tagname = 'current-user-privilege-set';
    client2.propfind( basePath() + 'testDir/file.txt', function( status, data ) {
      if ( status !== 207 ) {
        nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to read properties' );
        return;
      }
      
      var response = data.getResponse( basePath() + 'testDir/file.txt' );
      var prop = response.getProperty( 'DAV:', 'current-user-privilege-set' );
      var privs = prop.getParsedValue();
      for ( var key in privs ) {
        if (
          (
            ( privs[ key ].namespace !== 'DAV:' ) ||
            (
              ( privs[ key ].tagname !== 'unbind' ) &&
              ( privs[ key ].tagname !== 'write-content' ) &&
              ( privs[ key ].tagname !== 'read-acl' ) &&
              ( privs[ key ].tagname !== 'read-current-user-privilege-set' )
            )
          ) &&
          (
            ( privs[ key ].namespace !== 'http://beehub.nl/' ) ||
            ( privs[ key ].tagname !== 'read-content' )
          )
        ){
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unexpected privileges returned: ' + privs[ key ].namespace + ' ' + privs[ key ].tagname );
          return;
        }
      }

      // And test this by trying requests GET (should succeed) and PUT (should be forbidden)
      client2.get( basePath() + 'testDir/file.txt', function( status, data ) {
        if ( ( status !== 200 ) || ( data !== 'Lorem ipsum' ) ) {
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to correctly retrieve the file' );
          return;
        }
        
        client2.put( basePath() + 'testDir/file.txt', function( status ) {
          if ( status !== 204 ) {
            nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 should get a 204 (no content) return code when attempting to write to the file. Instead received this code: ' + status );
            return;
          }

          client2.acl( basePath() + 'testDir/file.txt', function( status ) {
            if ( status !== 403 ) {
              nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 should get a 403 (forbidden) return code when attempting to change the acl of the file. Instead received this code: ' + status );
              return;
            }

            nl.sara.testutil.finishTest( testId, nl.sara.testutil.SUCCESS );
            return;

          }, acl );
        }, 'Lorem ipsum' );
      } );
      
    }, 0, [ prop ] );

  }, acl );
} );


// User 1: give user 2 only write-acl privileges to file, user 2 should have write-acl privileges (using DAV:current-user-privilege-set)
nl.sara.testutil.queueTest( 'Grant user 2 write-acl and read access to file of user 1', function( testId ) {
  // Give user 2 read and write privileges to file
  var readPrivilege = new nl.sara.webdav.Privilege();
  readPrivilege.namespace = 'DAV:';
  readPrivilege.tagname = 'read';
  var writePrivilege = new nl.sara.webdav.Privilege();
  writePrivilege.namespace = 'DAV:';
  writePrivilege.tagname = 'write-acl';
  var ace = new nl.sara.webdav.Ace();
  ace.addPrivilege( readPrivilege );
  ace.addPrivilege( writePrivilege );
  ace.grantdeny = nl.sara.webdav.Ace.GRANT;
  ace.invertprincipal = false;
  ace.principal = '/system/users/' + username2;
  var acl = new nl.sara.webdav.Acl();
  acl.addAce( ace, 0 );
  var client1 = clientUser1();
  client1.acl( basePath() + 'testDir/file.txt', function( status ){
    if ( status !== 200 ) {
      nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to set ACL' );
      return;
    }
    
    // User 2 should have read and write privileges
    // Test this using DAV:current-user-privilege-set)
    var client2 = clientUser2();
    var prop = new nl.sara.webdav.Property();
    prop.namespace = 'DAV:';
    prop.tagname = 'current-user-privilege-set';
    client2.propfind( basePath() + 'testDir/file.txt', function( status, data ) {
      if ( status !== 207 ) {
        nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to read properties' );
        return;
      }
      
      var response = data.getResponse( basePath() + 'testDir/file.txt' );
      var prop = response.getProperty( 'DAV:', 'current-user-privilege-set' );
      var privs = prop.getParsedValue();
      for ( var key in privs ) {
        if (
          (
            ( privs[ key ].namespace !== 'DAV:' ) ||
            (
              ( privs[ key ].tagname !== 'unbind' ) &&
              ( privs[ key ].tagname !== 'write-acl' ) &&
              ( privs[ key ].tagname !== 'read-acl' ) &&
              ( privs[ key ].tagname !== 'read-current-user-privilege-set' )
            )
          ) &&
          (
            ( privs[ key ].namespace !== 'http://beehub.nl/' ) ||
            ( privs[ key ].tagname !== 'read-content' )
          )
        ){
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unexpected privileges returned: ' + privs[ key ].namespace + ' ' + privs[ key ].tagname );
          return;
        }
      }

      // And test this by trying requests GET and ACL should succeed and PUT should be forbidden
      client2.get( basePath() + 'testDir/file.txt', function( status, data ) {
        if ( ( status !== 200 ) || ( data !== 'Lorem ipsum' ) ) {
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to correctly retrieve the file' );
          return;
        }
        
        client2.put( basePath() + 'testDir/file.txt', function( status ) {
          if ( status !== 403 ) {
            nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 should get a 403 (forbidden) return code when attempting to write to the file. Instead received this code: ' + status );
            return;
          }
        
          client2.acl( basePath() + 'testDir/file.txt', function( status ) {
            if ( status !== 200 ) {
              nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 is unable to set ACL' );
              return;
            }

            nl.sara.testutil.finishTest( testId, nl.sara.testutil.SUCCESS );
            return;

          }, acl );
        }, 'Lorem ipsum' );
      } );
      
    }, 0, [ prop ] );

  }, acl );
} );


// User 1: give user 2 one 'deny write' ACE followed by an 'allow all', user 2 should now have read and write-acl privileges, but no write privileges
nl.sara.testutil.queueTest( 'Deny user 2 read and write-acl access to file of user 1', function( testId ) {
  // Give user 2 read and write privileges to file
  var allPrivilege = new nl.sara.webdav.Privilege();
  allPrivilege.namespace = 'DAV:';
  allPrivilege.tagname = 'all';
  var allAce = new nl.sara.webdav.Ace();
  allAce.addPrivilege( allPrivilege );
  allAce.grantdeny = nl.sara.webdav.Ace.GRANT;
  allAce.invertprincipal = false;
  allAce.principal = '/system/users/' + username2;
  var writePrivilege = new nl.sara.webdav.Privilege();
  writePrivilege.namespace = 'DAV:';
  writePrivilege.tagname = 'write';
  var writeAce = new nl.sara.webdav.Ace();
  writeAce.addPrivilege( writePrivilege );
  writeAce.grantdeny = nl.sara.webdav.Ace.DENY;
  writeAce.invertprincipal = false;
  writeAce.principal = '/system/users/' + username2;
  var acl = new nl.sara.webdav.Acl();
  acl.addAce( writeAce, 0 );
  acl.addAce( allAce, 1 );
  var client1 = clientUser1();
  client1.acl( basePath() + 'testDir/file.txt', function( status ){
    if ( status !== 200 ) {
      nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to set ACL' );
      return;
    }
    
    // User 2 should have read and write acl, but no write privileges
    // Test this using DAV:current-user-privilege-set)
    var client2 = clientUser2();
    var prop = new nl.sara.webdav.Property();
    prop.namespace = 'DAV:';
    prop.tagname = 'current-user-privilege-set';
    client2.propfind( basePath() + 'testDir/file.txt', function( status, data ) {
      if ( status !== 207 ) {
        nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to read properties' );
        return;
      }
      
      var response = data.getResponse( basePath() + 'testDir/file.txt' );
      var prop = response.getProperty( 'DAV:', 'current-user-privilege-set' );
      var privs = prop.getParsedValue();
      for ( var key in privs ) {
        if (
          (
            ( privs[ key ].namespace !== 'DAV:' ) ||
            (
              ( privs[ key ].tagname !== 'unbind' ) &&
              ( privs[ key ].tagname !== 'write-acl' ) &&
              ( privs[ key ].tagname !== 'read-acl' ) &&
              ( privs[ key ].tagname !== 'read-current-user-privilege-set' )
            )
          ) &&
          (
            ( privs[ key ].namespace !== 'http://beehub.nl/' ) ||
            ( privs[ key ].tagname !== 'read-content' )
          )
        ){
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unexpected privileges returned: ' + privs[ key ].namespace + ' ' + privs[ key ].tagname );
          return;
        }
      }

      // And test this by trying requests GET and ACL should succeed and PUT should be forbidden
      client2.get( basePath() + 'testDir/file.txt', function( status, data ) {
        if ( ( status !== 200 ) || ( data !== 'Lorem ipsum' ) ) {
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to correctly retrieve the file' );
          return;
        }
        
        client2.put( basePath() + 'testDir/file.txt', function( status ) {
          if ( status !== 403 ) {
            nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 should get a 403 (forbidden) return code when attempting to write to the file. Instead received this code: ' + status );
            return;
          }
        
          client2.acl( basePath() + 'testDir/file.txt', function( status ) {
            if ( status !== 200 ) {
              nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 is unable to set ACL' );
              return;
            }

            nl.sara.testutil.finishTest( testId, nl.sara.testutil.SUCCESS );
            return;

          }, acl );
        }, 'Lorem ipsum' );
      } );
      
    }, 0, [ prop ] );

  }, acl );
} );


// User 1: give user 2 one 'allow all' ACE followed by a 'deny write', user 2 should now have all privileges (first match counts)
nl.sara.testutil.queueTest( 'Combine allow all and deny write for user 2 to access to file of user 1', function( testId ) {
  // Give user 2 read and write privileges to file
  var allPrivilege = new nl.sara.webdav.Privilege();
  allPrivilege.namespace = 'DAV:';
  allPrivilege.tagname = 'all';
  var allAce = new nl.sara.webdav.Ace();
  allAce.addPrivilege( allPrivilege );
  allAce.grantdeny = nl.sara.webdav.Ace.GRANT;
  allAce.invertprincipal = false;
  allAce.principal = '/system/users/' + username2;
  var writePrivilege = new nl.sara.webdav.Privilege();
  writePrivilege.namespace = 'DAV:';
  writePrivilege.tagname = 'write';
  var writeAce = new nl.sara.webdav.Ace();
  writeAce.addPrivilege( writePrivilege );
  writeAce.grantdeny = nl.sara.webdav.Ace.DENY;
  writeAce.invertprincipal = false;
  writeAce.principal = '/system/users/' + username2;
  var acl = new nl.sara.webdav.Acl();
  acl.addAce( allAce, 0 );
  acl.addAce( writeAce, 1 );
  var client1 = clientUser1();
  client1.acl( basePath() + 'testDir/file.txt', function( status ){
    if ( status !== 200 ) {
      nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to set ACL' );
      return;
    }
    
    // User 2 should have read and write acl, but no write privileges
    // Test this using DAV:current-user-privilege-set)
    var client2 = clientUser2();
    var prop = new nl.sara.webdav.Property();
    prop.namespace = 'DAV:';
    prop.tagname = 'current-user-privilege-set';
    client2.propfind( basePath() + 'testDir/file.txt', function( status, data ) {
      if ( status !== 207 ) {
        nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to read properties' );
        return;
      }
      
      var response = data.getResponse( basePath() + 'testDir/file.txt' );
      var prop = response.getProperty( 'DAV:', 'current-user-privilege-set' );
      var privs = prop.getParsedValue();
      for ( var key in privs ) {
        if (
          (
            ( privs[ key ].namespace !== 'DAV:' ) ||
            (
              ( privs[ key ].tagname !== 'unbind' ) &&
              ( privs[ key ].tagname !== 'write-acl' ) &&
              ( privs[ key ].tagname !== 'write-content' ) &&
              ( privs[ key ].tagname !== 'read-acl' ) &&
              ( privs[ key ].tagname !== 'read-current-user-privilege-set' )
            )
          ) &&
          (
            ( privs[ key ].namespace !== 'http://beehub.nl/' ) ||
            ( privs[ key ].tagname !== 'read-content' )
          )
        ){
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unexpected privileges returned: ' + privs[ key ].namespace + ' ' + privs[ key ].tagname );
          return;
        }
      }

      // And test this by trying requests GET and ACL should succeed and PUT should be forbidden
      client2.get( basePath() + 'testDir/file.txt', function( status, data ) {
        if ( ( status !== 200 ) || ( data !== 'Lorem ipsum' ) ) {
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to correctly retrieve the file' );
          return;
        }
        
        client2.put( basePath() + 'testDir/file.txt', function( status ) {
          if ( status !== 204 ) {
            nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 should get a 204 (no content) return code when attempting to write to the file. Instead received this code: ' + status );
            return;
          }
        
          client2.acl( basePath() + 'testDir/file.txt', function( status ) {
            if ( status !== 200 ) {
              nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 is unable to set ACL' );
              return;
            }

            nl.sara.testutil.finishTest( testId, nl.sara.testutil.SUCCESS );
            return;

          }, acl );
        }, 'Lorem ipsum' );
      } );
      
    }, 0, [ prop ] );

  }, acl );
} );


// User 2: put deny all privileges on file, user 1 should still have access (due to owner ACE)
nl.sara.testutil.queueTest( 'User 2 cannot deny access to owner of file', function( testId ) {
  // Give user 1 deny all privileges to file
  var allPrivilege = new nl.sara.webdav.Privilege();
  allPrivilege.namespace = 'DAV:';
  allPrivilege.tagname = 'all';
  var allAce = new nl.sara.webdav.Ace();
  allAce.addPrivilege( allPrivilege );
  allAce.grantdeny = nl.sara.webdav.Ace.DENY;
  allAce.invertprincipal = false;
  allAce.principal = '/system/users/' + username1;
  var acl = new nl.sara.webdav.Acl();
  acl.addAce( allAce, 0 );
  var client2 = clientUser2();
  client2.acl( basePath() + 'testDir/file.txt', function( status ){
    if ( status !== 200 ) {
      nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to set ACL' );
      return;
    }
    
    // User 1 should still have all privileges
    // Test this using DAV:current-user-privilege-set)
    var client1 = clientUser1();
    var prop = new nl.sara.webdav.Property();
    prop.namespace = 'DAV:';
    prop.tagname = 'current-user-privilege-set';
    client1.propfind( basePath() + 'testDir/file.txt', function( status, data ) {
      if ( status !== 207 ) {
        nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to read properties' );
        return;
      }
      
      var response = data.getResponse( basePath() + 'testDir/file.txt' );
      var prop = response.getProperty( 'DAV:', 'current-user-privilege-set' );
      var privs = prop.getParsedValue();
      for ( var key in privs ) {
        if (
          (
            ( privs[ key ].namespace !== 'DAV:' ) ||
            (
              ( privs[ key ].tagname !== 'unbind' ) &&
              ( privs[ key ].tagname !== 'write-acl' ) &&
              ( privs[ key ].tagname !== 'write-content' ) &&
              ( privs[ key ].tagname !== 'read-acl' ) &&
              ( privs[ key ].tagname !== 'read-current-user-privilege-set' )
            )
          ) &&
          (
            ( privs[ key ].namespace !== 'http://beehub.nl/' ) ||
            ( privs[ key ].tagname !== 'read-content' )
          )
        ){
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unexpected privileges returned: ' + privs[ key ].namespace + ' ' + privs[ key ].tagname );
          return;
        }
      }

      // And test this by trying requests GET and ACL should succeed and PUT should be forbidden
      client1.get( basePath() + 'testDir/file.txt', function( status, data ) {
        if ( ( status !== 200 ) || ( data !== 'Lorem ipsum' ) ) {
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to correctly retrieve the file' );
          return;
        }
        
        client1.put( basePath() + 'testDir/file.txt', function( status ) {
          if ( status !== 204 ) {
            nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 1 should get a 204 (no content) return code when attempting to write to the file. Instead received this code: ' + status );
            return;
          }
        
          client1.acl( basePath() + 'testDir/file.txt', function( status ) {
            if ( status !== 200 ) {
              nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 1 is unable to set ACL' );
              return;
            }

            nl.sara.testutil.finishTest( testId, nl.sara.testutil.SUCCESS );
            return;

          }, acl );
        }, 'Lorem ipsum' );
      } );
      
    }, 0, [ prop ] );

  }, acl );
} );


// User 1: change ACL on directory; check that the file inherits the ACL correctly and whether DAV:inherited-acl-set is set correctly
nl.sara.testutil.queueTest( 'File should inherit ACL from directory', function( testId ) {
  // Give user 2 read privileges to the directory
  var privilege = new nl.sara.webdav.Privilege();
  privilege.namespace = 'DAV:';
  privilege.tagname = 'read';
  var ace = new nl.sara.webdav.Ace();
  ace.addPrivilege( privilege );
  ace.grantdeny = nl.sara.webdav.Ace.GRANT;
  ace.invertprincipal = false;
  ace.principal = '/system/users/' + username2;
  var acl = new nl.sara.webdav.Acl();
  acl.addAce( ace, 0 );
  var client1 = clientUser1();
  client1.acl( basePath() + 'testDir', function( status ){
    if ( status !== 200 ) {
      nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to set ACL' );
      return;
    }

    // User 2 should have read privileges to the file too
    // Test this using DAV:current-user-privilege-set)
    var client2 = clientUser2();
    var currentUserPrivilegeSetProp = new nl.sara.webdav.Property();
    currentUserPrivilegeSetProp.namespace = 'DAV:';
    currentUserPrivilegeSetProp.tagname = 'current-user-privilege-set';
    var inheritedAclSetProp = new nl.sara.webdav.Property();
    inheritedAclSetProp.namespace = 'DAV:';
    inheritedAclSetProp.tagname = 'inherited-acl-set';
    var aclProp = new nl.sara.webdav.Property();
    aclProp.namespace = 'DAV:';
    aclProp.tagname = 'acl';
    client2.propfind( basePath() + 'testDir/file.txt', function( status, data ) {
      if ( status !== 207 ) {
        nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to read properties' );
        return;
      }

      // Test the current-user-privilege-set
      var response = data.getResponse( basePath() + 'testDir/file.txt' );
      var privs = response.getProperty( 'DAV:', 'current-user-privilege-set' ).getParsedValue();
      for ( var key in privs ) {
        if (
          (
            ( privs[ key ].namespace !== 'DAV:' ) ||
            (
              ( privs[ key ].tagname !== 'unbind' ) &&
              ( privs[ key ].tagname !== 'read-acl' ) &&
              ( privs[ key ].tagname !== 'read-current-user-privilege-set' )
            )
          ) &&
          (
            ( privs[ key ].namespace !== 'http://beehub.nl/' ) ||
            ( privs[ key ].tagname !== 'read-content' )
          )
        ){
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unexpected privileges returned: ' + privs[ key ].namespace + ' ' + privs[ key ].tagname );
          return;
        }
      }

      // Test the inherited-acl-set
      var inheritedAclHrefs = response.getProperty( 'DAV:', 'inherited-acl-set' ).getParsedValue();
      if ( ( inheritedAclHrefs === null ) || ( inheritedAclHrefs.length !== 1 ) || ( inheritedAclHrefs[0] !== basePath() + 'testDir/' ) ) {
        nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'The file should (only) inherit from its direct parent directory (' + basePath() + 'testDir/' + '). Returned value: ' + JSON.stringify( inheritedAclHrefs ) );
        return;
      }

      // Test the acl
      var retrievedAcl = response.getProperty( 'DAV:', 'acl' ).getParsedValue();
      if ( retrievedAcl.getLength() !== 4 ) {
        nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'ACL should be 4 ACE\'s long. Real length: ' + retrievedAcl.getLength() );
        return;
      }
      var priv1 = retrievedAcl.getAce( 0 );
      var priv2 = retrievedAcl.getAce( 1 );
      var priv3 = retrievedAcl.getAce( 2 );
      var priv4 = retrievedAcl.getAce( 3 );
      if (
        ( priv1.principal.namespace !== 'DAV:' ) ||
        ( priv1.principal.tagname !== 'owner' ) ||
        ( priv1.invertprincipal !== false ) ||
        ( priv1.grantdeny !== nl.sara.webdav.Ace.GRANT ) ||
        ( priv1.inherited !== false ) ||
        ( priv1.isprotected !== true ) ||
        ( priv1.getPrivilege( 'DAV:', 'all' ) === undefined ) ||
        ( priv2.principal !== nl.sara.webdav.Ace.ALL ) ||
        ( priv2.invertprincipal !== false ) ||
        ( priv2.grantdeny !== nl.sara.webdav.Ace.GRANT ) ||
        ( priv2.inherited !== false ) ||
        ( priv2.isprotected !== true ) ||
        ( priv2.getPrivilege( 'DAV:', 'unbind' ) === undefined ) ||
        ( priv3.principal !== '/system/users/' + username1 ) ||
        ( priv3.invertprincipal !== false ) ||
        ( priv3.grantdeny !== nl.sara.webdav.Ace.DENY ) ||
        ( priv3.inherited !== false ) ||
        ( priv3.isprotected !== false ) ||
        ( priv3.getPrivilege( 'DAV:', 'all' ) === undefined ) ||
        ( priv4.principal !== '/system/users/' + username2 ) ||
        ( priv4.invertprincipal !== false ) ||
        ( priv4.grantdeny !== nl.sara.webdav.Ace.GRANT ) ||
        ( priv4.inherited !== basePath() + 'testDir/' ) ||
        ( priv4.isprotected !== false ) ||
        ( priv4.getPrivilege( 'DAV:', 'read' ) === undefined )
      ){
        nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'File ACL is not correct' );
        return;
      }

      // And test this by trying requests GET (should succeed) and PUT (should be forbidden)
      client2.get( basePath() + 'testDir/file.txt', function( status, data ) {
        if ( ( status !== 200 ) || ( data !== 'Lorem ipsum' ) ) {
          nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'Unable to correctly retrieve the file' );
          return;
        }

        client2.put( basePath() + 'testDir/file.txt', function( status ) {
          if ( status !== 403 ) {
            nl.sara.testutil.finishTest( testId, nl.sara.testutil.FAILURE, 'User 2 should get a 403 (forbidden) return code when attempting to write to the file. Instead received this code: ' + status );
            return;
          }

          nl.sara.testutil.finishTest( testId, nl.sara.testutil.SUCCESS );
          return;
        }, 'Lorem ipsum' );
      } );

    }, 0, [ currentUserPrivilegeSetProp, inheritedAclSetProp, aclProp ] );

  }, acl );
} );

// End of file