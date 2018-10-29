<?php
use Migrations\AbstractMigration;

class CorrectNetPrice extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            UPDATE fcs_order_detail od 
            LEFT JOIN fcs_order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
            SET od.total_price_tax_excl = od.total_price_tax_incl - odt.total_amount;
        ");
    }
}
