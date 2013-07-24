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
      $collection = BeeHub::getNoSQL()->files;
      $document = $collection->findOne( array('path' => DAV::unslashify( $this->path ) ) );
      $this->stored_props = array();
      
      if ( !is_null( $document ) && !empty( $document['props'] ) ) {
        foreach ( $document['props'] as $key => $value ) {
          $decoded_key = rawurldecode( $key );
          if ( $decoded_key === DAV::PROP_OWNER ) {
            $value = BeeHub::USERS_PATH . rawurlencode( $value );
          }
          if ( $decoded_key === BeeHub::PROP_SPONSOR ) {
            $value = BeeHub::SPONSORS_PATH . rawurlencode( $value );
          }
          $this->stored_props[ $decoded_key ] = $value;
        }
      }
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
    foreach (array_keys($this->stored_props) as $prop)
      if (!isset(DAV::$SUPPORTED_PROPERTIES[$prop]))
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
    $this->assert(DAVACL::PRIV_READ);
    if ( ! ( $sponsor = BeeHub_Registry::inst()->resource($sponsor) ) ||
         ! $sponsor instanceof BeeHub_Sponsor ||
         ! $sponsor->isVisible() )
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST
      );
    if ( $this->user_prop_owner() !==
           $this->user_prop_current_user_principal() &&
         ! BeeHub_ACL_Provider::inst()->wheel() )
      throw DAV::forbidden( 'Only the owner can change the sponsor of a resource.' );
    if (!in_array($sponsor->path, $this->current_user_sponsors()))
      throw DAV::forbidden( "You're not sponsored by {$sponsor->path}" );
    return $this->user_set( BeeHub::PROP_SPONSOR, $sponsor->path);
  }


  /**
   * @see DAVACL_Resource::user_set_owner()
   */
  protected function user_set_owner($owner) {
    $this->assert(DAVACL::PRIV_READ);
    if ( ! ( $owner = BeeHub_Registry::inst()->resource($owner) ) ||
         ! $owner->isVisible() ||
         ! $owner instanceof BeeHub_User)
      throw new DAV_Status(
        DAV::HTTP_BAD_REQUEST,
        DAV::COND_RECOGNIZED_PRINCIPAL
      );
    if ( !( $cup = $this->user_prop_current_user_principal() ) ||
         !( $cup = BeeHub_Registry::inst()->resource($cup) ) )
      throw DAV::forbidden();
    if ( ($sponsor = $this->user_prop_sponsor()) )
      $sponsor = BeeHub_Registry::inst()->resource($sponsor);
    if ( $this->user_prop_owner() === $cup->path &&
         in_array( $owner->path, $sponsor->user_prop_group_member_set() ) )
      return $this->user_set(DAV::PROP_OWNER, $owner->path);
    if ( $this->user_prop_owner() !== $cup->path &&
         $owner->path === $cup->path &&
         ( $this->assert(DAVACL::PRIV_WRITE) ||
           $this->collection() &&
           $this->collection()->assert(DAVACL::PRIV_WRITE) ) ) {
      if ( !in_array( $this->user_prop_sponsor(),
                      $this->current_user_sponsors() ) ) {
        if ( !in_array( $this->collection()->user_prop_sponsor(),
                        $this->current_user_sponsors() ) ) {
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
    if (!$this->touched) {
      return;
    }
    
    $collection = BeeHub::getNoSQL()->files;
    $document = $collection->findOne( array('path' => DAV::unslashify( $this->path ) ) );
    
    $urlencodedProps = array();
    foreach ( $this->stored_props as $key => $value ) {
      if ( ( $key === DAV::PROP_OWNER ) ||
           ( $key === BeeHub::PROP_SPONSOR ) ){
        $value = rawurldecode( basename( $value ) );
      }
      // This url encodes only the characters needed to create valid mongoDB keys. You can just run rawurldecode to decode it.
      $encodedKey = str_replace(
          array( '%'  , '$'  , '.'   ),
          array( '%25', '%24', '%2E' ),
          $key
      );
      $urlencodedProps[ $encodedKey ] = $value;
    }
    
    if ( is_null( $document ) ) {
      $document = array(
          'path' => DAV::unslashify( $this->path ),
          'props' => $urlencodedProps );
    }else{
      $document['props'] = $urlencodedProps;
    }
    $collection->save( $document );
    
    $this->touched = false;
  }


  /**
   * @see DAVACL_Resource::user_set_acl()
   */
  public function user_set_acl($aces) {
    $this->assert(DAVACL::PRIV_WRITE_ACL);
    $this->user_set(DAV::PROP_ACL, $aces ? DAVACL_Element_ace::aces2json($aces) : null);
    $this->storeProperties();
  }
  
  
  /**
   * Gets all members who have a certain property set
   * @param   string  $prop  The property which should be set on the member
   * @return  array          An array with all paths to members who have the property set
   */
  public function get_members_with_prop($prop) {
    $collection = BeeHub::getNoSQL()->files;
    $results = $collection->find(
            array(
                'path' => array( '$regex' => preg_quote( DAV::slashify( $this->path ) ) . '.*'),
                'props.' . $prop => array( '$exists' => true ) ),
            array('path' => 1, 'props.' . $prop => 1)
            );
    $returnVal = array();
    foreach ( $results as $result ) {
      $returnVal[$result['path']] = $result['props'][$prop];
    }
    return $returnVal;
  }


} // class BeeHub_XFSResource
