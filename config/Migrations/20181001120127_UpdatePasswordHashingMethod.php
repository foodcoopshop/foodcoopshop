<?php
use Migrations\AbstractMigration;

class UpdatePasswordHashingMethod extends AbstractMigration
{
    
    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_customer` CHANGE `passwd` `passwd` CHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
        ");
    }
}
