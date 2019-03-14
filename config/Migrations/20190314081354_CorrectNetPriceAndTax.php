<?php
use Migrations\AbstractMigration;

class CorrectNetPriceAndTax extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            UPDATE fcs_order_detail od
                SET
                    od.total_price_tax_incl = ROUND(od.total_price_tax_incl, 2),
                    od.total_price_tax_excl = ROUND(od.total_price_tax_excl, 2)
                WHERE
                    ROUND(od.total_price_tax_incl, 2) <> od.total_price_tax_incl;
        ");
        $this->execute("
            UPDATE fcs_order_detail_tax odt
                SET
                    odt.unit_amount = ROUND(odt.unit_amount, 2),
                    odt.total_amount = ROUND(odt.total_amount, 2)
                WHERE
                    ROUND(odt.unit_amount, 2) <> odt.unit_amount;
        ");
        $this->execute("
            UPDATE fcs_order_detail_tax odt
            JOIN fcs_order_detail od ON od.id_order_detail = odt.id_order_detail
            SET odt.total_amount = odt.unit_amount * od.product_amount
            WHERE odt.total_amount <> (od.total_price_tax_incl - od.total_price_tax_excl);
        ");
    }
}
