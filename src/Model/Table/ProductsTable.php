<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use App\Services\FolderService;
use Cake\Validation\Validator;
use App\Services\DeliveryRhythmService;
use App\Services\RemoteFileService;
use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;
use App\Model\Traits\AllowOnlyOneWeekdayValidatorTrait;
use App\Model\Traits\ProductImportTrait;
use App\Model\Entity\Product;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;
use Cake\I18n\Date;
use stdClass;
use App\Model\Entity\Manufacturer;
use App\Services\CalculationService;

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

    public function validationName(Validator $validator): Validator
    {
        $validator->notEmptyString('name', __('Please_enter_a_name.'));
        $validator->minLength('name', 2, __('The_name_of_the_product_needs_to_be_at_least_{0}_characters_long.', [2]));
        return $validator;
    }

    public function validationDeliveryRhythm(Validator $validator): Validator
    {
        $validator->add('delivery_rhythm_type', 'allowed-count-values', [
            'rule' => function ($value, $context) {
                if ($value == 'week') {
                    return in_array($context['data']['delivery_rhythm_count'], [1,2,3,4]);
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

    private function getCorrectDayOfMonthValidator(Validator $validator, string $field): Validator
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

    /**
     * @return array{productId: int, attributeId: int}
     */
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

    public function getCompositeProductIdAndAttributeId(int $productId, int $attributeId = 0): string|int
    {
        $compositeId = $productId;
        if ($attributeId > 0) {
            $compositeId .= '-'.$attributeId;
        }
        return $compositeId;
    }

    /**
     * @param array<int, array<int|string, mixed>> $products
     */
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

    /**
     * @param array<int, array<int|string, mixed>> $products
     */
    public function changeNewStatus(array $products): bool
    {

        $products2save = [];

        foreach ($products as $product) {
            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                throw new \Exception('change new status is not allowed for product attributes');
            }
            $status = $product[$ids['productId']];
            if (!in_array($status, [APP_OFF, APP_ON], true)) { // last param for type check
                throw new \Exception('Products.new for product ' .$ids['productId'] . ' needs to be ' .APP_OFF . ' or ' . APP_ON.'; was: ' . $status);
            } else {
                $newDate = Date::now();
                if ($status == APP_OFF) {
                    $newDate = Date::now()->subDays((int) Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW') + 1);
                }
                $products2save[] = [
                    'id_product' => $ids['productId'],
                    'new' => $newDate,
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
     * @param array<int, array<int|string, mixed>> $products
     */
    public function changeDeposit(array $products): bool
    {

        foreach ($products as $product) {
            $productId = key($product);
            $deposit = $product[$productId];
            if (is_string($deposit)) {
                $deposit = Configure::read('app.numberHelper')->parseFloatRespectingLocale($product[$productId]);
            }
            if ($deposit === false) {
                throw new \Exception('input format not correct: '.$product[$productId]);
            }
        }

        $success = false;
        foreach ($products as $product) {

            $productId = key($product);

            $deposit = $product[$productId];
            if (is_string($deposit)) {
                $deposit = Configure::read('app.numberHelper')->parseFloatRespectingLocale($product[$productId]);
            }

            $ids = $this->getProductIdAndAttributeId($productId);

            $depositProductsTable = TableRegistry::getTableLocator()->get('DepositProducts');
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
                    'deposit' => $deposit,
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
                    'deposit' => $deposit,
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

    /**
     * @param array<int, array<int|string, mixed>> $products
     */
    public function changePrice(array $products): bool
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
        $productAttributesTable = TableRegistry::getTableLocator()->get('ProductAttributes');
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

            $netPrice = $this->getNetPrice($price, $productEntity->tax_rate);

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

                $priceInclPerUnit = $product[$productId]['unit_product_price_incl_per_unit'];
                if (is_string($priceInclPerUnit)) {
                    $priceInclPerUnit = Configure::read('app.numberHelper')->getStringAsFloat($priceInclPerUnit);
                }
                $quantityInUnits = $product[$productId]['unit_product_quantity_in_units'];
                if (is_string($quantityInUnits)) {
                    $quantityInUnits = Configure::read('app.numberHelper')->getStringAsFloat($quantityInUnits);
                }

                $unitsTable = TableRegistry::getTableLocator()->get('Units');
                $unitsTable->saveUnits(
                    $ids['productId'],
                    $ids['attributeId'],
                    $product[$productId]['unit_product_price_per_unit_enabled'],
                    $priceInclPerUnit == -1 ? 0 : $priceInclPerUnit,
                    $product[$productId]['unit_product_name'],
                    $product[$productId]['unit_product_amount'],
                    $quantityInUnits == -1 ? 0 : $quantityInUnits,
                    $product[$productId]['use_weight_as_amount'] ?? 0,
                );
            }

        }

        return (bool) $success;

    }

    /**
     * @param array<int, array<int|string, mixed>> $products
     */
    public function changeQuantity(array $products): void
    {

        $stockAvailablesTable = TableRegistry::getTableLocator()->get('StockAvailables');

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

    /**
     * @param array<int, array<int|string, mixed>> $products
     */
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

    /**
     * @param array<int, array<int|string, mixed>> $products
     */
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

    /**
     * @param array<int, array<int|string, mixed>> $products
     */
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
                $barcodeProductsTable = TableRegistry::getTableLocator()->get('BarcodeProducts');
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

    public function isMainProduct(stdClass $product): bool
    {
        return (bool) preg_match('/main-product/', $product->row_class);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getForDropdown(int $manufacturerId): array
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
            $productNameForDropdown = $product->name . (!empty($product->manufacturer) ? ' - ' . $product->manufacturer->decoded_name : '');
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

    public function getUnitTax(string|float $grossPrice, string|float $netPrice, float $quantity): float
    {
        if ($quantity == 0) {
            return 0;
        }
        return round(($grossPrice - ($netPrice * $quantity)) / $quantity, 2);
    }

    public function getGrossPrice(string|float|null $netPrice, string|float $taxRate): float
    {
        return CalculationService::getGrossPrice((float) $netPrice, (float) $taxRate);
    }

    public function getNetPrice(string|float|null|false $grossPrice, string|float $taxRate): float
    {
        $netPrice = $grossPrice / (100 + $taxRate) * 100;
        $netPrice = round($netPrice, 6);
        return $netPrice;
    }

    public function getNetPriceForNewTaxRate(string|float|null $netPrice, string|float $oldTaxRate, string|float $newTaxRate): float
    {
        $netPrice = $netPrice / ((100 + $newTaxRate) / 100) * (1 + $oldTaxRate / 100);
        $netPrice = round($netPrice, 6);
        return $netPrice;
    }

    public function setDefaultAttributeId(int $productId, int $productAttributeId): void
    {
        $productAttributesTable = TableRegistry::getTableLocator()->get('ProductAttributes');
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
            'default_on' => 0,
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

    private function checkImageContentType(string $image): void
    {
        $mimeContentType = mime_content_type($image);
        if (!in_array($mimeContentType, Configure::read('app.allowedImageMimeTypes'))) {
            throw new \Exception('file is not an image: ' . $image);
        }
    }

    /**
     * @param array<int, array<int|string, mixed>> $products
     */
    public function changeImage(array $products): bool
    {

        foreach ($products as $product) {
            $productId = key($product);
            $imageFromRemoteServer = $product[$productId];
            $imageFromRemoteServer = Configure::read('app.htmlHelper')->removeTimestampFromFile($imageFromRemoteServer);
            if ($imageFromRemoteServer == 'no-image') {
                continue;
            }

            if (filter_var($imageFromRemoteServer, FILTER_VALIDATE_URL)) {
                $syncDomainsTable = TableRegistry::getTableLocator()->get('Network.SyncDomains');
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

            $imagesTable = TableRegistry::getTableLocator()->get('Images');
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
                $imagesTable->delete($image);
                FolderService::nonRecursivelyRemoveAllFiles($thumbsPath);
            }
        }

        return $success;
    }

    public function add(
        Manufacturer $manufacturer,
        string $productName,
        string $descriptionShort,
        string  $description,
        string $unity,
        int|string $isDeclarationOk,
        int|string $idStorageLocation,
        string $barcode,
        ): object
    {
        $defaultQuantity = 0;

        $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');

        $productEntity = $this->newEntity(
            [
                'id_manufacturer' => $manufacturer->id_manufacturer,
                'id_tax' => $manufacturersTable->getOptionDefaultTaxId($manufacturer->default_tax_id),
                'name' => $productName,
                'delivery_rhythm_send_order_list_weekday' => (new DeliveryRhythmService())->getSendOrderListsWeekday(),
                'description_short' => $descriptionShort,
                'description' => $description,
                'unity' => $unity,
                'is_declaration_ok' => $isDeclarationOk,
                'id_storage_location' => $idStorageLocation,
                'new' => Date::now(),
            ],
            [
                'validate' => 'name',
            ]
        );

        if ($productEntity->hasErrors()) {
            return $productEntity;
        }

        $barcodeProductsTable = TableRegistry::getTableLocator()->get('BarcodeProducts');
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

        if ($barcode != '') {
            $barcodeEntity2Save->product_id = $newProductId;
            $barcodeProductsTable->save($barcodeEntity2Save);
        }

        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $purchasePriceProductsTable = TableRegistry::getTableLocator()->get('PurchasePriceProducts');
            $entity2Save = $purchasePriceProductsTable->getEntityToSaveByProductId($newProductId);
            $patchedEntity = $purchasePriceProductsTable->patchEntity(
                $entity2Save,
                [
                    'tax_id' => $manufacturersTable->getOptionDefaultTaxId($manufacturer->default_tax_id_purchase_price),
                ],
            );
            $purchasePriceProductsTable->save($patchedEntity);
        }

        // INSERT CATEGORY_PRODUCTS
        $categoryProductsTable = TableRegistry::getTableLocator()->get('CategoryProducts');
        $categoryProductsTable->save(
            $categoryProductsTable->newEntity(
                [
                    'id_category' => Configure::read('app.categoryAllProducts'),
                    'id_product' => $newProductId,
                ]
            )
        );

        // INSERT STOCK AVAILABLE
        $stockAvailablesTable = TableRegistry::getTableLocator()->get('StockAvailables');
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
