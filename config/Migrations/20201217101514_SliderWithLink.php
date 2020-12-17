<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class SliderWithLink extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_sliders` ADD `link` VARCHAR(999) NULL DEFAULT NULL AFTER `image`;");
    }
}
