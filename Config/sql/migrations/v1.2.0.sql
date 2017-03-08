INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_APP_NAME', 'Name der Foodcoop', '', 'text', '5', '2017-01-12 00:00:00', '2017-01-12 00:00:00');
INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_APP_ADDRESS', 'Adresse der Foodcoop<br /><div class="small">Wird im Footer von Homepage und E-Mails, Datenschutzerklärung, Nutzungsbedingungen usw. verwendet.</div>', '', 'textarea', '6', '2017-01-12 00:00:00', '2017-01-12 00:00:00');
INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_APP_EMAIL', 'E-Mail-Adresse der Foodcoop<br /><div class="small"></div>', '', 'text', '7', '2017-01-12 00:00:00', '2017-01-12 00:00:00');
INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_PLATFORM_OWNER', 'Betreiber der Plattform<br /><div class="small">Für Datenschutzerklärung und Nutzungsbedingungen, bitte auch Adresse angeben. Kann leer gelassen werden, wenn die Foodcoop selbst die Plattform betreibt.</div>', '', 'textarea', '8', '2017-01-12 00:00:00', '2017-01-12 00:00:00');
INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_SHOP_ORDER_DEFAULT_STATE', 'Bestellstatus für Sofort-Bestellungen', '1', 'dropdown', '75', '2017-01-12 00:00:00', '2017-01-12 00:00:00');
UPDATE `fcs_configuration` SET `type` = 'textarea_big' WHERE `fcs_configuration`.`name` = 'FCS_FOOTER_CMS_TEXT';
UPDATE `fcs_configuration` SET `type` = 'textarea_big' WHERE `fcs_configuration`.`name` = 'FCS_RIGHT_INFO_BOX_HTML';
UPDATE `fcs_configuration` SET `type` = 'textarea_big' WHERE `fcs_configuration`.`name` = 'FCS_REGISTRATION_EMAIL_TEXT';
UPDATE `fcs_configuration` SET `text` = 'Name der Foodcoop<br /><div class="small">Bitte im laufenden Betrieb nur in Absprache mit dem Plattform-Betreuer ändern.</div>' WHERE `fcs_configuration`.`name` = 'FCS_APP_NAME';

ALTER TABLE `fcs_cake_payments` ADD `approval` TINYINT(4) NOT NULL AFTER `status`;
ALTER TABLE `fcs_cake_payments` ADD `approval_comment` TEXT NOT NULL AFTER `approval`;
ALTER TABLE `fcs_cake_payments` ADD `changed_by` INT(10) UNSIGNED NOT NULL AFTER `approval_comment`;

UPDATE `fcs_configuration` SET `text` = 'Zusätzlicher Text, der in der Bestätigungsmail nach einer Registrierung versendet wird. <br /> <a href="/admin/configurations/previewEmail/FCS_REGISTRATION_EMAIL_TEXT" target="_blank"><img src="/js/vendor/famfamfam-silk/dist/png/information.png?1483041252" alt=""> E-Mail-Vorschau anzeigen</a>' WHERE `fcs_configuration`.`name` = 'FCS_REGISTRATION_EMAIL_TEXT';

ALTER TABLE `fcs_order_detail`
  DROP `id_order_invoice`,
  DROP `id_warehouse`,
  DROP `product_quantity_in_stock`,
  DROP `product_quantity_refunded`,
  DROP `product_quantity_return`,
  DROP `product_quantity_reinjected`,
  DROP `reduction_percent`,
  DROP `reduction_amount`,
  DROP `reduction_amount_tax_incl`,
  DROP `reduction_amount_tax_excl`,
  DROP `group_reduction`,
  DROP `product_quantity_discount`,
  DROP `product_ean13`,
  DROP `product_upc`,
  DROP `product_reference`,
  DROP `product_supplier_reference`,
  DROP `product_weight`,
  DROP `tax_computation_method`,
  DROP `tax_name`,
  DROP `tax_rate`,
  DROP `ecotax`,
  DROP `ecotax_tax_rate`,
  DROP `discount_quantity_applied`,
  DROP `download_hash`,
  DROP `download_nb`,
  DROP `download_deadline`,
  DROP `total_shipping_price_tax_incl`,
  DROP `total_shipping_price_tax_excl`,
  DROP `purchase_supplier_price`,
  DROP `original_product_price`,
  DROP `id_tax_rules_group`,
  DROP `original_wholesale_price`;
  
ALTER TABLE `fcs_orders`
  DROP `id_shop_group`,
  DROP `id_carrier`,
  DROP `id_lang`,
  DROP `id_cart`,
  DROP `id_currency`,
  DROP `id_address_delivery`,
  DROP `id_address_invoice`,
  DROP `secure_key`,
  DROP `payment`,
  DROP `conversion_rate`,
  DROP `module`,
  DROP `recyclable`,
  DROP `gift`,
  DROP `gift_message`,
  DROP `mobile_theme`,
  DROP `shipping_number`,
  DROP `total_discounts`,
  DROP `total_discounts_tax_incl`,
  DROP `total_discounts_tax_excl`,
  DROP `total_paid_real`,
  DROP `total_products`,
  DROP `total_products_wt`,
  DROP `total_shipping`,
  DROP `total_shipping_tax_incl`,
  DROP `total_shipping_tax_excl`,
  DROP `carrier_tax_rate`,
  DROP `total_wrapping`,
  DROP `total_wrapping_tax_incl`,
  DROP `total_wrapping_tax_excl`,
  DROP `invoice_number`,
  DROP `delivery_number`,
  DROP `invoice_date`,
  DROP `delivery_date`,
  DROP `valid`,
  DROP `round_mode`,
  DROP `round_type`;
  