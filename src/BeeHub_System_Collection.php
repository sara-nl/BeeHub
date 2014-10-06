<?php

/*·************************************************************************
 * Copyright ©2007-2012 SARA b.v., Amsterdam, The Netherlands
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
 **************************************************************************/

/**
 * The system directory (/system/) is a virtual collection
 * @package BeeHub
 */

/**
 * Interface to the system folder.
 * @package BeeHub
 */
class BeeHub_System_Collection extends BeeHub_Directory {


  public function user_prop_acl_internal() {
    return array(
      new DAVACL_Element_ace(
        DAVACL::PRINCIPAL_ALL, false, array(
          DAVACL::PRIV_READ
        ), false, true
      )
    );
  }


  public function method_GET() {
    $this->assert( BeeHub::PRIV_READ_CONTENT );

    // You can request the POST authentication code through the system
    // collection with a 'POST_auth_code' query field set. However, this is only
    // allowed when using HTTPS
    if ( isset( $_GET['POST_auth_code'] ) ) {
      return BeeHub::getAuth()->getPostAuthCode();
    }

    $this->include_view();
  }


} // class BeeHub_System_Collection
