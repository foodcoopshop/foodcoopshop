<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class GermanIbanFix extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_manufacturer` CHANGE `iban` `iban` VARCHAR(22) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '';");
    }
}
