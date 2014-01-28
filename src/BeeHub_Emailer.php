<?php
/**
 * Contains the BeeHub_Emailer class
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
 * @package BeeHub
 */

/**
 * This class let's you sent e-mail
 *
 * @package BeeHub
 */
class BeeHub_Emailer {

  /**
   * Send an e-mail
   * @param   string|array  $recipients  The recipient or an array of recepients
   * @param   type          $subject     The subject of the message
   * @param   type          $message     The message body
   * @return  void
   */
  public function email($recipients, $subject, $message) {
    if (is_array($recipients)) {
      $recipients = implode(',', $recipients);
    }
    mail($recipients, $subject, $message, 'From: ' . BeeHub::$CONFIG['email']['sender_name'] . ' <' . BeeHub::$CONFIG['email']['sender_address'] . '>', '-f ' . BeeHub::$CONFIG['email']['sender_address']);
  }

} // class BeeHub_Emailer

// End of file