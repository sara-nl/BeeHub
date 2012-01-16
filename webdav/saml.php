<?php
$CONF_SIMPLESAMLPHPPATH = 'simplesamlphp';
require_once ( $CONF_SIMPLESAMLPHPPATH . '/lib/_autoload.php');

class SSPAuth {
        private $ssp;

        public function __construct() {
                $this->ssp = new SimpleSAML_Auth_Simple('beedrive');
        }

        public function login() {
                if ($this->isLoggedIn())
                        return;

                $this->ssp->requireAuth();
                $attr = $this->ssp->getAttributes();

                $_SESSION['userId'] = $attr['nameid'][0];
                $_SESSION['userAttr'] = $attr;
                $_SESSION['userDisplayName'] = $attr['urn:mace:dir:attribute-def:displayName'][0];
        }

        function logout($url = NULL) {
		unset($_SESSION['userId']);
                $this->ssp->logout($url);
        }

	private function isLoggedIn() {
		return (isset ($_SESSION['userId']) && isset($_SESSION['userAttr']) && isset ($_SESSION['userDisplayName']));
	}

	public function logout($url = NULL) {
                if ($this->isLoggedIn()) {
                        unset ($_SESSION['userId']);
                        unset ($_SESSION['userAttr']);
                        unset ($_SESSION['userDisplayName']);
                        if ($url !== NULL)
                                header("Location: $url");
                } else {
                        throw new Exception("not logged in");
                }
        }

}
