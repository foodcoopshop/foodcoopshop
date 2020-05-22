# replace :manufacturerId and 'yyyy-mm'

UPDATE fcs_order_detail od
JOIN fcs_product p ON od.product_id = p.id_product
SET od.order_state = 3
WHERE p.id_manufacturer = :manufacturerId
AND DATE_FORMAT(od.pickup_day, '%Y-%m') = 'yyyy-mm';