<?php
/**
 * Product
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class Product extends AppModel
{

    public $useTable = 'product';
    public $primaryKey = 'id_product';

    public $belongsTo = array(
        'Manufacturer' => array(
            'foreignKey' => 'id_manufacturer'
        ),
        'ProductLang' => array(
            'foreignKey' => 'id_product'
        ),
        'ProductShop' => array(
            'foreignKey' => 'id_product'
        ),
        'StockAvailable' => array(
            'foreignKey' => 'id_product'
        ),
        'Tax' => array(
            'foreignKey' => 'id_tax'
        )
    );

    public $hasOne = array(
        'CakeDepositProduct' => array(
            'foreignKey' => 'id_product'
        ),
        'ImageShop' => array(
            'className' => 'ImageShop',
            'foreignKey' => 'id_product',
            'conditions' => array(
                'ImageShop.id_shop' => 1,
                'ImageShop.cover' => 1
            ),
            'order' => array(
                'ImageShop.id_image' => 'DESC'
            )
        )
    );

    public $hasMany = array(
        'ProductAttributes' => array(
            'className' => 'ProductAttribute',
            'foreignKey' => 'id_product'
        ),
        'CategoryProducts' => array(
            'className' => 'CategoryProduct',
            'foreignKey' => 'id_product'
        )
    );

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        App::uses('Configuration', 'Model');
        $this->Configuration = new Configuration();
    }

    /**
     *
     * @param string $productId
     *            (eg. 4 or '4-10' or '4'
     * @return array ids (productId, attributeId)
     */
    public function getProductIdAndAttributeId($productId)
    {
        $attributeId = 0;
        $explodedProductId = explode('-', $productId);
        if (count($explodedProductId) == 2) {
            $productId = $explodedProductId[0];
            $attributeId = $explodedProductId[1];
        }
        return array(
            'productId' => $productId,
            'attributeId' => $attributeId
        );
    }

    public function getCountByManufacturerId($manufacturerId)
    {
        $productCount = $this->find('count', array(
            'fields' => 'DISTINCT ' . $this->name . '.id_product',
            'conditions' => array(
                $this->name . '.active' => APP_ON,
                'Manufacturer.holiday' => APP_OFF,
                $this->name . '.id_manufacturer' => $manufacturerId
            )
        ));
        return $productCount;
    }

    public function isNew($date)
    {
        $showAsNewExpirationDate = date('Y-m-d', strtotime($date . ' + ' . Configure::read('app.db_config_FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' days'));
        if (strtotime($showAsNewExpirationDate) > strtotime(date('Y-m-d'))) {
            return true;
        }
        return false;
    }

    public function getForDropdown($appAuth, $manufacturerId)
    {
        $conditions = array();
        
        if ($appAuth->isManufacturer()) {
            $manufacturerId = $appAuth->getManufacturerId();
        }
        
        if ($manufacturerId > 0) {
            $conditions['Manufacturer.id_manufacturer'] = $manufacturerId;
        }
        
        $this->unbindModel(array(
            'hasMany' => array(
                'ProductAttributes',
                'CategoryProducts'
            ),
            'hasOne' => array(
                'CakeDepositProduct',
                'ImageShop'
            )
        ));
        // ->find('list') a does not return associated model data
        $products = $this->find('all', array(
            'fields' => array(
                'Product.id_product',
                'ProductLang.name',
                'Manufacturer.name',
                'ProductShop.active',
                'ProductLang.link_rewrite'
            ),
            'conditions' => $conditions,
            'order' => array(
                'ProductShop.active' => 'DESC',
                'ProductLang.name' => 'ASC'
            )
        ));
        
        $offlineProducts = array();
        $onlineProducts = array();
        foreach ($products as $product) {
            $productNameForDropdown = $product['ProductLang']['name'] . ' - ' . $product['Manufacturer']['name'];
            if ($product['ProductShop']['active'] == 0) {
                $offlineProducts[$product['Product']['id_product']] = $productNameForDropdown;
            } else {
                $onlineProducts[$product['Product']['id_product']] = $productNameForDropdown;
            }
        }
        
        $productsForDropdown = array();
        if (! empty($onlineProducts)) {
            $onlineCount = count($onlineProducts);
            $productsForDropdown['online-' . $onlineCount] = $onlineProducts;
        }
        
        if (! empty($offlineProducts)) {
            $offlineCount = count($offlineProducts);
            $productsForDropdown['offline-' . $offlineCount] = $offlineProducts;
        }
        
        return $productsForDropdown;
    }

    /**
     * @param float $grossPrice (for all units)
     * @param float $netPrice (for one unit)
     * @param int $quantity
     * @return float
     */
    public function getUnitTax($grossPrice, $netPrice, $quantity)
    {
        return round(($grossPrice - ($netPrice * $quantity)) / $quantity, 2);
    }

    private function getTaxJoins()
    {
        $taxJoins = 'FROM '.$this->tablePrefix.'product p
             LEFT JOIN '.$this->tablePrefix.'tax t ON t.id_tax = p.id_tax
             WHERE t.active = 1
               AND p.id_product = :productId';
        return $taxJoins;
    }

    /**
     * needs to be called AFTER taxId of product was updated
     */
    public function getNetPriceAfterTaxUpdate($productId, $oldNetPrice, $oldTaxRate)
    {
        $sql = 'SELECT ROUND(:oldNetPrice / ((100 + t.rate) / 100) * (1 + :oldTaxRate / 100), 6) as new_net_price ';
        $sql .= $this->getTaxJoins();
        $params = array(
            'oldNetPrice' => $oldNetPrice,
            'oldTaxRate' => $oldTaxRate,
            'productId' => $productId
        );
        $rate = $this->getDataSource()->fetchAll($sql, $params);
        
        // if tax == 0 %, tax is empty
        if (empty($rate)) {
            $newNetPrice = $oldNetPrice * (1 + $oldTaxRate / 100);
        } else {
            $newNetPrice = $rate[0][0]['new_net_price'];
        }
        
        return $newNetPrice;
    }

    public function getGrossPrice($productId, $netPrice)
    {
        $productId = (int) $productId;
        $sql = 'SELECT ROUND(:netPrice * (100 + t.rate) / 100, 2) as gross_price ';
        $sql .= $this->getTaxJoins();
        $params = array(
            'netPrice' => $netPrice,
            'productId' => $productId
        );
        $rate = $this->getDataSource()->fetchAll($sql, $params);
        
        // if tax == 0% rate is empty...
        if (empty($rate)) {
            $grossPrice = $netPrice;
        } else {
            $grossPrice = $rate[0][0]['gross_price'];
        }
        
        return $grossPrice;
    }

    public function getNetPrice($productId, $grossPrice)
    {
        $grossPrice = str_replace(',', '.', $grossPrice);
        
        if (! $grossPrice > - 1) { // allow 0 as new price
            return false;
        }
        
        $sql = 'SELECT ROUND(:grossPrice / (100 + t.rate) * 100, 6) as net_price ';
        $sql .= $this->getTaxJoins();
        $params = array(
            'productId' => $productId,
            'grossPrice' => $grossPrice
        );
        $rate = $this->getDataSource()->fetchAll($sql, $params);
        
        // if tax == 0% rate is empty...
        if (empty($rate)) {
            $netPrice = $grossPrice;
        } else {
            $netPrice = $rate[0][0]['net_price'];
        }
        
        return $netPrice;
    }

    public function getProductParams($appAuth, $productId, $manufacturerId, $active)
    {
        $conditions = array();
        $group = array();
        
        if ($manufacturerId != '') {
            $conditions['Product.id_manufacturer'] = $manufacturerId;
        } else {
            // do not show any products if no manufactuerId is set
            $conditions['Product.id_manufacturer'] = - 1;
        }
        
        if ($productId != '') {
            $conditions['Product.id_product'] = $productId;
        }
        
        if ($active != 'all') {
            $conditions['Product.active'] = $active;
        }
        
        // attributes cause duplicate entries
        $fields = array(
            'DISTINCT Product.id_product, Product.*'
        );
        
        $contain = array(
            'Product',
            'CategoryProducts'
        );
        
        $pParams = array(
            'fields' => $fields,
            'conditions' => $conditions,
            'order' => array(
                'ProductShop.active' => 'DESC',
                'ProductLang.name' => 'ASC'
            ),
            'contain' => $contain,
            'group' => $group
        );
        
        return $pParams;
    }

    public function changeDefaultAttributeId($productId, $productAttributeId)
    {
        $productAttributes = $this->ProductAttributes->find('all', array(
            'conditions' => array(
                'ProductAttributes.id_product' => $productId
            )
        ));
        $productAttributeIds = Set::extract('{n}.ProductAttributes.id_product_attribute', $productAttributes);
        
        // first set all associated attributes to 0
        $this->ProductAttributes->ProductAttributeShop->updateAll(array(
            'ProductAttributeShop.default_on' => 0
        ), array(
            'id_product_attribute IN (' . join(', ', $productAttributeIds) . ')',
            'id_shop' => 1
        ));
        
        // then set the new one
        $this->ProductAttributes->ProductAttributeShop->updateAll(array(
            'ProductAttributeShop.default_on' => 1
        ), array(
            'ProductAttributeShop.id_product_attribute' => $productAttributeId,
            'ProductAttributeShop.id_shop' => 1
        ));
    }

    public function deleteProductAttribute($productId, $attributeId, $oldProduct)
    {
        
        // START: set cache_default_attribute
        
        // 1) detect actual default attribute (if not the last attribute is deleted)
        $defaultAttributeId = 0;
        if (count($oldProduct['ProductAttributes']) > 1) {
            foreach ($oldProduct['ProductAttributes'] as $pa) {
                if ($pa['ProductAttributeShop']['default_on'] == 1 && $pa['ProductAttributeShop']['id_product_attribute'] != $attributeId) {
                    $defaultAttributeId = $pa['ProductAttributeShop']['id_product_attribute'];
                    break;
                }
            }
        }
        
        // 2) not the last attribute is deleted and no default attribute is set: take first attribute as new default attribute
        if (count($oldProduct['ProductAttributes']) > 1 && $defaultAttributeId == 0) {
            foreach ($oldProduct['ProductAttributes'] as $pa) {
                if ($pa['ProductAttributeShop']['id_product_attribute'] != $attributeId) {
                    $defaultAttributeId = $pa['ProductAttributeShop']['id_product_attribute'];
                    break;
                }
            }
            $this->changeDefaultAttributeId($productId, $defaultAttributeId);
        }
        
        // product. und product_shop.'cache_default_attribute'
        $this->ProductShop->id = $productId;
        $this->ProductShop->save(array(
            'cache_default_attribute' => $defaultAttributeId
        ));
        $this->id = $productId;
        $this->save(array(
            'cache_default_attribute' => $defaultAttributeId
        ));
        
        // END: set cache_default_attribute
        
        $pac = $this->ProductAttributes->ProductAttributeCombination->find('first', array(
            'conditions' => array(
                'ProductAttributeCombination.id_product_attribute' => $attributeId
            )
        ));
        $productAttributeId = $pac['ProductAttributeCombination']['id_product_attribute'];
        
        $this->ProductAttributes->deleteAll(array(
            'ProductAttributes.id_product_attribute' => $productAttributeId
        ), false);
        
        $this->ProductAttributes->ProductAttributeCombination->deleteAll(array(
            'ProductAttributeCombination.id_product_attribute' => $productAttributeId
        ), false);
        
        $this->ProductAttributes->ProductAttributeShop->deleteAll(array(
            'ProductAttributeShop.id_product_attribute' => $productAttributeId
        ), false);
        
        // deleteAll can only get primary key as condition
        $this->StockAvailable->primaryKey = 'id_product_attribute';
        $this->StockAvailable->deleteAll(array(
            'StockAvailable.id_product_attribute' => $attributeId
        ), false);
        
        $this->StockAvailable->updateQuantityForMainProduct($productId);
    }

    public function addProductAttribute($productId, $attributeId)
    {
        $defaultQuantity = 999;
        
        $productAttributesCount = $this->ProductAttributes->find('count', array(
            'conditions' => array(
                'ProductAttributes.id_product' => $productId
            )
        ));
        
        $this->ProductAttributes->save(array(
            'id_product' => $productId,
            'default_on' => $productAttributesCount == 0 ? 1 : 0
        ));
        $productAttributeId = $this->ProductAttributes->getLastInsertID();
        
        // INSERT in ProductAttributeCombination tricky because of set primary_key
        $this->query('INSERT INTO '.$this->tablePrefix.'product_attribute_combination (id_attribute, id_product_attribute) VALUES(' . $attributeId . ', ' . $productAttributeId . ')');
        
        $this->ProductAttributes->ProductAttributeShop->save(array(
            'id_product_attribute' => $productAttributeId,
            'default_on' => $productAttributesCount == 0 ? 1 : 0,
            'id_shop' => 1,
            'id_product' => $productId
        ));
        
        // set price of article back to 0 => if not, the price of the attribute is added to the price of the article
        $this->ProductShop->id = $productId;
        $this->ProductShop->save(array(
            'price' => 0
        ));
        
        // avoid Integrity constraint violation: 1062 Duplicate entry '64-232-1-0' for key 'product_sqlstock'
        // with custom sql
        $this->query('INSERT INTO '.$this->tablePrefix.'stock_available (id_product, id_product_attribute, id_shop, quantity) VALUES(' . $productId . ', ' . $productAttributeId . ', 1, ' . $defaultQuantity . ')');
        
        $this->StockAvailable->updateQuantityForMainProduct($productId);
    }

    public function add($manufacturer)
    {
        $defaultQuantity = 999;
        
        $defaultTaxId = Configure::read('app.defaultTaxId');
        $addressOther = StringComponent::decodeJsonFromForm($manufacturer['Address']['other']);
        if (isset($addressOther['defaultTaxId'])) {
            $defaultTaxId = $addressOther['defaultTaxId'];
        }
        
        // INSERT PRODUCT
        /*
         * $query = "INSERT INTO `".$this->tablePrefix."product` (`id_product`, `id_supplier`, `id_manufacturer`, `id_category_default`, `id_shop_default`, `id_tax_rules_group`, `on_sale`, `online_only`, `ean13`, `upc`, `ecotax`, `quantity`, `minimal_quantity`, `price`, `wholesale_price`, `unity`, `unit_price_ratio`, `additional_shipping_cost`, `reference`, `supplier_reference`, `location`, `width`, `height`, `depth`, `weight`, `out_of_stock`, `quantity_discount`, `customizable`, `uploadable_files`, `text_fields`, `active`, `redirect_type`, `id_product_redirected`, `available_for_order`, `available_date`, `condition`, `show_price`, `indexed`, `visibility`, `cache_is_pack`, `cache_has_attachments`, `is_virtual`, `cache_default_attribute`, `date_add`, `date_upd`, `advanced_stock_management`) VALUES
         * ( 428, 0, 31, 13, 1, 4, 0, 0, '', '', 0.000000, 0, 1, 0.000000, 0.000000, '', 0.000000, 0.00, '', '', '', 0.000000, 0.000000, 0.000000, 0.000000, 2, 0, 0, 0, 0, 1, '404', 0, 1, '0000-00-00', 'new', 1, 1, 'both', 0, 0, 0, 0, '2015-02-23 16:02:25', '2015-02-23 16:02:25', 0);";
         */
        $this->save(array(
            'id_manufacturer' => $manufacturer['Manufacturer']['id_manufacturer'],
            'id_supplier' => 0,
            'id_category_default' => Configure::read('app.categoryAllProducts'),
            'id_tax' => $defaultTaxId,
            'ean13' => '',
            'upc' => '',
            'unity' => '',
            'reference' => '',
            'supplier_reference' => '',
            'location' => '',
            'cache_default_attribute' => 0,
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s')
        ));
        $newProductId = $this->getLastInsertID();
        
        // INSERT PRODUCT_SHOP
        /*
         * INSERT INTO `".$this->tablePrefix."product_lang` (`id_product`, `id_shop`, `id_lang`, `description`, `description_short`, `link_rewrite`, `meta_description`, `meta_keywords`, `meta_title`, `name`, `available_now`, `available_later`) VALUES
         * (428, 1, 1, '', '', 'noch-ein-neuer-artikel', '', '', '', 'noch ein neuer artikel', '', '');
         */
        $this->ProductShop->save(array(
            'id_product' => $newProductId,
            'id_shop' => 1,
            'id_category_default' => Configure::read('app.categoryAllProducts'),
            'unity' => '',
            'cache_default_attribute' => 0,
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s')
        ));
        
        // cake cannot save enum fields...
        $this->query('UPDATE ' . $this->tablePrefix . $this->useTable . ' SET redirect_type = "404" WHERE id_product = ' . $newProductId);
        $this->query('UPDATE ' . $this->ProductShop->tablePrefix . $this->ProductShop->useTable . ' SET redirect_type = "404" WHERE id_product = ' . $newProductId);
        
        // INSERT CATEGORY_PRODUCTS
        // $query = "INSERT INTO `".$this->tablePrefix."category_product` (`id_category`, `id_product`, `position`) VALUES (13, 428, 131)
        $this->CategoryProducts->save(array(
            'id_category' => Configure::read('app.categoryAllProducts'),
            'id_product' => $newProductId
        ));
        
        // INSERT PRODUCT_LANG
        /*
         * INSERT INTO `".$this->tablePrefix."product_lang` (`id_product`, `id_shop`, `id_lang`, `description`, `description_short`, `link_rewrite`, `meta_description`, `meta_keywords`, `meta_title`, `name`, `available_now`, `available_later`) VALUES
         * (428, 1, 1, '', '', 'noch-ein-neuer-artikel', '', '', '', 'noch ein neuer artikel', '', '');
         */
        $name = StringComponent::removeSpecialChars('Neuer Artikel von ' . $manufacturer['Manufacturer']['name']);
        $this->ProductLang->save(array(
            'id_product' => $newProductId,
            'id_lang' => 1,
            'id_shop' => 1,
            'name' => $name,
            'description' => '',
            'description_short' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'meta_title' => '',
            'available_now' => '',
            'available_later' => '',
            'link_rewrite' => StringComponent::slugify($name)
        ));
        
        // INSERT STOCK AVAILABLE
        /*
         * INSERT INTO `".$this->tablePrefix."stock_available` (`id_stock_available`, `id_product`, `id_product_attribute`, `id_shop`, `id_shop_group`, `quantity`, `depends_on_stock`, `out_of_stock`) VALUES
         * (704, 428, 0, 1, 0, 100, 0, 2);";
         */
        $this->StockAvailable->save(array(
            'id_product' => $newProductId,
            'id_shop' => 1,
            'quantity' => $defaultQuantity
        ));
        
        // TODDO out_of_stock cannot be set to 2 via cake...
        $this->query('UPDATE ' . $this->StockAvailable->tablePrefix . $this->StockAvailable->useTable . ' SET out_of_stock = 2 WHERE id_product = ' . $newProductId);
        
        $newProduct = $this->find('first', array(
            'conditions' => array(
                'Product.id_product' => $newProductId
            )
        ));
        return $newProduct;
    }
}

?>