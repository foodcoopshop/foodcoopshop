<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AuthRefactoring extends BaseMigration
{
    public function change(): void
    {
        $this->execute("ALTER TABLE `fcs_customer` DROP `auto_login_hash`;");
    }
}
