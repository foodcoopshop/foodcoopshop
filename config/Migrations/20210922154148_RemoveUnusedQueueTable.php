<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveUnusedQueueTable extends AbstractMigration
{
    public function change()
    {
        $this->execute("DROP TABLE IF EXISTS queued_tasks;");
    }
}
