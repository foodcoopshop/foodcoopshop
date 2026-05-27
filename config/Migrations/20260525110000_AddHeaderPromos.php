<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddHeaderPromos extends BaseMigration
{
    public function up(): void
    {
        $this->table('fcs_header_promos')
            ->addColumn('page_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 55,
                'null' => true,
            ])
            ->addColumn('lead_text', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('text', 'text', [
                'default' => null,
                'null' => true,
            ])
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
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(['page_id'], [
                'name' => 'idx_header_promos_page_id',
                'unique' => true,
            ])
            ->create();
    }
}
