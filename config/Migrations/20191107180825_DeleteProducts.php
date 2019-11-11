<?php
use Migrations\AbstractMigration;

class DeleteProducts extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_product` CHANGE `active` `active` INT(1) NOT NULL DEFAULT '0';");
    }
}
