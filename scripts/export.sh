#!/bin/bash
cd "$(dirname '$0')"/..
touch debug.txt
chmod 666 debug.txt
if [ ! -f secret.php ]; do
	touch secret.php
	cat >secret.php <<EOS
<?php
define('DATABASE_PASSWORD', 'XXX');
EOS
