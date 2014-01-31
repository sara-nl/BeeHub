#!/bin/bash

################################################################################
#                                                                              #
# A script to interactively create a config.ini file                           #
#                                                                              #
# Copyright ©2014 SURFsara b.v., Amsterdam, The Netherlands                    #
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
  echo "A script to interactively create a config.ini file"
  echo "Usage: ${0} [prefix]"
  echo "  If prefix is supplied, it will be prepended to the config.ini file. Note that this could also be a path, although in that case, it should end with a /"
  exit 0
fi

set -e
cd "$( dirname "${BASH_SOURCE[0]}" )/../"

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
cat >"${1}config.ini" <<EOS
[environment]
datadir       = "${DATADIR}"
simplesamlphp = "${SIMPLESAML}"

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

exit 0
