<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class PrefixForInvoices extends AbstractMigration
{
    public function change()
    {

        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Präfix für Rechnungs-Nummernkreis';
                break;
            default:
                $text = 'Prefix for invoice numbers';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_INVOICE_NUMBER_PREFIX', '".$text."', '', 'readonly', '583', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

    }
}
