<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveV2Migrations extends AbstractMigration
{
    public function change()
    {
        $this->execute("TRUNCATE TABLE phinxlog;");
    }
}
