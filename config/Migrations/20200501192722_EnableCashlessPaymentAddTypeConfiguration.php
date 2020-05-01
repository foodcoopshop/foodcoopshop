<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class EnableCashlessPaymentAddTypeConfiguration extends AbstractMigration
{
    public function change()
    {
        $this->execute("UPDATE fcs_configuration SET type='dropdown' WHERE name = 'FCS_CASHLESS_PAYMENT_ADD_TYPE'");
    }
}
