<?php
use Migrations\AbstractMigration;

class AddAutoLoginHash extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_customer` ADD `auto_login_hash` VARCHAR(40) DEFAULT NULL AFTER `activate_new_password_code`;");
    }
}
