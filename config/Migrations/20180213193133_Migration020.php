<?php
use Migrations\AbstractMigration;

class Migration020 extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_images` CHANGE `id_image` `id_image` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
        ");
    }
}
