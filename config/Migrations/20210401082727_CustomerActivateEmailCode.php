<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CustomerActivateEmailCode extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_customer` ADD `activate_email_code` VARCHAR(12) NULL DEFAULT NULL AFTER `terms_of_use_accepted_date`;");
    }
}
