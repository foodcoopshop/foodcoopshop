<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class DifferentPricesForCustomers extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_customer` ADD `shopping_price` VARCHAR(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'SP' AFTER `user_id_registrierkasse`;");
        $this->execute("ALTER TABLE `fcs_order_detail` ADD `shopping_price` VARCHAR(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'SP' AFTER `pickup_day`;");
    }
}
