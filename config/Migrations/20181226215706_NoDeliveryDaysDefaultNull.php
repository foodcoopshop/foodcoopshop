<?php
use Migrations\AbstractMigration;

class NoDeliveryDaysDefaultNull extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_manufacturer` CHANGE `no_delivery_days` `no_delivery_days` TEXT DEFAULT NULL;");
    }
}
