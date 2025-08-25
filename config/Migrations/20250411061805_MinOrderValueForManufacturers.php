<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class MinOrderValueForManufacturers extends BaseMigration
{
    public function change(): void
    {
        $this->table('fcs_manufacturer')
            ->addColumn('min_order_value', 'decimal', [
            'default' => '0.00',
            'null' => false,
            'precision' => 10,
            'scale' => 2,
            ])
            ->update();
    }
}
