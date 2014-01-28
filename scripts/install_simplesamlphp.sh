#!/bin/bash

# Install SimpleSamlPHP

if [ "${1}" == "-h" ] || [ "${1}" == "--help" ]; then
  echo "Install SimpleSamlPHP"
  echo ""
  echo "Usage: ${0}"
  exit 0
fi

set -e
cd "$( dirname "${BASH_SOURCE[0]}" )/../"

# Link to simplesamlphp
echo ""
echo "Please enter the path to SimpleSamlPHP or leave empty to skip its configuration:"
read SIMPLESAML
if [[ "${SIMPLESAML}" != "" ]]; then
  if [[ "${SIMPLESAML:(-1)}" != "/" ]]; then
    SIMPLESAML="${SIMPLESAML}/"
  fi
  rm -vf public/system/simplesaml
  ln -vs "${SIMPLESAML}www/" public/system/simplesaml
else
  echo "If SimpleSamlPhp is not configured yet or you have problems using SURFconext"
  echo "run ${0} again after installing SimpleSamlPHP."
fi

exit 0
