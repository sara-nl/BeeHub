#!/bin/bash

cd "$( dirname "$0" )/../"
DIRNAME="$PWD"
mkdir docs 2>/dev/null
rm -rf docs/* 2>/dev/null

phpdoc \
  --directory 'src,webdav-php/lib' \
  --filename '*.php' \
  --target "${DIRNAME}/docs" \
  --parseprivate \
  --sourcecode \
  --defaultpackagename BeeHub \
  --title "BeeHub Documentation"
