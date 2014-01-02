#!/bin/bash

DATADIR='/space/data'
cd "$DATADIR"

[ $EUID -eq 0 ] || {
	echo "This script must be run by root." >&2
	exit 1
}

set -x

# Maak een nieuwe directory /home/
#[ -d home ] || mkdir home

# Verplaats alle home-dirs naar /home/
#for i in ./\~*
#do
#	mv "$i" home/"${i:3}"
#	#[ -d "$i" ] && mv "$i" home/${i:1}
#done

######## ACEs ########

# Dump alle acl attributen in een text-file:
#getfattr -h -R -d -n 'user.DAV%3A%20acl' . >/tmp/getfattr.txt 2>/dev/null

# Verander alle paden naar principals:
#perl -p -e 's/\\"(?:\\\\)?\/(users|groups)(?:\\\\)?\//\\"\/system\/$1\//g;' \
#  </tmp/getfattr.txt >/tmp/getfattr2.txt

# En dan de echte verandering:
#diff -y -W160 /tmp/getfattr.txt /tmp/getfattr2.txt
#setfattr -h --restore=/tmp/getfattr2.txt

######## Owners ########

# Dump alle owner attributen in een text-file:
#getfattr -h -R -d -n 'user.DAV%3A%20owner' . >/tmp/getfattr.txt 2>/dev/null

# Verander alle paden naar principals:
#perl -p -e 's/"\/users\//"\/system\/users\//g;' \
#  </tmp/getfattr.txt >/tmp/getfattr2.txt

# En dan de echte verandering:
#diff /tmp/getfattr.txt /tmp/getfattr2.txt
#setfattr -h --restore=/tmp/getfattr2.txt

######## Sponsors ########

# Dump alle sponsor attributen in een text-file:
#getfattr -h -R -d -n 'user.DAV%3A%20group' . >/tmp/getfattr.txt 2>/dev/null

# Verander alle paden naar principals:
# user.DAV%3A%20group="/groups/
#perl -p -e 's/^user\.DAV\%3A\%20group="\/groups\//user.http\%3A\%2F\%2Fbeehub.nl\%2F\%20sponsor="\/system\/sponsors\//g;' \
#  </tmp/getfattr.txt >/tmp/getfattr2.txt

#perl -p -e 's/^user\.DAV\%3A\%20group$/user.http\%3A\%2F\%2Fbeehub.nl\%2F\%20sponsor="\/system\/sponsors\/biggrid/g;' \
#  </tmp/getfattr2.txt >/tmp/getfattr3.txt

#perl -p -e 's/\/system\/sponsors\/biggrid/\/system\/sponsors\/e-infra/g;' \
#  </tmp/getfattr3.txt >/tmp/getfattr4.txt

# En dan de echte verandering:
#diff /tmp/getfattr.txt /tmp/getfattr2.txt
setfattr -h --restore=/tmp/getfattr4.txt
exit

find -print0 | xargs -0 setfattr -x 'user.DAV%3A%20group' 2>/dev/null
