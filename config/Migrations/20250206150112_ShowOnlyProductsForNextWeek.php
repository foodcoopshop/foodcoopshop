<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class ShowOnlyProductsForNextWeek extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('fcs_customer');
        $table->addColumn('show_only_products_for_next_week', 'tinyinteger', [
            'default' => 0,
            'null' => false,
        ]);
        $table->update();
    }
}
