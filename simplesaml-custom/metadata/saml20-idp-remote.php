<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://rnd.feide.no/content/idp-remote-metadata-reference
 */

$metadata['https://engine.surfconext.nl/authentication/idp/metadata'] = array (
  'name' => array(
        'en' => 'SURFconext',
  ),
  'SingleSignOnService' => 'https://engine.surfconext.nl/authentication/idp/single-sign-on',
  'certFingerprint'     => array('a36aac83b9a552b3dc724bfc0d7bba6283af5f8e'),
);
