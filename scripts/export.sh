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

# Create principals.js and make it apache-writable:
chmod a+rwx 'public/system/js/server'
