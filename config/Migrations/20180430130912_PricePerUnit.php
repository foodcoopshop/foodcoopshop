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
        ");
    }
    
}
