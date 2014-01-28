#!/bin/bash

# Updates all BeeHub dependencies

if [ "${1}" == "-h" ] || [ "${1}" == "--help" ]; then
  echo "Updates all BeeHub dependencies"
  echo ""
  echo "Usage: ${0}"
  exit 0
fi

set -e
cd "$( dirname "${BASH_SOURCE[0]}" )/../"

# Update Composer and all dependencies installed through Composer
php tools/composer.phar self-update
php tools/composer.phar update

# Load submodules
git submodule update

# 'compile' js-webdav-client and link the file
cd js-webdav-client
make dist.js
cd ..
rm -vf public/system/js/webdavlib.js
ln -vs "$(pwd)/js-webdav-client/dist.js" public/system/js/webdavlib.js

exit 0
