<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Configurations extends AbstractMigration
{
    public function change(): void
    {
        $this->table('fcs_configuration')
        ->removeColumn('text')
        ->update();
    }
}
