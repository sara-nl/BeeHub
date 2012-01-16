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
 * $Id: dav_server.php 170 2011-01-19 14:15:53Z kobasoft $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package DAV_Server
 */

/**
 * The DAV server.
 * @todo Documentation
 * @package DAV_Server
 */
class DAV_Server {


/**
 * COPY wrapper
 * @param DAV_Resource $resource
 * @return void
 * @throws DAV_Status
 */
private function method_COPY( $resource )
{
  if (!isset($_SERVER['HTTP_DESTINATION']))
    throw new DAV_Status(
      REST::HTTP_BAD_REQUEST,
      'Missing required Destination: header'
    );

  if ( $resource instanceof DAV_Collection )
    $_SERVER['HTTP_DESTINATION'] =
      DAV::slashify($_SERVER['HTTP_DESTINATION']);
  // The next three lines are there to make the litmus test succeed. The author
  // of litmus had eir own doubts wether this is actually desirable behaviour,
  // but chose to require this behaviour anyway:
  else
    $_SERVER['HTTP_DESTINATION'] =
      DAV::unslashify($_SERVER['HTTP_DESTINATION']);
  
  $destination = DAV::url2path( $_SERVER['HTTP_DESTINATION'], false );
  $overwrite = isset( $_SERVER['HTTP_OVERWRITE'] )
    ? ( $_SERVER['HTTP_OVERWRITE'] == 'F' ? false : true )
    : true;
    
  $depth = ( isset( $_SERVER['HTTP_DEPTH'] ) &&
             $_SERVER['HTTP_DEPTH'] == DAV::DEPTH_0 )
    ? DAV::DEPTH_0
    : DAV::DEPTH_INF;
    
  if ( $depth == DAV::DEPTH_INF &&
       $resource instanceof DAV_Collection )
    $result = $resource->method_COPY_recursive(
      $destination,
      $overwrite,
      DAV::$PATH
    );
  else
    $result = ($destination[0] == '/')
      ? $resource->method_COPY( $destination, $overwrite )
      : $resource->method_COPY_external( $destination , $overwrite );
  if ( $result === true )
    REST::header( array( 'status' => REST::HTTP_NO_CONTENT ) );
  elseif ( $result === false )
    REST::header( array(
      'status' => REST::HTTP_CREATED,
      'Location' => $_SERVER['HTTP_DESTINATION']
    ));
  else
    throw new DAV_Status(REST::HTTP_INTERNAL_SERVER_ERROR, 'on line ' . __LINE__);
}


/**
 * DELETE wrapper
 * @param DAV_Resource $resource
 * @return void
 * @throws DAV_Status
 */
private function method_DELETE( $resource ) {
  if ( $resource instanceof DAV_Collection )
    $resource->method_DELETE_recursive( DAV::$PATH );
  else
    $resource->method_DELETE();
  REST::header(array(
    'status' => REST::HTTP_NO_CONTENT
  ));
}


/**
 * HEAD wrapper
 * @param DAV_Resource $resource
 * @return void
 * @throws DAV_Status
 */
private function method_HEAD( $resource )
{
  $headers = array();
  $resource->method_HEAD( $headers );
  self::setDefaultHeaders( $resource, $headers );
  REST::header($headers);
}


/**
 * Enter description here...
 * @param DAV_Resource $resource
 * @param string|null $member
 * @return void
 * @throws DAV_Status
 */
private function method_LOCK( $resource, $member ) {
  $lockinfo = new DAV_Request_LOCK();
  $lock_token = null;

  if ( is_null( $lockinfo->lockscope ) )
  {
    // Lock refresh!
    if ( $member ) 
      throw new DAV_Status( REST::HTTP_NOT_FOUND );
    if ( !isset( $_SERVER['HTTP_IF'] ) ||
         !preg_match(
           '@^\\s*(<[^\\s>]+>)?\\s*\\(\\s*<([^\\s>])>\\s*\\)\\s*$@',
           $_SERVER['HTTP_IF'],
           $matches
         ) )
      throw new DAV_Status(
        REST::HTTP_BAD_REQUEST,
        'If: header invalid for lock refresh'
      );
    // @todo this can't be right. $matches[1] isn't an array, I think...
    if (( $path = $matches[1] )) {
      if ( $path[0] == '/' )
        $path = REST::urlbase() . $path;
      $path = DAV::url2path($path);
      if ( $path != DAV::$PATH )
        throw new DAV_Status(
          REST::HTTP_BAD_REQUEST,
          "Unexpected path <{$path}> in If: header"
        );
    }

    $resource->method_LOCK_refresh(
      $matches[2],
      $lockinfo->timeout
    );
  } // Lock refresh

  elseif ( is_null($member) and
           $resource instanceof DAV_Collection and
           $lockinfo->depth == DAV::DEPTH_INF ) {
    // A deep lock on a directory
    $lock_token = $resource->method_LOCK_recursive(
      $lockinfo->lockscope,
      $lockinfo->timeout,
      $lockinfo->owner
    );
  }
  
  elseif (is_null($member))
    // A normal lock on a resource
    $lock_token = $resource->method_LOCK(
      $lockinfo->lockscope,
      $lockinfo->timeout,
      $lockinfo->owner
    );
    
  else
    // A lock on an unmapped resource
    $lock_token = $resource->method_LOCK_unmapped(
      $member,
      $lockinfo->lockscope,
      $lockinfo->timeout,
      $lockinfo->owner
    );
  
  $headers = array( 'Content-Type' => 'application/xml; charset=UTF-8' );
  if ($lock_token !== null)
    $headers['Lock-Token'] = $lock_token;

  if (!is_null($member)) {
    $resource = $this->resource(DAV::$PATH);
    if ( is_null($resource) )
      throw new DAV_Status();
    $headers['status'] = REST::HTTP_CREATED;
    $headers['Location'] = DAV::path2url( DAV::$PATH );
  }
  
  REST::header($headers);
  echo DAV::xml_header() .
    "<D:prop xmlns:D=\"DAV:\">\n<D:lockdiscovery>\n" .
    $resource->lockdiscovery() .
    "</D:lockdiscovery>\n</D:prop>\n";
}


/**
 * MKCOL wrapper
 * @param DAV_Collection $collection
 * @param string $member without a trailing slash
 * @return void
 * @throws DAV_Status
 */
private function method_MKCOL( $collection, $member )
{
  if ( @$_SERVER['CONTENT_LENGTH'] ||
       'chunked' == @$_SERVER['HTTP_TRANSFER_ENCODING'] )
    throw new DAV_Status(REST::HTTP_UNSUPPORTED_MEDIA_TYPE);
  $collection->method_MKCOL( $member );
  $url = DAV::path2url( DAV::$PATH );
  $headers = array(
    'status' => REST::HTTP_CREATED,
    'Location' => $url
  );
  REST::header($headers);
}


/**
 * MOVE wrapper
 * @param DAV_Resource $resource
 * @return void
 * @throws DAV_Status
 */
private function method_MOVE( $resource )
{
  if (!isset($_SERVER['HTTP_DESTINATION']))
    throw new DAV_Status(
      REST::HTTP_BAD_REQUEST, 'Missing required Destination: header'
    );
    
  if ( $resource instanceof DAV_Collection )
    $_SERVER['HTTP_DESTINATION'] =
      DAV::slashify($_SERVER['HTTP_DESTINATION']);
  // The next three lines are there to make the litmus test succeed. The author
  // of litmus had eir own doubts wether this is actually desirable behaviour,
  // but chose to require this behaviour anyway:
  else
    $_SERVER['HTTP_DESTINATION'] =
      DAV::unslashify($_SERVER['HTTP_DESTINATION']);
  
  $destination = DAV::url2path( $_SERVER['HTTP_DESTINATION'], false );
  $overwrite = isset( $_SERVER['HTTP_OVERWRITE'] )
    ? ( $_SERVER['HTTP_OVERWRITE'] == 'F' ? false : true )
    : true;
  
  if ( $resource instanceof DAV_Collection )
    $result = $resource->method_MOVE(
      $destination, $overwrite, DAV::$PATH
    );
  else
    $result = $resource->method_MOVE( $destination, $overwrite );
    
  if ( $result === true )
    REST::header( array( 'status' => REST::HTTP_NO_CONTENT ) );
  elseif ( $result === false )
    REST::header( array(
      'status' => REST::HTTP_CREATED,
      'Location' => $_SERVER['HTTP_DESTINATION']
    ));
  else
    throw new DAV_Status();
}


/**
 * OPTIONS method handler.
 * The OPTIONS method handler creates a valid OPTIONS reply including Dav:
 * and Allowed: headers. For the motivation for not requiring authorization
 * for OPTIONS requests on / see http://pear.php.net/bugs/bug.php?id=5363
 * @param DAV_Resource $resource
 * @return void
 * @throws DAV_Status
 */
private function method_OPTIONS( $resource )
{
  $headers = array(
    'MS-Author-Via' => 'DAV',
    'Content-Length' => 0,
    'DAV' => '1,2,3',
    'Accept-Ranges' => 'bytes',
    'Allow' => 'OPTIONS,GET,HEAD,POST,PUT,DELETE,PROPFIND,PROPPATCH,MKCOL,COPY,MOVE,LOCK,UNLOCK',
    'status' => REST::HTTP_OK, // Should be 204, but Microsoft Corp. doesn't like that:
  );
  REST::header( $headers );
}

  
/**
 * @param DAV_Resource $resource
 * @return void
 * @throws DAV_Status
 */
private function method_POST( $resource ) {
  $headers = array();
  $result = $resource->method_POST( $headers );
  if (is_resource($result)) {
    REST::header($headers);
    fpassthru($result);
    fclose($result);
  } elseif (is_null($result)) {
    if ( !isset($headers['status']) ||
         substr( $headers['status'], 0, 3 ) == '200' )
      $headers['status'] = REST::HTTP_NO_CONTENT;
    REST::header($headers);
  } else {
    $headers['Content-Length'] = strlen("$result");
    REST::header($headers);
    echo "$result";
  }
}


/**
 * @param DAV_Resource $resource
 * @throws DAV_Status
 * @todo check how this method and DAV_Resource::method_PROPPATCH work together.
 */
private function method_PROPPATCH( $resource )
{
  $proppatch = new DAV_Request_PROPPATCH();
  try {
    $result = $resource->method_PROPPATCH( $proppatch->props );
  }
  catch (DAV_Status $e) {
//    $m = new DAV_Multistatus();
//    $m->addStatus(DAV::$PATH, $e);
    throw $e;
  }
  
  // Otherwise, an array of status codes is returned:
  $props = new DAV_Props;
  $failed = count($result) > 0;
  foreach ( array_keys( $proppatch->props ) as $prop ) {
    $props->setProperty( $prop );
    if ( isset( $result[$prop] ) )
      $props->setStatus( $prop, $result[$prop] );
    elseif ( $failed )
      $props->setStatus( $prop, '424' ); // Failed Dependency
  }
  self::props( array( DAV::$PATH => $props ), true );
}


/**
 * @param DAV_Resource $resource
 * @param string $member
 * @throws DAV_Status
 */
private function method_PUT( $resource, $member ) {
  // Check for unknown Content-* headers. According to RFC2616, PUT requests
  // with unknown Content-* headers must result in 5xx Not Implemented.
  $known_content_headers = array(
    'encoding' => 1,
    'language' => 1,
    'length'   => 1,
    'md5'      => 1,
    'range'    => 1,
    'type'     => 1,
  );
  foreach (apache_request_headers() as $header => $value) {
    $header = strtolower($header);
    if ( substr( $header, 0, 8 ) == 'content-' &&
         !isset( $known_content_headers[ substr( $header, 8 ) ] ) )
      throw new DAV_Status(
        REST::HTTP_NOT_IMPLEMENTED,
        "Unknown header $header"
      );
  }
  
  $headers = array();
  if ( is_null($member) ) {
    if (isset($_SERVER['HTTP_CONTENT_RANGE'])) {
      $content_range = DAV_Headers::content_range_header($_SERVER['HTTP_CONTENT_RANGE']);
      $resource->method_PUT_range(
        $headers,
        $content_range['start'],
        $content_range['end'],
        $content_range['total']
      );
    }
    elseif (strstr(@$_SERVER['CONTENT_TYPE'], 'multipart/byteranges') !== false) {
      throw new DAV_Status(
        REST::HTTP_NOT_IMPLEMENTED,
        "multipart/byteranges aren't yet implemented"
      );
    }
    else
      $resource->method_PUT( $headers );
  }
  else {
    if (isset($_SERVER['HTTP_CONTENT_RANGE']))
      throw new DAV_Status(REST::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE);
    $headers['status'] = REST::HTTP_CREATED;
    $headers['Location'] = DAV::path2url(DAV::$PATH);
    $resource->method_PUT( $headers, $member );
  }
  REST::header($headers);
}


/**
 * @param DAV_Resource $resource
 * @throws DAV_Status
 */
private function method_UNLOCK( $resource ) {
  if (!isset($_SERVER['HTTP_LOCK_TOKEN']))
    throw new DAV_Status(
      REST::HTTP_BAD_REQUEST,
      'Missing required Lock-Token: header'
    );
  $token = $_SERVER['HTTP_LOCK_TOKEN'];
  if (preg_match('/^<([^\\s>]+)>$/', $token, $matches))
    $token = $matches[1];
  $resource->method_UNLOCK( $token );
  REST::header( array( 'status' => REST::HTTP_NO_CONTENT ) );
}


} // class DAV_Server


