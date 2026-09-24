<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddManufacturerOrderNumberToProducts extends BaseMigration
{
    public function up(): void
    {
        $this->table('fcs_product')
            ->addColumn('manufacturer_order_number', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
                'after' => 'new',
            ])
            ->update();
    }
}
