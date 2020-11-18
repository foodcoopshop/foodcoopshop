<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddRetailMode2 extends AbstractMigration
{
    public function change()
    {
        $sql = "ALTER TABLE `fcs_order_detail` ADD `id_invoice` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `id_customer`;";
        $this->execute($sql);

        $sql = "ALTER TABLE `fcs_invoices` ADD `status` TINYINT(4) NOT NULL DEFAULT '0' AFTER `email_status`;";
        $this->execute($sql);
    }
}
