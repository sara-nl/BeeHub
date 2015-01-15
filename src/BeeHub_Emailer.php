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

use Zend\Mail;

/**
 * This class let's you sent e-mail
 *
 * @package BeeHub
 */
class BeeHub_Emailer {
  
  /**
   * @var  \Zend\Mail\Transport\TransportInterface  The transport instance used for sending e-mail
   */
  private $emailer;


  /**
   * Prepares the \Zend\Mail module
   */
  public function __construct() {
    $beehubConfig = \BeeHub::config();
    $config = $beehubConfig['email'];
    
    if ( ! empty( $config['host'] ) ) {
      // Configure the transporter for sending through SMTP
      $transport = new Mail\Transport\Smtp();
      
      $emailConfig = array( 'host' => $config['host'] );
      if ( !empty( $config['port'] ) ) {
        $emailConfig['port'] = $config['port'];
      }
      if ( !empty( $config['security'] ) ) {
        $emailConfig['connection_config'] = array();
        $emailConfig['connection_config']['ssl'] = $config['security'];
      }
      if ( !empty( $config['auth_method'] ) ) {
        $emailConfig['connection_class'] = $config['auth_method'];
        if ( !isset( $emailConfig['connection_config'] ) ) {
          $emailConfig['connection_config'] = array();
        }
        $emailConfig['connection_config']['username'] = $config['username'];
        $emailConfig['connection_config']['password'] = $config['password'];
      }
      $options = new Mail\Transport\SmtpOptions( $emailConfig );
      $transport->setOptions( $options );
    }else{
      // Else we use the Sendmail transporter (which actually just uses mail()
      $transport = new Mail\Transport\Sendmail();
    }
    
    $this->emailer = $transport;
  }


  /**
   * Send an e-mail
   * @param   array  $recipients  An array of the recipients. The key represents the e-mail address, the value is the displayname
   * @param   type   $subject     The subject of the message
   * @param   type   $message     The message body
   * @return  void
   */
  public function email( $recipients, $subject, $message ) {
    $beehubConfig = \BeeHub::config();
    $config = $beehubConfig['email'];
    
    $mail = new Mail\Message();
    $mail->setBody( $message )
         ->addTo( $recipients )
         ->setSubject( $subject )
         ->setFrom( $config['sender_address'], $config['sender_name'] )
         ->setEncoding( 'UTF-8' );
    $this->emailer->send( $mail );
  }

} // class BeeHub_Emailer

// End of file