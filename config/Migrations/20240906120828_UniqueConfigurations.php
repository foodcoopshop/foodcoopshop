<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class UniqueConfigurations extends AbstractMigration
{

    public function change(): void
    {
        $this->table('fcs_configuration')
        ->removeIndex(['name'])
        ->addIndex(['name'], ['unique' => true])
        ->update();
    }
}
