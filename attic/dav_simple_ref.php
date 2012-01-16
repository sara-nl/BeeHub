<?php

/*·************************************************************************
 * Copyright ©2007-2011 Pieter van Beek <http://pieterjavanbeek.hyves.nl/>
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
 * $Id: dav_request.php 170 2011-01-19 14:15:53Z kobasoft $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package DAV_Server
 */

/**
 * Represents a Simple-ref.
 * 8.3.  URL Handling
 * 
 * URLs appear in many places in requests and responses.
 * Interoperability experience with [RFC2518] showed that many clients
 * parsing Multi-Status responses did not fully implement the full
 * Reference Resolution defined in Section 5 of [RFC3986].  Thus,
 * servers in particular need to be careful in handling URLs in
 * responses, to ensure that clients have enough context to be able to
 * interpret all the URLs.  The rules in this section apply not only to
 * resource URLs in the 'href' element in Multi-Status responses, but
 * also to the Destination and If header resource URLs.
 * 
 * The sender has a choice between two approaches: using a relative
 * reference, which is resolved against the Request-URI, or a full URI.
 * A server MUST ensure that every 'href' value within a Multi-Status
 * response uses the same format.
 * 
 * WebDAV only uses one form of relative reference in its extensions,
 * the absolute path.
 * 
 *    Simple-ref = absolute-URI | ( path-absolute [ "?" query ] )
 *    
 * The absolute-URI, path-absolute and query productions are defined in
 * Sections 4.3, 3.3, and 3.4 of [RFC3986].
 * Within Simple-ref productions, senders MUST NOT:
 * 
 * o  use dot-segments ("." or ".."), or
 * 
 * o  have prefixes that do not match the Request-URI (using the
 *    comparison rules defined in Section 3.2.3 of [RFC2616]).
 *    
 * Identifiers for collections SHOULD end in a '/' character.
 * @author pieterb
 *
 */
class DAV_Simple_ref {
  public $scheme = null;
  public $servername = null;
  public $serverport = null;
  public $path = null;
  public $query = null;
  public $original;
  /**
   * Constructor.
   * @param string $uriref some RFC3986 URI-reference
   * @throws Exception
   */
  public function __construct($uriref) {
    $this->original = $uriref;
    // URI
    if ( preg_match( '@^([a-z][a-z0-9+\\-.]*:)(//[^/?#:]+)(:\\d+)?((?:/[^/?#]*)*)(\\?[^#]*)?$@i', $uriref, $matches ) )
      list( $dummy, $this->scheme, $this->servername, $this->serverport,
            $this->path, $this->query, $this->fragment ) = $matches;
    // relative-ref with path-absolute:
    elseif ( preg_match( '@^(/[^/?#]+(?:/[^/?#]*)*)(\\?[^#]*)?$@' ) )
      list( $dummy, $this->path, $this->query, $this->fragment ) = $matches;
    // relative-ref with path-noscheme or path-empty:
//    elseif ( $allow_relative &&
//             preg_match( '@^([^/?#:]+(?:/[^/?#]*)*)?(?:?([^#]*))?(?:#(.*))?@' ) )
//      list( $dummy, $this->path, $this->query, $this->fragment ) = $matches;
    else throw new Exception('Invalid URI-reference syntax: ' . $uriref);
  }
//  public static $DEFAULT_BASE_URI = null;
//  /**
//   * @return RESTURIRef
//   */
//  public static function default_base_uri() {
//    if (is_null(self::$DEFAULT_BASE_URI)) {
//      $tmp = empty($_SERVER['HTTPS']) ?
//        'http://' : 'https://';
//      $tmp .= $_SERVER['SERVER_NAME'];
//      if ( !empty($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] != 443 or
//            empty($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] != 80 )
//        $tmp .= ":{$_SERVER['SERVER_PORT']}";
//      $request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
//      $tmp .= $request_uri[0];
//      self::$DEFAULT_BASE_URI = new RESTURIRef($tmp);
//    }
//    return self::$DEFAULT_BASE_URI;
//  }
//  /**
//   * @param bool $include_query
//   * @param string|RESTURIRef $baseURI
//   * @return string an RFC3986 absolute-URI, or null on failure.
//   */
//  public function path_absolute($include_query = true, $baseURI = null) {
//    $retval = $this->path;
//    if ( $retval && '/' !== $retval[0] ) {
//      if (is_null($baseURI))
//        $baseURI = self::default_base_uri();
//      elseif ( ! $baseURI instanceof RESTURIRef )
//        $baseURI = new RESTURIRef("$baseURI");
//      $basePath = $baseURI->path_absolute(false);
//      if ('/' !== substr($basePath, -1))
//        $basePath = dirname($basePath) . '/';
//      $retval = $basePath . $retval;
//      $retval = preg_replace('@/+@', '/', $retval);
//      while ( !preg_match( '@^/\\.\\.(?:/|$)@', $retval ) &&
//               preg_match('@^(.*?)/[^/]+/\\.\\.(/:$)@', $path, $matches) ) {
//        $baseURI = dirname($baseURI) . '/';
//        $path = $matches[1];
//      }
//    }
//    if (!isset($servername))
//    if ( $query ) $retval .= $this->query;
//  }
  public function __toString() { return $this->original; }
} // class RESTURIRef


