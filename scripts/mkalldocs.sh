#!/bin/bash

cd "$( dirname "${BASH_SOURCE[0]}" )/../"
DOCDIR=public/system/phpdoc
mkdir -p "$DOCDIR" 2>/dev/null
rm -rf "$DOCDIR"/* 2>/dev/null

phpdoc \
  --filename 'src/*.php,webdav-php/lib/*.php' \
  --target "$DOCDIR" \
  --parseprivate \
  --sourcecode \
  --defaultpackagename BeeHub \
  --output "HTML:Smarty:PHP"
  --title "BeeHub Documentation"
