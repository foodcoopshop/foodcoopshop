<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class DiscountForCustomers extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_customer` ADD `discount` VARCHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' AFTER `user_id_registrierkasse`;");
        $this->execute("ALTER TABLE `fcs_order_detail` ADD `discount` VARCHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' AFTER `pickup_day`;");
    }
}
