<?php

namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use App\Lib\Error\Exception\InvalidParameterException;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

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

    public function initialize(array $config)
    {
        $this->setTable('product');
        parent::initialize($config);
        $this->setPrimaryKey('id_product');
        $this->belongsTo('Manufacturers', [
            'foreignKey' => 'id_manufacturer'
        ]);
        $this->belongsTo('ProductLangs', [
            'foreignKey' => 'id_product'
        ]);
        $this->belongsTo('ProductShops', [
            'foreignKey' => 'id_product'
        ]);
        $this->belongsTo('StockAvailables', [
            'foreignKey' => 'id_product'
        ]);
        $this->belongsTo('Taxes', [
            'foreignKey' => 'id_tax'
        ]);
        $this->hasOne('DepositProducts', [
            'foreignKey' => 'id_product'
        ]);
        $this->hasOne('Images', [
            'foreignKey' => 'id_product',
            'order' => [
                'Images.id_image' => 'DESC'
            ]
        ]);
        $this->hasMany('ProductAttributes', [
            'foreignKey' => 'id_product'
        ]);
        $this->hasMany('CategoryProducts', [
            'foreignKey' => 'id_product'
        ]);
        $this->hasOne('UnitProducts', [
            'foreignKey' => 'id_product'
        ]);
        $this->addBehavior('Timestamp');
    }

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');
    }

    /**
     * @param int $productId
     * @param int $manufacturerId
     * @return boolean success
     */
    public function isOwner($productId, $manufacturerId)
    {

        $found = $this->find('all', [
            'conditions' => [
                'Products.id_product' => $productId,
                'Products.id_manufacturer' => $manufacturerId
            ]
        ])->count();
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
    
    public function getCompositeProductIdAndAttributeId($productId, $attributeId = 0)
    {
        $compositeId = $productId;
        if ($attributeId > 0) {
            $compositeId .= '-'.$attributeId;
        }
        return $compositeId;
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
                throw new InvalidParameterException('Products.active for product ' .$ids['productId'] . ' needs to be ' .APP_OFF . ' or ' . APP_ON.'; was: ' . $status);
            } else {
                $products2save[] = [
                    'id_product' => $ids['productId'],
                    'active' => $status
                ];
            }
        }

        $success = false;
        if (!empty($products2save)) {
            $entities = $this->newEntities($products2save);
            $result = $this->saveMany($entities);
            $success = !empty($result);
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
     * @param string $string
     * @return boolean / float
     */
    public function getStringAsFloat($string)
    {
        $float = trim($string);
        $float = Configure::read('app.numberHelper')->replaceCommaWithDot($float);

        if (!is_numeric($float)) {
            return -1; // do not return false, because 0 is a valid return value!
        }
        $float = floatval($float);

        return $float;
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
            $deposit = $this->getStringAsFloat($product[$productId]);
            if ($deposit < 0) {
                throw new InvalidParameterException('Eingabeformat von Pfand ist nicht korrekt: '.$product[$productId]);
            }
        }

        $success = false;
        foreach ($products as $product) {
            $productId = key($product);
            $deposit = $this->getStringAsFloat($product[$productId]);

            $ids = $this->getProductIdAndAttributeId($productId);

            if ($ids['attributeId'] > 0) {
                $oldDeposit = $this->DepositProducts->find('all', [
                    'conditions' => [
                        'id_product_attribute' => $ids['attributeId']
                    ]
                ])->first();

                if (empty($oldDeposit)) {
                    $entity = $this->DepositProducts->newEntity();
                } else {
                    $this->DepositProducts->setPrimaryKey('id_product_attribute');
                    $entity = $this->DepositProducts->get($oldDeposit->id_product_attribute);
                }

                $deposit2save = [
                    'id_product_attribute' => $ids['attributeId'],
                    'deposit' => $deposit
                ];
            } else {
                // deposit is set for productId
                $oldDeposit = $this->DepositProducts->find('all', [
                    'conditions' => [
                        'id_product' => $productId
                    ]
                ])->first();

                if (empty($oldDeposit)) {
                    $entity = $this->DepositProducts->newEntity();
                } else {
                    $entity = $this->DepositProducts->get($oldDeposit->id_product);
                }

                $deposit2save = [
                    'id_product' => $productId,
                    'deposit' => $deposit
                ];
            }

            $this->DepositProducts->setPrimaryKey('id');
            $result = $this->DepositProducts->save(
                $this->DepositProducts->patchEntity($entity, $deposit2save)
            );
            $this->DepositProducts->setPrimaryKey('id_product');
            $success |= is_object($result);
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
            $price = $this->getStringAsFloat($product[$productId]);
            if ($price < 0) {
                throw new InvalidParameterException('Eingabeformat von Preis ist nicht korrekt: '.$product[$productId]);
            }
        }

        $success = false;
        foreach ($products as $product) {
            $productId = key($product);
            $price = $this->getStringAsFloat($product[$productId]);

            $ids = $this->getProductIdAndAttributeId($productId);

            $netPrice = $this->getNetPrice($ids['productId'], $price);

            if ($ids['attributeId'] > 0) {
                // update attribute - updateAll needed for multi conditions of update
                $success = $this->ProductAttributes->ProductAttributeShops->updateAll([
                    'price' => $netPrice
                ], [
                    'id_product_attribute' => $ids['attributeId']
                ]);
            } else {
                $product2update = [
                    'price' => $netPrice
                ];
                $entity = $this->ProductShops->get($ids['productId']);
                $result = $this->ProductShops->save(
                    $this->ProductShops->patchEntity($entity, $product2update)
                );
                $success |= is_object($result);
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
                $this->ProductAttributes->StockAvailables->updateAll([
                    'quantity' => $quantity
                ], [
                    'id_product_attribute' => $ids['attributeId'],
                    'id_product' => $ids['productId']
                ]);
                $this->StockAvailables->updateQuantityForMainProduct($ids['productId']);
            } else {
                $product2update = [
                    'quantity' => $quantity
                ];
                $entity = $this->StockAvailables->get($ids['productId']);
                $this->StockAvailables->save($this->StockAvailables->patchEntity($entity, $product2update));
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
        $productCount = $this->find('all', [
            'conditions' => [
                'Products.active' => APP_ON,
                $useHolidayMode ? $this->getManufacturerHolidayConditions() : null,
                'Products.id_manufacturer' => $manufacturerId
            ],
            'contain' => [
                'Manufacturers'
            ]
        ])->count();
        return $productCount;
    }

    public function isNew($date)
    {

        $showAsNewExpirationDate = date('Y-m-d', strtotime($date . ' + ' . Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' days'));
        if (strtotime($showAsNewExpirationDate) > strtotime(date('Y-m-d'))) {
            return true;
        }
        return false;
    }

    /**
     * @param array $products
     * @return array $preparedProducts
     */
    public function getProductsForBackend($appAuth, $productId, $manufacturerId, $active, $categoryId = '', $isQuantityMinFilterSet = 0, $isPriceZero = 0, $addProductNameToAttributes = false, $controller = null)
    {

        $conditions = [];

        if ($manufacturerId != 'all') {
            $conditions['Products.id_manufacturer'] = $manufacturerId;
        } else {
            // do not show any non-associated products that might be found in database
            $conditions[] = 'Products.id_manufacturer > 0';
        }

        if ($productId != '') {
            $conditions['Products.id_product'] = $productId;
        }

        if ($active != 'all') {
            $conditions['Products.active'] = $active;
        }

        if ($isQuantityMinFilterSet != '') {
            $conditions[] = $this->getIsQuantityMinFilterSetCondition();
        }

        if ($isPriceZero != '') {
            $conditions[] = $this->getIsPriceZeroCondition();
        }

        $quantityIsZeroFilterOn = false;
        $priceIsZeroFilterOn = false;
        foreach ($conditions as $condition) {
            if (preg_match('/'.$this->getIsQuantityMinFilterSetCondition().'/', $condition)) {
                $this->getAssociation('ProductAttributes')->setConditions(
                    [
                        'StockAvailables.quantity < 3'
                    ]
                );
                $quantityIsZeroFilterOn = true;
            }
            if (preg_match('/'.$this->getIsPriceZeroCondition().'/', $condition)) {
                $this->ProductAttributes->getAssociation('ProductAttributeShops')->setConditions(
                    [
                        'ProductAttributeShops.price' => 0
                    ]
                );
                $priceIsZeroFilterOn = true;
            }
        }

        $contain = [
            'CategoryProducts',
            'CategoryProducts.Categories',
            'ProductShops',
            'ProductLangs',
            'DepositProducts',
            'Images',
            'Taxes',
            'UnitProducts',
            'Manufacturers',
            'StockAvailables' => [
                'conditions' => [
                    'StockAvailables.id_product_attribute' => 0
                ]
            ],
            'ProductAttributes',
            'ProductAttributes.StockAvailables' => [
                'conditions' => [
                    'StockAvailables.id_product_attribute > 0'
                ]
            ],
            'ProductAttributes.ProductAttributeShops',
            'ProductAttributes.DepositProductAttributes',
            'ProductAttributes.UnitProductAttributes',
            'ProductAttributes.ProductAttributeCombinations.Attributes'
        ];

        $order = [
            'Products.active' => 'DESC',
            'ProductLangs.name' => 'ASC'
        ];

        $query = $this->find('all', [
            'conditions' => $conditions,
            'contain' => $contain,
            'order' => ($controller === null ? $order : null)
        ]);

        if ($categoryId != '') {
            $query->matching('CategoryProducts', function ($q) use ($categoryId) {
                return $q->where(['id_category IN' => $categoryId]);
            });
        }

        $query
        ->select('Products.id_product')->distinct()
        ->select($this) // Products
        ->select($this->ProductShops)
        ->select($this->ProductLangs)
        ->select($this->DepositProducts)
        ->select('Images.id_image')
        ->select($this->Taxes)
        ->select($this->Manufacturers)
        ->select($this->UnitProducts)
        ->select($this->StockAvailables);
        
        if ($controller) {
            $query = $controller->paginate($query, [
                'sortWhitelist' => [
                    'Images.id_image', 'ProductLangs.name', 'ProductLangs.is_declaration_ok', 'Taxes.rate', 'Products.active', 'Manufacturers.name'
                ],
                'order' => $order
            ]);
        }

        $i = 0;
        $preparedProducts = [];
        foreach ($query as $product) {
            $product->category = (object) [
                'names' => [],
                'all_products_found' => false
            ];
            foreach ($product->category_products as $category) {
                // assignment to "all products" has to be checked... otherwise show error message
                if ($category->id_category == Configure::read('app.categoryAllProducts')) {
                    $product->category->all_products_found = true;
                } else {
                    // sometimes associated category does not exist any more...
                    if (!empty($category->category)) {
                        $product->category->names[] = $category->category->name;
                    }
                }
            }
            $product->selected_categories = Hash::extract($product->category_products, '{n}.id_category');

            $product->is_new = true;
            if ($product->product_shop->created) {
                $product->is_new = $this->isNew($product->product_shop->created->i18nFormat(Configure::read('DateFormat.Database')));
            }

            $product->gross_price = $this->getGrossPrice($product->id_product, $product->product_shop->price);

            $rowClass = [];
            if (! $product->active) {
                $rowClass[] = 'deactivated';
            }

            if (!empty($product->deposit_product)) {
                $product->deposit = $product->deposit_product->deposit;
            } else {
                $product->deposit = 0;
            }

            
            // show unity only for main products
            $additionalProductNameInfos = [];
            if (empty($product->product_attributes) && $product->product_lang->unity != '') {
                $additionalProductNameInfos[] = '<span class="unity-for-dialog">' . $product->product_lang->unity . '</span>';
            }
            
            $product->price_is_zero = false;
            if (empty($product->product_attributes) && $product->gross_price == 0) {
                $product->price_is_zero = true;
            }
            $product->unit = null;
            if (!empty($product->unit_product)) {
                
                $product->unit = $product->unit_product;
                
                $quantityInUnitsString = Configure::read('app.htmlHelper')->getQuantityInUnitsWithWrapper($product->unit_product->price_per_unit_enabled, $product->unit_product->quantity_in_units, $product->unit_product->name);
                if ($quantityInUnitsString != '') {
                    $additionalProductNameInfos[] = $quantityInUnitsString;
                }
                
                if ($product->unit_product->price_per_unit_enabled) {
                    if ($product->unit_product->price_incl_per_unit == 0) {
                        $product->price_is_zero = true;
                    } else  {
                        $product->price_is_zero = false;
                    }
                }
                
            }
            
            $product->product_lang->unchanged_name = $product->product_lang->name;
            $product->product_lang->name = '<span class="product-name">' . $product->product_lang->name . '</span>';
            if (!empty($additionalProductNameInfos)) {
                $product->product_lang->name = $product->product_lang->name . ': ' . join(', ', $additionalProductNameInfos);
            }
            
            if (empty($product->tax)) {
                $product->tax = (object) [
                    'rate' => 0
                ];
            }

            $rowClass[] = 'main-product';
            $rowIsOdd = false;
            if ($i % 2 == 0) {
                $rowIsOdd = true;
                $rowClass[] = 'custom-odd';
            }
            $product->row_class = join(' ', $rowClass);

            $preparedProducts[] = $product;
            $i ++;

            if (! empty($product->product_attributes)) {
                $currentPreparedProduct = count($preparedProducts) - 1;
                $preparedProducts[$currentPreparedProduct]['AttributesRemoved'] = 0;

                foreach ($product->product_attributes as $attribute) {
                    if (($quantityIsZeroFilterOn && empty($attribute->stock_available)) || ($priceIsZeroFilterOn && empty($attribute->product_attribute_shop))) {
                        $preparedProducts[$currentPreparedProduct]['AttributesRemoved'] ++;
                        continue;
                    }

                    $grossPrice = 0;
                    if (! empty($attribute->product_attribute_shop->price)) {
                        $grossPrice = $this->getGrossPrice($product->id_product, $attribute->product_attribute_shop->price);
                    }

                    $rowClass = [
                        'sub-row'
                    ];
                    if (! $product->active) {
                        $rowClass[] = 'deactivated';
                    }

                    if ($rowIsOdd) {
                        $rowClass[] = 'custom-odd';
                    }
                    
                    
                    $priceIsZero = false;
                    if ($grossPrice == 0) {
                        $priceIsZero = true;
                    }
                    if (!empty($attribute->unit_product_attribute) && $attribute->unit_product_attribute->price_per_unit_enabled) {
                        $productName =  $unity = Configure::read('app.htmlHelper')->getQuantityInUnitsStringForAttributes(
                            $attribute->product_attribute_combination->attribute->name,
                            $attribute->product_attribute_combination->attribute->can_be_used_as_unit,
                            $attribute->unit_product_attribute->price_per_unit_enabled,
                            $attribute->unit_product_attribute->quantity_in_units,
                            $attribute->unit_product_attribute->name
                        );
                        if ($attribute->unit_product_attribute->price_incl_per_unit == 0) {
                            $priceIsZero = true;
                        } else {
                            $priceIsZero = false;
                        }
                    } else {
                        $productName = $attribute->product_attribute_combination->attribute->name;
                        if ($addProductNameToAttributes) {
                            $productName = $product->product_lang->name . ': ' . $productName;
                        }
                    }
                    
                    $preparedProduct = [
                        'id_product' => $product->id_product . '-' . $attribute->id_product_attribute,
                        'gross_price' => $grossPrice,
                        'active' => - 1,
                        'price_is_zero' => $priceIsZero,
                        'row_class' => join(' ', $rowClass),
                        'product_lang' => [
                            'unchanged_name' => $product->product_lang->unchanged_name,
                            'name' => $productName,
                            'description_short' => '',
                            'description' => '',
                            'unity' => ''
                        ],
                        'manufacturer' => [
                            'name' => (!empty($product->manufacturer) ? $product->manufacturer->name : '')
                        ],
                        'product_attribute_shop' => [
                            'default_on' => $attribute->product_attribute_shop->default_on
                        ],
                        'stock_available' => [
                            'quantity' => $attribute->stock_available->quantity
                        ],
                        'deposit' => !empty($attribute->deposit_product_attribute) ? $attribute->deposit_product_attribute->deposit : 0,
                        'unit' => !empty($attribute->unit_product_attribute) ? $attribute->unit_product_attribute : [],
                        'category' => [
                            'names' => [],
                            'all_products_found' => true
                        ],
                        'image' => null
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
                if (isset($preparedProducts[$key]['AttributesRemoved']) && $preparedProducts[$key]['AttributesRemoved'] == count($preparedProducts[$key]->product_attributes)) {
                    unset($preparedProducts[$key]);
                }
            }
        }

        $preparedProducts = json_decode(json_encode($preparedProducts), false); // convert array recursively into object
        
        return $preparedProducts;
    }

    public function getForDropdown($appAuth, $manufacturerId)
    {
        $conditions = [];

        if ($appAuth->isManufacturer()) {
            $manufacturerId = $appAuth->getManufacturerId();
        }

        if ($manufacturerId > 0) {
            $conditions['Manufacturers.id_manufacturer'] = $manufacturerId;
        }

        // ->find('list') a does not return associated model data
        $products = $this->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'ProductLangs',
                'Manufacturers',
            ],
            'order' => [
                'Products.active' => 'DESC',
                'ProductLangs.name' => 'ASC'
            ]
        ]);

        $offlineProducts = [];
        $onlineProducts = [];
        foreach ($products as $product) {
            $productNameForDropdown = $product->product_lang->name . (!empty($product->manufacturer) ? ' - ' . $product->manufacturer->name : '');
            if ($product->active == 0) {
                $offlineProducts[$product->id_product] = $productNameForDropdown;
            } else {
                $onlineProducts[$product->id_product] = $productNameForDropdown;
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
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $rate = $statement->fetchAll('assoc');

        // if tax == 0 %, tax is empty
        if (empty($rate)) {
            $newNetPrice = $oldNetPrice * (1 + $oldTaxRate / 100);
        } else {
            $newNetPrice = $rate[0]['new_net_price'];
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
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $rate = $statement->fetchAll('assoc');

        // if tax == 0% rate is empty...
        if (empty($rate)) {
            $grossPrice = $netPrice;
        } else {
            $grossPrice = $rate[0]['gross_price'];
        }

        return $grossPrice;
    }

    public function getNetPrice($productId, $grossPrice)
    {
        $grossPrice = Configure::read('app.numberHelper')->replaceCommaWithDot($grossPrice);

        if (! $grossPrice > - 1) { // allow 0 as new price
            return false;
        }

        $sql = 'SELECT ROUND(:grossPrice / (100 + t.rate) * 100, 6) as net_price ';
        $sql .= $this->getTaxJoins();
        $params = [
            'productId' => $productId,
            'grossPrice' => $grossPrice
        ];
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $rate = $statement->fetchAll('assoc');

        // if tax == 0% rate is empty...
        if (empty($rate)) {
            $netPrice = $grossPrice;
        } else {
            $netPrice = $rate[0]['net_price'];
        }

        return $netPrice;
    }

    private function getIsQuantityMinFilterSetCondition()
    {
        return 'StockAvailables.quantity < 3';
    }

    private function getIsPriceZeroCondition()
    {
        return 'ProductShops.price = 0';
    }

    public function changeDefaultAttributeId($productId, $productAttributeId)
    {
        $productAttributes = $this->ProductAttributes->find('all', [
            'conditions' => [
                'ProductAttributes.id_product' => $productId
            ]
        ])->toArray();

        $productAttributeIds = [];
        foreach ($productAttributes as $attribute) {
            $productAttributeIds[] = $attribute->id_product_attribute;
        }

        // first set all associated attributes to 0
        $this->ProductAttributes->ProductAttributeShops->updateAll([
            'default_on' => 0
        ], [
            'id_product_attribute IN (' . join(', ', $productAttributeIds) . ')',
            'id_shop' => 1
        ]);

        // then set the new one
        $this->ProductAttributes->ProductAttributeShops->updateAll([
            'default_on' => 1
        ], [
            'id_product_attribute' => $productAttributeId,
            'id_shop' => 1
        ]);
    }

    public function deleteProductAttribute($productId, $attributeId)
    {

        $pac = $this->ProductAttributes->ProductAttributeCombinations->find('all', [
            'conditions' => [
                'ProductAttributeCombinations.id_product_attribute' => $attributeId
            ]
        ])->first();
        $productAttributeId = $pac->id_product_attribute;

        $this->ProductAttributes->deleteAll([
            'ProductAttributes.id_product_attribute' => $productAttributeId
        ]);

        $this->ProductAttributes->ProductAttributeCombinations->deleteAll([
            'ProductAttributeCombinations.id_product_attribute' => $productAttributeId
        ]);

        $this->ProductAttributes->ProductAttributeShops->deleteAll([
            'ProductAttributeShops.id_product_attribute' => $productAttributeId
        ]);

        $this->ProductAttributes->UnitProductAttributes->deleteAll([
            'UnitProductAttributes.id_product_attribute' => $productAttributeId
        ]);
        
        // deleteAll can only get primary key as condition
        $originalPrimaryKey = $this->StockAvailables->getPrimaryKey();
        $this->StockAvailables->setPrimaryKey('id_product_attribute');
        $this->StockAvailables->deleteAll([
            'StockAvailables.id_product_attribute' => $attributeId
        ]);
        $this->StockAvailables->setPrimaryKey($originalPrimaryKey);

        $this->StockAvailables->updateQuantityForMainProduct($productId);
    }

    public function add($manufacturer)
    {
        $defaultQuantity = 999;

        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $defaultTaxId = $this->Manufacturer->getOptionDefaultTaxId($manufacturer->default_tax_id);

        // INSERT PRODUCT
        $newProduct = $this->save(
            $this->newEntity(
                [
                    'id_manufacturer' => $manufacturer->id_manufacturer,
                    'id_category_default' => Configure::read('app.categoryAllProducts'),
                    'id_tax' => $defaultTaxId,
                    'unity' => ''
                ]
            )
        );
        $newProductId = $newProduct->id_product;

        // INSERT PRODUCT_SHOP
        $this->ProductShops->save(
            $this->ProductShops->newEntity(
                [
                    'id_product' => $newProductId,
                    'id_shop' => 1,
                    'id_category_default' => Configure::read('app.categoryAllProducts')
                ]
            )
        );

        // INSERT CATEGORY_PRODUCTS
        $this->CategoryProducts->save(
            $this->CategoryProducts->newEntity(
                [
                    'id_category' => Configure::read('app.categoryAllProducts'),
                    'id_product' => $newProductId
                ]
            )
        );

        // INSERT PRODUCT_LANG
        $name = StringComponent::removeSpecialChars('Neues Produkt von ' . $manufacturer->name);
        $this->ProductLangs->save(
            $this->ProductLangs->newEntity(
                [
                    'id_product' => $newProductId,
                    'name' => $name,
                    'description' => '',
                    'description_short' => '',
                    'unity' => ''
                ]
            )
        );

        // INSERT STOCK AVAILABLE
        $this->StockAvailables->save(
            $this->StockAvailables->newEntity(
                [
                    'id_product' => $newProductId,
                    'id_shop' => 1,
                    'quantity' => $defaultQuantity
                ]
            )
        );

        $newProduct = $this->find('all', [
            'conditions' => [
                'Products.id_product' => $newProductId
            ]
        ])->first();

        return $newProduct;
    }
}
