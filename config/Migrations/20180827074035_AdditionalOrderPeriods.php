<?php
use Migrations\AbstractMigration;

class AdditionalOrderPeriods extends AbstractMigration
{
    public function change()
    {
        
        $this->execute("
            ALTER TABLE `fcs_product` ADD `delivery_rhythm_type` VARCHAR(10) NOT NULL DEFAULT 'week' AFTER `active`,
                ADD `delivery_rhythm_count` TINYINT(10) NOT NULL DEFAULT '1' AFTER `delivery_rhythm_type`,
                ADD `delivery_rhythm_first_delivery_day` DATE NULL AFTER `delivery_rhythm_count`;
        ");
        
    }
}
