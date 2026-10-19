<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddMemberType extends BaseMigration
{
    public function change(): void
    {
        $this->table('fcs_customer')->addColumn('member_type', 'tinyinteger', [
            'default' => null,
            'null' => true,
        ])->update();
    }
}