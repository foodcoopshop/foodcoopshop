<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class MarkInitialMigrationsAsMigrated extends AbstractMigration
{
    public function change(): void
    {

        $this->execute("TRUNCATE phinxlog");

        $table = $this->table('phinxlog');
        $table->setData([
            [
                'version' => 20220928063531,
                'migration_name' => 'Initial',
            ],
            [
                'version' => 20220928064125,
                'migration_name' => 'AlterDataOnQueuedJobsToMediumtext',
            ],
        ]);

        $table->saveData();

    }
}
