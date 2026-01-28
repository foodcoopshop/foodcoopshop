<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class MarkInitialMigrationsAsMigrated extends BaseMigration
{
    public function change(): void
    {

        $this->execute("TRUNCATE phinxlog");

        $table = $this->table('phinxlog');
        $table->insert([
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
