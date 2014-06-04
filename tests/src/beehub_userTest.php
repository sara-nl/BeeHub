<?php
/**
 * Contains tests for the class BeeHub_User
 *
 * Copyright ©2007-2014 SURFsara b.v., Amsterdam, The Netherlands
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
 *
 * @package     BeeHub
 * @subpackage  tests
 */

declare( encoding = 'UTF-8' );
namespace BeeHub\tests;

/**
 * Tests for the class BeeHub_User
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_UserTest extends BeeHub_Tests_Db_Test_Case {

  public function testPassword() {
    $password = 'some password for John';
    $user = new \BeeHub_User( '/system/users/john' );
    $user->set_password( $password );

    $this->assertTrue( $user->check_password( $password ) );
    $this->assertFalse( $user->check_password( $password . 'wrong password' ) );
  }


  public function testPassword_reset_code() {
    $user = new \BeeHub_User( '/system/users/jane' );
    $code = $user->create_password_reset_code();

    $this->assertFalse( $user->check_password_reset_code( $code . 'wrong secret code' ) );
    $this->assertTrue( $user->check_password_reset_code( $code ) );
  }


  public function testCurrent_user_sponsors() {
    $user = new \BeeHub_User( '/system/users/john' );
    $expected = array( '/system/sponsors/sponsor_a', '/system/sponsors/sponsor_b' );
    $this->assertSame( $expected, $user->current_user_sponsors() );
  }


  public function testUser_set_sponsorWithoutSponsors() {
    $user = new \BeeHub_User( '/system/users/jane' );
    
    $this->setExpectedException( '\DAV_Status', null, \DAV::HTTP_CONFLICT );
    $user->user_set_sponsor( \BeeHub::SPONSORS_PATH . 'sponsor_a' );
  }


  public function testUser_get_sponsorWithOneSponsor() {
    $user = new \BeeHub_User( '/system/users/jane' );

    // There can not be a default sponsor; Jane does not have any sponsors at all
    $this->assertNull( $user->user_prop_sponsor() );

    // Give Jane a sponsor, and this should automatically be her default sponsor
    $this->setCurrentUser( '/system/users/john' );
    $sponsor = new \BeeHub_Sponsor( '/system/sponsors/sponsor_a' );
    $sponsor->change_memberships( array( 'jane' ), true, false, true, false );
    $userWithSponsor = \DAV::$REGISTRY->resource( '/system/users/jane' );
    $this->assertSame( '/system/sponsors/sponsor_a', $userWithSponsor->user_prop_sponsor() );

    // Remove the only sponsor Jane has and she should have no default sponsor either
    $sponsor->change_memberships( array( 'jane' ), false, false, false, false );
    $userWithoutSponsor = \DAV::$REGISTRY->resource( '/system/users/jane' );
    $this->assertNull( $userWithoutSponsor->user_prop_sponsor() );
  }


  public function testIs_admin() {
    $this->setCurrentUser( '/system/users/jane' );

    $john = new \BeeHub_User( '/system/users/john' );
    $jane = new \BeeHub_User( '/system/users/jane' );

    $this->assertFalse( $john->is_admin() );
    $this->assertTrue( $jane->is_admin() );
  }


  public function testMethod_GET() {
    $this->setCurrentUser( '/system/users/jane' );
    $expected = array( 'unverified_address' => 'j.doe@mailservice.com' );

    $user = $this->getMock( '\BeeHub_User', array( 'include_view' ), array( '/system/users/jane' ) );
    $user->expects( $this->once() )
        ->method( 'include_view' )
        ->with( $this->equalTo( null ), $this->equalTo( $expected ) );
    $user->method_GET();
  }


  public function testMethod_POST_verifyEmail() {
    $this->setCurrentUser( '/system/users/jane' );
    $_POST['verification_code'] = 'somesecretcode';
    $_POST['password'] = 'password_of_jane';
    $headers = array();

    $user = new \BeeHub_User( '/system/users/jane' );
    $this->assertSame( 'jane.doe@mailservice.com', $user->user_prop( \BeeHub::PROP_EMAIL ) );

    $this->expectOutputRegex( '/https 303 See Other/' );
    $user->method_POST( $headers );
    $this->assertSame( 'j.doe@mailservice.com', $user->user_prop( \BeeHub::PROP_EMAIL ) );
  }


  public function testMethod_POST_newPassword() {
    $this->setCurrentUser( '/system/users/jane' );
    $_POST['new_password'] = 'new password for jane';
    $_POST['password'] = 'password_of_jane';
    $headers = array();

    $user = new \BeeHub_User( '/system/users/jane' );
    $this->assertTrue( $user->check_password( 'password_of_jane' ) );

    $this->expectOutputRegex( '/https 303 See Other/' );
    $user->method_POST( $headers );
    $this->assertTrue( $user->check_password( 'new password for jane' ) );
  }


  public function testProperty_priv_read() {
    if ( ! setUpStorageBackend() ) {
      $this->markTestSkipped( 'No storage backend specified; all tests depending on the storage backend are skipped' );
      return;
    }
    $this->setCurrentUser( '/system/users/jane' );

    $john = new \BeeHub_User( '/system/users/john' );
    $propsJohn = $john->property_priv_read( array( \BeeHub::PROP_EMAIL, \BeeHub::PROP_SURFCONEXT, \BeeHub::PROP_SURFCONEXT_DESCRIPTION, \BeeHub::PROP_X509, \BeeHub::PROP_SPONSOR, \DAV::PROP_GROUP_MEMBERSHIP ) );
    $this->assertFalse( $propsJohn[ \BeeHub::PROP_EMAIL ] );
    $this->assertFalse( $propsJohn[ \BeeHub::PROP_SURFCONEXT] );
    $this->assertFalse( $propsJohn[ \BeeHub::PROP_SURFCONEXT_DESCRIPTION] );
    $this->assertFalse( $propsJohn[ \BeeHub::PROP_X509] );
    $this->assertFalse( $propsJohn[ \BeeHub::PROP_SPONSOR] );
    $this->assertFalse( $propsJohn[ \DAV::PROP_GROUP_MEMBERSHIP] );

    $jane = new \BeeHub_User( '/system/users/jane' );
    $propsJane = $jane->property_priv_read( array( \BeeHub::PROP_EMAIL, \BeeHub::PROP_SURFCONEXT, \BeeHub::PROP_SURFCONEXT_DESCRIPTION, \BeeHub::PROP_X509, \BeeHub::PROP_SPONSOR, \DAV::PROP_GROUP_MEMBERSHIP ) );
    $this->assertTrue( $propsJane[ \BeeHub::PROP_EMAIL ] );
    $this->assertTrue( $propsJane[ \BeeHub::PROP_SURFCONEXT] );
    $this->assertTrue( $propsJane[ \BeeHub::PROP_SURFCONEXT_DESCRIPTION] );
    $this->assertTrue( $propsJane[ \BeeHub::PROP_X509] );
    $this->assertTrue( $propsJane[ \BeeHub::PROP_SPONSOR] );
    $this->assertTrue( $propsJane[ \DAV::PROP_GROUP_MEMBERSHIP] );
  }


  public function testProperty_priv_write() {
    if ( ! setUpStorageBackend() ) {
      $this->markTestSkipped( 'No storage backend specified; all tests depending on the storage backend are skipped' );
      return;
    }
    $this->setCurrentUser( '/system/users/jane' );

    $john = new \BeeHub_User( '/system/users/john' );
    $propsJohn = $john->property_priv_read( array( \BeeHub::PROP_EMAIL, \BeeHub::PROP_SURFCONEXT, \BeeHub::PROP_SURFCONEXT_DESCRIPTION, \BeeHub::PROP_X509, \BeeHub::PROP_SPONSOR, \DAV::PROP_GROUP_MEMBERSHIP ) );
    $this->assertFalse( $propsJohn[ \BeeHub::PROP_EMAIL ] );
    $this->assertFalse( $propsJohn[ \BeeHub::PROP_SURFCONEXT] );
    $this->assertFalse( $propsJohn[ \BeeHub::PROP_SURFCONEXT_DESCRIPTION] );
    $this->assertFalse( $propsJohn[ \BeeHub::PROP_X509] );
    $this->assertFalse( $propsJohn[ \BeeHub::PROP_SPONSOR] );
    $this->assertFalse( $propsJohn[ \DAV::PROP_GROUP_MEMBERSHIP] );

    $jane = new \BeeHub_User( '/system/users/jane' );
    $propsJane = $jane->property_priv_read( array( \BeeHub::PROP_EMAIL, \BeeHub::PROP_SURFCONEXT, \BeeHub::PROP_SURFCONEXT_DESCRIPTION, \BeeHub::PROP_X509, \BeeHub::PROP_SPONSOR, \DAV::PROP_GROUP_MEMBERSHIP ) );
    $this->assertTrue( $propsJane[ \BeeHub::PROP_EMAIL ] );
    $this->assertTrue( $propsJane[ \BeeHub::PROP_SURFCONEXT] );
    $this->assertTrue( $propsJane[ \BeeHub::PROP_SURFCONEXT_DESCRIPTION] );
    $this->assertTrue( $propsJane[ \BeeHub::PROP_X509] );
    $this->assertTrue( $propsJane[ \BeeHub::PROP_SPONSOR] );
    $this->assertTrue( $propsJane[ \DAV::PROP_GROUP_MEMBERSHIP] );
  }


  public function testStoreProperties() {
    $displayname = 'A user';

    $user = new \BeeHub_User( '/system/users/john' );
    $user->method_PROPPATCH( \DAV::PROP_DISPLAYNAME, $displayname );
    $user->storeProperties();

    // Now, if I create a new instance of BeeHub_User for the same user, it should have the properties set
    $userReloaded = new \BeeHub_User( '/system/users/john' );
    $this->assertSame( $displayname, $userReloaded->user_prop( \DAV::PROP_DISPLAYNAME ) );
  }


  public function testStorePropertiesNewEmail() {
    $emailer = $this->getMock( '\BeeHub_Emailer', array( 'email' ) );
    $emailer->expects( $this->once() )
            ->method( 'email' );
    \BeeHub::setEmailer( $emailer );

    $user = new \BeeHub_User( '/system/users/john' );
    $user->method_PROPPATCH( \BeeHub::PROP_EMAIL, 'j.doe@mailservice.com' );
    $user->storeProperties();

    \BeeHub::setEmailer( new \BeeHub_Emailer() );
  }


  public function testUser_prop_acl_internal() {
    $user = new \BeeHub_User( '/system/users/john' );
    $acl = $user->user_prop_acl_internal();
    $expected = array(
      new \DAVACL_Element_ace( \DAVACL::PRINCIPAL_SELF, false, array( \DAVACL::PRIV_READ, \DAVACL::PRIV_WRITE ), false, true ),
      new \DAVACL_Element_ace( \DAVACL::PRINCIPAL_AUTHENTICATED , false, array( \DAVACL::PRIV_READ ), false, true )
    );
    $this->assertEquals( $expected, $acl );
  }


  public function testUser_prop_group_membership() {
    $expected = array( '/system/groups/foo', '/system/groups/bar', '/system/groups/admin' );
    $user = new \BeeHub_User( '/system/users/john' );
    $this->assertSame( $expected, $user->user_prop_group_membership() );
  }


  public function testUser_propname() {
    $user = new \BeeHub_User( '/system/users/jane' );
    $this->assertSame( \BeeHub::$USER_PROPS, $user->user_propname() );
  }


  public function testUser_set() {
    $displayname = 'some displayname';
    $user = new \BeeHub_User( '/system/users/jane' );

    $user->user_set( \DAV::PROP_DISPLAYNAME, $displayname );
    $this->assertSame( $displayname, $user->user_prop( \DAV::PROP_DISPLAYNAME ) );

    $this->setExpectedException( 'DAV_Status', null, \DAV::HTTP_BAD_REQUEST );
    $user->user_set( \BeeHub::PROP_EMAIL, 'invalid e-mail address' );
  }


  public function testVerify_email_address() {
    $user = new \BeeHub_User( '/system/users/jane' );
    $this->assertSame( 'jane.doe@mailservice.com', $user->user_prop( \BeeHub::PROP_EMAIL ) );
    $this->assertFalse( $user->verify_email_address( 'The wrong e-mail verification code' ) );
    $this->assertTrue( $user->verify_email_address( 'somesecretcode' ) );
    $this->assertSame( 'j.doe@mailservice.com', $user->user_prop( \BeeHub::PROP_EMAIL ) );
  }

} // class BeeHub_UserTest

// End of file