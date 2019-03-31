<?php
use Migrations\AbstractMigration;

class DifferentCartForInstantOrder extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_carts` ADD `cart_type` INT UNSIGNED NOT NULL DEFAULT '1' AFTER `id_customer`;");
    }
}
