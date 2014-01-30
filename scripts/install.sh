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
  echo ""
  echo "Usage: ${0} [config.ini]"
  echo "  if 'config.ini' is provided, this will be used for the BeeHub configuration."
  echo "  You can interactively supply configuration options. If 'config.ini' is"
  echo "  provided, this will be used for the BeeHub configuration instead. Note that"
  echo "  this file should contain the options as specified in config_example.ini."
  exit 0
fi

set -e
cd "$( dirname "${BASH_SOURCE[0]}" )/../"

if [[ "${1}" != "" ]]; then
  echo "param: ${1}"
  cp -v "${1}" config.ini
else
  # No config file supplied, so let's start asking questions
  CONTINUE=0
  DATADIR=""
  SIMPLESAML=""
  WHEEL="admin"
  MYSQL_HOST="localhost"
  MYSQL_USERNAME=""
  MYSQL_PASSWORD=""
  MYSQL_DATABASE=""
  REALM=""
  MAIL_ADDRESS=""
  MAIL_NAME=""
  while [[ "${CONTINUE}" != "y" ]]; do
    echo "In what directory should the data be stored that is accessible through webDAV? [${DATADIR}] "
    read DATADIR_2
    if [[ "${DATADIR_2}" != "" ]]; then
      DATADIR="${DATADIR_2}"
    fi
    echo "Where is SimpleSAMLphp installed? [${SIMPLESAML}] "
    read SIMPLESAML_2
    if [[ "${SIMPLESAML_2}" != "" ]]; then
      SIMPLESAML="${SIMPLESAML_2}"
    fi
    echo "What username should the administrator have? [${WHEEL}] "
    read WHEEL_2
    if [[ "${WHEEL_2}" != "" ]]; then
      WHEEL="${WHEEL_2}"
    fi
    echo "What host runs the mySQL database? [${MYSQL_HOST}] "
    read MYSQL_HOST_2
    if [[ "${MYSQL_HOST_2}" != "" ]]; then
      MYSQL_HOST="${MYSQL_HOST_2}"
    fi
    echo "What is the mySQL username? [${MYSQL_USERNAME}] "
    read MYSQL_USERNAME_2
    if [[ "${MYSQL_USERNAME_2}" != "" ]]; then
      MYSQL_USERNAME="${MYSQL_USERNAME_2}"
    fi
    echo "What is the mySQL password? [${MYSQL_PASSWORD}] "
    read MYSQL_PASSWORD_2
    if [[ "${MYSQL_PASSWORD_2}" != "" ]]; then
      MYSQL_PASSWORD="${MYSQL_PASSWORD_2}"
    fi
    echo "What is the mySQL database? [${MYSQL_DATABASE}] "
    read MYSQL_DATABASE_2
    if [[ "${MYSQL_DATABASE_2}" != "" ]]; then
      MYSQL_DATABASE="${MYSQL_DATABASE_2}"
    fi
    echo "When using HTTP authentication, what realm should be used? [${REALM}] "
    read REALM_2
    if [[ "${REALM_2}" != "" ]]; then
      REALM="${REALM_2}"
    fi
    echo "When sending e-mail, which sender address should be used? [${MAIL_ADDRESS}] "
    read MAIL_ADDRESS_2
    if [[ "${MAIL_ADDRESS_2}" != "" ]]; then
      MAIL_ADDRESS="${MAIL_ADDRESS_2}"
    fi
    echo "What name should be used for sending e-mail? [${MAIL_NAME}] "
    read MAIL_NAME_2
    if [[ "${MAIL_NAME_2}" != "" ]]; then
      MAIL_NAME="${MAIL_NAME_2}"
    fi
    echo ""
    echo "Do you want to store these values (y/n)? [n] "
    read CONTINUE
  done

  if [[ "${DATADIR:(-1)}" != "/" ]]; then
    DATADIR="${DATADIR}/"
  fi
  if [[ "${SIMPLESAML:(-1)}" != "/" ]]; then
    SIMPLESAML="${SIMPLESAML}/"
  fi

  # Create a config.ini based on the questions just asked
  cat >config.ini <<EOS
datadir                  = "${DATADIR}"
simplesamlphp_autoloader = "${SIMPLESAML}/lib/_autoload.php"

[namespace]
wheel_path    = "/system/users/${WHEEL}"

[mysql]
host     = "${MYSQL_HOST}"
username = "${MYSQL_USERNAME}"
password = "${MYSQL_PASSWORD}"
database = "${MYSQL_DATABASE}"

[authentication]
realm = "${REALM}"

[email]
sender_address = "${MAIL_ADDRESS}"
sender_name    = "${MAIL_NAME}"
EOS
fi

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

# I have separated some installation steps into different files. This makes it easier to install or reconfigure just a small part.
./scripts/update_dependencies.sh
export SIMPLESAML
./scripts/install_simplesamlphp.sh
./scripts/install_db.php

# Initialize the datadir, so the minimal number of subdirectories are created
START=''
while [[ "${START}" != "y" ]] && [[ "${START}" != "n" ]]; do
  echo "Initialize data dir? This will remove all end-user data (i.e. all data accessible through webDAV. Are you sure (y/n)? "
  read START
done
FAILURE="false"
if [[ "${START}" == "y" ]]; then
  mkdir -p "${DATADIR}/home" || FAILURE="true"
  mkdir -p "${DATADIR}/system/groups" || FAILURE="true"
  mkdir -p "${DATADIR}/system/sponsors" || FAILURE="true"
  mkdir -p "${DATADIR}/system/users" || FAILURE="true"
  if [[ "${FAILURE}" == "true" ]]; then
    echo "Unable to create the system directories"
  else
    setfattr -n 'user.DAV%3A%20owner' -v "/system/users/${WHEEL}" "${DATADIR}"
    setfattr -n 'user.DAV%3A%20owner' -v "/system/users/${WHEEL}" "${DATADIR}/home"
    setfattr -n 'user.DAV%3A%20owner' -v "/system/users/${WHEEL}" "${DATADIR}/system"
    setfattr -n 'user.DAV%3A%20owner' -v "/system/users/${WHEEL}" "${DATADIR}/system/groups"
    setfattr -n 'user.DAV%3A%20owner' -v "/system/users/${WHEEL}" "${DATADIR}/system/sponsors"
    setfattr -n 'user.DAV%3A%20owner' -v "/system/users/${WHEEL}" "${DATADIR}/system/users"
  fi
else
  FAILURE="true"
fi
# If no datadir initialization was done; let the user (of this script) know that he/she should do this himself/herself
if [[ "${FAILURE}" == "true" ]]; then
  echo "Don't forget to create the data directory, /home/ /system/, /system/users/, /system/groups/, /system/sponsors/ directories and for all these directories run:"
  echo "setfattr -n 'user.DAV%3A%20owner' -v '/system/users/${WHEEL}' path/to/dir"
fi

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
