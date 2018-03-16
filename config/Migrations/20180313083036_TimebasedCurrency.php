<?php
use Migrations\AbstractMigration;

class TimebasedCurrency extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            UPDATE fcs_configuration SET position = 190 WHERE name = 'FCS_BACKUP_EMAIL_ADDRESS_BCC';
            INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_TIMEBASED_CURRENCY_ENABLED', 'Zeitwährungs-Modul aktiv?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/zeitwaehrungs-modul\" target=\"_blank\">Infos zum Zeitwährungs-Modul</a></div>', '0', 'boolean', '200', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
            INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_TIMEBASED_CURRENCY_NAME', 'Zeitwährung: Name<br /><div class=\"small\">max. 10 Zeichen</div>', 'Stunden', 'text', '210', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
            INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_TIMEBASED_CURRENCY_SHORTCODE', 'Zeitwährung: Abkürzung<br /><div class=\"small\">max. 3 Zeichen</div>', 'h', 'text', '220', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
            INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE', 'Zeitwährung: Umrechnungskurs<br /><div class=\"small\">in €, 2 Kommastellen</div>', '10,00', 'number', '230', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
            INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_TIMEBASED_CURRENCY_MAX_OVERDRAFT_CUSTOMER', 'Zeitwährung: Überziehungsrahmen für Mitglieder<br /><div class=\"small\">Wie viele Stunden kann ein Mitglied maximal ins Minus gehen?</div>', '0', 'number', '240', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
            INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_TIMEBASED_CURRENCY_MAX_OVERDRAFT_MANUFACTURER', 'Zeitwährung: Überziehungsrahmen für Hersteller<br /><div class=\"small\">Wie viele Stunden kann ein Hersteller maximal ins Minus gehen?</div>', '0', 'number', '250', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
            ALTER TABLE `fcs_manufacturer`
                ADD `timebased_currency_enabled` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `enabled_sync_domains`,
                ADD `timebased_currency_max_percentage` TINYINT UNSIGNED NOT NULL DEFAULT '30' AFTER `timebased_currency_enabled`,
                ADD `timebased_currency_max_credit_balance` TINYINT UNSIGNED NULL DEFAULT '100' AFTER `timebased_currency_max_percentage`;
            ALTER TABLE `fcs_customer` ADD `timebased_currency_enabled` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `date_upd`;
            ALTER TABLE `fcs_orders` ADD `timebased_currency_money_sum` DECIMAL (6,2) UNSIGNED NULL AFTER `comment`;
            ALTER TABLE `fcs_orders` ADD `timebased_currency_time_sum` DECIMAL (6,2) UNSIGNED NULL AFTER `timebased_currency_money_sum`;
            ALTER TABLE `fcs_order_detail` ADD `timebased_currency_money` DECIMAL (6,2) UNSIGNED NULL AFTER `deposit`;
            ALTER TABLE `fcs_order_detail` ADD `timebased_currency_time` DECIMAL (6,2) UNSIGNED NULL AFTER `timebased_currency_money`;
        ");
    }
}
