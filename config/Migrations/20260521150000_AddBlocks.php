<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddBlocks extends BaseMigration
{
    public function up(): void
    {
        $this->table('fcs_blocks')
            ->addColumn('image', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('image_position', 'tinyinteger', [
                'default' => '1',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('heading', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('content', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('position', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('active', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->create();
    }

    public function down(): void
    {
        $this->table('fcs_blocks')->drop()->save();
    }
}
