#!/bin/bash

################################################################################
#                                                                              #
# Runs the unit tests for the BeeHub code                                      #
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

if [ "${1}" == "-h" ] || [ "${1}" == "--help" ];then
  echo "Runs the unit tests for the BeeHub code"
  echo ""
  echo "Usage: ${0} [path_to_test]"
  echo "  If path_to_test is supplied, then only that test is run. Note that this path"
  echo "  is relative to the tests directory!"
  exit 0
fi

cd "$( dirname "${BASH_SOURCE[0]}" )/.."

# If a command line parameter was given, this should indicate which test to run
if [[ "${1}" != "" ]]; then
  echo ${1}
  TESTS="./tests/${1}"
else
  TESTS="./tests"
fi

./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php "${TESTS}"
