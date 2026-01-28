<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AnonymizedCustomerDataForManufacturers extends BaseMigration
{
    public function change(): void
    {
        $this->table('fcs_manufacturer')
            ->addColumn('anonymize_customers', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
        ->update();
    }
}
