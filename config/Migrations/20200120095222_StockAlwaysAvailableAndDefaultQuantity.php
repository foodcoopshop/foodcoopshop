<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class StockAlwaysAvailableAndDefaultQuantity extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_stock_available` ADD `always_available` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `sold_out_limit`,
            ADD `default_quantity_after_sending_order_lists` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `always_available`;
            UPDATE `fcs_stock_available` SET `always_available` = 0;
            UPDATE `fcs_stock_available` SET `always_available` = 1 WHERE `quantity` > 99;
            ALTER TABLE `fcs_product_attribute` DROP `quantity`;
        ");
    }
}
