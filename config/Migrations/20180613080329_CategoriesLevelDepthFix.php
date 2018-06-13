<?php
use Migrations\AbstractMigration;

class CategoriesLevelDepthFix extends AbstractMigration
{

    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_category` DROP `level_depth`;
        ");
    }
}
