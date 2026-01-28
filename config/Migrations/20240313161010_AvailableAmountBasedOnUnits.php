<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AvailableAmountBasedOnUnits extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('fcs_stock_available');
        $options = [
            'precision' => 10,
            'scale' => 3,
            'null' => true,
        ];
        $table->changeColumn('quantity', 'decimal', $options);
        $table->changeColumn('quantity_limit', 'decimal', $options);
        $table->changeColumn('sold_out_limit', 'decimal', $options);
        $table->update();

        $table = $this->table('fcs_units');
        $table->addColumn('use_weight_as_amount', 'tinyinteger', [
            'default' => 0,
            'null' => false,
        ]);
        $table->update();

    }
}
