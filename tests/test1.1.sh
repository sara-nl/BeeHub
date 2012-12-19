DESCRIPTION='Home directory listing'

source 'common.inc'

function systemtest() (
	${CURL} \
		--header 'Depth: 1' \
		--request 'PROPFIND' \
		${BASEURL}/
)
