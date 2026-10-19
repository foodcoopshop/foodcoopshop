<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddConfigurationMemberTypesEnabled extends BaseMigration
{
    public function change(): void
    {
        $this->table('fcs_configuration')->insert([
            ['name' => 'FCS_MEMBER_TYPES_ENABLED', 'active' => 1, 'value' => '0', 'type' => 'boolean', 'position' => 3420,],
        ])->saveData();
    }
}