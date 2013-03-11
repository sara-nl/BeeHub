DESCRIPTION='Running the litmus test suite'

source 'common.inc.sh'

function systemtest() (
  pushd "${TMPDIR}"
    ${LITMUS_HOME}/bin/litmus -k \
      "http://${BEEHUB_SERVER}/~${BEEHUB_USER}/" \
      "${BEEHUB_USER}" "${BEEHUB_PASS}" \
      >litmus_out
  popd
  diff -c "${TMPDIR}/litmus_out" test_litmus.expect
  pushd "${TMPDIR}"
    ${LITMUS_HOME}/bin/litmus -k \
      "https://${BEEHUB_SERVER}/~${BEEHUB_USER}/" \
      "${BEEHUB_USER}" "${BEEHUB_PASS}" \
      >litmus_out
  popd
  diff -c "${TMPDIR}/litmus_out" test_litmus.expect
)
