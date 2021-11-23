<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class DeactivateCheckCreditReminder extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_customer` ADD `check_credit_reminder_enabled` TINYINT UNSIGNED NULL DEFAULT '1' AFTER `shopping_price`;");
    }
}
