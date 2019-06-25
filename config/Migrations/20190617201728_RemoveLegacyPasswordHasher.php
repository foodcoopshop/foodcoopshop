<?php
use Migrations\AbstractMigration;

class RemoveLegacyPasswordHasher extends AbstractMigration
{
    public function change()
    {
        $this->execute("UPDATE fcs_customer SET passwd = '' WHERE LENGTH(passwd) = 32;");
    }
}
