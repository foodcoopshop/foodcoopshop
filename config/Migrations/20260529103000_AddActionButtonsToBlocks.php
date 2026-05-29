<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddActionButtonsToBlocks extends BaseMigration
{
    public function up(): void
    {
        $this->table('fcs_blocks')
            ->addColumn('primary_label', 'string', [
                'default' => null,
                'limit' => 25,
                'null' => true,
            ])
            ->addColumn('primary_href', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('secondary_label', 'string', [
                'default' => null,
                'limit' => 25,
                'null' => true,
            ])
            ->addColumn('secondary_href', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->update();
    }

    public function down(): void
    {
        $this->table('fcs_blocks')
            ->removeColumn('primary_label')
            ->removeColumn('primary_href')
            ->removeColumn('secondary_label')
            ->removeColumn('secondary_href')
            ->update();
    }
}