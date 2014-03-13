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
MONGO_HOST="localhost"
MONGO_PORT="27017"
MONGO_USERNAME=""
MONGO_PASSWORD=""
MONGO_DATABASE=""
REALM=""
MAIL_ADDRESS=""
MAIL_NAME=""
while [[ "${CONTINUE}" != "y" ]]; do
  while [[ 1 -eq 1 ]]; do
    echo "In what directory should the data be stored that is accessible through webDAV? [${DATADIR}] "
    read DATADIR_2
    if [[ "${DATADIR_2}" != "" ]]; then
      DATADIR="${DATADIR_2}"
    fi
    if [[ "${DATADIR:0:1}" != "/" ]]; then
      DATADIR="/${DATADIR}"
    fi
    DIR_EXISTS=1
    ls "${DATADIR}/" >/dev/null 2>&1 || DIR_EXISTS=0
    if [[ "${DATADIR}" == "/" ]] || [[ ${DIR_EXISTS} -eq 0 ]]; then
      echo "The specified directory does not exist"
    else
      break
    fi
  done

  while [[ 1 -eq 1 ]]; do
    echo "Where is SimpleSAMLphp installed? [${SIMPLESAML}] "
    read SIMPLESAML_2
    if [[ "${SIMPLESAML_2}" != "" ]]; then
      SIMPLESAML="${SIMPLESAML_2}"
    fi
    if [[ "${SIMPLESAML:0:1}" != "/" ]]; then
      SIMPLESAML="/${SIMPLESAML}"
    fi
    DIR_EXISTS=1
    ls "${SIMPLESAML}/" >/dev/null 2>&1 || DIR_EXISTS=0
    if [[ "${SIMPLESAML}" == "/" ]] || [[ ${DIR_EXISTS} -eq 0 ]]; then
      echo "The specified directory does not exist"
    else
      break
    fi
  done

  echo "What username should the administrator have? [${WHEEL}] "
  read WHEEL_2
  if [[ "${WHEEL_2}" != "" ]]; then
    WHEEL="${WHEEL_2}"
  fi

  while [[ 1 -eq 1 ]]; do
    echo "What host runs MongoDB? [${MONGO_HOST}] "
    read MONGO_HOST_2
    if [[ "${MONGO_HOST_2}" != "" ]]; then
      MONGO_HOST="${MONGO_HOST_2}"
    fi
    echo "What port is being used by MongoDB? [${MONGO_PORT}] "
    read MONGO_PORT_2
    if [[ "${MONGO_PORT_2}" != "" ]]; then
      MONGO_PORT="${MONGO_PORT_2}"
    fi
    echo "What is the MongoDB username? [${MONGO_USERNAME}] "
    read MONGO_USERNAME_2
    if [[ "${MONGO_USERNAME_2}" != "" ]]; then
      MONGO_USERNAME="${MONGO_USERNAME_2}"
    fi
    echo "What is the MongoDB password? [${MONGO_PASSWORD}] "
    read MONGO_PASSWORD_2
    if [[ "${MONGO_PASSWORD_2}" != "" ]]; then
      MONGO_PASSWORD="${MONGO_PASSWORD_2}"
    fi
    echo "What is the MongoDB database? [${MONGO_DATABASE}] "
    read MONGO_DATABASE_2
    if [[ "${MONGO_DATABASE_2}" != "" ]]; then
      MONGO_DATABASE="${MONGO_DATABASE_2}"
    fi

    TMPFILE=$(mktemp --tmpdir beehub.mongo_instructions.XXXXXXXXXX)
    echo 'db;' > "${TMPFILE}"
    MONGO_PARAMS=''
    if [[ "${MONGO_USERNAME}" != "" ]]; then
      $MONGO_PARAMS="--username '${MONGO_USERNAME}'"
    fi
    if [[ "${MONGO_PASSWORD}" != "" ]]; then
      $MONGO_PARAMS="${MONGO_PARAMS} --password '${MONGO_PASSWORD}'"
    fi
    MONGO_WORKS=1
    mongo --host "${MONGO_HOST}" --port "${MONGO_PORT}" ${MONGO_PARAMS} "${MONGO_DATABASE}" "${TMPFILE}" >/dev/null 2>&1 || MONGO_WORKS=0
    rm -f "${TMPFILE}"
    if [[ ${MONGO_WORKS} -eq 0 ]]; then
      echo -e "\nEither you entered wrong MongoDB connection information, or the MongoDB server is unreachable. Please make sure the MongoDB server is running and reachable and/or retry entering your MongoDB connection information"
    else
      break
    fi
  done

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

[mongo]
host     = "${MONGO_HOST}"
port     = "${MONGO_PORT}"
user     = "${MONGO_USERNAME}"
password = "${MONGO_PASSWORD}"
database = "${MONGO_DATABASE}"

[authentication]
realm = "${REALM}"

[email]
sender_address = "${MAIL_ADDRESS}"
sender_name    = "${MAIL_NAME}"

[install]
run_install = "false"
EOS

exit 0
