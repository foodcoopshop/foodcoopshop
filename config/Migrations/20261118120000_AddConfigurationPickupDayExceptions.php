<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddConfigurationPickupDayExceptions extends BaseMigration
{
    public function change(): void
    {
        $this->table('fcs_configuration')->insert([
            ['name' => 'FCS_PICKUP_DAY_EXCEPTIONS', 'active' => 1, 'value' => '', 'type' => 'json', 'position' => 110,],
        ])->saveData();
    }
}
