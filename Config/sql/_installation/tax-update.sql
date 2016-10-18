
#preise bei produkten ohne varianten updaten
UPDATE fcs_product p SET p.price = ROUND(p.price * 1.12 / 1.10, 6) WHERE p.id_tax_rules_group = 4;
UPDATE fcs_product_shop ps SET ps.price = ROUND(ps.price * 1.12 / 1.10, 6) WHERE ps.id_tax_rules_group = 4;

UPDATE fcs_product_attribute pa JOIN fcs_product p ON p.id_product = pa.id_product
SET pa.price = ROUND(pa.price * 1.12 / 1.10, 6) WHERE p.id_tax_rules_group = 4;

#preise bei produkten mit varianten updaten
UPDATE fcs_product_attribute_shop pas
JOIN fcs_product_attribute pa ON pa.id_product_attribute = pas.id_product_attribute
JOIN fcs_product p ON p.id_product = pa.id_product
SET pas.price = ROUND(pas.price * 1.12 / 1.10, 6) WHERE p.id_tax_rules_group = 4;

#bereits getaetigte bestellungen updaten
UPDATE fcs_order_detail od
JOIN fcs_product p ON od.product_id = p.id_product
SET od.product_price = ROUND(od.product_price * 1.12 / 1.10, 6),
od.original_product_price = ROUND(od.product_price * 1.12 / 1.10, 6),
od.unit_price_tax_excl = ROUND(ROUND(od.product_price * 1.12 / 1.10, 6), 2),
od.total_price_tax_excl = ROUND(ROUND(od.product_price * 1.12 / 1.10, 6), 2) * od.product_quantity
WHERE p.id_tax_rules_group = 4;

UPDATE fcs_order_detail_tax odt
JOIN fcs_order_detail od ON od.id_order_detail = odt.id_order_detail
JOIN fcs_product p ON od.product_id = p.id_product
SET odt.unit_amount = od.unit_price_tax_incl - od.unit_price_tax_excl,
odt.total_amount = od.total_price_tax_incl - od.total_price_tax_excl
WHERE p.id_tax_rules_group = 4;


UPDATE fcs_order_detail_tax odt SET odt.id_tax = 2 WHERE odt.id_tax = 3;
