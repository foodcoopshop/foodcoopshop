<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddMemberSettingUseScanAppForMobileBarcodeScanning extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_customer` ADD `use_barcode_scan_app` TINYINT(3) UNSIGNED NULL DEFAULT '0' AFTER `timebased_currency_enabled`;");
    }
}
