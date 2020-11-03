<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class AddRetailMode extends AbstractMigration
{
    public function change()
    {

        $sql = "UPDATE `fcs_configuration` SET position = position * 10";
        $this->execute($sql);

        switch(I18n::getLocale()) {
            case 'de_DE':
                $textA = 'Einzelhandels-Modus aktiviert?<br /><div class="small"><a href="https://foodcoopshop.github.io/de/einzelhandel" target="_blank">Infos zur Verwendung im Einzelhandel</a></div>';
                $textB = 'Umsatzsteuersatz für Pfand';
                $textC = 'Header-Text für Rechnungen an Mitglieder';
                break;
            default:
                $textA = 'Retail mode activated?.';
                $textB = 'VAT for deposit';
                $textC = 'Header text for invoices to members';
                break;
        }

        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_RETAIL_MODE_ENABLED', '".$textA."', '0', 'readonly', '580', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_DEPOSIT_TAX_RATE', '".$textB."', '20', 'readonly', '581', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_INVOICE_HEADER_TEXT', '".$textC."', '', 'readonly', '582', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

        $sql = "INSERT INTO `fcs_cronjobs` (`id`, `name`, `time_interval`, `day_of_month`, `weekday`, `not_before_time`, `active`) VALUES (NULL, 'SendInvoicesWithRetailModeEnabled', 'week', NULL, 'Saturday', '10:00:00', '0');";
        $this->execute($sql);

        $sql = "UPDATE fcs_cronjobs SET name = 'SendInvoicesWithRetailModeDisabled' WHERE name = 'SendInvoices';";
        $this->execute($sql);

        $sql = "ALTER TABLE `fcs_payments` ADD `invoice_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `transaction_text`;";
        $this->execute($sql);

    }
}
