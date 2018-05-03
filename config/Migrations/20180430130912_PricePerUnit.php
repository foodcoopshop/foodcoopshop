<?php
use Migrations\AbstractMigration;

class PricePerUnit extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            CREATE TABLE `fcs_units` (
              `id` int(10) NOT NULL AUTO_INCREMENT,
              `id_product` int(10) UNSIGNED DEFAULT NULL,
              `id_product_attribute` int(11) UNSIGNED DEFAULT NULL,
              `price_incl_per_unit` decimal(10,6) UNSIGNED DEFAULT NULL,
              `name` varchar(50) NOT NULL DEFAULT '',
              `price_per_unit_enabled` tinyint(4) NOT NULL DEFAULT '0',
              `quantity_in_units` decimal(10,6) UNSIGNED DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `id_product` (`id_product`),
              UNIQUE KEY `id_product_attribute` (`id_product_attribute`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            ALTER TABLE `fcs_order_detail` CHANGE `product_quantity` `product_amount` INT(10) UNSIGNED NOT NULL DEFAULT '0';
            ALTER TABLE `fcs_manufacturer` CHANGE `send_ordered_product_quantity_changed_notification` `send_ordered_product_amount_changed_notification` INT(10) UNSIGNED NULL DEFAULT NULL;
            UPDATE fcs_action_logs set type = 'order_detail_product_amount_changed' WHERE type = 'order_detail_product_quantity_changed';

            ALTER TABLE `fcs_order_detail` ADD `product_units` DECIMAL(10,2) UNSIGNED NULL AFTER `total_price_tax_excl`;
            ALTER TABLE `fcs_order_detail` ADD `product_unit_price` DECIMAL(10,2) UNSIGNED NULL AFTER `product_units`;

            ALTER TABLE `fcs_order_detail` DROP `product_price`;

        ");
    }
    
}
