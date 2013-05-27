<?php

class sspmod_pam_Auth_Source_PAM extends sspmod_core_Auth_UserPassBase {

  protected function login($username, $password) {
    $status = '';
    if (! pam_auth($username, $password, $status, true)) {
      throw new SimpleSAML_Error_Error('WRONGUSERPASS');
    }
    return array(
        'uid' => array($username)
    );
  }

}
