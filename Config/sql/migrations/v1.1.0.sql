ALTER TABLE `fcs_cake_payments` ADD `id_manufacturer` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `id_customer`;
UPDATE fcs_cake_action_logs SET type = 'payment_deposit_customer_added' WHERE type = 'payment_deposit_added';
UPDATE fcs_cake_action_logs SET type = 'payment_deposit_customer_deleted' WHERE type = 'payment_deposit_deleted';
INSERT INTO `fcs_configuration` (`id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES
(NULL, NULL, 1, 'FCS_SHOW_FOODCOOPSHOP_BACKLINK', 'Link auf www.foodcoopshop.com anzeigen?<br /><div class="small">Der Link wird im Footer und in den generierten PDFs (Bestelllisten, Rechnungen) angezeigt.</div>', '1', 'boolean', 180, '2016-11-27 00:00:00', '2016-11-27 00:00:00');
