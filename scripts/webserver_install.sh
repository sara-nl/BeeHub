#!/usr/bin/env bash

################################################################################
#                                                                              #
# A script to install the webserver                                            #
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

set -e
cd "$( dirname "${BASH_SOURCE[0]}" )/../"

# Set the configuration so it allows webserver installation
mv config.ini config.ini.orig
while read LINE; do
  if [[ "${LINE:0:11}" == "run_install" ]]; then
    echo 'run_install = "true"' >> config.ini
  else
    echo ${LINE} >> config.ini
  fi
done < config.ini.orig
rm config.ini.orig

# Obtain the URL to this instance
HTTPD_HOST=''
HTTPD_PORT=80
while [[ 1 -eq 1 ]]; do
  echo -en "\nMake sure your Apache webserver is configured as follows:\n"
  echo " - Use $(pwd)/public/ as document root"
  echo " - \"AccessFileName .htaccess\" and \"AllowOverride All\" for the document root, or copy the directives in $(pwd)/public/.htaccess into the Directory section of the central Apache configuration"
  echo " - Have at least the following modules installed:"
  echo "   * mod_rewrite"
  echo "   * mod_ssl"
  echo "   * php 5.3 or higher"
  echo " - Listen for HTTP connections (preferably on port 80)"
  echo " - Listen for HTTPS connections (preferably on port 443, but always 363 ports after the HTTP port)"
  echo " - Apache has write access to the data directory"
  echo " - Apache has write access to $(pwd)/public/system/js/server/"
  echo -e "\nAt which host is this instance available? [${HTTPD_HOST}] "
  read HTTPD_HOST_2
  if [[ "${HTTPD_HOST_2}" != "" ]]; then
    HTTPD_HOST="${HTTPD_HOST_2}"
  fi

  echo "At which port is this instance available over HTTP? [${HTTPD_PORT}] "
  read HTTPD_PORT_2
  if [[ "${HTTPD_PORT_2}" != "" ]]; then
    HTTPD_PORT="${HTTPD_PORT_2}"
  fi
  echo ""

  SSL_PORT=$[HTTPD_PORT+363];
  if curl --insecure --silent --fail "https://${HTTPD_HOST}:${SSL_PORT}/some/unexisting/path/" &&
     curl --silent --fail "http://${HTTPD_HOST}:${HTTPD_PORT}/some/unexisting/path/"; then
    break
  else
    echo -en "Either you entered wrong URL, or the webserver is unreachable. Please make sure the webserver is running and reachable and check the URL.\n\n"
  fi
done

# Ask what the first user should be named
CONTINUE=0
USERNAME='x'
PASSWORD='x'
EMAIL='x'
echo "I'll create the first user for you. Please give me some information:"
while [[ "${CONTINUE}" != "y" ]]; do
  echo "What should the username be? [${USERNAME}] "
  read USERNAME_2
  if [[ "${USERNAME_2}" != "" ]]; then
    PASSWORD="${USERNAME_2}"
  fi

  echo "What should the password be? [${PASSWORD}] "
  read PASSWORD_2
  if [[ "${PASSWORD_2}" != "" ]]; then
    PASSWORD="${PASSWORD_2}"
  fi

  echo "What is his/her e-mail address? [${EMAIL}] "
  read EMAIL_2
  if [[ "${EMAIL_2}" != "" ]]; then
    EMAIL="${EMAIL_2}"
  fi

  echo ""
  echo "Do you want to use these values (y/n)? [n] "
  read CONTINUE
done

curl --insecure --request POST --form "email=${EMAIL}" --user "${USER}:${PASSWORD}" "https://${HTTPD_HOST}:${SSL_PORT}/"

# Set the configuration so it will not allow webserver installation
mv config.ini config.ini.orig
while read LINE; do
  if [[ "${LINE:0:11}" == "run_install" ]]; then
    echo 'run_install = "false"' >> config.ini
  else
    echo ${LINE} >> config.ini
  fi
done < config.ini.orig
rm config.ini.orig

exit 0
