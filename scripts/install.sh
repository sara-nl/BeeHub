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

exit 0
