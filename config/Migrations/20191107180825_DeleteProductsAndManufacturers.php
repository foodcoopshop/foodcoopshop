<?php
use Migrations\AbstractMigration;

class DeleteProductsAndManufacturers extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_product` CHANGE `active` `active` INT(1) NOT NULL DEFAULT '0';");
        $this->execute("ALTER TABLE `fcs_manufacturer` CHANGE `active` `active` INT(1) NOT NULL DEFAULT '0';");
    }
}
