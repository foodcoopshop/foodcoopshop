<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AvailableAmountBasedOnUnits extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('fcs_stock_available');
        $decimalOptions = [
            'precision' => 10,
            'scale' => 3,
        ];
        $table->changeColumn('quantity', 'decimal', $decimalOptions);
        $table->changeColumn('quantity_limit', 'decimal', $decimalOptions);
        $table->changeColumn('sold_out_limit', 'decimal', $decimalOptions);
        $table->update();

        $table = $this->table('fcs_units');
        $table->addColumn('use_weight_as_amount', 'tinyinteger', [
            'default' => 0,
            'null' => false,
        ]);
        $table->update();

    }
}
