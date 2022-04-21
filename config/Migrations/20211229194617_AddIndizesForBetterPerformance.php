<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddIndizesForBetterPerformance extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_category_product` ADD INDEX(`id_category`);");
        $this->execute("ALTER TABLE `fcs_product` ADD INDEX(`is_stock_product`);");
        $this->execute("ALTER TABLE `fcs_manufacturer` ADD INDEX(`stock_management_enabled`);");
        $this->execute("ALTER TABLE `fcs_category` ADD INDEX(`active`);");
        $this->execute("ALTER TABLE `fcs_barcodes` ADD INDEX(`barcode`);");
    }
}
