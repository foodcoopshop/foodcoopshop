<?php
use Migrations\AbstractMigration;

class ProductTablesOptimization extends AbstractMigration
{
    public function change()
    {
        $this->execute("

            ALTER TABLE `fcs_product` DROP `id_category_default`;
            ALTER TABLE `fcs_product`
                ADD `name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `price`,
                ADD `description` TEXT NULL AFTER `name`,
                ADD `description_short` TEXT NULL AFTER `description`,
                ADD `unity` VARCHAR(255) NULL AFTER `description_short`,
                ADD `is_declaration_ok` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' AFTER `unity`;

            UPDATE fcs_product p JOIN fcs_product_lang pl ON pl.id_product = p.id_product SET
                p.name = pl.name,
                p.description = pl.description,
                p.description_short = pl.description_short,
                p.unity = pl.unity,
                p.is_declaration_ok = pl.is_declaration_ok,
                p.name = pl.name;

            UPDATE fcs_product p JOIN fcs_product_shop ps ON ps.id_product = p.id_product SET
                p.created = ps.created,
                p.price = ps.price;

            UPDATE fcs_product_attribute pa JOIN fcs_product_attribute_shop pas ON pas.id_product_attribute = pa.id_product_attribute SET
                pa.default_on = pas.default_on,
                pa.price = pas.price;

            DROP table fcs_product_shop;
            DROP table fcs_product_lang;
            DROP table fcs_product_attribute_shop;

            ALTER TABLE `fcs_customer` DROP `company`;
            ALTER TABLE `fcs_customer` DROP INDEX `id_shop`;
            ALTER TABLE `fcs_customer` CHANGE `newsletter` `email_order_reminder` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

        ");
    }
}
