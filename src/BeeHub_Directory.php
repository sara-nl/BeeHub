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
 * Interface to a folder.
 * @package BeeHub
 */
class BeeHub_Directory extends BeeHub_MongoResource implements DAV_Collection {


/**
 * Constructor.
 * @param string $path
 */
public function __construct($path) {
  parent::__construct(DAV::slashify($path));
}


//public function user_prop_getcontentlength() { return 4096; }


public function create_member( $name ) {
  return $this->internal_create_member( $name );
}


private function internal_create_member( $name, $collection = false ) {
  $this->assert(DAVACL::PRIV_WRITE);
  $path = $this->path . $name;
  $localPath = BeeHub::localPath( $path );
  $cups = $this->current_user_principals();

  // Determine the sponsor
  $user = BeeHub::getAuth()->current_user();
  $user_sponsors = $user->prop(BeeHub::PROP_SPONSOR_MEMBERSHIP);
  if (count($user_sponsors) == 0) { // If the user doesn't have any sponsors, he/she can't create files and directories
    throw DAV::forbidden();
  }
  $sponsor = $this->user_prop(BeeHub::PROP_SPONSOR); // The default is the directory sponsor
  if (!in_array($sponsor, $user_sponsors)) { //But a user can only create files sponsored by his own sponsors
    $sponsor = $user->user_prop(BeeHub::PROP_SPONSOR);
  }

  // Create the subdirectory or file
  if (file_exists($localPath))
    throw DAV::forbidden();
  $result = $collection ? @mkdir($localPath) : touch($localPath);
  if ( !$result )
    throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);

  // And set the attributes
  $new_resource = DAV::$REGISTRY->resource($path);
  if ( ! $collection ) {
    $new_resource->user_set( DAV::PROP_GETETAG, BeeHub::ETag() );
  }
  $new_resource->user_set( DAV::PROP_OWNER, $this->user_prop_current_user_principal() );
  $new_resource->user_set( BeeHub::PROP_SPONSOR, $sponsor );
  $new_resource->storeProperties();
  return $new_resource;
}


public function method_COPY( $path ) {
  $parent = DAV::$REGISTRY->resource( dirname( $path ) );
  if (!$parent)
    throw new DAV_Status(DAV::HTTP_CONFLICT, 'Unable to COPY to unexisting collection');
  if (!$parent instanceof BeeHub_Directory)
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  
  // And copy the attributes
  $newResource = $parent->internal_create_member( basename( $path ), true );
  foreach( $this->stored_props as $prop => $value ) {
    if ( !in_array( $prop, array(
          DAV::PROP_GETETAG,
          DAV::PROP_OWNER,
          BeeHub::PROP_SPONSOR,
          DAV::PROP_ACL,
          DAV::PROP_LOCKDISCOVERY
          ) ) ) {
      $newResource->user_set( $prop, $value );
    }
  }
  $newResource->storeProperties();
}


public function method_DELETE( $name )
{
  $path = $this->path . $name;
  
  $resource = DAV::$REGISTRY->resource( $path );
  $resource->assert(DAVACL::PRIV_WRITE);
  
  // Remove the entry from mongoDB too
  $filesCollection = BeeHub::getNoSQL()->selectCollection( 'files' );
  $filesCollection->remove( array( 'path' => $path ) );

  // And then from the filesystem
  $localpath = BeeHub::localPath( $path );
  if (is_dir($localpath)) {
    if (!@rmdir($localpath))
      throw new DAV_Status(DAV::HTTP_CONFLICT, 'Unable to DELETE resource: ' . $name);
  }
  else {
    if (!@unlink($localpath))
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
  }

  DAV::$REGISTRY->forget( $path );
}


/**
 * @return string an HTML file
 * @see DAV_Resource::method_GET()
 */
public function method_GET() {
  $this->assert(DAVACL::PRIV_READ);
  $this->include_view();
}


public function method_HEAD() {
  $retval = parent::method_HEAD();
  $retval['Cache-Control'] = 'no-cache';
  return $retval;
}

/**
 * @param string $name
 * @throws DAV_Status
 */
public function method_MKCOL( $name ) {
  return $this->internal_create_member( $name, true );
}


public function method_MOVE( $member, $destination ) {
  // Get the ACL of the source (including inherited ACE's)
  $sourceAcl = DAV::$REGISTRY->resource( $this->path . $member )->user_prop_acl();

  // Determine if moving is allowed and if so, move the object
  DAV::$REGISTRY->resource( $this->path . $member )->assert( DAVACL::PRIV_WRITE );
  DAV::$REGISTRY->resource( dirname($destination) )->assert( DAVACL::PRIV_WRITE );
  $localDest = BeeHub::localPath($destination);
  rename(
    BeeHub::localPath( $this->path . $member ),
    $localDest
  );
  
  // Then move all properties to the new location
  $filesCollection = BeeHub::getNoSQL()->selectCollection( 'files' );
  $path = DAV::unslashify( $this->path . $member );
  if ( substr( $path, 0, 1 ) === '/' ) {
    $path = substr( $path, 1 );
  }
  $newPath = DAV::unslashify( $destination );
  if ( substr( $newPath, 0, 1 ) === '/' ) {
    $newPath = substr( $newPath, 1 );
  }
  $filesCollection->findAndModify(
    array( 'path' => $path ),
    array( '$set' => array( 'path' => $newPath ) )
  );

  // We need to make sure that the effective ACL at the destination is the same as at the resource
  $destinationAcl = array();
  $inheritedAcl = array();
  $copyInherited = true;
  foreach ( $sourceAcl as $ace ) {
    if ( $ace->protected ) { // Protected ACE's don't require copying; at this moment all resources have the same protected resources
      continue;
    }
    if ( $ace->inherited ) { // Inherited ACE's don't always need to be copied, so let's store them seperately for now
      $ace->inherited= null;
      $inheritedAcl[] = $ace;
    }else{
      // If there is already a 'deny all to everybody' ACE in the ACL, then no need to copy any inherited ACL's
      if ( ( $ace->principal === DAVACL::PRINCIPAL_ALL ) &&
           ! $ace->invert &&
           in_array( DAVACL::PRIV_ALL, $ace->privileges ) &&
           $ace->deny
      ) {
        $copyInherited = false;
      }
      $destinationAcl[] = $ace;
    }
  }

  $destinationResource = DAV::$REGISTRY->resource( $destination );

  // If the inherited ACE's at the destination are the same as at the source, then no need to copy them (for example when moving within the same directory). The effective ACL will still be the same
  if ( $copyInherited ) {
    $oldDestinationAcl = $destinationResource->user_prop_acl();
    $copyInherited = false;
    foreach ( $oldDestinationAcl as $ace ) {
      if ( ! $ace->inherited ) {
        continue;
      }
      if ( ( count( $inheritedAcl) > 0 ) &&
           ( $ace->principal === $inheritedAcl[0]->principal ) &&
           ( $ace->invert === $inheritedAcl[0]->invert ) &&
           ( $ace->deny === $inheritedAcl[0]->deny ) &&
           ( $ace->privileges === $inheritedAcl[0]->privileges )
      ) {
        array_shift( $inheritedAcl );
      }else{
        $copyInherited = true;
        break;
      }
    }
  }

  // If needed; copy the inherited ACE's so we have the complete ACL of the source. And end it with a 'deny all to everybody' ACE so inherited ACE's at the destination don't change the effective ACL
  if ( $copyInherited ) {
    $destinationAcl = array_merge( $destinationAcl, $inheritedAcl );
    $destinationAcl[] = new DAVACL_Element_ace( DAVACL::PRINCIPAL_ALL, false, array( DAVACL::PRIV_ALL ), true, false, null );
  }

  // And store the ACL at the destination
  $destinationResource->user_set( DAV::PROP_ACL, ( $destinationAcl ? DAVACL_Element_ace::aces2json( $destinationAcl ) : null ) );
  $destinationResource->storeProperties();
}


/**
 * @var DirectoryIterator
 */
private $dir = null;


/**
 * @return DirectoryIterator
 */
private function dir() {
  if (is_null($this->dir)) {
    $this->dir = new DirectoryIterator( $this->localPath );
    $this->skipInvalidMembers();
  }
  return $this->dir;
}


private function skipInvalidMembers() {
  while (
    $this->dir()->valid() && (
      $this->dir()->isDot() ||
      !DAV::$REGISTRY->resource(
        $this->path . $this->current()
      )->isVisible()
  ) ) {
    DAV::$REGISTRY->forget(
      $this->path . $this->current()
    );
    $this->dir->next();
  }
}


public function current() {
  $retval = rawurlencode($this->dir()->getFilename());
  if ('dir' == $this->dir()->getType())
    $retval .= '/';
  return $retval;
}
public function key()     { return $this->dir()->key(); }
public function next()    {
  $this->dir()->next();
  $this->skipInvalidMembers();
}
public function rewind()  {
  $this->dir()->rewind();
  $this->skipInvalidMembers();
}
public function valid()   { return $this->dir()->valid(); }


} // class BeeHub_Directory
