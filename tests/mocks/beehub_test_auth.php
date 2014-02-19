<?php
/**
 * Contains class BeeHub_Test_Auth
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
 * A child class of BeeHub_Auth with a public constructor for mocking
 * @package     BeeHub
 * @subpackage  tests
 */
class BeeHub_Auth extends \BeeHub_Auth {

  public function __construct( \SimpleSAML_Auth_Simple $simpleSAML ) {
    parent::__construct( $simpleSAML );
  }

} // Class BeeHub_Test_Auth

// End of file