<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class RenameReservedAtributeNameRank extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $this->table('fcs_storage_locations')
            ->renameColumn('rank', 'position')
            ->changeColumn('position', 'integer', [
                'default' => 0,
                'signed' => false,
                'null' => false,
            ])
            ->update();
    }
}
