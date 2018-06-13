<?php
use Migrations\AbstractMigration;

class TreeLeftRightFix extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_category` CHANGE `nright` `nright` INT(10) NOT NULL DEFAULT '0';
            ALTER TABLE `fcs_category` CHANGE `nleft` `nleft` INT(10) NOT NULL DEFAULT '0';
            ALTER TABLE `fcs_pages` CHANGE `lft` `lft` INT(10) NOT NULL DEFAULT '0';
            ALTER TABLE `fcs_pages` CHANGE `rght` `rght` INT(10) NOT NULL DEFAULT '0';
        ");
    }
}
