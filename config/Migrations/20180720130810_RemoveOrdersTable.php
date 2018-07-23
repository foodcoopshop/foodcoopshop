<?php
use Migrations\AbstractMigration;

class RemoveOrdersTable extends AbstractMigration
{
    public function change()
    {
        
        $this->execute("
            
            ALTER TABLE `fcs_order_detail` 
                ADD `id_customer` INT(10) UNSIGNED NOT NULL AFTER `deposit`, 
                ADD `id_cart_product` INT(10) UNSIGNED NOT NULL AFTER `id_customer`, 
                ADD `order_state` TINYINT(4) UNSIGNED NOT NULL AFTER `id_cart_product`, 
                ADD `pickup_day` DATE NOT NULL AFTER `order_state`, 
                ADD `created` DATETIME NOT NULL AFTER `pickup_day`, 
                ADD `modified` DATETIME NOT NULL AFTER `created`;
            
            UPDATE fcs_order_detail od 
                JOIN fcs_orders o ON od.id_order = o.id_order
                SET 
                od.id_customer = o.id_customer,
                od.order_state = o.current_state,
                od.created = o.date_add,
                od.modified = o.date_upd;

            UPDATE fcs_order_detail od 
                JOIN fcs_orders o ON od.id_order = o.id_order
                JOIN fcs_carts c ON c.id_cart = o.id_cart
                JOIN fcs_cart_products cp ON cp.id_cart = c.id_cart 
                    AND cp.id_product = od.product_id AND
                    cp.id_product_attribute = od.product_attribute_id
                SET 
                od.id_cart_product = cp.id_cart_product;

            ALTER TABLE `fcs_order_detail` DROP `id_order`;
            ALTER TABLE `fcs_order_detail` DROP `id_cart`;

            ALTER TABLE `fcs_order_detail` DROP INDEX `id_order_id_order_detail`;
            ALTER TABLE `fcs_order_detail` ADD INDEX(`id_customer`);
            ALTER TABLE `fcs_order_detail` ADD INDEX(`pickup_day`);
            ALTER TABLE `fcs_order_detail` ADD INDEX(`created`);
            ALTER TABLE `fcs_order_detail` ADD INDEX(`order_state`);
            ALTER TABLE `fcs_product` ADD INDEX(`id_manufacturer`);

        ");
        
    }
}
