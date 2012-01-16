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
 * $Id: dav_collection_old.php 170 2011-01-19 14:15:53Z kobasoft $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package DAV_Server
 */

/**
 * Abstract base class for all collections.
 * This base class implements the Iterator interface
 * @package DAV_Server
 */
abstract class DAV_Collection extends DAV_Resource implements Iterator {
const RESOURCETYPE = '<D:collection/>';


public function user_prop_resourcetype() {
  return DAV_Collection::RESOURCETYPE;
}
  

public function user_propname() {
  $retval = parent::user_propname();
  $retval[DAV::PROP_RESOURCETYPE] = true;
  return $retval;
}


/*
 * @see Iterator::current()
 * @return string the name of the current member.
 */
//public function current();
/*
 * @see Iterator::key()
 * @return scalar
 */
//public function key();
/*
 * @see Iterator::next()
 * @return void
 */
//public function next();
/*
 * @see Iterator::rewind()
 * @return void
 */
//public function rewind();
/*
 * @see Iterator::valid()
 * @return boolean
 */
//public function valid();


/*
 * @return string some HTML
 * @see DAV_Collection::method_GET()
 */
//public function toHTML() {
//  $retval = '';
//  if (strlen(DAV::$PATH) > 1)
//    $retval .= '<p><a href="../">Up one level</a></p>';
//  $retval .= '<ul>';
//  foreach ($this as $member)
//    $retval .= '<li><a href="' . DAV::path2url(
//        DAV::$PATH . $member
//      ) . '">' . htmlspecialchars($member, ENT_NOQUOTES) . "</a></li>\n";
//  $retval .= '</ul>';
//  return $retval;
//}


/**
 * Handle a recursive COPY request.
 * This method is called by DAV_Server when the client requests a recursive
 * COPY of this resource. This default implementation iterates over all the
 * members of the collection, invoking {@link DAV_Resource::method_COPY() } or
 * {@link method_COPY_recursive() } as appropriate.
 * 
 * For the copy of this collection itself, {@link DAV_Resource::method_COPY()
 * $this->method_COPY() } is called. Probably, you want to override that method,
 * not this one.
 * 
 * If you think you can do better than this default implementation, feel free to
 * override. But be sure to know what you're doing!
 * @param string $destination
 * @param bool $overwrite
 * @param string $path the path of $this resource
 * @return bool|null true on overwrite, false on new, null on error
 * @throws DAV_Status, DAV_Multistatus
 */
public function method_COPY_recursive($destination, $overwrite, $path)
{
  $multistatus = new DAV_Multistatus();
  
  // This might generate an exception, but that's OK:
  $retval = ($destination[0] == '/')
    ? $this->method_COPY( $destination, $overwrite )
    : $this->method_COPY_external( $destination, $overwrite );

  foreach ( $this as $member ) {
    try {
      $deeppath = DAV::slashify($path) . $member;
      $deepdest = DAV::slashify($destination) . (
        $destination[0] == '/' ? $member : DAV::urlencode($member)
      );
      $resource = DAV_Server::inst()->resource($deeppath);
      if (!$resource)
        $multistatus->addStatus(
          $deeppath, new DAV_Status(
            REST::HTTP_INTERNAL_SERVER_ERROR,
            "Resource not found: $deeppath"
          )
        );
      elseif ( $resource instanceof DAV_Collection )
        $resource->method_COPY_recursive( $deepdest, $overwrite, $deeppath );
      elseif ($destination[0] == '/')
        $resource->method_COPY( $deepdest, $overwrite );
      else
        $resource->method_COPY_external( $deepdest, $overwrite );
    }
    catch (DAV_Status $e) {
      $multistatus->addStatus($deeppath, $e);
    }
    catch (DAV_Multistatus $e) {
      $multistatus->mergeWith($e);
    }
  }
  if (count($multistatus->statuses()))
    throw $multistatus;
    
  return $retval;
}


/**
 * Handle a DELETE request.
 * This method is called by DAV_Server when the client requests a DELETE of this
 * collection resource. This default implementation iterates over all the
 * members of the collection, invoking {@link DAV_Resource::method_DELETE() } or
 * {@link method_DELETE_recursive() } as appropriate.
 * 
 * For the deletion of this collection itself, {@link DAV_Resource::method_DELETE()
 * $this->method_DELETE() } is called. Probably, you want to override that
 * method, not this one.
 * 
 * If you think you can do better than this default implementation, feel free to
 * override. But be sure to know what you're doing!
 * @param string $path the path to be deleted.
 * @return void
 * @throws DAV_Multistatus, DAV_Status
 */
public function method_DELETE_recursive($path)
{
  $multistatus = new DAV_Multistatus();
  foreach ( $this as $member ) {
    try {
      $deeppath = DAV::slashify($path) . $member;
      $resource = DAV_Server::inst()->resource($deeppath);
      if (!$resource)
        $multistatus->addStatus(
          $deeppath, new DAV_Status(
            REST::HTTP_INTERNAL_SERVER_ERROR,
            "Resource not found: $deeppath"
          )
        );
      elseif ( $resource instanceof DAV_Collection )
        $resource->method_DELETE_recursive($deeppath);
      else
        $resource->method_DELETE();
    }
    catch (DAV_Status $e) {
      $multistatus->addStatus($deeppath, $e);
    }
    catch (DAV_Multistatus $e) {
      $multistatus->mergeWith($e);
    }
  }
  if (count($multistatus->statuses()))
    throw $multistatus;
  $this->method_DELETE(); // Might throw an exception, which is OK.
}


/**
 * @return string an HTML file
 * @see DAV_Resource::method_GET()
 */
public function method_GET( &$headers ) {
  $retval = DAV::xml_header() . <<<EOS
<!DOCTYPE html  PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-us">
<head><title>Directory index</title></head>
<body>
EOS;
  if (strlen(DAV::$PATH) > 1)
    $retval .= '<p><a href="../">Up one level</a></p>';
  $retval .= '<ul>';
  foreach ($this as $member)
    $retval .= '<li><a href="' . DAV::path2url(
        DAV::$PATH . $member
      ) . '">' . htmlspecialchars($member, ENT_NOQUOTES) . "</a></li>\n";
  $retval .= '</ul></body></html>';
  return $retval;
}


/**
 * @param int $scope
 * @param int $timeout 0 means Infinite
 * @param string $owner an XML fragment.
 * @return string a lock token string
 * @throws DAV_Multistatus Depending on the requested lock depth.
 * Sec.9.10.6 mentions status 423 (Locked) potentially
 * with 'no-conflicting-lock' precondition code.
 */
public function method_LOCK_recursive( $scope, $timeout, $owner ) {
  $multistatus = new DAV_Multistatus();
  $multistatus->addStatus(
    DAV::$PATH, new DAV_Status(
      DAV_Request::$PARANOID ?
      REST::HTTP_CONFLICT :
      REST::HTTP_FORBIDDEN
    )
  );
  throw $multistatus;
}


/**
 * @param string $member
 * @param int $scope
 * @param int $timeout 0 means Infinite
 * @param string $owner an XML fragment.
 * @return string a lock token string
 * @throws DAV_Multistatus Sec.9.10.6 mentions the following statuses:
 * - 412 (Precondition Failed), with a 'lock-token-matches-request-uri'
 *   precondition code. The Request-URI did not fall within the scope of the
 *   lock identified by the token. The lock may have a scope that does not
 *   include the Request-URI, or the lock could have disappeared, or the
 *   token may be invalid.
 * - 423 (Locked) potentially with 'no-conflicting-lock' precondition code
 */
public function method_LOCK_unmapped( $member, $scope, $timeout, $owner ) {
  throw new DAV_Status( DAV_Request::$PARANOID ? REST::HTTP_CONFLICT : REST::HTTP_FORBIDDEN );
}


/**
 * @param string $member without trailing slash
 * @return void
 * @throws DAV_Status Sec.9.3.1 mentions the following status codes:
 * - 403 Forbidden - the server doesn't allow the creation of collections at
 *   the given location
 * - 403 Forbidden - the parent collection cannot accept members
 * - 409 Conflict - intermediate collections are missing
 * - 415 Unsupported Media Type - the client sent a request body that the
 *   server didn't understand
 * - 507 Insufficient Storage
 */
public function method_MKCOL( $member ) {
  throw new DAV_Status( DAV_Request::$PARANOID ? REST::HTTP_CONFLICT : REST::HTTP_FORBIDDEN );
}


/**
 * Handle the MOVE request.
 * @param string $destination the destination path
 * @param bool $overwrite
 * @param string $source the source path.
 * @return bool true if the destination was overwritten, false if it was newly
 * created
 * @throws DAV_Status|DAV_Multistatus Sec.9.8.5 mentions the following status codes:
 * - 403 Forbidden - also applicable if source and destination are equal,
 *   but this case is automatically handled for you.
 * - 409 Conflict - one or more intermediate collections are missing at the
 *   destination.
 * - 412 Precondition Failed - also applicable if the Overwrite: header was
 *   set to 'F' and the destination resource was mapped.
 * - 423 Locked - The destination (or members therein) are locked
 * - 507 Insufficient Storage
 */
public function method_MOVE( $destination, $overwrite, $path )
{
  $multistatus = new DAV_Multistatus();
  
  // This might generate an exception, but that's OK:
  $retval = ($destination[0] == '/')
    ? $this->method_COPY( $destination, $overwrite )
    : $this->method_COPY_external( $destination, $overwrite );
    
  foreach ( $this as $member ) {
    try {
      $deeppath = DAV::slashify($path) . $member;
      $deepdest = DAV::slashify($destination) . (
        $destination[0] == '/' ? $member : DAV::urlencode($member)
      );
      $resource = DAV_Server::inst()->resource($deeppath);
      if (!$resource)
        $multistatus->addStatus(
          $deeppath, new DAV_Status(
            REST::HTTP_INTERNAL_SERVER_ERROR,
            "Resource not found: $deeppath"
          )
        );
      elseif ( $resource instanceof DAV_Collection )
        $resource->method_MOVE( $deepdest, $overwrite, $deeppath );
      elseif ($destination[0] == '/')
        $resource->method_MOVE( $deepdest, $overwrite );
      else
        $resource->method_MOVE_external( $deepdest, $overwrite );
    }
    catch (DAV_Status $e) {
      $multistatus->addStatus($deeppath, $e);
    }
    catch (DAV_Multistatus $e) {
      $multistatus->mergeWith($e);
    }
  }
  if ( count( $multistatus->statuses() ) )
    throw $multistatus;
    
  try { $this->method_DELETE(); }
  catch (DAV_Status $e) {}
  
  return $retval;
}


/**
 * Handle a PUT request.
 * @param array &$headers Headers you want to submit in the HTTP response.
 * @param string $member
 * @return void
 * @throws DAV_Status
 */
public function method_PUT( &$headers, $member ) {
  throw new DAV_Status( DAV_Request::$PARANOID ? REST::HTTP_CONFLICT : REST::HTTP_FORBIDDEN );
}

  
} // class DAV_Collection_Impl
