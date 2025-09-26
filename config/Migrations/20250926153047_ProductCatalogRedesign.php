<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class ProductCatalogRedesign extends BaseMigration
{
    public function change(): void
    {
        $this->table('fcs_category')
            ->addColumn('icon', 'string', [
                'limit' => 32,
                'null' => true,
                'default' => null,
                'after' => 'description'
            ])
            ->update();
    }
}
