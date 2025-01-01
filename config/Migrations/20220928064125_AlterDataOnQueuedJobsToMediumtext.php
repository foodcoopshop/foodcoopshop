<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AlterDataOnQueuedJobsToMediumtext extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('queued_jobs');
        $table->changeColumn('data', 'text', [
            'limit' => 16777215,
        ]);
        $table->update();
    }
}
