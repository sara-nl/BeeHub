#!/bin/bash

################################################################################
#                                                                              #
# Installs BeeHub to this computer                                             #
#                                                                              #
# Copyright ©2007-2013 SURFsara b.v., Amsterdam, The Netherlands               #
#                                                                              #
# Licensed under the Apache License, Version 2.0 (the "License"); you may      #
# not use this file except in compliance with the License. You may obtain      #
# a copy of the License at <http://www.apache.org/licenses/LICENSE-2.0>        #
#                                                                              #
# Unless required by applicable law or agreed to in writing, software          #
# distributed under the License is distributed on an "AS IS" BASIS,            #
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.     #
# See the License for the specific language governing permissions and          #
# limitations under the License.                                               #
#                                                                              #
################################################################################

if [ "${1}" == "-h" ] || [ "${1}" == "--help" ]; then
  echo "Installs BeeHub to this computer"
  echo "Usage: ${0}"
  exit 0
fi

set -e
cd "$( dirname "${BASH_SOURCE[0]}" )/../"

# Check whether we have to create a default principals.js
chmod -v 2777 public/system/js/server/

# Initialize submodules
git submodule init

# Prepare a directory to install extra tools in
rm -rf tools 2>/dev/null | true
mkdir tools

# Then install Composer and let it install dependencies for this project
curl -sS https://getcomposer.org/installer | php -- --install-dir=tools
COMPOSER_RESULT=0
php tools/composer.phar install || COMPOSER_RESULT=1
if [[ ${COMPOSER_RESULT} -ne 0 ]]; then
  echo ""
  echo "For some reason Composer could not install all dependencies. Please fix the problem (see Composer output above) and run the following scripts to finish the installation:"
  echo "  php $(pwd)/tools/composer.phar install"
  echo "  $(pwd)/scripts/update_dependencies.sh"
  echo "  $(pwd)/scripts/install_simplesamlphp.php"
  echo "  $(pwd)/scripts/install_db.php"
  exit 2
fi

# I have separated some installation steps into different files. This makes it easier to install or reconfigure just a small part.
DEPENDENCIES_RESULT=0
./scripts/update_dependencies.sh || DEPENDENCIES_RESULT=1
if [[ ${DEPENDENCIES_RESULT} -ne 0 ]]; then
  echo ""
  echo "For some reason there was a problem with updating the dependencies. Please fix the problem (see output above) and run the following scripts to finish the installation:"
  echo "  $(pwd)/scripts/update_dependencies.sh"
  echo "  $(pwd)/scripts/install_simplesamlphp.php"
  echo "  $(pwd)/scripts/install_db.php"
  exit 3
fi

SIMPLESAML_RESULT=0
./scripts/install_simplesamlphp.php || SIMPLESAML_RESULT=1
if [[ ${SIMPLESAML_RESULT} -ne 0 ]]; then
  echo ""
  echo "For some reason there was a problem with installing simpleSAMLphp. Please fix the problem (see output above) and run the following scripts to finish the installation:"
  echo "  $(pwd)/scripts/install_simplesamlphp.php"
  echo "  $(pwd)/scripts/install_db.php"
  exit 4
fi
./scripts/install_db.php || true

# Some last information
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
echo ""
echo "Also, make sure Apache has write access to the data directory and all subdirectories'

exit 0
