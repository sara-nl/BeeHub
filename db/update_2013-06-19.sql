ALTER TABLE `beehub_users`
 ADD COLUMN `password_reset_code` varchar(32) NULL default NULL COMMENT 'If the user wants a password reset, he/she is sent an e-mail with this code',
 ADD COLUMN `password_reset_expiration` timestamp NULL default NULL COMMENT 'The moment the password_reset_code should expire';