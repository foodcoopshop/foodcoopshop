<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddMessengerInfoPreferences extends BaseMigration
{
    public function change(): void
    {
        $this->table('fcs_customer')
            ->addColumn('info_signal_enabled', 'tinyinteger', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('info_whatsapp_enabled', 'tinyinteger', [
                'default' => 0,
                'null' => false,
            ])
            ->update();
    }
}