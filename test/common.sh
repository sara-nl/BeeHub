declare -r USERPASS=$(<userpass.txt)
declare -r CURL="curl --insecure --fail --user '${USERPASS}'"
declare -r BASEURL='https://beehub.nl'

