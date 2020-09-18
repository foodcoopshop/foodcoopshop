TRUNCATE TABLE `fcs_order_detail`;
TRUNCATE TABLE `fcs_order_detail_tax`;
TRUNCATE TABLE `fcs_payments`;
TRUNCATE TABLE `fcs_carts`;
TRUNCATE TABLE `fcs_cart_products`;
DELETE FROM `fcs_action_logs` WHERE `type` LIKE 'payment_%';
DELETE FROM `fcs_action_logs` WHERE `type` = 'customer_order_finished';
