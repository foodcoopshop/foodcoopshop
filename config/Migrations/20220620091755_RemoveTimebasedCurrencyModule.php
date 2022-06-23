<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class RemoveTimebasedCurrencyModule extends AbstractMigration
{

    public function change()
    {
        $this->execute("DROP TABLE IF EXISTS fcs_timebased_currency_order_detail");
        $this->execute("DROP TABLE IF EXISTS fcs_timebased_currency_payments");
        $this->execute("DELETE FROM fcs_configuration WHERE name LIKE '%FCS_TIMEBASED_%'");
        $this->execute("ALTER TABLE fcs_customer DROP timebased_currency_enabled");
        $this->execute("ALTER TABLE fcs_manufacturer DROP timebased_currency_enabled");
        $this->execute("ALTER TABLE fcs_manufacturer DROP timebased_currency_max_percentage");
        $this->execute("ALTER TABLE fcs_manufacturer DROP timebased_currency_max_credit_balance");
    }
}
