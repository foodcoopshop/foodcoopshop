<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class MarkPricePerUnitAsSaved extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_order_detail_units` ADD `mark_as_saved` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `unit_amount`;");
    }
}
