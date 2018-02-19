<?php
use Migrations\AbstractMigration;

class Categories extends AbstractMigration
{
    public function change()
    {
        $this->execute('
            DELETE FROM fcs_category WHERE id_category = 1;
            DELETE FROM fcs_category WHERE id_category = 2;
            DELETE FROM fcs_category_product WHERE id_category = 2;
            UPDATE fcs_category SET id_parent = 0 WHERE id_parent = 2;
            ALTER TABLE fcs_product_lang DROP id_shop, DROP id_lang;
            ALTER TABLE fcs_category CHANGE date_add created DATETIME NULL DEFAULT NULL;
            ALTER TABLE fcs_category CHANGE date_upd modified DATETIME NULL DEFAULT NULL;
            ALTER TABLE fcs_carts CHANGE date_add created DATETIME NULL DEFAULT NULL;
            ALTER TABLE fcs_carts CHANGE date_upd modified DATETIME NULL DEFAULT NULL;
            ALTER TABLE fcs_cart_products CHANGE date_add created DATETIME NULL DEFAULT NULL;
            ALTER TABLE fcs_cart_products CHANGE date_upd modified DATETIME NULL DEFAULT NULL;
            ALTER TABLE fcs_product CHANGE date_add created DATETIME NULL DEFAULT NULL;
            ALTER TABLE fcs_product CHANGE date_upd modified DATETIME NULL DEFAULT NULL;
            ALTER TABLE fcs_product DROP INDEX date_add, ADD INDEX created (created);
            ALTER TABLE fcs_product_shop CHANGE date_add created DATETIME NULL DEFAULT NULL;
            ALTER TABLE fcs_product_shop CHANGE date_upd modified DATETIME NULL DEFAULT NULL;
        ');
    }
}
