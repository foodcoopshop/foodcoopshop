SELECT 
  COUNT(DISTINCT(OrderDetails.pickup_day)) AS DifferentOrderDays,
  CONCAT(Customers.firstname, " ", Customers.lastname) AS Name
FROM fcs_order_detail OrderDetails 
JOIN fcs_customer Customers ON Customers.id_customer = OrderDetails.id_customer
GROUP BY OrderDetails.id_customer
ORDER BY DifferentOrderDays DESC
LIMIT 1000