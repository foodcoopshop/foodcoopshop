<?php
use Migrations\AbstractMigration;

class AdditionalOrderPeriods extends AbstractMigration
{
    public function change()
    {
        
        $this->execute("
            ALTER TABLE `fcs_product` ADD `order_period_type` VARCHAR(10) NOT NULL DEFAULT 'week' AFTER `active`,
                ADD `order_period_amount` TINYINT(10) NOT NULL DEFAULT '1' AFTER `order_period_type`,
                ADD `first_delivery_day` DATE NULL AFTER `order_period_amount`;
        ");
        
    }
}
