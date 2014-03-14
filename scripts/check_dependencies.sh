#!/usr/bin/env bash

echo 'Checking for dependencies:'
FAILURE=0

function check_dependency {
  echo -n "  ${1}..."
  which ${1} >/dev/null 2>&1
  if [[ $? -eq 0 ]]; then
    echo 'ok'
  else
    FAILURE=1
    echo 'NOT FOUND'
  fi
}

check_dependency php
check_dependency composer.phar
check_dependency litmus
check_dependency mongo

if [[ $FAILURE -ne 0 ]]; then
  echo "Not all dependencies are installed, please do so" >&2
  exit 1
else
  exit 0
fi
