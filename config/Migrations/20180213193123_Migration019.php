<?php
use Migrations\AbstractMigration;

class Migration019 extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_product_lang` ADD `is_declaration_ok` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' AFTER `name`;
        ");
    }
}
