<?php
/**
 * Contains several functions to build the test environment
 *
 * Copyright ©2007-2014 SURFsara b.v., Amsterdam, The Netherlands
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
 * @package     BeeHub
 * @subpackage  stress
 */

declare( encoding = 'UTF-8' );
namespace BeeHub\stress;

function setUpDatabase() {
  $db = \BeeHub::getNoSQL();
  $collections = $db->listCollections();
  foreach ($collections as $collection) {
    $collection->drop();
  }

  // All the resources are stored
  $filesCollection = $db->createCollection( 'files' );
  $filesCollection->ensureIndex( array( 'props.http://beehub%2Enl/ sponsor' => 1 ) );
  $filesCollection->ensureIndex( array( 'props.DAV: owner' => 1 ) );
  $filesCollection->ensureIndex( array( 'path' => 1 ), array( 'unique' => 1 ) );
  $files = array();
  $files[] = array(
    'path' => 'home'
  );
  
  // Create 1 sponsor
  $collection = $db->createCollection( 'sponsors' );
  $collection->ensureIndex( array( 'name' => 1 ), array( 'unique' => 1 ) );
  $sponsors = array();
  $sponsorNames = array();
  $sponsors[] = array(
    'name' => 'sponsor1',
    'displayname' => 'sponsor1',
    'admins' => array( 'user1' )
  );
  $sponsorNames[] = 'sponsor1';
  $collection->batchInsert( $sponsors );
  
  // Create 200 groepen
  $collection = $db->createCollection( 'groups' );
  $collection->ensureIndex( array( 'name' => 1 ), array( 'unique' => 1 ) );
  $groups = array();
  $groupNames = array();
  for ( $counter = 1; $counter <= 500; $counter++ ) {
    $groups[] = array(
      'name' => 'group' . $counter,
      'displayname' => 'group' . $counter,
      'admins' => array( 'user1' )
    );
    $groupNames[] = 'group' . $counter;
    
    // Create the resources for this group; all for user1 who is in all groups
    $files[] = array(
      'path' => 'group' . $counter,
      'props' => array(
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    for ( $resourceCounter = 1; $resourceCounter <= 1000; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'group' . $counter . '/5mb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user1',
          'DAV: getcontentlength' => 5242880,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
    for ( $resourceCounter = 1; $resourceCounter <= 500; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'group' . $counter . '/100gb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user1',
          'DAV: getcontentlength' => 107374182400,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
  }
  $collection->batchInsert( $groups );
  unset( $groups );

  // 20 gebruikers
  $collection = $db->createCollection( 'users' );
  $collection->ensureIndex( array( 'name' => 1 ), array( 'unique' => 1 ) );
  $users = array();
  for ( $counter = 1; $counter <= 20; $counter++ ) {
    $users[] = array(
      'name' => 'user' . $counter,
      'displayname' => 'user' . $counter,
      'email' => 'user' . $counter . '@mailservice.com',
      'password' => '$6$rounds=5000$126b519331f5189c$liGp7IWjOlsZ7wwYbobzsMC9y7bE9JYERiS4ts503HKNqIQUrvNM8IyxoGDSBo30XwrdyTzI6rNZRL5lSEPTr0',
      'default_sponsor' => 'sponsor1',
      'sponsors' => $sponsorNames
    );

    // And create user files
    //   /home/userXX/    200 bestanden van 500 Mb
    $files[] = array(
      'path' => 'home/user' . $counter,
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    for ( $resourceCounter = 1; $resourceCounter <= 500; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/500mb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => 524288000,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
  }
  $users[0]['groups'] = $groupNames;
  $users[0]['sponsors'] = $sponsorNames;

  $tb = 1073741824 * 1024;
  for ( $counter = 21; $counter <= 40; $counter++ ) {
    $users[] = array(
      'name' => 'user' . $counter,
      'displayname' => 'user' . $counter,
      'email' => 'user' . $counter . '@mailservice.com',
      'password' => '$6$rounds=5000$126b519331f5189c$liGp7IWjOlsZ7wwYbobzsMC9y7bE9JYERiS4ts503HKNqIQUrvNM8IyxoGDSBo30XwrdyTzI6rNZRL5lSEPTr0',
      'default_sponsor' => 'sponsor1',
      'sponsors' => $sponsorNames
    );

    // And create user files
    //   /home/userXX/             10,000 bestanden van 1 Mb
    //   /home/userXX/dir1         1,000 bestanden van 1Gb
    //   /home/userXX/dir2/dir1    10 bestanden van 1 Tb
    $files[] = array(
      'path' => 'home/user' . $counter,
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    $files[] = array(
      'path' => 'home/user' . $counter . '/dir1',
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    $files[] = array(
      'path' => 'home/user' . $counter . '/dir2',
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    $files[] = array(
      'path' => 'home/user' . $counter . '/dir2/dir1',
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    for ( $resourceCounter = 1; $resourceCounter <= 10000; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/1mb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => 1048576,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
    for ( $resourceCounter = 1; $resourceCounter <= 1000; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/dir1/1gb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => 1073741824,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
    for ( $resourceCounter = 1; $resourceCounter <= 10; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/dir2/dir1/1tb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => $tb,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
  }

  for ( $counter = 41; $counter <= 60; $counter++ ) {
    $users[] = array(
      'name' => 'user' . $counter,
      'displayname' => 'user' . $counter,
      'email' => 'user' . $counter . '@mailservice.com',
      'password' => '$6$rounds=5000$126b519331f5189c$liGp7IWjOlsZ7wwYbobzsMC9y7bE9JYERiS4ts503HKNqIQUrvNM8IyxoGDSBo30XwrdyTzI6rNZRL5lSEPTr0',
      'default_sponsor' => 'sponsor1',
      'sponsors' => $sponsorNames
    );

    // And create user files
    //   /home/userXX/        10 bestanden van 1 Tb
    //   /home/userXX/dir1    20 bestanden van 500 Gb
    $files[] = array(
      'path' => 'home/user' . $counter,
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    $files[] = array(
      'path' => 'home/user' . $counter . '/dir1',
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    for ( $resourceCounter = 1; $resourceCounter <= 10; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/1tb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => $tb,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
    for ( $resourceCounter = 1; $resourceCounter <= 20; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/dir1/500gb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => 536870912000,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
  }

  for ( $counter = 61; $counter <= 80; $counter++ ) {
    $users[] = array(
      'name' => 'user' . $counter,
      'displayname' => 'user' . $counter,
      'email' => 'user' . $counter . '@mailservice.com',
      'password' => '$6$rounds=5000$126b519331f5189c$liGp7IWjOlsZ7wwYbobzsMC9y7bE9JYERiS4ts503HKNqIQUrvNM8IyxoGDSBo30XwrdyTzI6rNZRL5lSEPTr0',
      'default_sponsor' => 'sponsor1',
      'sponsors' => $sponsorNames
    );

    // And create user files
    //   /home/userXX/        10 bestanden van 1 Tb, 10,000 bestanden van 1Kb
    //   /home/userXX/dir1    200 bestanden van 500 Gb, 1,000 bestanden van 1 Mb
    $files[] = array(
      'path' => 'home/user' . $counter,
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    $files[] = array(
      'path' => 'home/user' . $counter . '/dir1',
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    for ( $resourceCounter = 1; $resourceCounter <= 10; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/1tb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => $tb,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
    for ( $resourceCounter = 1; $resourceCounter <= 10000; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/1kb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => 1024,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
    for ( $resourceCounter = 1; $resourceCounter <= 200; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/dir1/500gb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => 536870912000,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
    for ( $resourceCounter = 1; $resourceCounter <= 1000; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/dir1/1mb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => 1048576,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
  }

  for ( $counter = 81; $counter <= 100; $counter++ ) {
    $users[] = array(
      'name' => 'user' . $counter,
      'displayname' => 'user' . $counter,
      'email' => 'user' . $counter . '@mailservice.com',
      'password' => '$6$rounds=5000$126b519331f5189c$liGp7IWjOlsZ7wwYbobzsMC9y7bE9JYERiS4ts503HKNqIQUrvNM8IyxoGDSBo30XwrdyTzI6rNZRL5lSEPTr0',
      'default_sponsor' => 'sponsor1',
      'sponsors' => $sponsorNames
    );

    // And create user files
    //   /home/userXX/                       10 bestanden van 10 Mb
    //   /home/userXX/dir1                   10 bestanden van 10 Mb
    //   /home/userXX/dir2                   100 bestanden van 1 Gb
    //   /home/userXX/dir2/dir1              150 bestanden van 20 Mb
    //   /home/userXX/dir2/dir1/../dir50/    150 bestanden van 20 Mb in tussenliggende dirs
    //   /home/userXX/dir3/dir1/../dir20/    150 bestanden van 20 Mb in tussenliggende dirs
    $files[] = array(
      'path' => 'home/user' . $counter,
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    for ( $resourceCounter = 1; $resourceCounter <= 10; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/10mb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => 10485760,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
    $files[] = array(
      'path' => 'home/user' . $counter . '/dir1',
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    for ( $resourceCounter = 1; $resourceCounter <= 10; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/dir1/10mb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => 10485760,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
    $files[] = array(
      'path' => 'home/user' . $counter . '/dir2',
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    for ( $resourceCounter = 1; $resourceCounter <= 100; $resourceCounter++ ) {
      $files[] = array(
        'path' => 'home/user' . $counter . '/dir2/1gb_' . $resourceCounter,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'DAV: getcontentlength' => 1073741824,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        )
      );
    }
    $filesCollection->batchInsert( $files );
    $files = array();
    $path = 'home/user' . $counter . '/dir2';
    for ( $dirCounter = 1; $dirCounter <= 50; $dirCounter++ ) {
      $path .= '/dir' . $dirCounter;
      $files[] = array(
        'path' => $path,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        ),
        'collection' => true
      );
      for ( $resourceCounter = 1; $resourceCounter <= 150; $resourceCounter++ ) {
        $files[] = array(
          'path' => $path . '/20mb_' . $resourceCounter,
          'props' => array(
            'DAV: owner' => 'user' . $counter,
            'DAV: getcontentlength' => 20971520,
            'http://beehub%2Enl/ sponsor' => 'sponsor1'
          )
        );
      }
      $filesCollection->batchInsert( $files );
      $files = array();
    }
    $files[] = array(
      'path' => 'home/user' . $counter . '/dir3',
      'props' => array(
        'DAV: owner' => 'user' . $counter,
        'http://beehub%2Enl/ sponsor' => 'sponsor1'
      ),
      'collection' => true
    );
    $path = 'home/user' . $counter . '/dir3';
    for ( $dirCounter = 1; $dirCounter <= 20; $dirCounter++ ) {
      $path .= '/dir' . $dirCounter;
      $files[] = array(
        'path' => $path,
        'props' => array(
          'DAV: owner' => 'user' . $counter,
          'http://beehub%2Enl/ sponsor' => 'sponsor1'
        ),
        'collection' => true
      );
      for ( $resourceCounter = 1; $resourceCounter <= 150; $resourceCounter++ ) {
        $files[] = array(
          'path' => $path . '/20mb_' . $resourceCounter,
          'props' => array(
            'DAV: owner' => 'user' . $counter,
            'DAV: getcontentlength' => 20971520,
            'http://beehub%2Enl/ sponsor' => 'sponsor1'
          )
        );
      }
      $filesCollection->batchInsert( $files );
      $files = array();
    }
  }

  $collection->batchInsert( $users );
  unset( $users );
  unset( $files );

  \BeeHub_Principal::update_principals_json();
}


function loadTestConfig() {
  $configFile = \dirname( __FILE__ ) . \DIRECTORY_SEPARATOR . 'config.ini';
  if ( !\file_exists( $configFile ) ) {
    print( 'No configuration file exists. Please copy ' . \dirname(  __DIR__ ) . \DIRECTORY_SEPARATOR . 'config_example.ini to ' . $configFile . " and edit it to set the right configuration options\n" );
    die( 1 );
  }
  \BeeHub::loadConfig( $configFile );
  \BeeHub::changeConfigField( 'namespace', 'admin_group', '/system/groups/admin' );
}

// End of file
