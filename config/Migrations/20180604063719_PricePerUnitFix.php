<?php
use Migrations\AbstractMigration;

class PricePerUnitFix extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_order_detail_units` CHANGE `product_quantity_in_units` `product_quantity_in_units` DECIMAL(10,3) UNSIGNED NULL DEFAULT NULL;
        ");
    }
}
