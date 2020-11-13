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
                $textA = 'Rechnungsversand an Mitglieder aktiviert?<br /><div class="small"><a href="https://foodcoopshop.github.io/de/einzelhandel" target="_blank">Infos zur Verwendung im Einzelhandel</a></div>';
                $textB = 'Umsatzsteuersatz für Pfand';
                $valueB = '20,00';
                $textC = 'Header-Text für Rechnungen an Mitglieder';
                break;
            default:
                $textA = 'Send invoices to members?';
                $textB = 'VAT for deposit';
                $valueB = '20.00';
                $textC = 'Header text for invoices to members';
                break;
        }

        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_SEND_INVOICES_TO_CUSTOMERS', '".$textA."', '0', 'readonly', '580', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_DEPOSIT_TAX_RATE', '".$textB."', '".$valueB."', 'readonly', '581', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_INVOICE_HEADER_TEXT', '".$textC."', '', 'readonly', '582', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

        $sql = "INSERT INTO `fcs_cronjobs` (`id`, `name`, `time_interval`, `day_of_month`, `weekday`, `not_before_time`, `active`) VALUES (NULL, 'SendInvoicesToCustomers', 'week', NULL, 'Saturday', '10:00:00', '0');";
        $this->execute($sql);

        $sql = "UPDATE fcs_cronjobs SET name = 'SendInvoicesToManufacturers' WHERE name = 'SendInvoices';";
        $this->execute($sql);

        $sql = "ALTER TABLE `fcs_payments` ADD `invoice_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `transaction_text`;";
        $this->execute($sql);

        $sql = "
            ALTER TABLE `fcs_invoices` CHANGE `send_date` `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE `fcs_invoices` CHANGE `user_id` `id_customer` INT(10) UNSIGNED NOT NULL DEFAULT '0';
            UPDATE `fcs_invoices` SET id_customer = 0;
            ALTER TABLE `fcs_invoices` ADD `paid_in_cash` TINYINT(4) UNSIGNED NULL DEFAULT '0' AFTER `id_customer`;
            CREATE TABLE `fcs_invoice_taxes` (
              `id` int(11) NOT NULL,
              `invoice_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
              `tax_rate` double(20,6) NOT NULL DEFAULT '0.000000',
              `total_price_excl` double(20,6) NOT NULL DEFAULT '0.000000',
              `total_price_tax` double(20,6) NOT NULL DEFAULT '0.000000',
              `total_price_tax_incl` double(20,6) NOT NULL DEFAULT '0.000000'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ALTER TABLE `fcs_invoice_taxes`
              ADD PRIMARY KEY (`id`),
              ADD KEY `invoice_id` (`invoice_id`);
            ALTER TABLE `fcs_invoice_taxes`
              MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
            ";
        $this->execute($sql);

        $sql = "
            ALTER TABLE `fcs_invoices` ADD `filename` varchar(512) NOT NULL DEFAULT '' AFTER `paid_in_cash`;
            ALTER TABLE `fcs_invoices` ADD `email_status` datetime DEFAULT NULL AFTER `filename`;
        ";
        $this->execute($sql);

        $sql = "ALTER TABLE `fcs_invoices` CHANGE `invoice_number` `invoice_number` VARCHAR(14) NOT NULL DEFAULT '0';";
        $this->execute($sql);

    }
}
