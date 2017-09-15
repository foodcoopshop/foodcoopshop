CREATE TABLE `fcs_sync_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `domain` varchar(128) NOT NULL DEFAULT '',
  `active` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `fcs_sync_products` (
  `id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `sync_domain_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `local_product_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `remote_product_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `local_product_attribute_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `remote_product_attribute_id` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `fcs_manufacturer` ADD `enabled_sync_domains` VARCHAR(50) DEFAULT NULL AFTER `send_ordered_product_quantity_changed_notification`;

INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_NETWORK_PLUGIN_ENABLED', 'Netzwerk-Plugin aktiviert?<br /><div class=\"small\"><a href="https://foodcoopshop.github.io/de/netzwerk-plugin" target="_blank">Infos zum Netzwerk-Plugin</a></div>', '0', 'readonly', '50', '2017-09-14 00:00:00', '2017-09-14 00:00:00');
