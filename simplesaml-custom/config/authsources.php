<?php

$config = array(

	// This is a authentication source which handles admin authentication.
	'admin' => array(
		// The default is to use core:AdminPassword, but it can be replaced with
		// any authentication source.

		'core:AdminPassword',
	),

  'CUA' => array(
          'pam:PAM'
  ),


	'BeeHub' => array(
	        'saml:SP',

	        // The entity ID of this SP.
	        // Can be NULL/unset, in which case an entity ID is generated based on the metadata URL.
	        'entityID' => NULL,

	        // The entity ID of the IdP this should SP should contact.
	        // Can be NULL/unset, in which case the user will be shown a list of available IdPs.
	        'idp' => 'https://engine.surfconext.nl/authentication/idp/metadata',

	        // The URL to the discovery service.
	        // Can be NULL/unset, in which case a builtin discovery service will be used.
	        'discoURL' => NULL,

	        // The entries below are all OPTIONAL but RECOMMENDED to tell SURFconext
	        // some details about your service and the attributes it requires.
	        'name' => array(
	                'en' => 'BeeHub development site',
	                'nl' => 'BeeHub ontwikkel site',
	        ),

	        'description' => array(
	                'en' => 'BeeHub development is done on this site. If you don\'t know what BeeHub is, or you are not developing it, this is probably not something you want to authenticate for.',
	                'nl' => 'Op deze site werken we aan de ontwikkeling van BeeHub. Als je niet weet wat BeeHub is of als je niet betrokken bent bij de ontwikkeling, dan wil je je hiervoor waarschijnlijk niet authenticeren.',
	        ),
          'OrganizationName' => array(
                  'en' => 'SURFsara',
                  'nl' => 'SURFsara',
          ),
          'OrganizationURL' => array(
                  'en' => 'http://www.surfsara.nl/',
                  'nl' => 'http://www.surfsara.nl/nl',
          ),
          'url' => array(
                  'en' => 'http://beehub.nl/',
                  'nl' => 'http://beehub.nl/',
          ),
	        // We would like to get the mail and displayName attributes
	        'attributes' => array(
	                'urn:mace:dir:attribute-def:mail',
	                'urn:mace:dir:attribute-def:displayName',
                  'urn:mace:terena.org:attribute-def:schacHomeOrganization',
                  'urn:mace:dir:attribute-def:eduPersonAffiliation'
	        ),

	        // But only the mail attribute is strictly required
	        'attributes.required' => array(
	                'urn:mace:dir:attribute-def:mail',
	                'urn:mace:dir:attribute-def:displayName',
                  'urn:mace:terena.org:attribute-def:schacHomeOrganization',
                  'urn:mace:dir:attribute-def:eduPersonAffiliation'
	        ),

	        // Only expose HTTP-POST binding
	        'acs.Bindings' => array (
	                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
	        ),

	        // We want to have a persistent NameID instead of transient in order to be
	        // able to distinguish users on subsequent visits
	        'NameIDPolicy' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',

	        // Sign outgoing and verify incoming SAML messages
	        // 'privatekey' => 'sp.pem',
	        // 'certificate' => 'sp.crt',
	        // 'sign.authnrequest' => TRUE,
	        // 'redirect.sign' => TRUE,
	        // 'redirect.validate' => TRUE,

	        // Limit the SAML IdPs that SURFconext lists in the WAYF to
	        // the following list of IdPs (with entity IDs)
	        //
	        // This is essentially a SP-based access control list for IdPs

	        //'IDPList' => array (
	        //    'https://frkosp.wind.surfnet.nl/simplesamlphp/saml2/idp/metadata.php',
	        //    'https://idp.surfnet.nl',
	        //),

	        // The maximum number of proxies allowed, since SURFconext is
	        // a proxy and the institute may have one we set this to 2.

	        // 'ProxyCount' => 2,
	),
);
