<?php

/*·************************************************************************
 * Copyright ©2007-2011 Pieter van Beek, Almere, The Netherlands
 * 		    <http://purl.org/net/6086052759deb18f4c0c9fb2c3d3e83e>
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
 * $Id: sd_directory.php 3364 2011-08-04 14:11:03Z pieterb $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package SD
 */

/**
 * Interface to a folder.
 * @package SD
 */
class SD_Directory extends SD_Resource implements DAV_Collection {


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
  //return SD::best_xhtml_type() . '; charset="utf-8"';
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
  $localPath = SD::localPath( $path );
  $cups = $this->current_user_principals();
  $group = $this->user_prop_group();
  if (!isset($cups[$group]))
    $group = DAV::$REGISTRY->resource($this->user_prop_current_user_principal())->user_prop_group();
  if (file_exists($localPath))
    throw new DAV_Status(DAV::forbidden());
  $result = $collection ? @mkdir($localPath) : touch($localPath);
  if ( !$result )
    throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
  xattr_set( $localPath, rawurlencode(DAV::PROP_GETETAG), SD::ETag(0) );
  xattr_set( $localPath, rawurlencode(DAV::PROP_OWNER  ), $this->user_prop_current_user_principal() );
  xattr_set( $localPath, rawurlencode(DAV::PROP_GROUP  ), $group );
  return DAV::$REGISTRY->resource($path);
}


public function method_COPY( $path ) {
  $parent = SD_Registry::inst()->resource(dirname($path));
  if (!$parent)
    throw new DAV_Status(DAV::HTTP_CONFLICT);
  if (!$parent instanceof SD_Directory)
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
  $localpath = SD::localPath( $path );
  $this->assert(DAVACL::PRIV_WRITE);
  if (is_dir($localpath)) {
    if (!@rmdir($localpath))
      throw new DAV_Status(DAV::HTTP_CONFLICT);
  }
  else {
    if (!@unlink($localpath))
      throw new DAV_Status(DAV::HTTP_INTERNAL_SERVER_ERROR);
  }
  SD_Registry::inst()->forget($path);
}


/**
 * @return string an HTML file
 * @see DAV_Resource::method_GET()
 */
public function method_GET() {
  if ( 0 === strpos( $_SERVER['HTTP_USER_AGENT'], 'Mozilla' ) )
    throw new DAV_Status(
      DAV::HTTP_TEMPORARY_REDIRECT,
      DAV::abs2uri('/client/index.html#' . $this->path)
    );
  $this->assert(DAVACL::PRIV_READ);
  $retval = DAV::xml_header() . <<<EOS
<!DOCTYPE html  PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-us">
<head><title>Directory index</title></head>
<body>
EOS;
  if ( '/' != $this->path )
    $retval .= '<p><a href="../">Up one level</a></p>';
  $retval .= '<ul>';
  $members = array();
  foreach ($this as $member) $members[] = $member;
  natcasesort( $members );
  foreach ($members as $member)
    $retval .= "<li><a href=\"{$this->path}{$member}\">" .
    DAV::xmlescape(rawurldecode($member)) . "</a></li>\n";
  $retval .= '</ul></body></html>';
  return $retval;
}

  
public function method_HEAD() {
  $this->assert(DAVACL::PRIV_READ);
  return array('Content-Type' => SD::best_xhtml_type() . '; charset="utf-8"');
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
  SD_Registry::inst()->resource(dirname($destination))->assert(DAVACL::PRIV_WRITE);
  $localDest = SD::localPath($destination);
  rename(
    SD::localPath( $this->path . $member ),
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
      !SD_Registry::inst()->resource(
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

} // class SD_Directory


