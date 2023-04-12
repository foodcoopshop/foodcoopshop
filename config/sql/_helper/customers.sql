SELECT c.id_customer, c.firstname, c.lastname, c.email, c.date_add, a.address1, a.address2, a.postcode, a.city, a.phone, a.phone_mobile
FROM fcs_customer c
LEFT join fcs_address a ON a.id_customer = c.id_customer
WHERE a.id_manufacturer = 0
ORDER BY c.lastname
LIMIT 10000
