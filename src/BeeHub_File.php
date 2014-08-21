<?php

/*·************************************************************************
 * Copyright ©2007-2014 SARA b.v., Amsterdam, The Netherlands
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
class BeeHub_File extends BeeHub_MongoResource {

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
  return $this->user_prop( DAV::PROP_GETCONTENTLENGTH );
//  return $this->stat['size'];
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


public function method_COPY( $path ) {
  $this->assert( BeeHub::PRIV_READ_CONTENT );
  $this->assert( DAVACL::PRIV_READ_ACL );
  $destinationResource = DAV::$REGISTRY->resource( $path );
  $parent = DAV::$REGISTRY->resource( dirname( $path ) );
  if (!$parent)
    throw new DAV_Status(DAV::HTTP_CONFLICT, 'Unable to COPY to unexisting collection');
  if (!$parent instanceof BeeHub_Directory)
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  if ( $destinationResource instanceof DAVACL_Resource ) {
    $destinationResource->assert( DAVACL::PRIV_WRITE_CONTENT );
    $destinationResource->assert( DAVACL::PRIV_WRITE_ACL );
    $parent->method_DELETE( basename( $path ) );
  }else{
    $parent->assert( DAVACL::PRIV_WRITE_CONTENT );
  }

  // Determine the sponsor
  $user = BeeHub::getAuth()->current_user();
  $user_sponsors = $user->user_prop_sponsor_membership();
  if ( count( $user_sponsors ) === 0 ) { // If the user doesn't have any sponsors, he/she can't create files and directories
    throw DAV::forbidden();
  }

  $localPath = BeeHub::localPath( $path );
  exec( 'cp ' . BeeHub::escapeshellarg( $this->localPath ) . ' ' . BeeHub::escapeshellarg( $localPath ) );
  
  // And copy the attributes
  $new_resource = new BeeHub_File( $path );

  foreach( $this->stored_props as $prop => $value ) {
    if ( !in_array( $prop, array(
          DAV::PROP_OWNER,
          BeeHub::PROP_SPONSOR,
          DAV::PROP_ACL,
          DAV::PROP_GETETAG,
          DAV::PROP_LOCKDISCOVERY
          ) ) ) {
      $new_resource->user_set( $prop, $value );
    }
  }

  $sponsor = $parent->user_prop_sponsor(); // The default is the directory sponsor
  if ( ! in_array( $sponsor, $user_sponsors ) ) { //But a user can only create files sponsored by his own sponsors
    $sponsor = $user->user_prop( BeeHub::PROP_SPONSOR );
  }

  // And set the new properties
  $new_resource->user_set( DAV::PROP_OWNER, $this->user_prop_current_user_principal() );
  $new_resource->user_set( BeeHub::PROP_SPONSOR, $sponsor );
  $new_resource->user_set( DAV::PROP_GETETAG, BeeHub::ETag() );

  $new_resource->storeProperties();
}


public function method_GET() {
  $this->assert( BeeHub::PRIV_READ_CONTENT );
  return fopen( $this->localPath , 'r' );
}


public function method_PUT($stream) {
  // Assert the privileges of the current user
  if ( DAV::getPath() === $this->path ) {
    $this->assert( DAVACL::PRIV_WRITE_CONTENT );
  }

  // Try to open the (local) file for writing
  if ( ! ( $resource = fopen( $this->localPath, 'w' ) ) ) {
    throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
  }

  // Try to write to the file.
  try {
    $size = 0;

    // Do we know how big this file will be?
    if ( ( $cl = (int)(@$_SERVER['CONTENT_LENGTH']) ) ||
         ( $cl = (int)(@$_SERVER['HTTP_X_EXPECTED_ENTITY_LENGTH']) ) ) {
      # The client has indicated the length of the request entity body:
      $time = time();

      // Loop until we reach the end of the request body (or when we have read the indicated length)
      while ( $cl && !feof( $stream ) ) {
        // Make sure the script doesn't timeout
        if ( time() - $time > 60 ) {
          set_time_limit(120);
          $time = time();
        }

        // Determine the size of the chunk we will read
        $chunk_size = $cl;
        if ( $chunk_size > DAV::$CHUNK_SIZE ) {
          $chunk_size = DAV::$CHUNK_SIZE;
        }

        // Read a chunk from the request body and write it to the (local) file
        $buffer = fread( $stream, $chunk_size );
        $chunk_size = strlen( $buffer );
        if ( $chunk_size !== fwrite( $resource, $buffer ) ) {
          throw new DAV_Status( DAV::HTTP_INSUFFICIENT_STORAGE );
        }
        $size += $chunk_size;

        // $cl contains the number of bytes to read: the HTTP 'Content-Length'
        // header minus the number of bytes we have already read
        $cl -= $chunk_size;
      }

      // If $cl is still bigger than 0, that means we've met the end of the body
      // before reading the number of bytes indicated by the HTTP header
      if ( $cl ) {
        throw new DAV_Status( DAV::HTTP_BAD_REQUEST, 'Request entity too small' );
      }
    } else {
      # The client didn't give us any clue about the request body entity size.
      # Let's make the best of it...
      $time = time();

      // We break out of this loop, from within it
      while ( true ) {
        // Make sure the script doesn't timeout
        if ( time() - $time > 60 ) {
          set_time_limit(120);
          $time = time();
        }

        // Read a chunk from the request body ...
        $buffer = fread( $stream, DAV::$CHUNK_SIZE );
        if ( $buffer === false || $buffer === '' ) {
          // If the buffer is empty, we apparantly reached the end of the file
          break;
        }

        // ... and write it to the (local) file
        $bufferSize = strlen( $buffer );
        if ( $bufferSize !== fwrite( $resource, $buffer ) ) {
          throw new DAV_Status(DAV::HTTP_INSUFFICIENT_STORAGE);
        }
        $size += $bufferSize;
      }
    }
  } catch (DAV_Status $e) {
    // If at some point this fails, we will close the file properly before
    // rethrowing the (DAV_Status) exception.
    fclose($resource);
    unlink($this->localPath);
    throw $e;
  }

  // The file is successfully saved, close it and store some metadata
  fclose($resource);
  $contenttype = $this->user_prop_getcontenttype();
  if ( ! $contenttype ) {
    // If we can't determine or set the content type, just ignore this problem
    try {
      $this->set_getcontenttype( BeeHub::determineContentType( $this->path ) );
    } catch ( DAV_Status $e ) {}
  }
  $this->user_set( DAV::PROP_GETCONTENTLENGTH, $size );
  $this->user_set( DAV::PROP_GETETAG, BeeHub::ETag() );
  $this->storeProperties();

  // Reread the file system data
  $this->stat = stat($this->localPath);
}


public function method_PUT_range($stream, $start, $end, $total) {
  // Assert the privileges of the current user
  $this->assert( DAVACL::PRIV_WRITE_CONTENT );

  // Open file for reading and writing
  if ( !( $resource = fopen( $this->localPath, 'r+' ) ) ) {
    throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
  }

  // Try to write to the file.
  try {
    // Go to the point in the file where we want to write
    if ( 0 !== fseek( $resource, $start, SEEK_SET ) ) {
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    }
    
    // Check the HTTP headers; length and range should match
    $sizeLeft = $end - $start + 1;
    $newSize = $start; // The start byte indicates the number of bytes before this ranged PUT
    if ( !array_key_exists( 'CONTENT_LENGTH', $_SERVER ) ) {
      throw new DAV_Status( DAV::HTTP_LENGTH_REQUIRED );
    }
    if ( (int)( $_SERVER['CONTENT_LENGTH'] ) !== $sizeLeft ) {
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST,
        'Content-Range and Content-Length are incompatible.'
      );
    }
    
    // Read the request body and write it to the file
    while ( $sizeLeft && !feof( $stream ) ) {
      // We keep resetting the time limit to 10 minutes so the script won't get killed during long uploads. This means your minimum connection speed should be DAV::$CHUNK_SIZE / 600 bytes per second
      set_time_limit( 120 );
      $buffer = fread( $stream, DAV::$CHUNK_SIZE );
      $bufferSize = strlen( $buffer );
      $sizeLeft -= $bufferSize;
      if ( strlen( $buffer ) !== fwrite( $resource, $buffer ) ) {
        throw new DAV_Status(DAV::HTTP_INSUFFICIENT_STORAGE);
      }
      $newSize += $bufferSize;
    }
    
    // If not the complete indicated sized could be read from the request body, then the request was wrong!
    if ( $sizeLeft ) {
      throw new DAV_Status( DAV::HTTP_BAD_REQUEST, 'Request entity too small' );
    }
  }catch ( DAV_Status $e ) {
    // If at some point this fails, we will close the file properly before
    // rethrowing the (DAV_Status) exception.
    fclose($resource);
    throw $e;
  }

  // The file is successfully saved, close it and store some metadata
  fclose($resource);
  $this->user_set( DAV::PROP_GETETAG, BeeHub::ETag() );
  if ( $newSize > $this->user_prop_getcontentlength() ) {
    // If the new size is not bigger than the old size, then the range was in
    // the middle of the file, and the file size did not change. Else we need to
    // update the filesize
    $this->user_set( DAV::PROP_GETCONTENTLENGTH, $newSize );
  }
  $this->storeProperties();
  $this->stat = stat($this->localPath);
}

} // class BeeHub_File


