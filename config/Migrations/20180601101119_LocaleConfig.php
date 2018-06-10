<?php
use Migrations\AbstractMigration;

class LocaleConfig extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_configuration` ADD `locale` VARCHAR(5) NULL DEFAULT NULL AFTER `position`;
            UPDATE fcs_configuration SET locale = 'de_DE';
        ");
    }
}
