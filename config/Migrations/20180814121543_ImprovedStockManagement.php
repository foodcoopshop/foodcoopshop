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
            ALTER TABLE `fcs_stock_available` ADD `is_negative_quantity_allowed` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `quantity`;
            ALTER TABLE `fcs_stock_available` ADD `sold_out_limit` INT(10) NULL DEFAULT NULL AFTER `is_negative_quantity_allowed`;
        ");
        
    }
}
