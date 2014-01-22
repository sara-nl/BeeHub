<?php
/**
 * Contains an abstract test case for database tests
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
 * An abstract test case for database tests
 * @package     BeeHub
 * @subpackage  tests
 */
abstract class BeeHub_Tests_Db_Test_Case extends \PHPUnit_Framework_TestCase {

  public function setUp() {
    $config = \BeeHub::config();
    if ( empty( $config['mysql']['host'] ) ) {
      $this->markTestSkipped( 'No mySQL credentials specified; all tests depending on mySQL are skipped' );
      return;
    }
    setUpDatabase();
    parent::setUp();
    setUp();
  }


  /**
   * Mocks BeeHub_Auth to make it show as if a certain user is logged in
   *
   * @param   string  $path  The path to the user
   * @return  void
   */
  protected function setCurrentUser( $path ) {
    $user = new \BeeHub_User( $path );
    $auth = $this->getMock( '\BeeHub\tests\BeeHub_Auth', array( 'current_user' ), array( new \SimpleSAML_Auth_Simple( 'BeeHub' ) ) );
    $auth->expects( $this->any() )
         ->method( 'current_user' )
         ->will( $this->returnValue( $user ) );
    \BeeHub::setAuth( $auth );
  }

} // Class BeeHub_Tests_Db_Test_Case

// End of file