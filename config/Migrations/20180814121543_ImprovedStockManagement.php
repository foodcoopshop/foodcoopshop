<?php
use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class ImprovedStockManagement extends AbstractMigration
{
    public function change()
    {
        
        $this->StockAvailable = TableRegistry::getTableLocator()->get('StockAvailables');
        foreach($this->StockAvailable->getSchema()->columns() as $column) {
            if ($column == 'id_shop_group') {
                $this->execute("ALTER TABLE `fcs_stock_available` DROP `".$column."`");
            }
            if ($column == 'depends_on_stock') {
                $this->execute("ALTER TABLE `fcs_stock_available` DROP `".$column."`");
            }
            if ($column == 'out_of_stock') {
                $this->execute("ALTER TABLE `fcs_stock_available` DROP `".$column."`");
            }
        }
        
        $this->execute("
            ALTER TABLE `fcs_product` DROP `quantity`;
            ALTER TABLE `fcs_stock_available` ADD `quantity_limit` INT(10) NOT NULL DEFAULT '0' AFTER `quantity`;
            ALTER TABLE `fcs_stock_available` ADD `sold_out_limit` INT(10) NULL DEFAULT '0' AFTER `quantity_limit`;
            ALTER TABLE `fcs_product` ADD `is_stock_product` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' AFTER `is_declaration_ok`;
            ALTER TABLE `fcs_manufacturer` ADD `stock_management_enabled` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' AFTER `timebased_currency_max_credit_balance`;
            ALTER TABLE `fcs_manufacturer` ADD `send_product_sold_out_limit_reached_for_manufacturer` TINYINT(4) UNSIGNED NOT NULL DEFAULT '1' AFTER `stock_management_enabled`;
            ALTER TABLE `fcs_manufacturer` ADD `send_product_sold_out_limit_reached_for_contact_person` TINYINT(4) UNSIGNED NOT NULL DEFAULT '1' AFTER `send_product_sold_out_limit_reached_for_manufacturer`;
        ");
        
    }
}
