#!/bin/bash

################################################################################
#                                                                              #
# Updates all BeeHub dependencies                                              #
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
  echo "Updates all BeeHub dependencies"
  echo ""
  echo "Usage: ${0}"
  exit 0
fi

set -e
cd "$( dirname "${BASH_SOURCE[0]}" )/../"

# Update Composer and all dependencies installed through Composer
composer.phar update

# 'compile' js-webdav-client and link the file
cd js-webdav-client
make dist.js
cd ..
rm -vf public/system/js/webdavlib.js
ln -vs "$(pwd)/js-webdav-client/dist.js" public/system/js/webdavlib.js

exit 0
