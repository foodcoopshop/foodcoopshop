<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CancellationEmailOptionForCustomers extends BaseMigration
{
    public function change(): void
    {
        $this->table('fcs_customer')
            ->addColumn('send_cancellation_email', 'tinyinteger', [
                'default' => 1,
                'null' => false,
                'unsigned' => true,
            ])
            ->update();
    }
}
