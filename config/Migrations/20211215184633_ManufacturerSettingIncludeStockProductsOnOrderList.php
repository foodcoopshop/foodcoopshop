<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ManufacturerSettingIncludeStockProductsOnOrderList extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_manufacturer` ADD `include_stock_products_in_order_lists` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' AFTER `no_delivery_days`;");
    }
}
