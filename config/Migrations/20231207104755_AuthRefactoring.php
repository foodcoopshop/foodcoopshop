<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AuthRefactoring extends AbstractMigration
{
    public function change(): void
    {
        $this->execute("ALTER TABLE `fcs_customer` DROP `auto_login_hash`;")
    }
}
