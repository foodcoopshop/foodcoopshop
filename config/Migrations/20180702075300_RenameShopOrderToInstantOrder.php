<?php
use Migrations\AbstractMigration;

class RenameShopOrderToInstantOrder extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE `fcs_manufacturer` CHANGE `send_shop_order_notification` `send_instant_order_notification` TINYINT(4) UNSIGNED NULL DEFAULT NULL;
            UPDATE fcs_configuration SET NAME = 'FCS_INSTANT_ORDER_DEFAULT_STATE' WHERE NAME = 'FCS_SHOP_ORDER_DEFAULT_STATE';
        ");
    }
}
