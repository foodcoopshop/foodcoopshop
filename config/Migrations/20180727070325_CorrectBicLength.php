<?php
use Migrations\AbstractMigration;

class CorrectBicLength extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_manufacturer` CHANGE `bic` `bic` VARCHAR(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
        ");
    }
}
