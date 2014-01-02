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
 * File documentation (who cares)
 * @package BeeHub
 */

/**
 * A class.
 * @package BeeHub
 *
 */
class BeeHub_File extends BeeHub_XFSResource {

public function __construct ($path) {
  parent::__construct($path);
}


public function user_prop_executable() {
  $retval = $this->user_prop(DAV::PROP_EXECUTABLE);
  return is_null($retval) ? null : (bool)$retval;
}


protected function user_set_executable($value) {
  return $this->user_set(DAV::PROP_EXECUTABLE, is_null($value) ? null : ($value ? '1' : '0') );
}


public function user_prop_getcontentlanguage() {
  return $this->user_prop(DAV::PROP_GETCONTENTLANGUAGE);
}


/**
 * @return void
 * @throws DAV_Status
 */
protected function user_set_getcontentlanguage($value) {
  return $this->user_set(DAV::PROP_GETCONTENTLANGUAGE, $value);
}


public function user_prop_getcontentlength() {
  return $this->stat['size'];
}


public function user_prop_getcontenttype() {
  $type = $this->user_prop(DAV::PROP_GETCONTENTTYPE);
  if (DAV::determine_client() & DAV::CLIENT_GVFS) {
    $parts = explode(';', $type);
    return $parts[0];
  }else{
    return $type;
  }
}


protected function user_set_getcontenttype($type) {
  return $this->user_set(DAV::PROP_GETCONTENTTYPE, $type);
}


public function user_prop_getetag() {
  return $this->user_prop(DAV::PROP_GETETAG);
}


/**
 * @TODO set owner and sponsor correctly!
 */
public function method_COPY( $path ) {
  $this->assert(DAVACL::PRIV_READ);
  $parent = BeeHub_Registry::inst()->resource( dirname( $path ) );
  $parent->assert(DAVACL::PRIV_WRITE);
  $parent->create_member( basename( $path ) );
  $localPath = BeeHub::localPath($path);
  exec( 'cp ' . BeeHub::escapeshellarg($this->localPath) . ' ' . BeeHub::escapeshellarg($localPath) );
  
  // And copy the attributes
  $new_resource = BeeHub_Registry::inst()->resource( $path );
  foreach( $this->stored_props as $prop => $value ) {
    if ( !in_array( $prop, array(
          DAV::PROP_GETETAG,
          DAV::PROP_OWNER,
          BeeHub::PROP_SPONSOR,
          DAV::PROP_ACL,
          DAV::PROP_LOCKDISCOVERY
          ) ) ) {
      $new_resource->user_set( $prop, $value );
    }
  }

  // Determine the sponsor
  $user = BeeHub_Auth::inst()->current_user();
  $user_sponsors = $user->prop(BeeHub::PROP_SPONSOR_MEMBERSHIP);
  if (count($user_sponsors) == 0) { // If the user doesn't have any sponsors, he/she can't create files and directories
    throw DAV::forbidden();
  }
  $sponsor = $parent->prop(BeeHub::PROP_SPONSOR); // The default is the directory sponsor
  if (!in_array($sponsor, $user_sponsors)) { //But a user can only create files sponsored by his own sponsors
    $sponsor = $user->user_prop(BeeHub::PROP_SPONSOR);
  }

  // And set the new properties
  $new_resource->user_set( DAV::PROP_GETETAG, BeeHub_DB::ETag() );
  $new_resource->user_set( DAV::PROP_OWNER, $this->user_prop_current_user_principal() );
  $new_resource->user_set( BeeHub::PROP_SPONSOR, $sponsor );

  $new_resource->storeProperties();
}


public function method_GET() {
  return fopen( $this->localPath , 'r' );
}


public function method_PUT($stream) {
  if (DAV::$PATH === $this->path)
    $this->assert(DAVACL::PRIV_WRITE);
  if ( !($resource = fopen( $this->localPath, 'w' )) )
    throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
  try {
    $size = 0;
    if ( ( $cl = (int)(@$_SERVER['CONTENT_LENGTH']) ) ||
         ( $cl = (int)(@$_SERVER['HTTP_X_EXPECTED_ENTITY_LENGTH']) ) ) {
      # The client has indicated the length of the request entity body:
      $time = time();
      while ( $cl && !feof( $stream ) ) {
        if ( time() - $time > 60 ) {
          set_time_limit(120);
          $time = time();
        }
        $chunk_size = $cl;
        if ( $chunk_size > DAV::$CHUNK_SIZE )
          $chunk_size = DAV::$CHUNK_SIZE;
        $buffer = fread( $stream, $chunk_size );
        $chunk_size = strlen( $buffer );
        if ( $chunk_size !== fwrite( $resource, $buffer ) )
          throw new DAV_Status( DAV::HTTP_INSUFFICIENT_STORAGE );
        $cl -= $chunk_size;
      }
      if ( $cl )
        throw new DAV_Status(
          DAV::HTTP_BAD_REQUEST,
          'Request entity too small'
        );
    } else {
      # The client didn't give us any clue about the request body entity size.
      # Let's make the best of it...
      $time = time();
      while ( true ) {
        if ( time() - $time > 60 ) {
          set_time_limit(120);
          $time = time();
        }
        $buffer = fread( $stream, DAV::$CHUNK_SIZE );
        if ( $buffer === false || $buffer === '' )
          break;
        if ( strlen( $buffer ) !== fwrite( $resource, $buffer ) )
          throw new DAV_Status(DAV::HTTP_INSUFFICIENT_STORAGE);
      }
    }
  }
  catch (DAV_Status $e) {
    fclose($resource);
    unlink($this->localPath);
    throw $e;
  }
  fclose($resource);
  $contenttype = $this->user_prop_getcontenttype();
  if (!$contenttype || 'application/x-empty' === $contenttype) {
    $finfo = new finfo(FILEINFO_MIME);
    // TODO: Shouldn't we call user_set_getcontenttype() here?
    try { $this->set_getcontenttype( $finfo->file( $this->localPath ) ); }
    catch (DAV_Status $e) {}
  }
  $this->user_set( DAV::PROP_GETETAG, BeeHub::ETag() );
  $this->storeProperties();
}


public function method_PUT_range($stream, $start, $end, $total) {
  $this->assert(DAVACL::PRIV_WRITE);
  if ( !($resource = fopen( $this->localPath, 'r+' )) )
    throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
  try {
    if ( 0 !== fseek( $resource, $start, SEEK_SET ) )
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    $size = $end - $start + 1;
    if ( !array_key_exists('CONTENT_LENGTH', $_SERVER) )
      throw new DAV_Status( DAV::HTTP_LENGTH_REQUIRED );
    if ( (int)($_SERVER['CONTENT_LENGTH']) !== $size )
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST,
        'Content-Range and Content-Length are incompatible.'
      );
    while ($size && !feof($stream)) {
      set_time_limit(120); // We keep resetting the time limit to 10 minutes so the script won't get killed during long uploads. This means your minimum connection speed should be DAV::$CHUNK_SIZE / 600 bytes per second
      $buffer = fread($stream, DAV::$CHUNK_SIZE );
      $size -= strlen( $buffer );
      if ( strlen( $buffer ) !== fwrite( $resource, $buffer ) )
        throw new DAV_Status(DAV::HTTP_INSUFFICIENT_STORAGE);
    }
    if ($size)
      throw new DAV_Status(DAV::HTTP_BAD_REQUEST, 'Request entity too small');
    //$buffer = fread( $stream, 1 );
    //if (!feof($stream))
    //  throw new DAV_Status(DAV::HTTP_REQUEST_ENTITY_TOO_LARGE);
  }
  catch (DAV_Status $e) {
    fclose($resource);
    throw $e;
  }
  fclose($resource);
  $this->user_set( DAV::PROP_GETETAG, BeeHub::ETag() );
  $this->storeProperties();
}

} // class BeeHub_File


