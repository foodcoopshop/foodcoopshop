<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddIsCompanyFieldForCustomer extends AbstractMigration
{
    public function change()
    {
        $sql = "ALTER TABLE `fcs_customer` ADD `is_company` TINYINT NOT NULL DEFAULT '0' AFTER `id_default_group`;";
        $this->execute($sql);
    }
}
