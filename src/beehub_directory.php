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
class BeeHub_Directory extends BeeHub_Resource implements DAV_Collection {


/**
 * Constructor.
 * @param string $path
 */
public function __construct($path) {
  parent::__construct(DAV::slashify($path));
}


//public function user_prop_getcontentlength() { return 4096; }


public function user_prop_getcontenttype() {
  return 'httpd/unix-directory';
  //return BeeHub::best_xhtml_type() . '; charset="utf-8"';
}


protected function user_set_getcontenttype($value) {
  throw new DAV_Status(
    DAV::HTTP_FORBIDDEN,
    DAV::COND_CANNOT_MODIFY_PROTECTED_PROPERTY
  );
}


public function create_member( $name ) {
  return $this->internal_create_member( $name );
}


private function internal_create_member( $name, $collection = false ) {
  $this->assert(DAVACL::PRIV_WRITE);
  $path = $this->path . $name;
  $localPath = BeeHub::localPath( $path );
  $cups = $this->current_user_principals();
  $group = $this->user_prop_group();
  if (!isset($cups[$group]))
    $group = DAV::$REGISTRY->resource($this->user_prop_current_user_principal())->user_prop_group();
  if (file_exists($localPath))
    throw new DAV_Status(DAV::forbidden());
  $result = $collection ? @mkdir($localPath) : touch($localPath);
  if ( !$result )
    throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
  xattr_set( $localPath, rawurlencode(DAV::PROP_GETETAG), BeeHub::ETag(0) );
  xattr_set( $localPath, rawurlencode(DAV::PROP_OWNER  ), $this->user_prop_current_user_principal() );
  xattr_set( $localPath, rawurlencode(DAV::PROP_GROUP  ), $group );
  return DAV::$REGISTRY->resource($path);
}


public function method_COPY( $path ) {
  $parent = BeeHub_Registry::inst()->resource(dirname($path));
  if (!$parent)
    throw new DAV_Status(DAV::HTTP_CONFLICT);
  if (!$parent instanceof BeeHub_Directory)
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  $parent->internal_create_member(basename($path), true);
  foreach(xattr_list($this->localPath) as $xattr)
    if ( !in_array( rawurldecode($xattr), array(
      DAV::PROP_GETETAG,
      DAV::PROP_OWNER,
      DAV::PROP_GROUP,
      DAV::PROP_ACL,
      DAV::PROP_LOCKDISCOVERY
    ) ) )
      xattr_set( $localPath, $xattr, xattr_get( $this->localPath, $xattr ) );
}


public function method_DELETE( $name )
{
  $path = $this->path . $name;
  $localpath = BeeHub::localPath( $path );
  $this->assert(DAVACL::PRIV_WRITE);
  if (is_dir($localpath)) {
    if (!@rmdir($localpath))
      throw new DAV_Status(DAV::HTTP_CONFLICT);
  }
  else {
    if (!@unlink($localpath))
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
  }
  BeeHub_Registry::inst()->forget($path);
}


/**
 * @return string an HTML file
 * @see DAV_Resource::method_GET()
 */
public function method_GET($headers) {
  // We willen hier de client gaan teruggeven:
  $this->assert(DAVACL::PRIV_READ);
  // This was a switch() statement. I hate those. --pieterb
  if ( BeeHub::$CONFIG['webdav_namespace']['homepage'] == $this->path ) {
    $view = new BeeHub_View('homepage.php');
  } else {
    $view = new BeeHub_View('directory.php');
    $view->setVar('directory', $this);
    $members = array();
    # TODO oops, the document isn't generated as a stream? Here, an object is
    # created for each member resource, and stored in memory. This will crash
    # the server for large directories!
    # It would be nicer if these objects were created one at a time, and then
    # forgotten.
    # @see BeeHub::Registry::forget()
    foreach ($this as $member){
      $members[strtolower($member)] = DAV::$REGISTRY->resource($this->path . $member);
    }
    ksort($members, SORT_STRING);
    $view->setVar('members', $members);
  }
  return ((BeeHub::best_xhtml_type() != 'text/html') ? DAV::xml_header() : '' ) . $view->getParsedView();
}


public function method_HEAD() {
  $this->assert(DAVACL::PRIV_READ);
  return array(
    'Content-Type' => BeeHub::best_xhtml_type() . '; charset="utf-8"',
    'Cache-Control' => 'no-cache'
  );
}

/**
 * @param string $name
 * @throws DAV_Status
 */
public function method_MKCOL( $name ) {
  return $this->internal_create_member( $name, true );
}


public function method_MOVE( $member, $destination ) {
  $this->assert(DAVACL::PRIV_WRITE);
  BeeHub_Registry::inst()->resource(dirname($destination))->assert(DAVACL::PRIV_WRITE);
  $localDest = BeeHub::localPath($destination);
  rename(
    BeeHub::localPath( $this->path . $member ),
    $localDest
  );
}


/**
 * @var DirectoryIterator;
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
      !BeeHub_Registry::inst()->resource(
        $this->path . $this->current()
      )->isVisible()
  ) )
    $this->dir->next();
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


