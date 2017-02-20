ALTER TABLE `fcs_cake_payments` ADD `approval` TINYINT(4) NOT NULL AFTER `status`;
ALTER TABLE `fcs_cake_payments` ADD `approval_comment` TEXT NOT NULL AFTER `approval`;