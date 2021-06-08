<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class EnablePurchasePrices extends AbstractMigration
{
    public function change()
    {

        // fix obsolete docs url
        $sql = "UPDATE fcs_configuration SET text = REPLACE(text, 'https://foodcoopshop.github.io/de/einzelhandel', 'https://foodcoopshop.github.io/de/dorfladen-online');";
        $this->execute($sql);

        // add new configuration
        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Einkaufspreis für Produkte erfassen?<div class="small">Der Einkaufspreis ist die Datengrundlage für die Gewinn-Statistik und für Lieferscheine an die Hersteller.</div>';
                break;
            default:
                $text = 'Enable input of purchase price?<div class="small">The purchase price is the base for profit statistics and bill of delivery to manufacturers.</div>';
                break;
        }
        $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_PURCHASE_PRICE_ENABLED', '".$text."', '0', 'readonly', '583', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

        $sql = "ALTER TABLE `fcs_units` ADD `purchase_price_incl_per_unit` DECIMAL(10,2) UNSIGNED NULL DEFAULT NULL AFTER `price_incl_per_unit`;";
        $this->execute($sql);

        $sql = "ALTER TABLE `fcs_manufacturer` ADD `default_tax_id_purchase_price` INT(8) UNSIGNED NULL DEFAULT NULL AFTER `default_tax_id`;";
        $this->execute($sql);

        $sql = "DROP TABLE IF EXISTS `fcs_purchase_prices`;
                CREATE TABLE `fcs_purchase_prices` (
                  `id` int(10) UNSIGNED NOT NULL,
                  `product_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                  `product_attribute_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                  `tax_id` int(10) UNSIGNED DEFAULT '0',
                  `price` decimal(20,6) NOT NULL DEFAULT '0.000000'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ALTER TABLE `fcs_purchase_prices`
              ADD PRIMARY KEY (`id`),
              ADD KEY `product_id` (`product_id`,`product_attribute_id`);
            ALTER TABLE `fcs_purchase_prices`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
            COMMIT;";
        $this->execute($sql);

        $sql = "DROP TABLE IF EXISTS `fcs_order_detail_purchase_prices`;
        CREATE TABLE `fcs_order_detail_purchase_prices` (
            `id_order_detail` int(10) UNSIGNED NOT NULL,
            `tax_rate` decimal(10,3) NOT NULL DEFAULT '0.000',
            `total_price_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
            `total_price_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
            `tax_unit_amount` decimal(16,6) NOT NULL DEFAULT '0.000000',
            `tax_total_amount` decimal(16,6) NOT NULL DEFAULT '0.000000'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ALTER TABLE `fcs_order_detail_purchase_prices`
            ADD PRIMARY KEY (`id_order_detail`);
            COMMIT;";
        $this->execute($sql);

        $sql = "ALTER TABLE `fcs_order_detail_units` ADD `purchase_price_incl_per_unit` DECIMAL(10,2) UNSIGNED NULL DEFAULT NULL AFTER `price_incl_per_unit`;";
        $this->execute($sql);


    }
}
