<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class SendDeliveryNotesEveryMonth extends AbstractMigration
{
    public function change()
    {
        $sql = "ALTER TABLE `fcs_manufacturer` ADD `send_delivery_notes` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `include_stock_products_in_order_lists`;";
        $this->execute($sql);
        $sql = "INSERT INTO `fcs_cronjobs` (`id`, `name`, `time_interval`, `day_of_month`, `weekday`, `not_before_time`, `active`) VALUES (NULL, 'SendDeliveryNotes', 'month', '5', null, '06:00:00', '0');";
        $this->execute($sql);
    }
}
