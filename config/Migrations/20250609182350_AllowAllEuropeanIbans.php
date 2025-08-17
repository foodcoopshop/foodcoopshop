<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AllowAllEuropeanIbans extends BaseMigration
{

    public function change(): void
    {
        $this->table('fcs_manufacturer')
            ->changeColumn('iban', 'string', [
                'limit' => 34,
            ])
            ->update();
    }

}
