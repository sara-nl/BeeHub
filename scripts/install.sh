#!/bin/bash

# Prepare the environment and initialize submodules
set -e
pushd "$( dirname "${BASH_SOURCE[0]}" )/../"
git submodule init
git submodule update

# Build and link the webdav javascript library
pushd ./js-webdav-client
make dist.js
popd
rm -vf public/system/js/webdavlib.js
ln -vs "$(pwd)/js-webdav-client/dist.js" public/system/js/webdavlib.js

# Build and link the client
./client/link_submodules
./client/makeapp
rm -vf public/system/client
ln -vs "$(pwd)/client/build/system/client" public/system/client
rm -vf views/beehub_directory.php
ln -vs "$(pwd)/client/build/views/directory.php" views/beehub_directory.php

# Prepare a default principals.js file
chmod -v 2777 public/system/js/server/
if [[ -e public/system/js/server/principals.js ]]; then
  cat > public/system/js/server/principals.js <<EOM
nl.sara.beehub.users_path = "/system/users/";nl.sara.beehub.groups_path = "/system/groups/";nl.sara.beehub.sponsors_path = "/system/sponsors/";nl.sara.beehub.principals = {"users":{},"groups":{},"sponsors":{}};
EOM
fi

# Download, install and configure simpleSamlPHP
wget http://simplesamlphp.googlecode.com/files/simplesamlphp-1.10.0.tar.gz
tar -zxf simplesamlphp-1.10.0.tar.gz
rm simplesamlphp-1.10.0.tar.gz
SIMPLESAML_DIR="${PWD}/simplesamlphp-1.10.0"
rm -vf public/system/simplesaml
ln -vs "${SIMPLESAML_DIR}/www/" public/system/simplesaml
cp -vf simplesaml-custom/metadata/saml20-idp-remote.php "${SIMPLESAML_DIR}/metadata/saml20-idp-remote.php"
cp -vfr simplesaml-custom/modules/* "${SIMPLESAML_DIR}/modules/"
cp -vf simplesaml-custom/config/config.php "${SIMPLESAML_DIR}/config/config.php"
echo -e "\n\nEdit the simpleSamlPHP configuration. In the next file, have a look at the next values:"
echo " - auth.adminpassword"
echo " - admin.protectindexpage"
echo " - secretsalt"
echo " - technicalcontact_name"
echo " - technicalcontact_email"
read -n 1 -s
vi "${SIMPLESAML_DIR}/config/config.php"
cp -vf simplesaml-custom/config/authsources.php "${SIMPLESAML_DIR}/config/authsources.php"
echo -e "\n\nCheck whether the authsources.php is correct. Check especially of URLs and description for SURFconext are correct."
echo "DO NOT change the names of the authentication sources!"
read -n 1 -s
vi "${SIMPLESAML_DIR}/config/authsources.php"

# Finish installation
popd

echo -e "\n\nDon't forget:"
echo " - to create sponsor e-infra"
echo " - to create an admin user"
echo " - to create the data directory, /home/ /system/, /system/users/, /system/groups/, /system/sponsors/ directories (no x-attributes required)"

exit 0
