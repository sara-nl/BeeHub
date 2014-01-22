#!/bin/bash

cd "$( dirname "${BASH_SOURCE[0]}" )/.."

if [[ "${1}" != "" ]]; then
  echo ${1}
  TESTS="./tests/${1}"
else
  TESTS="./tests"
fi

./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php "${TESTS}"
