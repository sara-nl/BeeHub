#!/bin/bash

### CONFIGURATION BEGIN
declare -r TMPTEST=$(mktemp -d)
declare -r LOGFILE='log.txt'
### CONFIGURATION END

### Preamble BEGIN
cd "$( dirname "${BASH_SOURCE[0]}" )"
rm "${LOGFILE}" &>/dev/null
### Preamble END

# This script iterates over all scripts called "test<something>.sh".
# Each of these scripts is must define:
#   - ${DESCRIPTION}: a one-line description of the test
#   - function systemtest(), which performs the actual test. This function must
#     have a non-zero exit status on failure.
# Each script is sourced and executed in its own execution environment.
# Temporary files can safely be stored in $TMPDIR.
for TESTFILE in test?*.sh; do
  TMPDIR="${TMPTEST}/${TESTFILE}"
  mkdir "${TMPDIR}"
  echo -n "${TESTFILE}: "
  (
    source "./${TESTFILE}"
    if [ -z "${DESCRIPTION}" ]; then
      echo "Variable 'DESCRIPTION' not defined in file ${TESTFILE}"
      exit 1
    fi
    echo -n "${DESCRIPTION}: " >&3
    touch "${TMPDIR}.run"
    echo "Running ${TESTFILE}:"
    set -x
    systemtest
  ) 3>&1 1>"${TMPDIR}.log" 2>&1
  TESTSTATUS=$?
  cat "${TMPDIR}.log" >>"$LOGFILE"
  echo >>"$LOGFILE"; echo >>"$LOGFILE"
  if [ \( $TESTSTATUS -ne 0 \) -o \( ! -f "${TMPDIR}.run" \) ]; then
    echo 'FAILED'
    cat "${TMPDIR}.log"
    echo
    rm -rf "${TMPDIR}"* &>/dev/null
    exit 1
  fi
  echo 'OK'
  rm -rf "${TMPDIR}*" &>/dev/null
done
echo 'All tests completed successfully.'
