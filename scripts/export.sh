#!/bin/bash

# Go to the top level BeeHub directory:
cd "$(dirname "${BASH_SOURCE[0]}")"/..

# Preamble:
errortrap() {
  set +x
  echo "Something went wrong. Fix it and try again. Namaste." >&2
}
trap errortrap ERR
set -xe

###### The actual work starts here ######

# Create a debug log file:
touch debug.txt
chmod 666 debug.txt

# Create a symlink from bootstrap to our own less-file:
pushd public/system/bootstrap/less
ln -sf ../../beehub.less
popd

