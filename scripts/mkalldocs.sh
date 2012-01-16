#!/bin/bash

cd "$( dirname '$0' )"/..
DIRNAME="$PWD"
mkdir docs 2>/dev/null
rm -rf docs/devel docs/user 2>/dev/null


mkdir docs/devel 2>/dev/null
phpdoc \
  --filename 'dav/*.php','sd/*.php' \
  --ignore '*/.svn' \
  --target "${DIRNAME}/docs/devel" \
  --output HTML:frames:default \
  --parseprivate on \
  --sourcecode on \
  --defaultpackagename DAV_Server \
  --title "DAV_Server Documentation"
exit

mkdir docs/user 2>/dev/null
phpdoc \
  --filename 'dav/*.php','sd/*.php' \
  --ignore '*/.svn/' \
  --target "${DIRNAME}/docs/user" \
  --output HTML:frames:default \
  --parseprivate off \
  --sourcecode on \
  --defaultpackagename DAV_Server \
  --title "DAV_Server Documentation"
