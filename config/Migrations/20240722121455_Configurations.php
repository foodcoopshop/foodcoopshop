<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Configurations extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('fcs_configuration');
        
        $table->removeColumn('id_configuration')
        ->removeColumn('text')
        ->removeColumn('locale')
        ->update();

        $table->addPrimaryKey(['name'])->update();
        
    }
}
