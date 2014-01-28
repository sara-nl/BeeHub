#!/bin/bash

# Creates API reference documentation for all BeeHub code

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
rm -rf docs-parser 2>/dev/null | true

# Everything worked out fine
exit 0
