<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddRetailMode3 extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_manufacturer` CHANGE `name` `name` VARCHAR(64) NULL DEFAULT NULL, CHANGE `uid_number` `uid_number` VARCHAR(30) NULL DEFAULT NULL, CHANGE `additional_text_for_invoice` `additional_text_for_invoice` MEDIUMTEXT NULL DEFAULT NULL, CHANGE `iban` `iban` VARCHAR(22) NULL DEFAULT NULL, CHANGE `bic` `bic` VARCHAR(11) NULL DEFAULT NULL, CHANGE `bank_name` `bank_name` VARCHAR(255) NULL DEFAULT NULL, CHANGE `firmenbuchnummer` `firmenbuchnummer` VARCHAR(20) NULL DEFAULT NULL, CHANGE `firmengericht` `firmengericht` VARCHAR(150) NULL DEFAULT NULL, CHANGE `aufsichtsbehoerde` `aufsichtsbehoerde` VARCHAR(150) NULL DEFAULT NULL, CHANGE `kammer` `kammer` VARCHAR(150) NULL DEFAULT NULL, CHANGE `homepage` `homepage` VARCHAR(255) NULL DEFAULT NULL;");
    }
}
