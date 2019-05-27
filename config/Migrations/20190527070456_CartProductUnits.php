<?php
use Migrations\AbstractMigration;

class CartProductUnits extends AbstractMigration
{
    public function change()
    {
        $this->execute('
            CREATE TABLE `fcs_cart_product_units` (
               `id_cart_product` int(11) UNSIGNED NOT NULL,
               `quantity_in_units` decimal(10,3) UNSIGNED DEFAULT NULL
            );
        ');
    }
}
