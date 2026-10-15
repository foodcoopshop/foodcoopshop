<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddConfigurationMessengerInfo extends BaseMigration
{
    public function change(): void
    {
        $this->table('fcs_configuration')->insert([
            ['name' => 'FCS_INFO_SIGNAL_ENABLED', 'active' => 1, 'value' => '0', 'type' => 'boolean', 'position' => 3410,],
            ['name' => 'FCS_INFO_WHATSAPP_ENABLED', 'active' => 1, 'value' => '0', 'type' => 'boolean', 'position' => 3420,],
        ])->saveData();
    }
}