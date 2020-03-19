<?php
declare(strict_types=1);

use Cake\Datasource\ConnectionManager;
use Migrations\AbstractMigration;

class ChangeDefaultTableCollationToUtf8mb4 extends AbstractMigration
{
    public function change()
    {
        $tables = ConnectionManager::get('default')->getSchemaCollection()->listTables();
        foreach($tables as $table) {
            $this->execute("ALTER TABLE ".$table." CONVERT TO CHARACTER SET utf8mb4;");
        }
    }
}
