<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AnonymizedMemberDataForManufacturers extends AbstractMigration
{
    public function change()
    {
        $this->table('fcs_manufacturer')
            ->addColumn('anonymize_members', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
        ->update();
    }
}
