<?php
declare(strict_types=1);

use Cake\I18n\FrozenTime;
use Migrations\AbstractMigration;

class MarkInitialMigrationsAsMigrated extends AbstractMigration
{
    public function change()
    {

        $this->execute("TRUNCATE phinxlog");

        $table = $this->table('phinxlog');
        $table->setData([
            [
                'version' => 20220928063531,
                'migration_name' => 'Initial',
                'start_time' => FrozenTime::now(),
                'end_time' => FrozenTime::now(),
            ],
            [
                'version' => 20220928064125,
                'migration_name' => 'AlterDataOnQueuedJobsToMediumtext',
                'start_time' => FrozenTime::now(),
                'end_time' => FrozenTime::now(),
            ],
        ]);

        $table->saveData();

    }
}
