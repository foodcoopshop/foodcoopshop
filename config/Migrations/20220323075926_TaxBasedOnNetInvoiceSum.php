<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class TaxBasedOnNetInvoiceSum extends AbstractMigration
{
    public function change()
    {

        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Rechnungslegung für pauschalierte Betriebe<br /><div class="small">Die Berechnung der Umsatzsteuer erfolgt auf Basis der Netto-Rechnungsumme und ist <b>nicht</b> die Summe der Umsatzsteuerbeträge pro Stück.</div>';
                break;
            default:
                $text = 'Invoices for companies with fixed tax rate<br /><div class="small">Vat is calculated based on the sum of net price of the invoice.</div>';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_TAX_BASED_ON_NET_INVOICE_SUM', '".$text."', '0', 'readonly', '584', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

        $sql = "UPDATE fcs_configuration SET position = 585 WHERE name = 'FCS_TAX_BASED_ON_NET_INVOICE_SUM'";
        $this->execute($sql);
        $sql = "UPDATE fcs_configuration SET position = 586 WHERE name = 'FCS_INVOICE_NUMBER_PREFIX'";
        $this->execute($sql);
        $sql = "UPDATE fcs_configuration SET position = 587 WHERE name = 'FCS_PURCHASE_PRICE_ENABLED'";
        $this->execute($sql);

    }
}
