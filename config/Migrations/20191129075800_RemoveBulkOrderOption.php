<?php
use Migrations\AbstractMigration;

class RemoveBulkOrderOption extends AbstractMigration
{
    public function change()
    {
        
        $updateManufacturerOptionsSql = "
            UPDATE fcs_manufacturer SET
                send_order_list = 0,
                send_ordered_product_amount_changed_notification = 0,
                send_ordered_product_price_changed_notification = 0,
                send_ordered_product_deleted_notification = 0,
                send_instant_order_notification = 0
            WHERE bulk_orders_allowed = 1;
        ";
        $this->execute($updateManufacturerOptionsSql);
        
        $this->execute("ALTER TABLE `fcs_manufacturer` DROP `bulk_orders_allowed`;");
        
    }
}
