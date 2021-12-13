<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ImproveCustomerNotifications extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_customer` ADD `invoices_per_email_enabled` TINYINT UNSIGNED NULL DEFAULT '1' AFTER `check_credit_reminder_enabled`;");
        $this->execute("ALTER TABLE `fcs_customer` ADD `pickup_day_reminder_enabled` TINYINT UNSIGNED NULL DEFAULT '1' AFTER `invoices_per_email_enabled`;");
        $this->execute("ALTER TABLE `fcs_customer` ADD `credit_upload_reminder_enabled` TINYINT UNSIGNED NULL DEFAULT '1' AFTER `pickup_day_reminder_enabled`;");
        $this->execute("ALTER TABLE `fcs_customer` CHANGE `email_order_reminder` `email_order_reminder_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';");
    }
}
