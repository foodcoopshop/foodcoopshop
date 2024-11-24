<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ProductsAddFieldForNew extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('fcs_product');
        $table->addColumn('new', 'date', [
            'default' => null,
            'null' => true,
        ])->update();
        $this->execute('UPDATE fcs_product SET new = created');
    }
}
