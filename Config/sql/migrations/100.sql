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
