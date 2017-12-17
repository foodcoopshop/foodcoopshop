ALTER TABLE `fcs_customer` DROP `last_passwd_gen`;
ALTER TABLE `fcs_customer` ADD `change_password_code` VARCHAR(12) NULL DEFAULT NULL AFTER `passwd`;
