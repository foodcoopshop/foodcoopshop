ALTER TABLE `fcs_cake_payments` ADD `id_manufacturer` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `id_customer`;
UPDATE fcs_cake_action_logs SET type = 'payment_deposit_customer_added' WHERE type = 'payment_deposit_added';
UPDATE fcs_cake_action_logs SET type = 'payment_deposit_customer_deleted' WHERE type = 'payment_deposit_deleted';
INSERT INTO `fcs_configuration` (`id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES
(NULL, NULL, 1, 'FCS_SHOW_FOODCOOPSHOP_BACKLINK', 'Link auf www.foodcoopshop.com anzeigen?<br /><div class="small">Der Link wird im Footer und in den generierten PDFs (Bestelllisten, Rechnungen) angezeigt.</div>', '1', 'boolean', 180, '2016-11-27 00:00:00', '2016-11-27 00:00:00');
INSERT INTO `fcs_configuration` (`id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES
(NULL, NULL, 1, 'FCS_PAYMENT_PRODUCT_MAXIMUM', 'Maximalbetrag für jede Guthaben-Aufladung in Euro', '500', 'number', 127, '2016-11-28 00:00:00', '2016-11-28 00:00:00');
UPDATE `fcs_configuration` SET `position` = '125' WHERE `fcs_configuration`.`name` = 'FCS_MINIMAL_CREDIT_BALANCE';
ALTER TABLE `fcs_orders` ADD `general_terms_and_conditions_accepted` TINYINT(4) UNSIGNED NOT NULL AFTER `total_deposit`, ADD `cancellation_terms_accepted` TINYINT(4) UNSIGNED NOT NULL AFTER `general_terms_and_conditions_accepted`;
ALTER TABLE `fcs_manufacturer` ADD `firmenbuchnummer` VARCHAR(20) NOT NULL AFTER `bank_name`, ADD `firmengericht` VARCHAR(150) NOT NULL AFTER `firmenbuchnummer`, ADD `aufsichtsbehoerde` VARCHAR(150) NOT NULL AFTER `firmengericht`, ADD `kammer` VARCHAR(150) NOT NULL AFTER `aufsichtsbehoerde`, ADD `homepage` VARCHAR(255) NOT NULL AFTER `kammer`;
ALTER TABLE `fcs_customer` ADD `terms_of_use_accepted_date` DATE NOT NULL AFTER `secure_key`;
ALTER TABLE `fcs_cms` ADD `is_private` INT(11) UNSIGNED NOT NULL AFTER `id_customer`;
