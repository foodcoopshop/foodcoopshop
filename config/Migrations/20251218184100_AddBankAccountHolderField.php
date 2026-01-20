<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddBankAccountHolderField extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('fcs_manufacturer');
        $table->addColumn('bank_account_holder', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
            'after' => 'bank_name',
        ]);
        $table->update();
    }
}
