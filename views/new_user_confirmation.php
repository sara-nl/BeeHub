<?php require('views/header.php'); ?>
<p>You need to confirm you're e-mail address. There was an e-mail sent to <?= htmlspecialchars($email_address, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?> with instructions on how to do this.</p>
<?php require('views/footer.php'); ?>
