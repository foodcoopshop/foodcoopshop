<?php
use Migrations\AbstractMigration;

class Configurations extends AbstractMigration
{
    public function change()
    {
        $this->execute('
            DELETE FROM `fcs_configuration` WHERE `fcs_configuration`.`name` = "FCS_DB_VERSION";
            DELETE FROM `fcs_configuration` WHERE `fcs_configuration`.`name` = "FCS_DB_UPDATE";
        ');
    }
}
