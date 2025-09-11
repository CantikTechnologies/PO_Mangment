ALTER TABLE `users_login_signup` ADD `role` VARCHAR(50) NOT NULL DEFAULT 'employee' AFTER `password`;
UPDATE `users_login_signup` SET `role` = 'admin' WHERE `email` = 'admin@123.com';