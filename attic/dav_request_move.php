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
 * $Id: dav_request_move.php 171 2011-01-24 21:01:13Z kobasoft $
 **************************************************************************/

/**
 * File documentation (who cares)
 * @package DAV
 */

/**
 * Helper class for parsing LOCK request bodies.
 * @internal
 * @package DAV
 */
class DAV_Request_MOVE extends DAV_Request {


public function depth() {
  $retval = parent::depth();
  return is_null($retval) ? DAV::DEPTH_INF : $retval;
}


/**
 * @param DAV_Resource $resource
 * @return void
 * @throws DAV_Status
 */
protected function handle( $resource ) {
  if (
       $this instanceof DAV_Request_MOVE && (
         ( $lockroot = DAV::assertLock( dirname( DAV::$PATH ) ) ) ||
         ( $lockroot = DAV::assertLock( DAV::$PATH ) ) ||
         ( $lockroot = DAV::assertMemberLocks( DAV::$PATH ) )
       ) ||
       ( $lockroot = DAV::assertLock( dirname( $this->destination() ) ) ) ||
       $this->overwrite() && (
         ( $lockroot = DAV::assertLock( $this->destination() ) ) ||
         ( $lockroot = DAV::assertMemberLocks($this->destination()) )
       )
     )
    throw new DAV_Status(
      DAV::HTTP_LOCKED,
      array( DAV::COND_LOCK_TOKEN_SUBMITTED => $lockroot )
    );
    
  if ( DAV::DEPTH_1 == $this->depth() or
       $this instanceof DAV_Request_MOVE &&
       DAV::DEPTH_0 != $this->depth() )
    throw new DAV_Status(
      DAV::HTTP_BAD_REQUEST,
      'Illegal value for Depth: header.'
    );

  if ( $this instanceof DAV_Request_MOVE &&
       '/' == DAV::$PATH )
    throw new DAV_Status(DAV::HTTP_FORBIDDEN);
  $destination = $this->destination();
  if ($resource instanceof DAV_Collection)
    $destination = DAV::slashify($destination);
  // The next two lines are there to make the litmus test succeed. The author
  // of litmus had eir own doubts wether this is actually desirable behaviour,
  // but chose to require this behaviour anyway:
  else
    $destination = DAV::unslashify($destination);

  // Check: Can't move a collection to one of its members.
  if ( '/' == substr(DAV::$PATH, -1) &&
       0 === strpos( $destination, DAV::$PATH ) )
    throw new DAV_Status(
      DAV::HTTP_FORBIDDEN,
      "Can't move a collection to itself or one of its members."
    );

  if ('/' !== $destination[0] ) {
    $result = $resource->method_COPY_external( $destination, $this->overwrite() );
    if (!DAV_Multistatus::active()) self::delete( $resource );
    if ( DAV_Multistatus::active())
      DAV_Multistatus::inst()->close();
    elseif ($result)
      DAV::redirect(DAV::HTTP_CREATED, DAV::path2url($destination));
    else
      DAV::header( array( 'status' => DAV::HTTP_NO_CONTENT ) );
    return;
  }

  // Check: Won't move a resource to one of its parents.
  if ( 0 === strpos(
         DAV::slashify(DAV::$PATH),
         DAV::slashify($destination)
       ) )
    throw new DAV_Status(
      DAV::HTTP_NOT_IMPLEMENTED,
      "Won't move or copy a resource to one of its parents."
    );

  $destinationResource = DAV::$REGISTRY->resource( $destination );
  if ( $destinationResource ) {
    if ($this->overwrite()) {
      self::delete($destinationResource);
      if (DAV_Multistatus::active()) {
        DAV_Multistatus::inst()->close();
        return;
      }
    }
    else
      throw new DAV_Status(DAV::HTTP_PRECONDITION_FAILED);
  }
  elseif (!DAV::$REGISTRY->resource( dirname( $destination ) ) )
    throw new DAV_Status(DAV::HTTP_CONFLICT);

  if ( DAV::$LOCKPROVIDER &&
       $locks = DAV::$LOCKPROVIDER->memberLocks( DAV::$PATH ) )
    foreach ($locks as $lock)
      DAV::$LOCKPROVIDER->unlock( $lock->lockroot );

  if (!DAV_Multistatus::active())
    $resource->method_MOVE( $destination );
  
  if (DAV_Multistatus::active())
    DAV_Multistatus::inst()->close();
  elseif ( $destinationResource )
    DAV::header( array( 'status' => DAV::HTTP_NO_CONTENT ) );
  else
    DAV::redirect(DAV::HTTP_CREATED, DAV::path2url($destination));
}


} // class
