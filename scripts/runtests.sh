#!/bin/bash

# Runs the unit tests for the BeeHub code

if [ "${1}" == "-h" ] || [ "${1}" == "--help" ];then
  echo "Runs the unit tests for the BeeHub code"
  echo ""
  echo "Usage: ${0} [path_to_test]"
  echo "  If path_to_test is supplied, then only that test is run. Note that this path"
  echo "  is relative to the tests directory!"
  exit 0
fi

cd "$( dirname "${BASH_SOURCE[0]}" )/.."

if [[ "${1}" != "" ]]; then
  echo ${1}
  TESTS="./tests/${1}"
else
  TESTS="./tests"
fi

./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php "${TESTS}"
