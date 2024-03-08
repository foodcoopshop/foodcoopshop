<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Utility\Hash;
use Cake\Core\Configure;
use App\Services\FolderService;
use Cake\Validation\Validator;
use Cake\Datasource\FactoryLocator;
use App\Services\DeliveryRhythmService;
use App\Services\CatalogService;
use App\Services\RemoteFileService;
use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;
use App\Model\Traits\AllowOnlyOneWeekdayValidatorTrait;
use App\Model\Traits\ProductImportTrait;
use App\Model\Entity\Product;
use Cake\Routing\Router;
use App\Model\Entity\Customer;
use App\Services\ChangeSellingPriceService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductsTable extends AppTable
{

    use AllowOnlyOneWeekdayValidatorTrait;
    use ProductCacheClearAfterSaveAndDeleteTrait;
    use ProductImportTrait;

    protected $Configuration;
    protected $Manufacturer;
    protected $Unit;

    public function initialize(array $config): void
    {
        $this->setTable('product');
        parent::initialize($config);
        $this->setPrimaryKey('id_product');
        $this->belongsTo('Manufacturers', [
            'foreignKey' => 'id_manufacturer'
        ]);
        $this->hasOne('StockAvailables', [
            'foreignKey' => 'id_product'
        ]);
        $this->belongsTo('PurchasePriceProducts', [
            'foreignKey' => 'id_product',
            'conditions' => [
                'PurchasePriceProducts.product_attribute_id = 0',
            ],
        ]);
        $this->hasOne('BarcodeProducts', [
            'foreignKey' => 'product_id',
            'conditions' => [
                'BarcodeProducts.product_attribute_id = 0',
            ],
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
        $this->hasOne('ProductAttribute', [
            'foreignKey' => 'id_product'
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
        $this->belongsTo('StorageLocations', [
            'foreignKey' => 'id_storage_location',
        ]);
        $this->addBehavior('Timestamp');
    }

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->Configuration = FactoryLocator::get('Table')->get('Configurations');
    }

    public function validationName(Validator $validator)
    {
        $validator->notEmptyString('name', __('Please_enter_a_name.'));
        $validator->minLength('name', 2, __('The_name_of_the_product_needs_to_be_at_least_{0}_characters_long.', [2]));
        return $validator;
    }

    public function validationDeliveryRhythm(Validator $validator)
    {
        $validator->add('delivery_rhythm_type', 'allowed-count-values', [
            'rule' => function ($value, $context) {
                if ($value == 'week') {
                    return in_array($context['data']['delivery_rhythm_count'], [1,2,4]);
                }
                if ($value == 'month') {
                    return in_array($context['data']['delivery_rhythm_count'], [0,1,2,3,4]);
                }
                if ($value == 'individual') {
                    return in_array($context['data']['delivery_rhythm_count'], [0]);
                }
                return false;
            },
            'message' => __('The_delivery_ryhthm_is_not_valid.')
        ]);
        $validator->allowEmptyString('delivery_rhythm_first_delivery_day');
        $validator->notEquals('delivery_rhythm_first_delivery_day', '1970-01-01', __('The_first_delivery_day_is_not_valid.'));
        $validator->allowEmptyString('delivery_rhythm_order_possible_until');
        $validator->notEquals('delivery_rhythm_order_possible_until', '1970-01-01', __('The_order_possible_until_field_is_not_valid.'));
        $validator->add('delivery_rhythm_order_possible_until', 'allowed-only-smaller-than-first-delivery-day', [
            'rule' => function ($value, $context) {
                if ($context['data']['delivery_rhythm_type'] == 'individual') {
                    return $context['data']['delivery_rhythm_first_delivery_day'] > $value;
                }
                return true;
            },
            'message' => __('The_order_possible_until_field_needs_to_be_smaller_than_the_delivery_date.')
        ]);
        $validator = $this->getCorrectDayOfMonthValidator($validator, 'delivery_rhythm_first_delivery_day');
        $validator = $this->getCorrectDayOfMonthValidator($validator, 'delivery_rhythm_first_delivery_day');
        $validator = $this->getAllowOnlyOneWeekdayValidator($validator, 'delivery_rhythm_first_delivery_day', __('The_first_delivery_day'));
        $validator->range('delivery_rhythm_send_order_list_weekday', [0, 6], __('Please_enter_a_number_between_{0}_and_{1}.', [0, 6]));
        $validator->allowEmptyString('delivery_rhythm_send_order_list_day');
        $validator->notEquals('delivery_rhythm_send_order_list_day', '1970-01-01', __('The_send_order_list_day_field_is_not_valid.'));
        $validator->add('delivery_rhythm_send_order_list_day', 'allowed-only-between-two-dates', [
            'rule' => function ($value, $context) {
                if ($context['data']['delivery_rhythm_type'] == 'individual') {
                    return $context['data']['delivery_rhythm_first_delivery_day'] > $value && $context['data']['delivery_rhythm_order_possible_until'] < $value;
                }
                return true;
            },
            'message' => __('The_send_order_list_day_field_needs_to_be_between_order_possible_until_date_and_first_delivery_day.')
        ]);

        return $validator;
    }

    private function getCorrectDayOfMonthValidator(Validator $validator, $field)
    {
        $validator->add($field, 'allow-only-correct-weekday-of-month', [

            'rule' => function ($value, $context) {

                if ($context['data']['delivery_rhythm_type'] == 'month') {

                    switch($context['data']['delivery_rhythm_count']) {
                        case '1':
                            $ordinal = 'first';
                            $ordinalForWeekday = __('first_for_weekday');
                            break;
                        case '2':
                            $ordinal = 'second';
                            $ordinalForWeekday = __('second_for_weekday');
                            break;
                        case '3':
                            $ordinal = 'third';
                            $ordinalForWeekday = __('third_for_weekday');
                            break;
                        case '4':
                            $ordinal = 'fourth';
                            $ordinalForWeekday = __('fourth_for_weekday');
                            break;
                        case '0':
                            $ordinal = 'last';
                            $ordinalForWeekday = __('last_for_weekday');
                            break;
                    }

                    $deliveryDayAsWeekdayInEnglish = strtolower(date('l', strtotime($context['data']['delivery_rhythm_first_delivery_day'])));

                    if (isset($ordinal)) {
                        $calculatedPickupDay = date(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), strtotime($context['data']['delivery_rhythm_first_delivery_day'] . ' ' . $ordinal . ' ' . $deliveryDayAsWeekdayInEnglish . ' of this month'));
                    }

                    $deliveryWeekdayName = Configure::read('app.timeHelper')->getWeekdayName((new DeliveryRhythmService())->getDeliveryWeekday());
                    if (isset($ordinalForWeekday)) {
                        $message = __('The_first_delivery_day_needs_to_be_a_{0}_{1}_of_the_month.', [
                            $ordinalForWeekday,
                            $deliveryWeekdayName,
                        ]);
                    }

                    if (isset($calculatedPickupDay)
                        && isset($message)
                        && $calculatedPickupDay != $value) {
                        return $message;
                    }

                }

                return true;

            }

        ]);
        return $validator;
    }

    private function deliveryBreakEnabledBase(string|null $noDeliveryDaysAsString, string $deliveryDate): bool
    {
        return $noDeliveryDaysAsString != '' && preg_match('`' . $deliveryDate . '`', $noDeliveryDaysAsString);
    }

    public function deliveryBreakGlobalEnabled(string|null $noDeliveryDaysAsString, string $deliveryDate): bool
    {
        return $this->deliveryBreakEnabledBase($noDeliveryDaysAsString, $deliveryDate);
    }

    /**
     * manufacturer based delivery break is never applied for stock products
     */
    public function deliveryBreakManufacturerEnabled(
        string|null $noDeliveryDaysAsString,
        string $deliveryDate,
        bool|int $stockManagementEnabled,
        bool|int $isStockProduct): bool
    {
        if ($stockManagementEnabled && $isStockProduct) {
            return false;
        }
        return $this->deliveryBreakEnabledBase($noDeliveryDaysAsString, $deliveryDate);
    }

    public function isOwner(int $productId, int $manufacturerId): bool
    {
        $found = $this->find('all', conditions: [
            'Products.id_product' => $productId,
            'Products.id_manufacturer' => $manufacturerId
        ])->count();
        return (bool) $found;
    }

    public function getProductIdAndAttributeId(string|int $productId): array
    {
        $attributeId = 0;
        $explodedProductId = explode('-', (string) $productId);
        if (count($explodedProductId) == 2) {
            $productId = $explodedProductId[0];
            $attributeId = $explodedProductId[1];
        }
        return [
            'productId' => (int) $productId,
            'attributeId' => (int) $attributeId,
        ];
    }

    public function getCompositeProductIdAndAttributeId(int $productId, int $attributeId = 0)
    {
        $compositeId = $productId;
        if ($attributeId > 0) {
            $compositeId .= '-'.$attributeId;
        }
        return $compositeId;
    }

    public function changeStatus(array $products): bool
    {

        $products2save = [];

        foreach ($products as $product) {
            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                throw new \Exception('change status is not allowed for product attributes');
            }
            $status = $product[$ids['productId']];
            if (!in_array($status, Product::ALLOWED_STATUSES, true)) { // last param for type check
                throw new \Exception('Products.active for product ' .$ids['productId'] . ' needs to be ' .APP_OFF . ' or ' . APP_ON.'; was: ' . $status);
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

    public function getQuantityAsInteger(string|int $quantity): int
    {
        $quantity = trim($quantity);

        if (!is_numeric($quantity)) {
            return -1; // do not return false, because 0 is a valid return value!
        }
        $quantity = (int) ($quantity);

        return $quantity;
    }

    public function changeDeposit(array $products): bool
    {

        foreach ($products as $product) {
            $productId = key($product);
            $deposit = $product[$productId];
            if (is_string($deposit)) {
                $deposit = Configure::read('app.numberHelper')->getStringAsFloat($product[$productId]);
            }
            if ($deposit < 0) {
                throw new \Exception('input format not correct: '.$product[$productId]);
            }
        }

        $success = false;
        foreach ($products as $product) {

            $productId = key($product);

            $deposit = $product[$productId];
            if (is_string($deposit)) {
                $deposit = Configure::read('app.numberHelper')->getStringAsFloat($product[$productId]);
            }

            $ids = $this->getProductIdAndAttributeId($productId);

            $depositProductsTable = FactoryLocator::get('Table')->get('DepositProducts');
            if ($ids['attributeId'] > 0) {
                $oldDeposit = $depositProductsTable->find('all',
                    conditions: [
                        'id_product_attribute' => $ids['attributeId']
                    ]
                )->first();

                if (empty($oldDeposit)) {
                    $entity = $depositProductsTable->newEntity([]);
                } else {
                    $depositProductsTable->setPrimaryKey('id_product_attribute');
                    $entity = $depositProductsTable->get($oldDeposit->id_product_attribute);
                }

                $deposit2save = [
                    'id_product_attribute' => $ids['attributeId'],
                    'deposit' => $deposit
                ];
            } else {
                // deposit is set for productId
                $oldDeposit = $depositProductsTable->find('all',
                    conditions: [
                        'id_product' => $productId
                    ]
                )->first();

                if (empty($oldDeposit)) {
                    $entity = $depositProductsTable->newEntity([]);
                } else {
                    $entity = $depositProductsTable->get($oldDeposit->id_product);
                }

                $deposit2save = [
                    'id_product' => $productId,
                    'deposit' => $deposit
                ];
            }

            $depositProductsTable->setPrimaryKey('id');
            $result = $depositProductsTable->save(
                $depositProductsTable->patchEntity($entity, $deposit2save)
            );
            $depositProductsTable->setPrimaryKey('id_product');
            $success |= is_object($result);
        }

        return (bool) $success;
    }

    public function changePrice(array $products, $changeOpenOrderDetailPrice = false): bool
    {

        foreach ($products as $product) {
            $productId = key($product);
            $price = $product[$productId]['gross_price'];
            if (is_string($price)) {
                $price = Configure::read('app.numberHelper')->getStringAsFloat($product[$productId]['gross_price']);
            }
            if ($price < 0) {
                throw new \Exception('input format not correct: '.$product[$productId]['gross_price']);
            }
        }

        $success = false;
        $productAttributesTable = FactoryLocator::get('Table')->get('ProductAttributes');
        foreach ($products as $product) {

            $productId = key($product);
            $price = $product[$productId]['gross_price'];
            if (is_string($price)) {
                $price = Configure::read('app.numberHelper')->getStringAsFloat($price);
            }

            $ids = $this->getProductIdAndAttributeId($productId);
            $productEntity = $this->find('all',
            conditions: [
                'Products.id_product' => $ids['productId'],
            ],
            contain: [
                'Taxes',
            ])->first();
            $taxRate = $productEntity->tax->rate ?? 0;

            $netPrice = $this->getNetPrice($price, $taxRate);

            if ($ids['attributeId'] > 0) {
                // update attribute - updateAll needed for multi conditions of update
                $result = $productAttributesTable->updateAll([
                    'price' => $netPrice
                ], [
                    'id_product_attribute' => $ids['attributeId']
                ]);
                // if results are not the returned row count would be 0, so always set to true;
                $success |= true;
            } else {
                $product2update = [
                    'price' => $netPrice
                ];
                $entity = $this->get($ids['productId']);
                $result = $this->save(
                    $this->patchEntity($entity, $product2update)
                );
                $success |= is_object($result);
            }

            if (isset($product[$productId]['unit_product_price_per_unit_enabled']) && isset($product[$productId]['unit_product_price_incl_per_unit'])) {

                $this->Unit = FactoryLocator::get('Table')->get('Units');

                $priceInclPerUnit = $product[$productId]['unit_product_price_incl_per_unit'];
                if (is_string($priceInclPerUnit)) {
                    $priceInclPerUnit = Configure::read('app.numberHelper')->getStringAsFloat($priceInclPerUnit);
                }
                $quantityInUnits = $product[$productId]['unit_product_quantity_in_units'];
                if (is_string($quantityInUnits)) {
                    $quantityInUnits = Configure::read('app.numberHelper')->getStringAsFloat($quantityInUnits);
                }

                $unitName = $product[$productId]['unit_product_name'];
                $unitAmount = (int) $product[$productId]['unit_product_amount'];

                $this->Unit->saveUnits(
                    $ids['productId'],
                    $ids['attributeId'],
                    $product[$productId]['unit_product_price_per_unit_enabled'],
                    $priceInclPerUnit == -1 ? 0 : $priceInclPerUnit,
                    $unitName,
                    $unitAmount,
                    $quantityInUnits == -1 ? 0 : $quantityInUnits
                );

                if ($changeOpenOrderDetailPrice && $priceInclPerUnit > 0) {
                    (new ChangeSellingPriceService())->changeOpenOrderDetailPricePerUnit(
                        $ids,
                        $priceInclPerUnit,
                        $unitName,
                        $unitAmount,
                        $quantityInUnits,
                    );
                }

            } else {
                if ($changeOpenOrderDetailPrice) {
                    (new ChangeSellingPriceService())->changeOpenOrderDetailPrice($ids, $price);
                }
            }

        }

        return (bool) $success;

    }

    public function changeQuantity(array $products): void
    {

        $stockAvailablesTable = FactoryLocator::get('Table')->get('StockAvailables');

        foreach ($products as $product) {
            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);
            $entity = $stockAvailablesTable->newEntity($product[$productId]);
            if ($entity->hasErrors()) {
                throw new \Exception(join(' ', $stockAvailablesTable->getAllValidationErrors($entity)));
            }
        }

        foreach ($products as $product) {
            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                $entity = $stockAvailablesTable->find('all',
                    conditions: [
                        'id_product_attribute' => $ids['attributeId'],
                        'id_product' => $ids['productId'],
                    ],
                )->first();
                $originalPrimaryKey = $stockAvailablesTable->getPrimaryKey();
                $stockAvailablesTable->setPrimaryKey('id_product_attribute');
                $stockAvailablesTable->save(
                    $stockAvailablesTable->patchEntity($entity, $product[$productId])
                );
                $stockAvailablesTable->setPrimaryKey($originalPrimaryKey);
                $stockAvailablesTable->updateQuantityForMainProduct($ids['productId']);
            } else {
                $entity = $stockAvailablesTable->get($ids['productId']);
                $stockAvailablesTable->save(
                    $stockAvailablesTable->patchEntity($entity, $product[$productId])
                );
            }
        }
    }

    public function changeDeliveryRhythm(array $products): bool
    {

        $products2save = [];

        foreach ($products as $product) {
            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                throw new \Exception('change delivery_rhythm is not allowed for product attributes');
            }
            $entity = $this->newEntity(
                $product[$productId],
                [
                    'validate' => 'deliveryRhythm'
                ]
            );
            if ($entity->hasErrors()) {
                throw new \Exception(join(' ', $this->getAllValidationErrors($entity)));
            } else {
                $products2save[] = array_merge(
                    ['id_product' => $ids['productId']], $product[$productId]
                );
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

    public function changeIsStockProduct(array $products): bool
    {

        $products2save = [];
        foreach ($products as $product) {
            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                throw new \Exception('change is_stock_product is not allowed for product attributes');
            }
            $isStockProduct = (int) $product[$ids['productId']];
            $allowed = [APP_OFF, APP_ON];
            if (!in_array($isStockProduct, $allowed, true)) { // last param for type check
                throw new \Exception('Products.is_stock_product for product ' .$ids['productId'] . ' needs to be ' .APP_OFF . ' or ' . APP_ON.'; was: ' . $isStockProduct);
            } else {
                $products2save[] = [
                    'id_product' => $ids['productId'],
                    'is_stock_product' => $isStockProduct
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

    public function changeName(array $products): bool
    {

        $products2save = [];

        foreach ($products as $product) {
            $productId = key($product);
            $name = $product[$productId];
            $ids = $this->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                throw new \Exception('change name is not allowed for product attributes');
            }

            $productEntity = $this->newEntity(
                [
                    'name' => $name['name'],
                ],
                [
                    'validate' => 'name',
                ]
            );
            if ($productEntity->hasErrors()) {
                throw new \Exception(join(' ', $this->getAllValidationErrors($productEntity)));
            }

            if (isset($name['barcode'])) {
                $barcodeProductsTable = FactoryLocator::get('Table')->get('BarcodeProducts');
                $barcodeProductEntity = $barcodeProductsTable->newEntity(
                    [
                        'barcode' => $name['barcode'],
                    ],
                    [
                        'validate' => true
                    ]
                );
                if ($barcodeProductEntity->hasErrors()) {
                    throw new \Exception(join(' ', $this->getAllValidationErrors($barcodeProductEntity)));
                }
            }

            $tmpProduct2Save = [
                'id_product' => $ids['productId'],
                'name' => $name['name'],
                'description_short' => $name['description_short'],
                'description' => $name['description'],
                'unity' => $name['unity'],
            ];
            if (isset($name['is_declaration_ok'])) {
                $tmpProduct2Save['is_declaration_ok'] = $name['is_declaration_ok'];
            }
            if (isset($name['id_storage_location']) && $name['id_storage_location'] > 0) {
                $tmpProduct2Save['id_storage_location'] = $name['id_storage_location'];
            }

            if (isset($name['barcode'])) {
                $tmpProduct2Save['barcode_product'] = [
                    'product_id' => $ids['productId'],
                    'barcode' => $name['barcode'],
                ];
            }
            $products2save[] = $tmpProduct2Save;

        }

        $success = false;

        if (!empty($products2save)) {
            $entities = $this->newEntities($products2save, [
                'associated' => [
                    'BarcodeProducts',
                ],
            ]);
            $result = $this->saveMany($entities);
            $success = !empty($result);
        }

        return $success;
    }

    public function isNew(string $date): bool
    {
        $showAsNewExpirationDate = date('Y-m-d', strtotime($date . ' + ' . Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' days'));
        if (strtotime($showAsNewExpirationDate) > strtotime(date('Y-m-d'))) {
            return true;
        }
        return false;
    }

    public function getProductsForBackend($productIds, $manufacturerId, $active, $categoryId = '', $isQuantityMinFilterSet = false, $isPriceZero = false, $addProductNameToAttributes = false, $controller = null)
    {

        $conditions = [];
        if ($manufacturerId != 'all') {
            $conditions['Products.id_manufacturer'] = $manufacturerId;
        } else {
            // do not show any non-associated products that might be found in database
            $conditions[] = 'Products.id_manufacturer > 0';
        }

        if ($productIds != '') {
            $conditions['Products.id_product IN'] = $productIds;
        }

        if ($active != 'all') {
            $conditions['Products.active'] = $active;
        } else {
            $conditions['Products.active >'] = APP_DEL;
        }

        if ($isQuantityMinFilterSet) {
            $conditions[] = $this->getIsQuantityMinFilterSetCondition();
        }

        if ($isPriceZero) {
            $conditions[] = $this->getIsPriceZeroCondition();
        }

        $quantityIsZeroFilterOn = false;
        $priceIsZeroFilterOn = false;
        foreach ($conditions as $condition) {
            if (is_int($condition) || !is_array($condition)) {
                continue;
            }
            if (is_string($condition)) {
                if (preg_match('/'.$this->getIsQuantityMinFilterSetCondition().'/', $condition)) {
                    $this->getAssociation('ProductAttributes')->setConditions(
                        [
                            'StockAvailables.quantity < 3'
                        ]
                    );
                    $quantityIsZeroFilterOn = true;
                }
                if (preg_match('/'.$this->getIsPriceZeroCondition().'/', $condition)) {
                    $this->getAssociation('ProductAttributes')->setConditions(
                        [
                            'ProductAttributes.price' => 0
                        ]
                    );
                    $priceIsZeroFilterOn = true;
                }
            }
        }
        $contain = [
            'CategoryProducts',
            'CategoryProducts.Categories',
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
            'ProductAttributes.DepositProductAttributes',
            'ProductAttributes.UnitProductAttributes',
            'ProductAttributes.ProductAttributeCombinations.Attributes'
        ];

        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $contain[] = 'PurchasePriceProducts.Taxes';
            $contain[] = 'ProductAttributes.PurchasePriceProductAttributes';
        }

        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $contain[] = 'BarcodeProducts';
            $contain[] = 'ProductAttributes.BarcodeProductAttributes';
        }

        $order = [
            'Products.active' => 'DESC',
            'Products.name' => 'ASC'
        ];

        $query = $this->find('all',
        conditions: $conditions,
        contain: $contain,
        order: $controller === null ? $order : null);

        if ($categoryId != '') {
            $query->matching('CategoryProducts', function ($q) use ($categoryId) {
                return $q->where(['id_category IN' => $categoryId]);
            });
        }

        $depositProductsTable = FactoryLocator::get('Table')->get('DepositProducts');
        $stockAvailablesTable = FactoryLocator::get('Table')->get('StockAvailables');
        $purchasePriceProductsTable = FactoryLocator::get('Table')->get('PurchasePriceProducts');
        $barcodeProductsTable = FactoryLocator::get('Table')->get('BarcodeProducts');
        $taxesTable = FactoryLocator::get('Table')->get('Taxes');
        $manufacturersTable = FactoryLocator::get('Table')->get('Manufacturers');
        $unitProductsTable = FactoryLocator::get('Table')->get('UnitProducts');

        $query
        ->select('Products.id_product')->distinct()
        ->select($this) // Products
        ->select($depositProductsTable)
        ->select('Images.id_image')
        ->select($taxesTable)
        ->select($manufacturersTable)
        ->select($unitProductsTable)
        ->select($stockAvailablesTable);

        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $query->select($purchasePriceProductsTable);
        }

        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $catalogService = new CatalogService();
            $query->select(['system_bar_code' => $catalogService->getProductIdentifierField()]);
            $query->select($barcodeProductsTable);
        }

        if ($controller) {
            $query = $controller->paginate($query, [
                'sortableFields' => [
                    'Images.id_image', 'Products.name', 'Products.is_declaration_ok', 'Taxes.rate', 'Products.active', 'Manufacturers.name', 'Products.is_stock_product'
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
            if ($product->created) {
                $product->is_new = $this->isNew($product->created->i18nFormat(Configure::read('DateFormat.Database')));
            }

            $taxRate = is_null($product->tax) ? 0 : $product->tax->rate;
            $product->gross_price = $this->getGrossPrice($product->price, $taxRate);

            $product->delivery_rhythm_string = Configure::read('app.htmlHelper')->getDeliveryRhythmString(
                $product->is_stock_product && $product->manufacturer->stock_management_enabled,
                $product->delivery_rhythm_type,
                $product->delivery_rhythm_count
            );
            $product->last_order_weekday = Configure::read('app.timeHelper')->getWeekdayName(
                Configure::read('app.timeHelper')->getNthWeekdayBeforeWeekday(1, $product->delivery_rhythm_send_order_list_weekday)
            );

            $rowClass = [];
            if (! $product->active) {
                $rowClass[] = 'deactivated';
            }

            if (!empty($product->deposit_product)) {
                $product->deposit = $product->deposit_product->deposit;
            } else {
                $product->deposit = 0;
            }

            if (!empty($product->image)) {
                $imageSrc = Configure::read('app.htmlHelper')->getProductImageSrc($product->image->id_image, 'home');
                $imageFile = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . $imageSrc);
                $imageFile = Configure::read('app.htmlHelper')->removeTimestampFromFile($imageFile);
                if ($imageFile != '' && !preg_match('/de-default-home/', $imageFile) && file_exists($imageFile)) {
                    $product->image->hash = sha1_file($imageFile);
                    $product->image->src = Configure::read('App.fullBaseUrl') . $imageSrc;
                }
            }

            // show unity only for main products
            $additionalProductNameInfos = [];
            if (empty($product->product_attributes) && $product->unity != '') {
                $additionalProductNameInfos[] = '<span class="unity-for-dialog">' . $product->unity . '</span>';
            }

            $product->price_is_zero = false;
            if (empty($product->product_attributes) && $product->gross_price == 0) {
                $product->price_is_zero = true;
            }
            $product->unit = null;
            if (empty($product->product_attributes) && !empty($product->unit_product)) {

                $product->unit = $product->unit_product;

                $quantityInUnitsString = Configure::read('app.pricePerUnitHelper')->getQuantityInUnitsWithWrapper($product->unit_product->price_per_unit_enabled, $product->unit_product->quantity_in_units, $product->unit_product->name);
                if ($quantityInUnitsString != '') {
                    $additionalProductNameInfos[] = $quantityInUnitsString;
                }

                if ($product->unit_product->price_per_unit_enabled) {
                    $product->price_is_zero = false;
                }

            }

            $product->unchanged_name = $product->name;

            $product->nameSetterMethodEnabled = false;
            $product->name = '<span class="product-name">' . $product->name . '</span>';
            if (!empty($additionalProductNameInfos)) {
                $product->name = $product->name . ': ' . join(', ', $additionalProductNameInfos);
            }
            $product->nameSetterMethodEnabled = true;

            if (empty($product->tax)) {
                $product->tax = (object) [
                    'rate' => 0,
                ];
            }

            if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {

                $product->purchase_price_is_zero = true;
                $product->purchase_price_is_set = $purchasePriceProductsTable->isPurchasePriceSet($product);

                if (empty($product->purchase_price_product) || $product->purchase_price_product->tax_id === null) {
                    $product->purchase_price_product = (object) [
                        'tax_id' => null,
                        'price' => null,
                        'tax' => [
                            'rate' => null,
                        ],
                    ];
                }
                if (!empty($product->purchase_price_product)) {

                    $purchasePriceTaxRate = $product->purchase_price_product->tax->rate ?? 0;
                    $purchasePrice = $product->purchase_price_product->price ?? null;
                    if ($purchasePrice === null) {
                        $product->purchase_gross_price = $purchasePrice;
                    } else {
                        $product->purchase_gross_price = $this->getGrossPrice($purchasePrice, $purchasePriceTaxRate);
                        if ($product->purchase_gross_price > 0) {
                            $product->purchase_price_is_zero = false;
                        }
                    }

                    if (!empty($product->unit) && $product->unit->price_per_unit_enabled) {
                        if (!is_null($product->unit->purchase_price_incl_per_unit)) {
                            $product->surcharge_percent = $purchasePriceProductsTable->calculateSurchargeBySellingPriceGross(
                                Configure::read('app.pricePerUnitHelper')->getPricePerUnit($product->unit->price_incl_per_unit, $product->unit_product->quantity_in_units, $product->unit_product->amount),
                                $taxRate,
                                Configure::read('app.pricePerUnitHelper')->getPricePerUnit($product->unit->purchase_price_incl_per_unit, $product->unit_product->quantity_in_units, $product->unit_product->amount),
                                $purchasePriceTaxRate,
                            );
                            $priceInclPerUnitAndAmount = $this->getNetPrice($product->unit->price_incl_per_unit, $taxRate) * $product->unit_product->quantity_in_units / $product->unit_product->amount;
                            $purchasePriceInclPerUnitAndAmount = $this->getNetPrice($product->unit->purchase_price_incl_per_unit, $purchasePriceTaxRate) * $product->unit_product->quantity_in_units / $product->unit_product->amount;
                            $product->surcharge_price = $priceInclPerUnitAndAmount - $purchasePriceInclPerUnitAndAmount;
                            if ($purchasePriceInclPerUnitAndAmount > 0) {
                                $product->purchase_price_is_zero = false;
                            }
                        }
                    } else {
                        $product->surcharge_percent = $purchasePriceProductsTable->calculateSurchargeBySellingPriceGross(
                            $product->gross_price,
                            $taxRate,
                            $product->purchase_gross_price,
                            $purchasePriceTaxRate,
                        );
                        $product->surcharge_price = $product->price - $purchasePrice;
                    }

                }
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
                    if (($quantityIsZeroFilterOn && empty($attribute->stock_available)) || ($priceIsZeroFilterOn && empty($attribute))) {
                        $preparedProducts[$currentPreparedProduct]['AttributesRemoved'] ++;
                        continue;
                    }

                    $grossPrice = 0;
                    if (! empty($attribute->price)) {
                        $grossPrice = $this->getGrossPrice($attribute->price, $taxRate);
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
                        $productName = Configure::read('app.pricePerUnitHelper')->getQuantityInUnitsStringForAttributes(
                            $attribute->product_attribute_combination->attribute->name,
                            $attribute->product_attribute_combination->attribute->can_be_used_as_unit,
                            $attribute->unit_product_attribute->price_per_unit_enabled,
                            $attribute->unit_product_attribute->quantity_in_units,
                            $attribute->unit_product_attribute->name
                        );
                        $priceIsZero = false;
                    } else {
                        $productName = $attribute->product_attribute_combination->attribute->name;
                        if ($addProductNameToAttributes) {
                            $productName = $product->name . ': ' . $productName;
                        }
                    }

                    $preparedProduct = [
                        'id_product' => $product->id_product . '-' . $attribute->id_product_attribute,
                        'gross_price' => $grossPrice,
                        'active' => - 1,
                        'is_stock_product' => $product->is_stock_product,
                        'price_is_zero' => $priceIsZero,
                        'row_class' => join(' ', $rowClass),
                        'unchanged_name' => $product->unchanged_name,
                        'name' => $productName,
                        'description_short' => '',
                        'description' => '',
                        'unity' => '',
                        'manufacturer' => [
                            'name' => (!empty($product->manufacturer) ? $product->manufacturer->name : ''),
                            'stock_management_enabled' => (!empty($product->manufacturer) ? $product->manufacturer->stock_management_enabled : false),
                        ],
                        'default_on' => $attribute->default_on,
                        'stock_available' => [
                            'quantity' => $attribute->stock_available->quantity,
                            'quantity_limit' => $attribute->stock_available->quantity_limit,
                            'sold_out_limit' => $attribute->stock_available->sold_out_limit,
                            'always_available' => $attribute->stock_available->always_available,
                            'default_quantity_after_sending_order_lists' => $attribute->stock_available->default_quantity_after_sending_order_lists,
                        ],
                        'deposit' => !empty($attribute->deposit_product_attribute) ? $attribute->deposit_product_attribute->deposit : 0,
                        'unit' => !empty($attribute->unit_product_attribute) ? $attribute->unit_product_attribute : [],
                        'category' => [
                            'names' => [],
                            'all_products_found' => true
                        ],
                        'image' => null,
                        'barcode_product' => $attribute->barcode_product_attribute,
                    ];

                    if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
                        $attributeId = $attribute->id_product_attribute ?? 0;
                        $preparedProduct['system_bar_code'] = $product->system_bar_code . Configure::read('app.numberHelper')->addLeadingZerosToNumber((string) $attributeId, 4);
                        $preparedProduct['image'] = $product->image;
                        if (!empty($attribute->unit_product_attribute) && $attribute->unit_product_attribute->price_per_unit_enabled) {
                            $preparedProduct['nameForBarcodePdf'] = $product->name . ': ' . $productName;
                        }
                    }

                    if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                        $productAttributesTable = FactoryLocator::get('Table')->get('ProductAttributes');
                        $preparedProduct['purchase_price_is_set'] = $productAttributesTable->PurchasePriceProductAttributes->isPurchasePriceSet($attribute);
                        $preparedProduct['purchase_price_is_zero'] = true;

                        $purchasePrice = $attribute->purchase_price_product_attribute->price ?? null;
                        if ($purchasePrice === null) {
                            $preparedProduct['purchase_gross_price'] = $purchasePrice;
                        } else {
                            $preparedProduct['purchase_gross_price'] = $this->getGrossPrice($purchasePrice, $purchasePriceTaxRate);
                            if ($preparedProduct['purchase_gross_price'] > 0) {
                                $preparedProduct['purchase_price_is_zero'] = false;
                            }
                        }

                        if (!empty($attribute->unit_product_attribute) && $attribute->unit_product_attribute->price_per_unit_enabled) {
                            if (!is_null($attribute->unit_product_attribute->purchase_price_incl_per_unit)) {
                                $preparedProduct['surcharge_percent'] = $purchasePriceProductsTable->calculateSurchargeBySellingPriceGross(
                                    Configure::read('app.pricePerUnitHelper')->getPricePerUnit($attribute->unit_product_attribute->price_incl_per_unit, $attribute->unit_product_attribute->quantity_in_units, $attribute->unit_product_attribute->amount),
                                    $taxRate,
                                    Configure::read('app.pricePerUnitHelper')->getPricePerUnit($attribute->unit_product_attribute->purchase_price_incl_per_unit, $attribute->unit_product_attribute->quantity_in_units, $attribute->unit_product_attribute->amount),
                                    $purchasePriceTaxRate,
                                );
                                $priceInclPerUnitAndAmount = $this->getNetPrice($attribute->unit_product_attribute->price_incl_per_unit, $taxRate) * $attribute->unit_product_attribute->quantity_in_units / $attribute->unit_product_attribute->amount;
                                $purchasePriceInclPerUnitAndAmount = $this->getNetPrice($attribute->unit_product_attribute->purchase_price_incl_per_unit, $purchasePriceTaxRate) * $attribute->unit_product_attribute->quantity_in_units / $attribute->unit_product_attribute->amount;
                                $preparedProduct['surcharge_price'] = $priceInclPerUnitAndAmount - $purchasePriceInclPerUnitAndAmount;
                                if ($purchasePriceInclPerUnitAndAmount > 0) {
                                    $preparedProduct['purchase_price_is_zero'] = false;
                                }
                            }
                        } else {
                            $preparedProduct['surcharge_percent'] = $purchasePriceProductsTable->calculateSurchargeBySellingPriceGross(
                                $grossPrice,
                                $taxRate,
                                $preparedProduct['purchase_gross_price'],
                                $purchasePriceTaxRate,
                            );
                            $preparedProduct['surcharge_price'] = $attribute->price - $purchasePrice;
                        }


                    }
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

    public function getForDropdown($manufacturerId)
    {
        $identity = Router::getRequest()->getAttribute('identity');
        $conditions = [];

        if ($identity->isManufacturer()) {
            $manufacturerId = $identity->getManufacturerId();
        }

        if ($manufacturerId > 0) {
            $conditions['Manufacturers.id_manufacturer'] = $manufacturerId;
        }

        // ->find('list') a does not return associated model data
        $products = $this->find('all',
        conditions: $conditions,
        contain: [
            'Manufacturers',
        ],
        order: [
            'Products.active' => 'DESC',
            'Products.name' => 'ASC'
        ]);

        $onlineProducts = [];
        $offlineProducts = [];
        $deletedProducts = [];
        foreach ($products as $product) {
            $productNameForDropdown = $product->name . (!empty($product->manufacturer) ? ' - ' . html_entity_decode($product->manufacturer->name) : '');
            switch($product->active) {
                case 1:
                    $onlineProducts[$product->id_product] = $productNameForDropdown;
                    break;
                case 0:
                    $offlineProducts[$product->id_product] = $productNameForDropdown;
                    break;
                case -1:
                    $deletedProducts[$product->id_product] = $productNameForDropdown;
                    break;
            }
        }

        $productsForDropdown = [];
        if (! empty($onlineProducts)) {
            $onlineCount = count($onlineProducts);
            $productsForDropdown[__('online') . '-' . $onlineCount] = $onlineProducts;
        }

        if (! empty($offlineProducts)) {
            $offlineCount = count($offlineProducts);
            $productsForDropdown[__('offline') . '-' . $offlineCount] = $offlineProducts;
        }

        if (! empty($deletedProducts)) {
            $deletedCount = count($deletedProducts);
            $productsForDropdown[__('deleted') . '-' . $deletedCount] = $deletedProducts;
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

    public function getGrossPrice($netPrice, $taxRate)
    {
        $grossPrice = $netPrice * (100 + $taxRate) / 100;
        $grossPrice = round($grossPrice, 2);
        return $grossPrice;
    }

    public function getNetPrice($grossPrice, $taxRate)
    {
        $netPrice = $grossPrice / (100 + $taxRate) * 100;
        $netPrice = round($netPrice, 6);
        return $netPrice;
    }

    public function getNetPriceForNewTaxRate($netPrice, $oldTaxRate, $newTaxRate) {
        $netPrice = $netPrice / ((100 + $newTaxRate) / 100) * (1 + $oldTaxRate / 100);
        $netPrice = round($netPrice, 6);
        return $netPrice;
    }

    private function getIsQuantityMinFilterSetCondition()
    {
        return '(StockAvailables.quantity < 3 && (StockAvailables.always_available = 0 || (Products.is_stock_product = 1 && Manufacturers.stock_management_enabled = 1)))';
    }

    private function getIsPriceZeroCondition()
    {
        return 'Products.price = 0';
    }

    public function setDefaultAttributeId($productId, $productAttributeId)
    {
        $productAttributesTable = FactoryLocator::get('Table')->get('ProductAttributes');
        $productAttributes = $productAttributesTable->find('all',
            conditions: [
                'ProductAttributes.id_product' => $productId,
            ]
        );

        $productAttributeIds = [];
        foreach ($productAttributes as $attribute) {
            $productAttributeIds[] = $attribute->id_product_attribute;
        }

        // first set all associated attributes to 0
        $productAttributesTable->updateAll([
            'default_on' => 0
        ], [
            'id_product_attribute IN (' . join(', ', $productAttributeIds) . ')',
        ]);

        // then set the new one
        $productAttributeEntity = $productAttributes->where([
            'ProductAttributes.id_product_attribute' => $productAttributeId,
        ])->first();
        $productAttributeEntity->default_on = APP_ON;
        $productAttributesTable->save($productAttributeEntity);
    }

    private function checkImageContentType($image)
    {
        $mimeContentType = mime_content_type($image);
        if (!in_array($mimeContentType, Configure::read('app.allowedImageMimeTypes'))) {
            throw new \Exception('file is not an image: ' . $image);
        }
    }

    public function changeImage($products)
    {

        foreach ($products as $product) {
            $productId = key($product);
            $imageFromRemoteServer = $product[$productId];
            $imageFromRemoteServer = Configure::read('app.htmlHelper')->removeTimestampFromFile($imageFromRemoteServer);
            if ($imageFromRemoteServer == 'no-image') {
                continue;
            }

            if (filter_var($imageFromRemoteServer, FILTER_VALIDATE_URL)) {
                $syncDomainsTable = FactoryLocator::get('Table')->get('Network.SyncDomains');
                $syncDomainHosts = $syncDomainsTable->getActiveSyncDomainHosts();
                if (!RemoteFileService::exists($imageFromRemoteServer, $syncDomainHosts)) {
                    throw new \Exception('remote image not existing: ' . $imageFromRemoteServer);
                }
                $tmpLocalImagePath = TMP . 'tmp-image';
                copy($imageFromRemoteServer, $tmpLocalImagePath);
                $this->checkImageContentType($tmpLocalImagePath);
                unset($tmpLocalImagePath);
            } else {
                $remoteImage = file_exists($imageFromRemoteServer);
                if (!$remoteImage) {
                    throw new \Exception('local image not existing: ' . $imageFromRemoteServer);
                }
                $this->checkImageContentType($imageFromRemoteServer);
            }

        }

        $success = false;
        foreach ($products as $product) {

            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);

            if ($ids['attributeId'] > 0) {
                continue;
            }

            $imageFromRemoteServer = $product[$productId];
            $imageFromRemoteServer = Configure::read('app.htmlHelper')->removeTimestampFromFile($imageFromRemoteServer);
            $extension = strtolower(pathinfo($imageFromRemoteServer, PATHINFO_EXTENSION));

            $product = $this->find('all',
            conditions: [
                'Products.id_product' => $ids['productId']
            ],
            contain: [
                'Images'
            ])->first();

            $imagesTable = FactoryLocator::get('Table')->get('Images');
            if (empty($product->image)) {
                // product does not yet have image => create the necessary record
                $image = $imagesTable->save(
                    $imagesTable->newEntity(
                        ['id_product' => $ids['productId']]
                    )
                );
            } else {
                $image = $product->image;
            }

            $imageIdAsPath = Configure::read('app.htmlHelper')->getProductImageIdAsPath($image->id_image);
            $thumbsPath = Configure::read('app.htmlHelper')->getProductThumbsPath($imageIdAsPath);

            if ($imageFromRemoteServer != 'no-image') {

                FolderService::nonRecursivelyRemoveAllFiles($thumbsPath);
                if (!file_exists($thumbsPath)) {
                    mkdir($thumbsPath, 0755, true);
                }
                foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {
                    $thumbsFileName = $thumbsPath . DS . $image->id_image . $options['suffix'] . '.' . $extension;
                    $remoteFileName = preg_replace('/-home_default/', $options['suffix'], $imageFromRemoteServer);
                    copy($remoteFileName, $thumbsFileName);
                }

            } else {

                // delete db records
                $imagesTable->deleteAll([
                    'Images.id_image' => $image->id_image
                ]);

                FolderService::nonRecursivelyRemoveAllFiles($thumbsPath);

            }
        }

        return $success;
    }

    public function add($manufacturer, $productName, $descriptionShort, $description, $unity, $isDeclarationOk, $idStorageLocation, $barcode)
    {
        $defaultQuantity = 0;

        $this->Manufacturer = FactoryLocator::get('Table')->get('Manufacturers');

        $productEntity = $this->newEntity(
            [
                'id_manufacturer' => $manufacturer->id_manufacturer,
                'id_tax' => $this->Manufacturer->getOptionDefaultTaxId($manufacturer->default_tax_id),
                'name' => $productName,
                'delivery_rhythm_send_order_list_weekday' => (new DeliveryRhythmService())->getSendOrderListsWeekday(),
                'description_short' => $descriptionShort,
                'description' => $description,
                'unity' => $unity,
                'is_declaration_ok' => $isDeclarationOk,
                'id_storage_location' => $idStorageLocation,
            ],
            [
                'validate' => 'name',
            ]
        );

        if ($productEntity->hasErrors()) {
            return $productEntity;
        }

        $barcodeProductsTable = FactoryLocator::get('Table')->get('BarcodeProducts');
        if ($barcode != '') {
            $barcodeEntity2Save = $barcodeProductsTable->newEntity([
                'barcode' => $barcode,
            ], ['validate' => true]);
            if ($barcodeEntity2Save->hasErrors()) {
                return $barcodeEntity2Save;
            }
        }

        $newProduct = $this->save($productEntity);
        $newProductId = $newProduct->id_product;

        if ($barcode != '' && isset($barcodeEntity2Save)) {
            $barcodeEntity2Save->product_id = $newProductId;
            $barcodeProductsTable->save($barcodeEntity2Save);
        }

        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $purchasePriceProductsTable = FactoryLocator::get('Table')->get('PurchasePriceProducts');
            $entity2Save = $purchasePriceProductsTable->getEntityToSaveByProductId($newProductId);
            $patchedEntity = $purchasePriceProductsTable->patchEntity(
                $entity2Save,
                [
                    'tax_id' => $this->Manufacturer->getOptionDefaultTaxId($manufacturer->default_tax_id_purchase_price),
                ],
            );
            $purchasePriceProductsTable->save($patchedEntity);
        }

        // INSERT CATEGORY_PRODUCTS
        $categoryProductsTable = FactoryLocator::get('Table')->get('CategoryProducts');
        $categoryProductsTable->save(
            $categoryProductsTable->newEntity(
                [
                    'id_category' => Configure::read('app.categoryAllProducts'),
                    'id_product' => $newProductId,
                ]
            )
        );

        // INSERT STOCK AVAILABLE
        $stockAvailablesTable = FactoryLocator::get('Table')->get('StockAvailables');
        $stockAvailablesTable->save(
            $stockAvailablesTable->newEntity(
                [
                    'id_product' => $newProductId,
                    'quantity' => $defaultQuantity
                ]
            )
        );

        $newProduct = $this->find('all', conditions: [
            'Products.id_product' => $newProductId
        ])->first();

        return $productEntity;
    }
}
