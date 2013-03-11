#!/bin/bash

cd $(dirname "${BASH_SOURCE[0]}")
cd ../src

echo 'url(en/de)code instead of rawurl(en/de)code:'
grep -P '\burl(en|de)code\b' *.php
[ $? -eq 0 ] || echo OK

echo
echo 'DAV::$REGISTRY instead of BeeHub_Registry::inst():'
grep -P 'DAV::\$REGISTRY' *.php
[ $? -eq 0 ] || echo OK

echo
echo 'htmlspecialchars()/htmlentities() instead of DAV::xml_escape():'
grep -P '\bhtml(?:specialchars|entities)\b' *.php
[ $? -eq 0 ] || echo OK

echo
echo 'Never call die() or exit():'
grep -P '\b(die|exit)\b' *.php
[ $? -eq 0 ] || echo OK
