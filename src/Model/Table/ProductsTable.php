<?php

namespace App\Model\Table;

/**
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
class ProductsTable extends AppTable
{

    public $useTable = 'product';
    public $primaryKey = 'id_product';

    public $belongsTo = [
        'Manufacturer' => [
            'foreignKey' => 'id_manufacturer'
        ],
        'ProductLang' => [
            'foreignKey' => 'id_product'
        ],
        'ProductShop' => [
            'foreignKey' => 'id_product'
        ],
        'StockAvailable' => [
            'foreignKey' => 'id_product'
        ],
        'Tax' => [
            'foreignKey' => 'id_tax'
        ],
        'CategoryProduct' => [
            'foreignKey' => 'id_product'
        ]
    ];

    public $hasOne = [
        'DepositProduct' => [
            'foreignKey' => 'id_product'
        ],
        'Image' => [
            'foreignKey' => 'id_product',
            'order' => [
                'Image.id_image' => 'DESC'
            ]
        ]
    ];

    public $hasMany = [
        'ProductAttributes' => [
            'className' => 'ProductAttribute',
            'foreignKey' => 'id_product'
        ],
        'CategoryProducts' => [
            'className' => 'CategoryProduct',
            'foreignKey' => 'id_product'
        ]
    ];

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        App::uses('Configuration', 'Model');
        $this->Configuration = new Configuration();
    }

    /**
     * @param int $productId
     * @param int $manufacturerId
     * @return boolean success
     */
    public function isOwner($productId, $manufacturerId)
    {

        $this->recursive = -1;
        $found = $this->find('count', [
            'conditions' => [
                'Product.id_product' => $productId,
                'Product.id_manufacturer' => $manufacturerId
            ]
        ]);
        return (boolean) $found;
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
        return [
            'productId' => $productId,
            'attributeId' => $attributeId
        ];
    }

    /**
     * @param array $products
     * Array
     *   (
     *       [0] => Array
     *           (
     *               [productId] => (int) status
     *           )
     *   )
     * @throws InvalidParameterException
     * @return boolean $success
     */
    public function changeStatus($products)
    {

        $products2save = [];

        foreach ($products as $product) {
            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                throw new InvalidParameterException('change status is not allowed for product attributes');
            }
            $status = $product[$ids['productId']];
            $whitelist = [APP_OFF, APP_ON];
            if (!in_array($status, $whitelist, true)) { // last param for type check
                throw new InvalidParameterException('Product.active for product ' .$ids['productId'] . ' needs to be ' .APP_OFF . ' or ' . APP_ON.'; was: ' . $status);
            } else {
                $products2save[] = [
                    'id_product' => $ids['productId'],
                    'active' => $status
                ];
            }
        }

        $success = false;
        if (!empty($products2save)) {
            $success = $this->saveAll($products2save);
        }

        return $success;
    }

    /**
     * @param string $quantity
     * @return boolean / int
     */
    public function getQuantityAsInteger($quantity)
    {
        $quantity = trim($quantity);

        if (!is_numeric($quantity)) {
            return -1; // do not return false, because 0 is a valid return value!
        }
        $quantity = (int) ($quantity);

        return $quantity;
    }

    /**
     * @param string $price
     * @return boolean / float
     */
    public function getPriceAsFloat($price)
    {
        $price = trim($price);
        $price = str_replace(',', '.', $price);

        if (!is_numeric($price)) {
            return -1; // do not return false, because 0 is a valid return value!
        }
        $price = floatval($price);

        return $price;
    }

    /**
     * @param array $products
     *  Array
     *  (
     *      [0] => Array
     *          (
     *              [productId] => (float) deposit
     *          )
     *  )
     * @return boolean $success
     */
    public function changeDeposit($products)
    {

        foreach ($products as $product) {
            $productId = key($product);
            $deposit = $this->getPriceAsFloat($product[$productId]);
            if ($deposit < 0) {
                throw new InvalidParameterException('Eingabeformat von Pfand ist nicht korrekt: '.$product[$productId]);
            }
        }

        $success = false;
        foreach ($products as $product) {
            $productId = key($product);
            $deposit = $this->getPriceAsFloat($product[$productId]);

            $ids = $this->getProductIdAndAttributeId($productId);

            if ($ids['attributeId'] > 0) {
                $oldDeposit = $this->DepositProduct->find('first', [
                    'conditions' => [
                        'DepositProduct.id_product_attribute' => $ids['attributeId']
                    ]
                ]);

                if (empty($oldDeposit)) {
                    $this->DepositProduct->id = null; // force new insert
                } else {
                    $this->DepositProduct->id = $oldDeposit['DepositProduct']['id'];
                }

                $deposit2save = [
                    'id_product_attribute' => $ids['attributeId'],
                    'deposit' => $deposit
                ];
            } else {
                // deposit is set for productId
                $oldDeposit = $this->DepositProduct->find('first', [
                    'conditions' => [
                        'DepositProduct.id_product' => $productId
                    ]
                ]);

                if (empty($oldDeposit)) {
                    $this->DepositProduct->id = null; // force new insert
                } else {
                    $this->DepositProduct->id = $oldDeposit['DepositProduct']['id'];
                }

                $deposit2save = [
                    'id_product' => $productId,
                    'deposit' => $deposit
                ];
            }

            $this->DepositProduct->primaryKey = 'id';
            $success |= $this->DepositProduct->save($deposit2save);
        }

        return $success;
    }

    /**
     * @param array $products
     *  Array
     *  (
     *      [0] => Array
     *          (
     *              [productId] => (float) price
     *          )
     *  )
     * @return boolean $success
     */
    public function changePrice($products)
    {

        foreach ($products as $product) {
            $productId = key($product);
            $price = $this->getPriceAsFloat($product[$productId]);
            if ($price < 0) {
                throw new InvalidParameterException('Eingabeformat von Preis ist nicht korrekt: '.$product[$productId]);
            }
        }

        $success = false;
        foreach ($products as $product) {
            $productId = key($product);
            $price = $this->getPriceAsFloat($product[$productId]);

            $ids = $this->getProductIdAndAttributeId($productId);

            $netPrice = $this->getNetPrice($ids['productId'], $price);

            if ($ids['attributeId'] > 0) {
                // update attribute - updateAll needed for multi conditions of update
                $success = $this->ProductAttributes->ProductAttributeShop->updateAll([
                    'ProductAttributeShop.price' => $netPrice
                ], [
                    'ProductAttributeShop.id_product_attribute' => $ids['attributeId']
                ]);
            } else {
                $product2update = [
                    'price' => $netPrice
                ];
                $this->ProductShop->id = $ids['productId'];
                $success |= $this->ProductShop->save($product2update);
            }
        }

        return $success;
    }

    /**
     * @param array $products
     *  Array
     *  (
     *      [0] => Array
     *          (
     *              [productId] => (int) quantity
     *          )
     *  )
     * @return boolean $success
     */
    public function changeQuantity($products)
    {

        foreach ($products as $product) {
            $productId = key($product);
            $quantity = $this->getQuantityAsInteger($product[$productId]);
            if ($quantity < 0) {
                throw new InvalidParameterException('Eingabeformat von Anzahl ist nicht korrekt: '.$product[$productId]);
            }
        }

        foreach ($products as $product) {
            $productId = key($product);
            $quantity = $product[$productId];

            $ids = $this->getProductIdAndAttributeId($productId);

            if ($ids['attributeId'] > 0) {
                // update attribute - updateAll needed for multi conditions of update
                $this->StockAvailable->updateAll([
                    'StockAvailable.quantity' => $quantity
                ], [
                    'StockAvailable.id_product_attribute' => $ids['attributeId'],
                    'StockAvailable.id_product' => $ids['productId']
                ]);
                $this->StockAvailable->updateQuantityForMainProduct($ids['productId']);
            } else {
                $product2update = [
                    'quantity' => $quantity
                ];
                $this->StockAvailable->id = $ids['productId'];
                $this->StockAvailable->save($product2update);
            }
        }
    }

    /**
     * @param int $manufacturerId
     * @param boolean $useHolidayMode
     * @return array
     */
    public function getCountByManufacturerId($manufacturerId, $useHolidayMode = false)
    {
        $productCount = $this->find('count', [
            'fields' => 'DISTINCT ' . $this->name . '.id_product',
            'conditions' => [
                $this->name . '.active' => APP_ON,
                $useHolidayMode ? $this->getManufacturerHolidayConditions() : null,
                $this->name . '.id_manufacturer' => $manufacturerId
            ]
        ]);
        return $productCount;
    }

    public function isNew($date)
    {
        $showAsNewExpirationDate = date('Y-m-d', strtotime($date . ' + ' . Configure::read('AppConfig.db_config_FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' days'));
        if (strtotime($showAsNewExpirationDate) > strtotime(date('Y-m-d'))) {
            return true;
        }
        return false;
    }

    /**
     * @param array $products
     * @return array $preparedProducts
     */
    public function prepareProductsForBackend($paginator, $pParams, $addProductNameToAttributes = false)
    {

        $paginator->settings = array_merge([
            'conditions' => $pParams['conditions'],
            'contain' => $pParams['contain'],
            'order' => $pParams['order'],
            'fields' => $pParams['fields'],
            'group' => $pParams['group']
        ], $paginator->settings);

        $this->recursive = 3;

        // reduce data
        $this->Manufacturer->unbindModel([
            'hasMany' => 'Invoices'
        ]);
        $this->ProductLang->unbindModel([
            'belongsTo' => 'Product'
        ]);
        $this->Manufacturer->unbindModel([
            'belongsTo' => 'Customer',
            'hasOne' => 'Address'
        ]);

        $quantityIsZeroFilterOn = false;
        $priceIsZeroFilterOn = false;
        foreach ($pParams['conditions'] as $condition) {
            if (preg_match('/'.$this->getIsQuantityZeroCondition().'/', $condition)) {
                $this->ProductAttributes->hasOne['StockAvailable']['conditions'] = [
                    'StockAvailable.quantity' => 0
                ];
                $quantityIsZeroFilterOn = true;
            }
            if (preg_match('/'.$this->getIsPriceZeroCondition().'/', $condition)) {
                $this->ProductAttributes->hasOne['ProductAttributeShop']['conditions'] = [
                    'ProductAttributeShop.price' => 0
                ];
                $priceIsZeroFilterOn = true;
            }
        }

        $products = $paginator->paginate('Product');

        $i = 0;
        $preparedProducts = [];
        foreach ($products as $product) {
            $products[$i]['Categories'] = [
                'names' => [],
                'allProductsFound' => false
            ];
            foreach ($product['CategoryProducts'] as $category) {
                if ($category['id_category'] == 2) {
                    continue; // do not consider category "produkte" - why was it needed???
                }

                // assignment to "all products" has to be checked... otherwise show error message
                if ($category['id_category'] == Configure::read('AppConfig.categoryAllProducts')) {
                    $products[$i]['Categories']['allProductsFound'] = true;
                } else {
                    // check if category was assigned to product but deleted afterwards
                    if (isset($category['Category']) && isset($category['Category']['name'])) {
                        $products[$i]['Categories']['names'][] = $category['Category']['name'];
                    }
                }
            }
            $products[$i]['selectedCategories'] = Set::extract('{n}.id_category', $product['CategoryProducts']);
            $products[$i]['Deposit'] = 0;

            $products[$i]['Product']['is_new'] = $this->isNew($product['ProductShop']['date_add']);
            $products[$i]['Product']['gross_price'] = $this->getGrossPrice($product['Product']['id_product'], $product['ProductShop']['price']);

            $rowClass = [];
            if (! $product['Product']['active']) {
                $rowClass[] = 'deactivated';
            }

            @$products[$i]['Deposit'] = $product['DepositProduct']['deposit'];
            if (empty($products[$i]['Tax'])) {
                $products[$i]['Tax']['rate'] = 0;
                $product = $products[$i];
            }

            $rowClass[] = 'main-product';
            $rowIsOdd = false;
            if ($i % 2 == 0) {
                $rowIsOdd = true;
                $rowClass[] = 'custom-odd';
            }
            $products[$i]['Product']['rowClass'] = join(' ', $rowClass);

            $preparedProducts[] = $products[$i];
            $i ++;

            if (! empty($product['ProductAttributes'])) {
                $currentPreparedProduct = count($preparedProducts) - 1;
                $preparedProducts[$currentPreparedProduct]['AttributesRemoved'] = 0;

                foreach ($product['ProductAttributes'] as $attribute) {
                    if (($quantityIsZeroFilterOn && empty($attribute['StockAvailable'])) || ($priceIsZeroFilterOn && empty($attribute['ProductAttributeShop']))) {
                        $preparedProducts[$currentPreparedProduct]['AttributesRemoved'] ++;
                        continue;
                    }

                    $grossPrice = 0;
                    if (! empty($attribute['ProductAttributeShop']['price'])) {
                        $grossPrice = $this->getGrossPrice($product['Product']['id_product'], $attribute['ProductAttributeShop']['price']);
                    }

                    $rowClass = [
                        'sub-row'
                    ];
                    if (! $product['Product']['active']) {
                        $rowClass[] = 'deactivated';
                    }

                    if ($rowIsOdd) {
                        $rowClass[] = 'custom-odd';
                    }

                    $preparedProduct = [
                        'Product' => [
                            'id_product' => $product['Product']['id_product'] . '-' . $attribute['id_product_attribute'],
                            'gross_price' => $grossPrice,
                            'active' => - 1,
                            'rowClass' => join(' ', $rowClass)
                        ],
                        'ProductLang' => [
                            'name' => ($addProductNameToAttributes ? $product['ProductLang']['name'] . ' : ' : '') . $attribute['ProductAttributeCombination']['Attribute']['name'],
                            'description_short' => '',
                            'description' => '',
                            'unity' => ''
                        ],
                        'Manufacturer' => [
                            'name' => $product['Manufacturer']['name']
                        ],
                        'ProductAttributeShop' => [
                            'default_on' => $attribute['ProductAttributeShop']['default_on']
                        ],
                        'StockAvailable' => [
                            'quantity' => $attribute['StockAvailable']['quantity']
                        ],
                        'Deposit' => isset($attribute['DepositProductAttribute']['deposit']) ? $attribute['DepositProductAttribute']['deposit'] : 0,
                        'Categories' => [
                            'names' => [],
                            'allProductsFound' => true
                        ],
                        'Image' => null
                    ];
                    $preparedProducts[] = $preparedProduct;
                }
            }
        }

        // price zero filter is difficult to implement, because if there are attributes assigned to the product, the product's price is always 0
        // which would lead to always showing the main product of attributes if price zero filter is set
        // this is not the case for quantity zero filter, because the main product's quantity is the sum of the associated attribute quantities
        if ($priceIsZeroFilterOn) {
            foreach ($preparedProducts as $key => $preparedProduct) {
                if (isset($preparedProducts[$key]['AttributesRemoved']) && $preparedProducts[$key]['AttributesRemoved'] == count($preparedProducts[$key]['ProductAttributes'])) {
                    unset($preparedProducts[$key]);
                }
            }
        }

        return $preparedProducts;
    }

    public function getForDropdown($appAuth, $manufacturerId)
    {
        $conditions = [];

        if ($appAuth->isManufacturer()) {
            $manufacturerId = $appAuth->getManufacturerId();
        }

        if ($manufacturerId > 0) {
            $conditions['Manufacturer.id_manufacturer'] = $manufacturerId;
        }

        $this->unbindModel([
            'hasMany' => [
                'ProductAttributes',
                'CategoryProducts'
            ],
            'hasOne' => [
                'DepositProduct',
                'ImageShop'
            ]
        ]);
        // ->find('list') a does not return associated model data
        $products = $this->find('all', [
            'fields' => [
                'Product.id_product',
                'ProductLang.name',
                'Manufacturer.name',
                'Product.active'
            ],
            'conditions' => $conditions,
            'order' => [
                'Product.active' => 'DESC',
                'ProductLang.name' => 'ASC'
            ]
        ]);

        $offlineProducts = [];
        $onlineProducts = [];
        foreach ($products as $product) {
            $productNameForDropdown = $product['ProductLang']['name'] . ' - ' . $product['Manufacturer']['name'];
            if ($product['Product']['active'] == 0) {
                $offlineProducts[$product['Product']['id_product']] = $productNameForDropdown;
            } else {
                $onlineProducts[$product['Product']['id_product']] = $productNameForDropdown;
            }
        }

        $productsForDropdown = [];
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
        if ($quantity == 0) {
            return 0;
        }
        return round(($grossPrice - ($netPrice * $quantity)) / $quantity, 2);
    }

    private function getTaxJoins()
    {
        // leave "t.active IN (0,1)" condition because 0% tax does not have a record in tax table
        $taxJoins = 'FROM '.$this->tablePrefix.'product p
             LEFT JOIN '.$this->tablePrefix.'tax t ON t.id_tax = p.id_tax
             WHERE t.active IN (0,1)
               AND p.id_product = :productId';
        return $taxJoins;
    }

    /**
     * needs to be called AFTER taxId of product was updated
     */
    public function getNetPriceAfterTaxUpdate($productId, $oldNetPrice, $oldTaxRate)
    {

        // if old tax was 0, $oldTaxRate === null (tax 0 has no record in table tax) and would reset the price to 0 €
        if (is_null($oldTaxRate)) {
            $oldTaxRate = 0;
        }

        $sql = 'SELECT ROUND(:oldNetPrice / ((100 + t.rate) / 100) * (1 + :oldTaxRate / 100), 6) as new_net_price ';
        $sql .= $this->getTaxJoins();
        $params = [
            'oldNetPrice' => $oldNetPrice,
            'oldTaxRate' => $oldTaxRate,
            'productId' => $productId
        ];
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
        $params = [
            'netPrice' => $netPrice,
            'productId' => $productId
        ];
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
        $params = [
            'productId' => $productId,
            'grossPrice' => $grossPrice
        ];
        $rate = $this->getDataSource()->fetchAll($sql, $params);

        // if tax == 0% rate is empty...
        if (empty($rate)) {
            $netPrice = $grossPrice;
        } else {
            $netPrice = $rate[0][0]['net_price'];
        }

        return $netPrice;
    }

    private function getIsQuantityZeroCondition()
    {
        return 'StockAvailable.quantity = 0';
    }

    private function getIsPriceZeroCondition()
    {
        return 'ProductShop.price = 0';
    }

    public function getProductParams($appAuth, $productId, $manufacturerId, $active, $category = '', $isQuantityZero = 0, $isPriceZero = 0)
    {
        $conditions = [];
        $group = [];

        if ($manufacturerId != 'all') {
            $conditions['Product.id_manufacturer'] = $manufacturerId;
        } else {
            // do not show any non-associated products that might be found in database
            $conditions[] = 'Product.id_manufacturer > 0';
        }

        if ($productId != '') {
            $conditions['Product.id_product'] = $productId;
        }

        if ($active != 'all') {
            $conditions['Product.active'] = $active;
        }

        if ($category != '') {
            $conditions['CategoryProduct.id_category'] = (int) $category;
        }

        if ($isQuantityZero != '') {
            $conditions[] = $this->getIsQuantityZeroCondition();
        }

        if ($isPriceZero != '') {
            $conditions[] = $this->getIsPriceZeroCondition();
        }

        // DISTINCT: attributes cause duplicate entries
        $fields = [
            'DISTINCT Product.id_product, Product.active, Product.id_manufacturer, Product.id_tax, ProductLang.*, Image.id_image'
        ];

        $contain = [
            'Product',
            'CategoryProducts'
        ];

        if ($manufacturerId == '') {
            $contain[] = 'Manufacturer';
            $fields[0] .= ', Manufacturer.name';
        }

        $pParams = [
            'fields' => $fields,
            'conditions' => $conditions,
            'order' => [
                'Product.active' => 'DESC',
                'ProductLang.name' => 'ASC'
            ],
            'contain' => $contain,
            'group' => $group
        ];

        return $pParams;
    }

    public function changeDefaultAttributeId($productId, $productAttributeId)
    {
        $productAttributes = $this->ProductAttributes->find('all', [
            'conditions' => [
                'ProductAttributes.id_product' => $productId
            ]
        ]);
        $productAttributeIds = Set::extract('{n}.ProductAttributes.id_product_attribute', $productAttributes);

        // first set all associated attributes to 0
        $this->ProductAttributes->ProductAttributeShop->updateAll([
            'ProductAttributeShop.default_on' => 0
        ], [
            'id_product_attribute IN (' . join(', ', $productAttributeIds) . ')',
            'id_shop' => 1
        ]);

        // then set the new one
        $this->ProductAttributes->ProductAttributeShop->updateAll([
            'ProductAttributeShop.default_on' => 1
        ], [
            'ProductAttributeShop.id_product_attribute' => $productAttributeId,
            'ProductAttributeShop.id_shop' => 1
        ]);
    }

    public function deleteProductAttribute($productId, $attributeId, $oldProduct)
    {

        $pac = $this->ProductAttributes->ProductAttributeCombination->find('first', [
            'conditions' => [
                'ProductAttributeCombination.id_product_attribute' => $attributeId
            ]
        ]);
        $productAttributeId = $pac['ProductAttributeCombination']['id_product_attribute'];

        $this->ProductAttributes->deleteAll([
            'ProductAttributes.id_product_attribute' => $productAttributeId
        ], false);

        $this->ProductAttributes->ProductAttributeCombination->deleteAll([
            'ProductAttributeCombination.id_product_attribute' => $productAttributeId
        ], false);

        $this->ProductAttributes->ProductAttributeShop->deleteAll([
            'ProductAttributeShop.id_product_attribute' => $productAttributeId
        ], false);

        // deleteAll can only get primary key as condition
        $this->StockAvailable->primaryKey = 'id_product_attribute';
        $this->StockAvailable->deleteAll([
            'StockAvailable.id_product_attribute' => $attributeId
        ], false);

        $this->StockAvailable->updateQuantityForMainProduct($productId);
    }

    public function addProductAttribute($productId, $attributeId)
    {
        $defaultQuantity = 999;

        $productAttributesCount = $this->ProductAttributes->find('count', [
            'conditions' => [
                'ProductAttributes.id_product' => $productId
            ]
        ]);

        $this->ProductAttributes->save([
            'id_product' => $productId,
            'default_on' => $productAttributesCount == 0 ? 1 : 0
        ]);
        $productAttributeId = $this->ProductAttributes->getLastInsertID();

        // INSERT in ProductAttributeCombination tricky because of set primary_key
        $this->query('INSERT INTO '.$this->tablePrefix.'product_attribute_combination (id_attribute, id_product_attribute) VALUES(' . $attributeId . ', ' . $productAttributeId . ')');

        $this->ProductAttributes->ProductAttributeShop->save([
            'id_product_attribute' => $productAttributeId,
            'default_on' => $productAttributesCount == 0 ? 1 : 0,
            'id_shop' => 1,
            'id_product' => $productId
        ]);

        // set price of product back to 0 => if not, the price of the attribute is added to the price of the product
        $this->ProductShop->id = $productId;
        $this->ProductShop->save([
            'price' => 0
        ]);

        // avoid Integrity constraint violation: 1062 Duplicate entry '64-232-1-0' for key 'product_sqlstock'
        // with custom sql
        $this->query('INSERT INTO '.$this->tablePrefix.'stock_available (id_product, id_product_attribute, quantity) VALUES(' . $productId . ', ' . $productAttributeId . ', ' . $defaultQuantity . ')');

        $this->StockAvailable->updateQuantityForMainProduct($productId);
    }

    public function add($manufacturer)
    {
        $defaultQuantity = 999;

        $defaultTaxId = $this->Manufacturer->getOptionDefaultTaxId($manufacturer['Manufacturer']['default_tax_id']);

        // INSERT PRODUCT
        $this->save([
            'id_manufacturer' => $manufacturer['Manufacturer']['id_manufacturer'],
            'id_category_default' => Configure::read('AppConfig.categoryAllProducts'),
            'id_tax' => $defaultTaxId,
            'unity' => '',
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s')
        ]);
        $newProductId = $this->getLastInsertID();

        // INSERT PRODUCT_SHOP
        $this->ProductShop->save([
            'id_product' => $newProductId,
            'id_shop' => 1,
            'id_category_default' => Configure::read('AppConfig.categoryAllProducts'),
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s')
        ]);

        // INSERT CATEGORY_PRODUCTS
        $this->CategoryProducts->save([
            'id_category' => Configure::read('AppConfig.categoryAllProducts'),
            'id_product' => $newProductId
        ]);

        // INSERT PRODUCT_LANG
        $name = StringComponent::removeSpecialChars('Neues Produkt von ' . $manufacturer['Manufacturer']['name']);
        $this->ProductLang->save([
            'id_product' => $newProductId,
            'id_lang' => 1,
            'id_shop' => 1,
            'name' => $name,
            'description' => '',
            'description_short' => '',
            'unity' => ''
        ]);

        // INSERT STOCK AVAILABLE
        $this->StockAvailable->save([
            'id_product' => $newProductId,
            'id_shop' => 1,
            'quantity' => $defaultQuantity
        ]);

        $newProduct = $this->find('first', [
            'conditions' => [
                'Product.id_product' => $newProductId
            ]
        ]);
        return $newProduct;
    }
}
