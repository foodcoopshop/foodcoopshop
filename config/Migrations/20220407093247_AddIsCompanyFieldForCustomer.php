<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddIsCompanyFieldForCustomer extends AbstractMigration
{
    public function change()
    {

        $sql = "ALTER TABLE `fcs_customer` ADD `is_company` TINYINT NOT NULL DEFAULT '0' AFTER `id_default_group`;";
        $this->execute($sql);

        $sql = "ALTER TABLE `fcs_customer` CHANGE `firstname` `firstname` VARCHAR(50) NOT NULL DEFAULT '', CHANGE `lastname` `lastname` VARCHAR(50) NOT NULL DEFAULT '';";
        $this->execute($sql);

        $sql = "ALTER TABLE `fcs_address` CHANGE `lastname` `lastname` VARCHAR(50) NOT NULL DEFAULT '', CHANGE `firstname` `firstname` VARCHAR(50) NOT NULL DEFAULT '';";
        $this->execute($sql);

    }
}
