TRUNCATE TABLE `fcs_orders`;
TRUNCATE TABLE `fcs_order_detail`;
TRUNCATE TABLE `fcs_order_detail_tax`;
TRUNCATE TABLE `fcs_cake_payments`;
TRUNCATE TABLE `fcs_cake_carts`;
TRUNCATE TABLE `fcs_cake_cart_products`;
DELETE FROM `fcs_cake_action_logs` WHERE `type` LIKE 'payment_%';
DELETE FROM `fcs_cake_action_logs` WHERE `type` = 'customer_order_finished';
