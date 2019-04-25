<?php
use Migrations\AbstractMigration;

class AddEmailForwardingForMembers extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $this->execute("ALTER TABLE `fcs_address` ADD `email_forwarding` varchar(255) DEFAULT NULL;");
    }
}
