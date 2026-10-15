<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddConfigurationInfoChannels extends BaseMigration
{
    public function change(): void
    {
        $this->table('fcs_configuration')->insert([
            ['name' => 'FCS_INFO_CHANNELS', 'active' => 1, 'value' => '', 'type' => 'multiple_dropdown', 'position' => 3410,],
        ])->saveData();
    }
}