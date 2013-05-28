$(function() {
  $('.second_backup_policy_link').click(function() {
    $('a[href="#pane-backup"]').not('.second_backup_policy_link').click();
  });
});
