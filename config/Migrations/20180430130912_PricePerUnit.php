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
              `price_incl_per_unit` decimal(10,2) UNSIGNED DEFAULT NULL,
              `name` varchar(50) NOT NULL DEFAULT '',
              `amount` int(10) UNSIGNED DEFAULT NULL,
              `price_per_unit_enabled` tinyint(4) NOT NULL DEFAULT '0',
              `quantity_in_units` decimal(10,3) UNSIGNED DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `id_product` (`id_product`),
              UNIQUE KEY `id_product_attribute` (`id_product_attribute`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            ALTER TABLE `fcs_units` DROP INDEX `id_product`;
            ALTER TABLE `fcs_units` DROP INDEX `id_product_attribute`;
            ALTER TABLE `fcs_units` ADD UNIQUE( `id_product`, `id_product_attribute`);

            ALTER TABLE `fcs_order_detail` DROP `product_price`;
            ALTER TABLE `fcs_order_detail` CHANGE `product_quantity` `product_amount` INT(10) UNSIGNED NOT NULL DEFAULT '0';

            ALTER TABLE `fcs_manufacturer` CHANGE `send_ordered_product_quantity_changed_notification` `send_ordered_product_amount_changed_notification` INT(10) UNSIGNED NULL DEFAULT NULL;
            UPDATE fcs_action_logs set type = 'order_detail_product_amount_changed' WHERE type = 'order_detail_product_quantity_changed';

            CREATE TABLE `fcs_order_detail_units` (
              `id_order_detail` int(11) NOT NULL DEFAULT '0',
              `product_quantity_in_units` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
              `price_incl_per_unit` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
              `quantity_in_units` DECIMAL(10,3) UNSIGNED DEFAULT NULL,
              `unit_name` VARCHAR(50) NOT NULL DEFAULT '',
              `unit_amount` int(10) UNSIGNED DEFAULT NULL,
              UNIQUE KEY `id_order_detail` (`id_order_detail`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            ALTER TABLE `fcs_attribute` ADD `can_be_used_as_unit` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' AFTER `name`;
            UPDATE fcs_attribute SET can_be_used_as_unit = 1 WHERE name REGEXP \"^.*[k]?[g]{1}$\";

        ");
    }

}
