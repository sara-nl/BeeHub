TRUNCATE TABLE `beehub_groups`;
INSERT INTO `beehub_groups` (`group_name`, `displayname`, `description`)
     VALUES ('foo', 'Foo', 'Group of all Foo employees'),
            ('bar', 'Bar', 'Group of all Bar members' );

TRUNCATE TABLE `beehub_group_members`;
INSERT INTO `beehub_group_members` (`group_name`, `user_name`, `is_admin`, `is_invited`, `is_requested`)
     VALUES ('foo', 'john', 1, 1, 1),
            ('foo', 'jane', 0, 1, 0),
            ('bar', 'john', 1, 1, 1),
            ('bar', 'jane', 0, 0, 1);

TRUNCATE TABLE `beehub_sponsors`;
INSERT INTO `beehub_sponsors` (`sponsor_name`, `displayname`, `description`)
     VALUES ('sponsor_a', 'Company A', 'Company A has paid for some storage'),
            ('sponsor_b', 'Company B', 'Company B has paid for even more storage');

TRUNCATE TABLE `beehub_sponsor_members`;
INSERT INTO `beehub_sponsor_members` (`sponsor_name`, `user_name`, `is_admin`, `is_accepted`)
     VALUES ('sponsor_a', 'john', 1, 1),
            ('sponsor_b', 'john', 1, 1),
            ('sponsor_b', 'jane', 0, 0);

TRUNCATE TABLE `beehub_users`;
INSERT INTO `beehub_users` (`user_name`, `displayname`, `email`, `unverified_email`, `password`, `surfconext_id`, `surfconext_description`, `x509`, `sponsor_name`, `verification_code`, `verification_expiration`, `password_reset_code`, `password_reset_expiration`)
     VALUES ('john' , 'John Doe' , 'john.doe@mailservice.com' , NULL                   , '$6$rounds=5000$126b519331f5189c$cvGKahLo6.q/TSTeLMxGC8qpwHC6QIA37NCdn6xKpBJVCU3vBzwJkK3HS7.d4RwJcCG.oXMHBiv06oMnZCwjM0', NULL        , NULL                                              , NULL         , 'sponsor_a', NULL            , NULL                 , NULL                 , NULL),
            ('jane' , 'Jane Doe' , 'jane.doe@mailservice.com' , 'j.doe@mailservice.com', '$6$rounds=5000$cvGKahLo6.q/TSTe$20dAPKtCcskhKC7SJ0ObfQodGu8dKhj5Eb4ipm09NK7RRJzTONJTsZgxASn3I1PtV6Yrwi186Xw9mn2mOxqKc.', 'qwertyuiop', 'Account at the top row of letters on my keyboard', 'CN=Jane Doe', NULL       , 'somesecretcode', '2038-01-19 03:14:07', 'someothersecretcode', '2038-01-19 03:14:07'),
            ('johny', 'Johny Doe', 'johny.doe@mailservice.com', NULL                   , '$6$rounds=5000$126b519331f5189c$cvGKahLo6.q/TSTeLMxGC8qpwHC6QIA37NCdn6xKpBJVCU3vBzwJkK3HS7.d4RwJcCG.oXMHBiv06oMnZCwjM0', NULL        , NULL                                              , NULL         , 'sponsor_a', NULL            , NULL                 , NULL                 , NULL);

TRUNCATE TABLE `Locks`;
INSERT INTO `Locks` (`lock_token`, `lock_root`, `lock_owner`, `lock_depth`, `lock_timeout`)
     VALUES ('opaquelocktoken:01234567-89ab-cdef-0123456789abcdef0', '/some/path/'      , 'Some client software' , 0, 123456),
            ('opaquelocktoken:fedcba98-7654-3210-fedcba9876543210f', '/some/other/path/', 'Other client software', 1, 987654);

TRUNCATE TABLE `ETag`;
INSERT INTO `ETag` (`etag`)
     VALUES (1),
            (2);