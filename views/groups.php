<?php
$this->setTemplateVar('active', "groups");
$this->setTemplateVar('header', '<style type="text/css">
.fieldname {
  text-align: right;
}
div.passwd {
  display: none;
}
.groupname {
  padding: 0.5em;
  background: #ddd;
}
.groupdescription {
  padding: 0.5em;
  margin-bottom: 2em;
}
.actions {
  margin: 10px 0;
  text-align: right;
}
</style>');
$this->setTemplateVar('footer', <<<EOS
<script type="text/javascript">
  $(function (){
    $('#change_password').change(function() {
      if ($(this).attr('checked') == 'checked') {
        $('div.passwd').show("blind");
      }else{
        $('div.passwd').hide("blind");
      }
    });
  });
</script>
EOS
);
?>
<h1>Groups</h1>
<?php while ($group = $groups->fetch_assoc()) : ?>
  <div class="row-fluid groupname">
    <div class="span10"><h4><?= $group['name'] ?></h4></div>
    <div class="span2 actions"><?= ($group['admin'] ? '<a href="#">Admin</a> / ' : '') ?><a href="#">Unsubscribe</a></div>
  </div>
  <div class="row-fluid groupdescription">
    <div class="span9 offset1"><?= $group['description'] ?></div>
  </div>
<?php endwhile; ?>
