<?php
use Migrations\AbstractMigration;

class AlterEmailLogTextField extends AbstractMigration
{
    public function change()
    {
        $this->execute('ALTER TABLE `fcs_email_logs` CHANGE `message` `message` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;');
    }
}
