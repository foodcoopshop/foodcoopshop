<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddIndexForOrderDetailPickupDayCustomerId extends BaseMigration
{
    /**
     * @return void
     */
    public function change(): void
    {
        $sql = "ALTER TABLE `fcs_order_detail` ADD INDEX(`id_customer`, `pickup_day`);";
        $this->execute($sql);
    }
}
