<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class BiggerQueuedJobDataField extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $this->execute("ALTER TABLE `queued_jobs` CHANGE `data` `data` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;");
    }
}
