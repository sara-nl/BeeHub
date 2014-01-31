#!/bin/bash
# Check if server dir is server writable!
if [[ -e public/system/js/server/principals.js ]]; then
  cat > public/system/js/server/principals.js <<EOM
nl.sara.beehub.principals = {"users":{ ${WHEEL}:"Administrator" },"groups":{},"sponsors":{}};
EOM
fi

# Initialize the datadir, so the minimal number of subdirectories are created
START=''
while [[ "${START}" != "y" ]] && [[ "${START}" != "n" ]]; do
  echo ""
  echo "Initialize data dir? This will remove all end-user data (i.e. all data accessible through webDAV. Are you sure (y/n)? "
  read START
done
FAILURE="false"
if [[ "${START}" == "y" ]]; then
  rm -rvf "${DATADIR}*" "${DATADIR}.*"
  mkdir -p "${DATADIR}home" || FAILURE="true"
  mkdir -p "${DATADIR}system/groups" || FAILURE="true"
  mkdir -p "${DATADIR}system/sponsors" || FAILURE="true"
  mkdir -p "${DATADIR}system/users" || FAILURE="true"
  if [[ "${FAILURE}" == "true" ]]; then
    echo "Unable to create the system directories"
  else
    setfattr -n 'user.DAV%3A%20owner' -v "/system/users/${WHEEL}" "${DATADIR}"
    setfattr -n 'user.DAV%3A%20owner' -v "/system/users/${WHEEL}" "${DATADIR}home"
    setfattr -n 'user.DAV%3A%20owner' -v "/system/users/${WHEEL}" "${DATADIR}system"
    setfattr -n 'user.DAV%3A%20owner' -v "/system/users/${WHEEL}" "${DATADIR}system/groups"
    setfattr -n 'user.DAV%3A%20owner' -v "/system/users/${WHEEL}" "${DATADIR}system/sponsors"
    setfattr -n 'user.DAV%3A%20owner' -v "/system/users/${WHEEL}" "${DATADIR}system/users"
  fi
else
  FAILURE="true"
fi
# If no datadir initialization was done; let the user (of this script) know that he/she should do this himself/herself
if [[ "${FAILURE}" == "true" ]]; then
  echo ""
  echo "Don't forget to create in the data directory, /home/ /system/, /system/users/, /system/groups/, /system/sponsors/ directories and for all these directories run:"
  echo "setfattr -n 'user.DAV%3A%20owner' -v '/system/users/${WHEEL}' path/to/dir"
  echo ""
fi

./scripts/install_db.php || true

exit 0
