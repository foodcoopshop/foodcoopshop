<?php
use Migrations\AbstractMigration;

class ImproveNewPasswordRequest extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_customer` CHANGE `change_password_code` `activate_new_password_code` VARCHAR(12) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
            ALTER TABLE `fcs_customer` ADD `tmp_new_passwd` CHAR(60) NULL DEFAULT NULL AFTER `passwd`;
        ");
    }
}
