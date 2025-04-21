<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class OptimizePaymentsTable extends BaseMigration
{
    public function change(): void
    {
        $sql = 'UPDATE `fcs_payments` SET type=1 WHERE type="product";
                UPDATE `fcs_payments` SET type=2 WHERE type="payback";
                UPDATE `fcs_payments` SET type=3 WHERE type="deposit";
                ALTER TABLE `fcs_payments` CHANGE `type` `type` TINYINT UNSIGNED NULL DEFAULT "1";
                ALTER TABLE `fcs_payments` ADD INDEX(`type`);
                ALTER TABLE `fcs_payments` ADD INDEX(`id_manufacturer`);
                ALTER TABLE `fcs_payments` ADD INDEX(`id_customer`);
                ALTER TABLE `fcs_payments` ADD INDEX(`status`);
                ALTER TABLE `fcs_payments` ADD INDEX(`approval`);
                ALTER TABLE `fcs_payments` ADD INDEX(`invoice_id`);
                ALTER TABLE `fcs_payments` ADD INDEX(`amount`);';
        $this->execute($sql);
    }
}
