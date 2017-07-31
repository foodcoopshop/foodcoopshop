UPDATE fcs_product p JOIN fcs_product_shop ps ON ps.id_product = p.id_product SET p.active = ps.active;  
ALTER TABLE `fcs_product_shop` DROP `active`;

ALTER TABLE `fcs_product_lang` ADD `unity` VARCHAR(255) DEFAULT NULL AFTER `description_short`;
UPDATE fcs_product_lang pl JOIN fcs_product_shop ps ON ps.id_product = pl.id_product SET pl.unity = ps.unity;
ALTER TABLE `fcs_product_shop` DROP `unity`;

ALTER TABLE `fcs_product_lang` DROP `link_rewrite`;
