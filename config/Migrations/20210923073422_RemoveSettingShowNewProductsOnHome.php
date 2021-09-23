<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveSettingShowNewProductsOnHome extends AbstractMigration
{
    public function change()
    {
        $this->execute("DELETE FROM `fcs_configuration` WHERE `name` = 'FCS_SHOW_NEW_PRODUCTS_ON_HOME';");
    }
}
