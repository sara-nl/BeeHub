<?php
/**
 * Contains an abstract test case for database tests
 *
 * Copyright ©2007-2013 SURFsara b.v., Amsterdam, The Netherlands
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
abstract class BeeHub_Tests_Db_Test_Case extends \PHPUnit_Extensions_Database_TestCase {

  static private $connection = null;


  private $dbUnitConnection = null;


  public function setUp() {
    parent::setUp();
    reset_SERVER();
    \DAV::$REGISTRY = \BeeHub_Registry::inst();
    \BeeHub::setAuth( BeeHub_Auth::inst() );
  }

  
  final public function getConnection() {
    if ( \is_null( $this->dbUnitConnection ) ) {
      $config = getConfig();
      if ( \is_null( self::$connection ) ) {
        self::$connection = new \PDO( 'mysql:dbname=' . $config['mysql']['database'] . ';host=' . $config['mysql']['host'], $config['mysql']['username'], $config['mysql']['password'] );
      }
      $this->dbUnitConnection = $this->createDefaultDBConnection( self::$connection, $config['mysql']['database'] );
    }

    return $this->dbUnitConnection;
  }


  public function getDataSet() {
    if ( \file_exists( $this->getDatasetPath() . 'basicDataset.xml' ) ) {
      return $this->createXMLDataSet( $this->getDatasetPath() . 'basicDataset.xml' );
    }else{
      return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
    }
  }


  protected function getDatasetPath() {
    return \dirname( __FILE__ ) . \DIRECTORY_SEPARATOR;
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