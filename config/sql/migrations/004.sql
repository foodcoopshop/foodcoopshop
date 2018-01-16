ALTER TABLE `fcs_manufacturer` ADD `compensation_percentage` INT(8) UNSIGNED NULL AFTER `homepage`, ADD `send_invoice` TINYINT(4) UNSIGNED NULL AFTER `compensation_percentage`, ADD `send_order_list` TINYINT(4) UNSIGNED NULL AFTER `send_invoice`, ADD `default_tax_id` INT(8) UNSIGNED NULL AFTER `send_order_list`, ADD `send_order_list_cc` VARCHAR(512) NULL AFTER `default_tax_id`, ADD `bulk_orders_allowed` TINYINT(4) UNSIGNED NULL AFTER `send_order_list_cc`, ADD `send_shop_order_notification` TINYINT(4) UNSIGNED NULL AFTER `bulk_orders_allowed`;
ALTER TABLE `fcs_manufacturer` ADD `id_customer` INT(10) UNSIGNED NULL AFTER `homepage`;
ALTER TABLE `fcs_manufacturer` ADD `send_ordered_product_deleted_notification` INT(10) UNSIGNED NULL AFTER `send_shop_order_notification`;
ALTER TABLE `fcs_manufacturer` ADD `send_ordered_product_price_changed_notification` INT(10) UNSIGNED NULL AFTER `send_ordered_product_deleted_notification`;
ALTER TABLE `fcs_manufacturer` ADD `send_ordered_product_quantity_changed_notification` INT(10) UNSIGNED NULL AFTER `send_ordered_product_price_changed_notification`;

/* migrate json encoded data into new db fields */
UPDATE fcs_manufacturer as Manufacturer
JOIN fcs_address as Address ON Manufacturer.id_manufacturer = Address.id_manufacturer AND Address.deleted = 0
SET compensation_percentage = 
    IF (INSTR(other, '"compensationPercentage"') > 0,
        REPLACE(REPLACE(
            SUBSTRING_INDEX(
                SUBSTRING(
                    SUBSTRING_INDEX(other, '"compensationPercentage"', -1),
                2),
            ',"', 1),
        '"', ''), '}', ''), NULL),
send_invoice = 
    IF (INSTR(other, '"sendInvoice"') > 0,
        REPLACE(REPLACE(
            SUBSTRING_INDEX(
                SUBSTRING(
                    SUBSTRING_INDEX(other, '"sendInvoice"', -1),
                2),
            ',"', 1),
        '"', ''), '}', ''), NULL),
send_order_list = 
    IF (INSTR(other, '"sendOrderList"') > 0,
        REPLACE(REPLACE(
            SUBSTRING_INDEX(
                SUBSTRING(
                    SUBSTRING_INDEX(other, '"sendOrderList"', -1),
                2),
            ',"', 1),
        '"', ''), '}', ''), NULL),
default_tax_id = 
    IF (INSTR(other, '"defaultTaxId"') > 0,
        REPLACE(REPLACE(
            SUBSTRING_INDEX(
                SUBSTRING(
                    SUBSTRING_INDEX(other, '"defaultTaxId"', -1),
                2),
            ',"', 1),
        '"', ''), '}', ''), NULL),
send_order_list_cc = 
    IF (INSTR(other, '"sendOrderListCc"') > 0,
        REPLACE(REPLACE(
            SUBSTRING_INDEX(
                SUBSTRING(
                    SUBSTRING_INDEX(other, '"sendOrderListCc"', -1),
                2),
            ',"', 1),
        '"', ''), '}', ''), NULL),
bulk_orders_allowed = 
    IF (INSTR(other, '"bulkOrdersAllowed"') > 0,
        REPLACE(REPLACE(
            SUBSTRING_INDEX(
                SUBSTRING(
                    SUBSTRING_INDEX(other, '"bulkOrdersAllowed"', -1),
                2),
            ',"', 1),
        '"', ''), '}', ''), NULL),
other = '';
