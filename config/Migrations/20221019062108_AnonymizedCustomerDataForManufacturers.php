<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AnonymizedCustomerDataForManufacturers extends AbstractMigration
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
