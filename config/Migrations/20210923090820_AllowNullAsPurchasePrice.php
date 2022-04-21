<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AllowNullAsPurchasePrice extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_purchase_prices` CHANGE `price` `price` DECIMAL(20,6) NULL DEFAULT NULL;");
    }
}
