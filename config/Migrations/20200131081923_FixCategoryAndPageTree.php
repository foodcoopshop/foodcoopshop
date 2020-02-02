<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class FixCategoryAndPageTree extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_category` CHANGE `id_parent` `id_parent` INT(10) UNSIGNED NULL DEFAULT '0';
            ALTER TABLE `fcs_pages` CHANGE `id_parent` `id_parent` INT(10) UNSIGNED NULL DEFAULT '0';
            UPDATE `fcs_category` SET `id_parent` = 0 WHERE `id_parent` IS NULL;
            UPDATE `fcs_pages` SET `id_parent` = 0 WHERE `id_parent` IS NULL;
        ");
    }
}
