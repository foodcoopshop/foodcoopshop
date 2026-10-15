<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddInfoChannel extends BaseMigration
{
    public function change(): void
    {
        $this->table('fcs_customer')->addColumn('info_channel', 'tinyinteger', [
            'default' => 0,
            'null' => false,
        ])->update();

        $this->table('fcs_configuration')->insert([
            ['name' => 'FCS_INFO_CHANNELS', 'active' => 1, 'value' => '', 'type' => 'multiple_dropdown', 'position' => 3410,],
        ])->saveData();
    }
}