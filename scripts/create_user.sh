#!/bin/bash

set -x
. $(dirname '$0')/common.sh

if [ "$#" -ne 3 ]; then
	echo "Usage:"
	echo $(basename '$0') '<login> <displayname> <passwd>'
	exit 1
fi

${CURL} -T - "${DAV_ROOT}users/$1" <<<''
${CURL} --data-binary @- \
	--request PROPPATCH \
	--header 'Content-Type: application/xml; charset="utf-8"' \
	"${DAV_ROOT}users/$1" <<eos
<?xml version="1.0" encoding="utf-8" ?>
<propertyupdate xmlns="DAV:">
	<set>
		<prop>
			<displayname>${2}</displayname>
			<passwd xmlns="http://webdav.sara.nl/">${3}</passwd>
		</prop>
	</set>
</propertyupdate>
eos

