<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddShoppingLists extends BaseMigration
{
    public function up(): void
    {
        $this->table('fcs_shopping_lists')
            ->addColumn('id_customer', 'integer', [
                'default' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('position', 'integer', [
                'default' => '0',
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('is_default', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['id_customer'])
            ->create();

        $this->table('fcs_shopping_list_products')
            ->addColumn('shopping_list_id', 'integer', [
                'default' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_product', 'integer', [
                'default' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_product_attribute', 'integer', [
                'default' => '0',
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('amount', 'integer', [
                'default' => '1',
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['shopping_list_id', 'id_product', 'id_product_attribute'], ['unique' => true])
            ->addIndex(['id_product'])
            ->create();
    }

    public function down(): void
    {
        $this->table('fcs_shopping_list_products')->drop()->save();
        $this->table('fcs_shopping_lists')->drop()->save();
    }
}
