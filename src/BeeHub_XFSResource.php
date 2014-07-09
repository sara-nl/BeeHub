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
 * Some class.
 * @package BeeHub
 */
class BeeHub_XFSResource extends BeeHub_Resource {


  /**
   * @var string the path of the resource on the local filesystem.
   */
  protected $localPath;


  /**
   * @var array
   */
  protected $stat;


  public function __construct($path) {
    parent::__construct($path);
    $this->localPath = BeeHub::localPath($path);
    $this->stat = stat($this->localPath);
  }


  protected function init_props() {
    if (is_null($this->stored_props)) {
      $this->stored_props = array();
      $attributes = xattr_list($this->localPath);
      foreach ($attributes as $attribute)
        $this->stored_props[rawurldecode($attribute)] =
                xattr_get($this->localPath, $attribute);
    }
  }


  /**
   * @return Array a list of ACEs.
   * @see BeeHub_Resource::user_prop_acl_internal()
   */
  public function user_prop_acl_internal() {
    return DAVACL_Element_ace::json2aces($this->user_prop(DAV::PROP_ACL));
  }


  /**
   * @see DAV_Resource::user_prop_getetag()
   */
  public function user_prop_getetag() {
    return $this->user_prop(DAV::PROP_GETETAG);
  }


  /**
   * @see DAV_Resource::user_prop_getlastmodified()
   */
  public function user_prop_getlastmodified() {
    return $this->stat['mtime'];
  }


  /**
   * @see DAV_Resource::user_propname()
   */
  public function user_propname() {
    $this->init_props();
    $retval = array();
    $SUPPORTED_PROPERTIES = DAV::getSupported_Properties();
    foreach (array_keys($this->stored_props) as $prop)
      if (!isset($SUPPORTED_PROPERTIES[$prop]))
        $retval[$prop] = true;
    return $retval;
  }


  /**
   * @see DAV_Resource::user_prop_displayname()
   */
  public function user_prop_displayname() {
    return rawurldecode(basename($this->path));
  }


  /**
   * @see BeeHub_Resource::user_set_sponsor()
   */
  protected function user_set_sponsor($sponsor) {
    // No (correct) sponsor given? Bad request!
    if ( ! ( $sponsor = DAV::$REGISTRY->resource($sponsor) ) ||
         ! $sponsor instanceof BeeHub_Sponsor ||
         ! $sponsor->isVisible() )
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST
      );

    // Only the resource owner (or an administrator) can change the sponsor
    if ( $this->user_prop_owner() !== $this->user_prop_current_user_principal() )
      throw DAV::forbidden( 'Only the owner can change the sponsor of a resource.' );

    // And I can only change the sponsor into a sponsor that sponsors me
    if ( !in_array( $sponsor->path, BeeHub::getAuth()->current_user()->current_user_sponsors() ) )
      throw DAV::forbidden( "You're not sponsored by {$sponsor->path}" );

    return $this->user_set( BeeHub::PROP_SPONSOR, $sponsor->path);
  }


  /**
   * @see DAVACL_Resource::user_set_owner()
   */
  protected function user_set_owner($owner) {
    // The owner should exist and be visible
    if ( ! ( $owner = DAV::$REGISTRY->resource($owner) ) ||
         ! $owner->isVisible() ||
         ! $owner instanceof BeeHub_User)
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST,
        DAV::COND_RECOGNIZED_PRINCIPAL
      );

    // You should be authenticated
    if ( !( $cup = $this->user_prop_current_user_principal() ) ||
         !( $cup = DAV::$REGISTRY->resource($cup) ) )
      throw DAV::forbidden();

    // Get the sponsor of this resource
    if ( ($sponsor = $this->user_prop_sponsor()) )
      $sponsor = DAV::$REGISTRY->resource($sponsor);

    // If you are owner, and the new owner is sponsored by the resource sponsor
    if ( $this->user_prop_owner() === $cup->path &&
         in_array( $owner->path, $sponsor->user_prop_group_member_set() ) )
      return $this->user_set(DAV::PROP_OWNER, $owner->path);

    // If you are not the owner, you can become owner if you have write
    // privileges on both the resource itself as its parent collection
    if ( $this->user_prop_owner() !== $cup->path &&
         $owner->path === $cup->path &&
         $this->collection() instanceof BeeHub_Directory ) {
      $this->assert( BeeHub::PRIV_READ_CONTENT );
      $this->assert( DAVACL::PRIV_READ_ACL );
      $this->assert( DAVACL::PRIV_WRITE_CONTENT );
      $this->collection()->assert( DAVACL::PRIV_WRITE_CONTENT );

      // If the user is not sponsored by the resource sponsor, we have to change
      // the resource sponsor
      if ( !in_array( $this->user_prop_sponsor(),
                      BeeHub::getAuth()->current_user()->current_user_sponsors() ) ) {

        // If the user is sponsored by the collection sponsor, then let's take
        // that sponsor
        if ( !in_array( $this->collection()->user_prop_sponsor(),
                        BeeHub::getAuth()->current_user()->current_user_sponsors() ) ) {

          // Else take the default sponsor of the user
          if ( !$cup->user_prop_sponsor() ) {
            throw DAV::forbidden();
          } else {
            $this->user_set(BeeHub::PROP_SPONSOR, $cup->user_prop_sponsor());
          }
        } else {
          $this->user_set( BeeHub::PROP_SPONSOR,
                           $this->collection()->user_prop_sponsor() );
        }
      }
      return $this->user_set(DAV::PROP_OWNER, $owner->path);
    }

    // If the owner still isn't changed, you are not allowed to do so
    throw DAV::forbidden();
  }


  protected function user_set_displayname($value) {
    throw new DAV_Status(
      DAV::HTTP_FORBIDDEN,
      DAV::COND_CANNOT_MODIFY_PROTECTED_PROPERTY
    );
  }


  /**
   * Stores properties set earlier by set().
   * @return void
   * @throws DAV_Status in particular 507 (Insufficient Storage)
   */
  public function storeProperties() {
    if (!$this->touched)
      return;
    foreach (xattr_list($this->localPath) as $attribute)
      if (!isset($this->stored_props[rawurldecode($attribute)]))
        xattr_remove($this->localPath, $attribute);
    foreach ($this->stored_props as $name => $value)
      xattr_set($this->localPath, rawurlencode($name), $value);
    $this->touched = false;
  }


  /**
   * @see DAVACL_Resource::user_set_acl()
   */
  public function user_set_acl($aces) {
    $this->user_set(DAV::PROP_ACL, $aces ? DAVACL_Element_ace::aces2json($aces) : null);
    $this->storeProperties();
  }


} // class BeeHub_XFSResource
