#!/bin/bash

set -e
pushd "$( dirname "${BASH_SOURCE[0]}" )/../"

git submodule init
git submodule update
./client/link_submodules
./client/makeapp
pushd ./js-webdav-client
make dist.js
popd

rm -vf public/system/client
ln -vs "$(pwd)/client/build/system/client" public/system/client
rm -vf views/beehub_directory.php
ln -vs "$(pwd)/client/build/views/directory.php" views/beehub_directory.php
rm -vf public/system/js/webdavlib.js
ln -vs "$(pwd)/js-webdav-client/dist.js" public/system/js/webdavlib.js

chmod -v 2777 public/system/js/server/
if [[ -e public/system/js/server/principals.js ]]; then
  cat > public/system/js/server/principals.js <<EOM
nl.sara.beehub.principals = {"users":{},"groups":{},"sponsors":{}};
EOM
fi

echo "Path to simplesamlphp: "
read SIMPLESAML
rm -vf public/system/simplesaml
ln -vs "${SIMPLESAML}/www/" public/system/simplesaml

popd

echo "Don't forget:"
echo " - to create sponsor e-infra"
echo " - to create an admin user"
echo " - to create the data directory, /home/ /system/, /system/users/, /system/groups/, /system/sponsors/ directories (no x-attributes required)"
echo ""
echo "The apache configuration should include the following:"
echo " - Use /public as document root"
echo " - \"AccessFileName .htaccess\" and \"AllowOverride All\" for the document root"
echo " - Have at least the following modules installed:"
echo "   * mod_rewrite"
echo "   * mod_ssl"
echo "   * php 5.3 or higher"
echo " - Listen for HTTP connections (preferably on port 80)"
echo " - Listen for HTTPS connections (preferably on port 443, but always 363 ports after the HTTP port)"
echo ""
echo "PHP should have the following extensions installed:"
echo " - xattr (from PECL)"
echo " - php-xml"
echo " - mysqli"

exit 0
