#!/bin/bash

# Installs BeeHub to this computer

if [ "${1}" == "-h" ] || [ "${1}" == "--help" ]; then
  echo "Installs BeeHub to this computer"
  echo ""
  echo "Usage: ${0}"
  exit 0
fi

set -e
cd "$( dirname "${BASH_SOURCE[0]}" )/../"

# Prepare a directory to install extra tools in
rm -rf tools 2>/dev/null | true
mkdir tools

# Then install Composer and let it install dependencies for this project
curl -sS https://getcomposer.org/installer | php -- --install-dir=tools
php tools/composer.phar install

# Initialize submodules
git submodule init

# Check whether we have to create a default principals.js
chmod -v 2777 public/system/js/server/
if [[ -e public/system/js/server/principals.js ]]; then
  cat > public/system/js/server/principals.js <<EOM
nl.sara.beehub.principals = {"users":{},"groups":{},"sponsors":{}};
EOM
fi

./scripts/update_dependencies.sh
./scripts/install_simplesamlphp.sh

# Some last information
echo "Don't forget:"
echo " - to create sponsor e-infra"
echo " - to create an admin user"
echo " - to create the data directory, /home/ /system/, /system/users/, /system/groups/, /system/sponsors/ directories (no x-attributes required)"
echo ""
echo "The apache configuration should include the following:"
echo " - Use $(pwd)public/ as document root"
echo " - \"AccessFileName .htaccess\" and \"AllowOverride All\" for the document root, or copy the directives in $(pwd)public/.htaccess into the Directory section of the central Apache configuration"
echo " - Have at least the following modules installed:"
echo "   * mod_rewrite"
echo "   * mod_ssl"
echo "   * php 5.3 or higher"
echo " - Listen for HTTP connections (preferably on port 80)"
echo " - Listen for HTTPS connections (preferably on port 443, but always 363 ports after the HTTP port)"

exit 0
