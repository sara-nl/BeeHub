#!/bin/bash

################################################################################
#                                                                              #
# Creates API reference documentation for all BeeHub code                      #
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

if [ "${1}" == "--help" ] || [ "${1}" == "-h" ]; then
  echo "Creates API reference documentation for all BeeHub code"
  echo "Usage: ${0}"
  exit 0
fi

set -e

# First let's make sure we're in the right directory and a tools directory is available
cd "$( dirname "${BASH_SOURCE[0]}" )/../"
DOCDIR=public/system/phpdoc
rm -rf "${DOCDIR}" docs-parser 2>/dev/null | true
mkdir -p "$DOCDIR" 2>/dev/null
./vendor/bin/phpdoc.php # configuration is stored in phpdoc.dist.xml

# Everything worked out fine
exit 0
