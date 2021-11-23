SELECT MAX(cp.modified) as Modified, COUNT(cp.id_cart) as Products, CONCAT(c.firstname, ' ', c.lastname) AS Name
FROM fcs_cart_products cp
JOIN fcs_carts ON fcs_carts.id_cart = cp.id_cart
JOIN fcs_customer c ON fcs_carts.id_customer = c.id_customer
WHERE fcs_carts.status = 1
AND fcs_carts.cart_type = 1
GROUP BY cp.id_cart
ORDER BY Modified DESC
LIMIT 1000
