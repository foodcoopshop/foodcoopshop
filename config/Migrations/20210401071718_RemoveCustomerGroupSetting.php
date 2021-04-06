<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveCustomerGroupSetting extends AbstractMigration
{
    public function change()
    {
        $this->execute("DELETE from fcs_configuration WHERE name = 'FCS_CUSTOMER_GROUP';");
    }
}
