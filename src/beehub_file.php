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
  return $this->user_prop(DAV::PROP_GETCONTENTTYPE);
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
  BeeHub_Registry::inst()->resource(dirname($path))->assert(DAVACL::PRIV_WRITE);
  $localPath = BeeHub::localPath($path);
  exec( 'cp --preserve=all ' . BeeHub::escapeshellarg($this->localPath) . ' ' . BeeHub::escapeshellarg($localPath) );
  xattr_remove( $localPath, rawurlencode(DAV::PROP_ACL) );
  xattr_remove( $localPath, rawurlencode(DAV::PROP_LOCKDISCOVERY) );
}


public function method_GET() {
  return fopen( $this->localPath , 'r' );
}


public function method_PUT($stream) {
  if (DAV::$PATH == $this->path)
    $this->assert(DAVACL::PRIV_WRITE);
  if ( !($resource = fopen( $this->localPath, 'w' )) )
    throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
  try {
    $size = 0;
    while (!feof($stream)) {
      $buffer = fread($stream, DAV::$CHUNK_SIZE );
      $size += strlen($buffer);
      if ( strlen( $buffer ) != fwrite( $resource, $buffer ) )
        throw new DAV_Status(DAV::HTTP_INSUFFICIENT_STORAGE);
    }
    if ( isset($_SERVER['CONTENT_LENGTH']) &&
         $size < $_SERVER['CONTENT_LENGTH'] )
      throw new DAV_Status(DAV::HTTP_BAD_REQUEST, 'Request entity too small');
  }
  catch (DAV_Status $e) {
    fclose($resource);
    unlink($this->localPath);
    throw $e;
  }
  fclose($resource);
  $contenttype = $this->user_prop_getcontenttype();
  if (!$contenttype || 'application/x-empty' == $contenttype) {
    $finfo = new finfo(FILEINFO_MIME);
    // TODO: Shouldn't we call user_set_getcontenttype() here?
    try { $this->set_getcontenttype( $finfo->file( $this->localPath ) ); }
    catch (DAV_Status $e) {}
  }
  $this->user_set( DAV::PROP_GETETAG, BeeHub_DB::ETag() );
  $this->storeProperties();
}


public function method_PUT_range($stream, $start, $end, $total) {
  $this->assert(DAVACL::PRIV_WRITE);
  if ( !($stream   = fopen( 'php://input',    'r'  )) ||
       !($resource = fopen( $this->localPath, 'r+' )) )
    throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
  try {
    if ( 0 != fseek( $resource, $start, SEEK_SET ) )
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
    $size = $end - $start + 1;
    while ($size && !feof($stream)) {
      $buffer = fread($stream, $size < DAV::$CHUNK_SIZE ? $size : DAV::$CHUNK_SIZE );
      $size -= strlen( $buffer );
      if ( strlen( $buffer ) != fwrite( $resource, $buffer ) )
        throw new DAV_Status(DAV::HTTP_INSUFFICIENT_STORAGE);
    }
    if ($size)
      throw new DAV_Status(DAV::HTTP_BAD_REQUEST, 'Request entity too small');
    //$buffer = fread( $stream, 1 );
    if (!feof($stream))
      throw new DAV_Status(DAV::HTTP_REQUEST_ENTITY_TOO_LARGE);
  }
  catch (DAV_Status $e) {
    fclose($resource);
    fclose($stream);
    throw $e;
  }
  fclose($resource);
  fclose($stream);
  $this->user_set( DAV::PROP_GETETAG, BeeHub_DB::ETag() );
  $this->storeProperties();
}

} // class BeeHub_File


