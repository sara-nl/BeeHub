#!/bin/bash

cd "$( dirname "${BASH_SOURCE[0]}"
source common.sh

#!/bin/bash

declare -r TMPTEST=$(mktemp)
declare -r LOGFILE='log.txt'

cd $(dirname "$0")
rm "${LOGFILE}" &>/dev/null
for TESTFILE in test?*.sh; do
  echo -n "${TESTFILE}: "
  (
    rm "./${TESTFILE}.run" 2>/dev/null
    source "./${TESTFILE}"
    if [ -z "${DESCRIPTION}" ]; then
      echo "Variable 'DESCRIPTION' not defined in file ${TESTFILE}"
      exit 1
    fi
    echo -n "${DESCRIPTION}: " >&3
    touch "./${TESTFILE}.run"
    echo
    echo "Running ${TESTFILE}:"
    set -x
    unittest
  ) 3>&1 1>>"$LOGFILE" 2>&1
  if [ \( $? -ne 0 \) -o \( ! -f "./${TESTFILE}.run" \) ]; then
    echo 'FAILED'
    echo "Check file '${LOGFILE}' for details."
    exit 1
  fi
  echo 'OK'
  rm -rf "./${TESTFILE}.run" "${TMPTEST}"* &>/dev/null
done
echo 'All tests completed successfully.'
