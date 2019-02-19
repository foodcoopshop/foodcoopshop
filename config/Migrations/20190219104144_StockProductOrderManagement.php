<?php
use Cake\I18n\I18n;
use Migrations\AbstractMigration;

class StockProductOrderManagement extends AbstractMigration
{
    public function change()
    {
        switch(I18n::getLocale()) {
            case 'de_DE':
                $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM', 'Sollen Lagerprodukte mit der wöchentlichen Bestellung bestellt werden können?', '1', 'boolean', '75', 'de_DE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                $sql .= "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS', 'In der Sofort-Bestellung ausschließlich Lagerprodukte anzeigen?', '0', 'boolean', '76', 'de_DE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                $sql .= "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES', 'Lagerprodukte in Rechnungen miteinbeziehen?', '1', 'readonly', '60', 'de_DE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                break;
            case 'en_US':
                $sql = "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM', 'Allow weekly orders for stock products?', '1', 'boolean', '75', 'en_US', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                $sql .= "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS', 'Only show stock products in instant orders?', '0', 'boolean', '76', 'en_US', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                $sql .= "INSERT INTO `fcs_configuration` (`id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`) VALUES (NULL, '1', 'FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES', 'Include stock products in invoices?', '1', 'readonly', '60', 'en_US', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
                break;
        }
        $this->execute($sql);
    }
}
