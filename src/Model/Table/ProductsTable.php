<?php

namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use App\Lib\Error\Exception\InvalidParameterException;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Datasource\FactoryLocator;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Cake\I18n\I18n;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductsTable extends AppTable
{

    public const ALLOWED_TAGS_DESCRIPTION_SHORT = '<p><b><strong><i><em><br>';
    public const ALLOWED_TAGS_DESCRIPTION       = '<p><b><strong><i><em><br><img>';

    public function initialize(array $config): void
    {
        $this->setTable('product');
        parent::initialize($config);
        $this->setPrimaryKey('id_product');
        $this->belongsTo('Manufacturers', [
            'foreignKey' => 'id_manufacturer'
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
        $validator = $this->getLastOrFirstDayOfMonthValidator($validator, 'delivery_rhythm_first_delivery_day', 'first');
        $validator = $this->getLastOrFirstDayOfMonthValidator($validator, 'delivery_rhythm_first_delivery_day', 'last');
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

    private function getLastOrFirstDayOfMonthValidator(Validator $validator, $field, $firstOrLast)
    {
        $checkedCountValue = 0;
        $deliveryWeekdayName = Configure::read('app.timeHelper')->getWeekdayName(Configure::read('app.timeHelper')->getDeliveryWeekday());
        $message = __('The_first_delivery_day_needs_to_be_a_last_{0}_of_the_month.', [$deliveryWeekdayName]);
        if ($firstOrLast == 'first') {
            $checkedCountValue = 1;
            $message = __('The_first_delivery_day_needs_to_be_a_first_{0}_of_the_month.', [$deliveryWeekdayName]);
        }
        $validator->add($field, 'allow-only-' . $firstOrLast . '-weekday-of-month', [
            'rule' => function ($value, $context) use ($checkedCountValue, $firstOrLast) {
                if ($context['data']['delivery_rhythm_type'] == 'month' && $context['data']['delivery_rhythm_count'] == $checkedCountValue) {
                    $originalLocale = I18n::getLocale();
                    I18n::setLocale('en_US');
                    $deliveryDayAsWeekdayInEnglish = strtolower(Configure::read('app.timeHelper')->getWeekdayName(Configure::read('app.timeHelper')->getDeliveryWeekday()));
                    I18n::setLocale($originalLocale);
                    $firstDayOfMonth = Configure::read('app.timeHelper')->formatToDbFormatDate($value . ' ' . $firstOrLast . ' ' . $deliveryDayAsWeekdayInEnglish . ' of this month');
                    if ($firstDayOfMonth != $value) {
                        return false;
                    }
                }
                return true;
            },
            'message' => $message
        ]);
        return $validator;
    }

    public function deliveryBreakEnabled($noDeliveryDaysAsString, $deliveryDate)
    {
        return $noDeliveryDaysAsString != '' && preg_match('`' . $deliveryDate . '`', $noDeliveryDaysAsString);
    }

    public function calculatePickupDayRespectingDeliveryRhythm($product, $currentDay=null)
    {

        if (is_null($currentDay)) {
            $currentDay = Configure::read('app.timeHelper')->getCurrentDateForDatabase();
        }

        $sendOrderListsWeekday = null;
        if (!is_null($product->delivery_rhythm_send_order_list_weekday)) {
            $sendOrderListsWeekday = $product->delivery_rhythm_send_order_list_weekday;
        }

        $pickupDay = Configure::read('app.timeHelper')->getDbFormattedPickupDayByDbFormattedDate($currentDay, $sendOrderListsWeekday);

        // assure that $product->is_stock_product also contains check for $product->manufacturer->stock_management_enabled
        if ($product->is_stock_product) {
            return $pickupDay;
        }

        if ($product->delivery_rhythm_type == 'week') {
            if (!is_null($product->delivery_rhythm_first_delivery_day)) {
                $calculatedPickupDay = $product->delivery_rhythm_first_delivery_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
                while($calculatedPickupDay < $pickupDay) {
                    $calculatedPickupDay = strtotime($calculatedPickupDay . '+' . $product->delivery_rhythm_count . ' week');
                    $calculatedPickupDay = date(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), $calculatedPickupDay);
                }
                $pickupDay = $calculatedPickupDay;
            }
        }

        if ($product->delivery_rhythm_type == 'month') {
            switch($product->delivery_rhythm_count) {
                case '1':
                    $ordinal = 'first';
                    break;
                case '2':
                    $ordinal = 'second';
                    break;
                case '3':
                    $ordinal = 'third';
                    break;
                case '4':
                    $ordinal = 'fourth';
                    break;
                case '0':
                    $ordinal = 'last';
                    break;
            }
            $deliveryDayAsWeekdayInEnglish = strtolower(date('l', strtotime($pickupDay)));
            $nthDeliveryDayOfThisMonth = date(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), strtotime($currentDay . ' ' . $ordinal . ' ' . $deliveryDayAsWeekdayInEnglish . ' of this month'));

            while($nthDeliveryDayOfThisMonth < $pickupDay) {
                $nthDeliveryDayOfThisMonth = date(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), strtotime($nthDeliveryDayOfThisMonth . ' ' . $ordinal . ' ' . $deliveryDayAsWeekdayInEnglish . ' of next month'));
            }
            $pickupDay = $nthDeliveryDayOfThisMonth;
        }

        if ($product->delivery_rhythm_type == 'individual') {
            $pickupDay = $product->delivery_rhythm_first_delivery_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
        }

        return $pickupDay;

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
            $allowed = [APP_OFF, APP_ON];
            if (!in_array($status, $allowed, true)) { // last param for type check
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
            $deposit = Configure::read('app.numberHelper')->getStringAsFloat($product[$productId]);
            if ($deposit < 0) {
                throw new InvalidParameterException('input format not correct: '.$product[$productId]);
            }
        }

        $success = false;
        foreach ($products as $product) {
            $productId = key($product);
            $deposit = Configure::read('app.numberHelper')->getStringAsFloat($product[$productId]);

            $ids = $this->getProductIdAndAttributeId($productId);

            if ($ids['attributeId'] > 0) {
                $oldDeposit = $this->DepositProducts->find('all', [
                    'conditions' => [
                        'id_product_attribute' => $ids['attributeId']
                    ]
                ])->first();

                if (empty($oldDeposit)) {
                    $entity = $this->DepositProducts->newEntity([]);
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
                    $entity = $this->DepositProducts->newEntity([]);
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
     *              [
     *                  productId] => [
     *                      'gross_price' => (float) price
     *                      'product unit fields'
     *                  ]
     *          )
     *  )
     * @return boolean $success
     */
    public function changePrice($products)
    {

        foreach ($products as $product) {
            $productId = key($product);
            $price = Configure::read('app.numberHelper')->getStringAsFloat($product[$productId]['gross_price']);
            if ($price < 0) {
                throw new InvalidParameterException('input format not correct: '.$product[$productId]['gross_price']);
            }
        }

        $success = false;
        foreach ($products as $product) {

            $productId = key($product);
            $price = Configure::read('app.numberHelper')->getStringAsFloat($product[$productId]['gross_price']);

            $ids = $this->getProductIdAndAttributeId($productId);

            $netPrice = $this->getNetPrice($ids['productId'], $price);

            if ($ids['attributeId'] > 0) {
                // update attribute - updateAll needed for multi conditions of update
                $result = $this->ProductAttributes->updateAll([
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

            if (isset($product[$productId]['unit_product_price_per_unit_enabled'])) {
                $this->Unit = FactoryLocator::get('Table')->get('Units');
                $priceInclPerUnit = Configure::read('app.numberHelper')->getStringAsFloat($product[$productId]['unit_product_price_incl_per_unit']);
                $quantityInUnits = Configure::read('app.numberHelper')->getStringAsFloat($product[$productId]['unit_product_quantity_in_units']);
                $this->Unit->saveUnits(
                    $ids['productId'],
                    $ids['attributeId'],
                    $product[$productId]['unit_product_price_per_unit_enabled'],
                    $priceInclPerUnit == -1 ? 0 : $priceInclPerUnit,
                    $product[$productId]['unit_product_name'],
                    $product[$productId]['unit_product_amount'],
                    $quantityInUnits == -1 ? 0 : $quantityInUnits
                );
            }
        }

        $success = (boolean) $success;
        return $success;
    }

    /**
     * @param array $products
     *  Array
     *  (
     *      [0] => Array
     *          (
     *              [productId] => [
     *                  'quantity' => (int) quantity
     *              ]
     *          )
     *  )
     * @return boolean $success
     */
    public function changeQuantity($products)
    {

        foreach ($products as $product) {
            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);
            $entity = $this->StockAvailables->newEntity($product[$productId]);
            if ($entity->hasErrors()) {
                throw new InvalidParameterException(join(' ', $this->StockAvailables->getAllValidationErrors($entity)));
            }
        }

        foreach ($products as $product) {
            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                // update attribute - updateAll needed for multi conditions of update
                $this->ProductAttributes->StockAvailables->updateAll($product[$productId], [
                    'id_product_attribute' => $ids['attributeId'],
                    'id_product' => $ids['productId']
                ]);
                $this->StockAvailables->updateQuantityForMainProduct($ids['productId']);
            } else {
                $entity = $this->StockAvailables->get($ids['productId']);
                $this->StockAvailables->save(
                    $this->StockAvailables->patchEntity($entity, $product[$productId])
                );
            }
        }
    }

    /**
     * @param array $products
     *  Array
     *  (
     *      [0] => Array
     *          (
     *              [productId] => (int) delivery_rhythm
     *          )
     *  )
     * @return boolean $success
     */
    public function changeDeliveryRhythm($products)
    {

        $products2save = [];

        foreach ($products as $product) {
            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                throw new InvalidParameterException('change delivery_rhythm is not allowed for product attributes');
            }
            $entity = $this->newEntity(
                $product[$productId],
                [
                    'validate' => 'deliveryRhythm'
                ]
            );
            if ($entity->hasErrors()) {
                throw new InvalidParameterException(join(' ', $this->getAllValidationErrors($entity)));
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
     * @param array $products
     *  Array
     *  (
     *      [0] => Array
     *          (
     *              [productId] => (int) is_stock_product
     *          )
     *  )
     * @return boolean $success
     */
    public function changeIsStockProduct($products)
    {

        $products2save = [];
        foreach ($products as $product) {
            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                throw new InvalidParameterException('change is_stock_product is not allowed for product attributes');
            }
            $isStockProduct = (int) $product[$ids['productId']];
            $allowed = [APP_OFF, APP_ON];
            if (!in_array($isStockProduct, $allowed, true)) { // last param for type check
                throw new InvalidParameterException('Products.is_stock_product for product ' .$ids['productId'] . ' needs to be ' .APP_OFF . ' or ' . APP_ON.'; was: ' . $isStockProduct);
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
     * @param array $products
     *  Array
     *  (
     *      [0] => Array
     *          (
     *              [productId] => Array
     *                  (
     *                      [name] => Brokkoli-1
     *                      [description] => grünes Gemüse: Strunk mit Röschen auch angeschwollenen Knospen-1
     *                      [description_short] => kbA, vom Gemüsehof Wild-Obermayr-1
     *                      [unity] => ca. 0,4 kg-1
     *                      [is_declaration_ok] => 1
     *                  )
     *          )
     *  )
     * @return boolean $success
     */
    public function changeName($products)
    {

        $products2save = [];

        foreach ($products as $product) {
            $productId = key($product);
            $name = $product[$productId];
            $ids = $this->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                throw new InvalidParameterException('change name is not allowed for product attributes');
            }
            $newName = StringComponent::removeSpecialChars(strip_tags(trim($name['name'])));

            $productEntity = $this->newEntity(
                [
                    'name' => $newName
                ],
                [
                    'validate' => 'name'
                ]
            );
            if ($productEntity->hasErrors()) {
                throw new InvalidParameterException(join(' ', $this->getAllValidationErrors($productEntity)));
            } else {
                $tmpProduct2Save = [
                    'id_product' => $ids['productId'],
                    'name' => StringComponent::removeSpecialChars(strip_tags(trim($name['name']))),
                    'description_short' => StringComponent::prepareWysiwygEditorHtml($name['description_short'], self::ALLOWED_TAGS_DESCRIPTION_SHORT),
                    'description' => StringComponent::prepareWysiwygEditorHtml($name['description'], self::ALLOWED_TAGS_DESCRIPTION),
                    'unity' => StringComponent::removeSpecialChars(strip_tags(trim($name['unity'])))
                ];
                if (isset($name['is_declaration_ok'])) {
                    $tmpProduct2Save['is_declaration_ok'] = $name['is_declaration_ok'];
                }
                $products2save[] = $tmpProduct2Save;
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
    public function getProductsForBackend($appAuth, $productIds, $manufacturerId, $active, $categoryId = '', $isQuantityMinFilterSet = 0, $isPriceZero = 0, $addProductNameToAttributes = false, $controller = null)
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

        if ($isQuantityMinFilterSet != '') {
            $conditions[] = $this->getIsQuantityMinFilterSetCondition();
        }

        if ($isPriceZero != '') {
            $conditions[] = $this->getIsPriceZeroCondition();
        }

        $quantityIsZeroFilterOn = false;
        $priceIsZeroFilterOn = false;
        foreach ($conditions as $condition) {
            if (!is_array($condition) && preg_match('/'.$this->getIsQuantityMinFilterSetCondition().'/', $condition)) {
                $this->getAssociation('ProductAttributes')->setConditions(
                    [
                        'StockAvailables.quantity < 3'
                    ]
                );
                $quantityIsZeroFilterOn = true;
            }
            if (!is_array($condition) && preg_match('/'.$this->getIsPriceZeroCondition().'/', $condition)) {
                $this->ProductAttributes->setConditions(
                    [
                        'ProductAttributes.price' => 0
                    ]
                );
                $priceIsZeroFilterOn = true;
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

        $order = [
            'Products.active' => 'DESC',
            'Products.name' => 'ASC'
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
        ->select($this->DepositProducts)
        ->select('Images.id_image')
        ->select($this->Taxes)
        ->select($this->Manufacturers)
        ->select($this->UnitProducts)
        ->select($this->StockAvailables);

        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $query->select(['bar_code' => $this->getProductIdentifierField()]);
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

            $product->gross_price = $this->getGrossPrice($product->id_product, $product->price);

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
                    $product->image->src = Configure::read('app.cakeServerName') . $imageSrc;
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
            if (!empty($product->unit_product)) {

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
            $product->name = '<span class="product-name">' . $product->name . '</span>';
            if (!empty($additionalProductNameInfos)) {
                $product->name = $product->name . ': ' . join(', ', $additionalProductNameInfos);
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
                    if (($quantityIsZeroFilterOn && empty($attribute->stock_available)) || ($priceIsZeroFilterOn && empty($attribute))) {
                        $preparedProducts[$currentPreparedProduct]['AttributesRemoved'] ++;
                        continue;
                    }

                    $grossPrice = 0;
                    if (! empty($attribute->price)) {
                        $grossPrice = $this->getGrossPrice($product->id_product, $attribute->price);
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
                        'image' => null
                    ];

                    if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
                        $preparedProduct['bar_code'] = $product->bar_code . Configure::read('app.numberHelper')->addLeadingZerosToNumber($attribute->id_product_attribute, 4);
                        $preparedProduct['image'] = $product->image;
                        if (!empty($attribute->unit_product_attribute) && $attribute->unit_product_attribute->price_per_unit_enabled) {
                            $preparedProduct['nameForBarcodePdf'] = $product->name . ': ' . $productName;
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
                'Manufacturers',
            ],
            'order' => [
                'Products.active' => 'DESC',
                'Products.name' => 'ASC'
            ]
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

        // if old tax was 0, $oldTaxRate === null (tax 0 has no record in table tax) and would reset the price to 0
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
            $grossPrice = round($netPrice, 2);
        } else {
            $grossPrice = $rate[0]['gross_price'];
        }

        return $grossPrice;
    }

    public function getNetPrice($productId, $grossPrice)
    {
        $grossPrice = Configure::read('app.numberHelper')->parseFloatRespectingLocale($grossPrice);

        if (!$grossPrice > -1) { // allow 0 as new price
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
        return '(StockAvailables.quantity < 3 && (StockAvailables.always_available = 0 || (Products.is_stock_product = 1 && Manufacturers.stock_management_enabled = 1)))';
    }

    private function getIsPriceZeroCondition()
    {
        return 'Products.price = 0';
    }

    public function setDefaultAttributeId($productId, $productAttributeId)
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
        $this->ProductAttributes->updateAll([
            'default_on' => 0
        ], [
            'id_product_attribute IN (' . join(', ', $productAttributeIds) . ')',
        ]);

        // then set the new one
        $this->ProductAttributes->updateAll([
            'default_on' => 1
        ], [
            'id_product_attribute' => $productAttributeId,
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

    public function changeImage($products)
    {

        foreach ($products as $product) {
            $productId = key($product);
            $imageFromRemoteServer = $product[$productId];
            $imageFromRemoteServer = Configure::read('app.htmlHelper')->removeTimestampFromFile($imageFromRemoteServer);
            if ($imageFromRemoteServer == 'no-image') {
                continue;
            }
            $remoteFile = file_exists($imageFromRemoteServer) && file_get_contents($imageFromRemoteServer);
            if (!$remoteFile) {
                throw new InvalidParameterException('image not found: ' . $imageFromRemoteServer);
            }
            $imageSize = getimagesize($imageFromRemoteServer);
            if ($imageSize === false) {
                throw new InvalidParameterException('file is not not an image: ' . $imageFromRemoteServer);
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

            $product = $this->find('all', [
                'conditions' => [
                    'Products.id_product' => $ids['productId']
                ],
                'contain' => [
                    'Images'
                ]
            ])->first();

            if (empty($product->image)) {
                // product does not yet have image => create the necessary record
                $image = $this->Images->save(
                    $this->Images->newEntity(
                        ['id_product' => $ids['productId']]
                    )
                );
            } else {
                $image = $product->image;
            }

            $imageIdAsPath = Configure::read('app.htmlHelper')->getProductImageIdAsPath($image->id_image);
            $thumbsPath = Configure::read('app.htmlHelper')->getProductThumbsPath($imageIdAsPath);

            if ($imageFromRemoteServer != 'no-image') {

                // recursively create path
                $dir = new Folder();
                $dir->create($thumbsPath);
                $dir->chmod($thumbsPath, 0755);

                foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {
                    $thumbsFileName = $thumbsPath . DS . $image->id_image . $options['suffix'] . '.' . 'jpg';
                    $remoteFileName = preg_replace('/-home_default/', $options['suffix'], $imageFromRemoteServer);
                    copy($remoteFileName, $thumbsFileName);
                }

            } else {

                // delete db records
                $this->Images->deleteAll([
                    'Images.id_image' => $image->id_image
                ]);

                // delete physical files
                foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {
                    $thumbsFileName = $thumbsPath . DS . $image->id_image . $options['suffix'] . '.' . 'jpg';
                    if (file_exists($thumbsFileName)) {
                        unlink($thumbsFileName);
                    }
                }

            }
        }

        return $success;
    }

    public function add($manufacturer, $productName, $descriptionShort, $description, $unity, $isDeclarationOk)
    {
        $defaultQuantity = 0;

        $this->Manufacturer = FactoryLocator::get('Table')->get('Manufacturers');

        // INSERT PRODUCT
        $productEntity = $this->newEntity(
            [
                'id_manufacturer' => $manufacturer->id_manufacturer,
                'id_tax' => $this->Manufacturer->getOptionDefaultTaxId($manufacturer->default_tax_id),
                'name' => StringComponent::removeSpecialChars(strip_tags(trim($productName))),
                'delivery_rhythm_send_order_list_weekday' => Configure::read('app.timeHelper')->getSendOrderListsWeekday(),
                'description_short' => StringComponent::prepareWysiwygEditorHtml($descriptionShort, self::ALLOWED_TAGS_DESCRIPTION_SHORT),
                'description' => StringComponent::prepareWysiwygEditorHtml($description, self::ALLOWED_TAGS_DESCRIPTION),
                'unity' => StringComponent::removeSpecialChars(strip_tags(trim($unity))),
                'is_declaration_ok' => $isDeclarationOk,
            ],
            [
                'validate' => 'name'
            ]
        );

        if ($productEntity->hasErrors()) {
            return $productEntity;
        }

        $newProduct = $this->save($productEntity);
        $newProductId = $newProduct->id_product;

        // INSERT CATEGORY_PRODUCTS
        $this->CategoryProducts->save(
            $this->CategoryProducts->newEntity(
                [
                    'id_category' => Configure::read('app.categoryAllProducts'),
                    'id_product' => $newProductId
                ]
            )
        );

        // INSERT STOCK AVAILABLE
        $this->StockAvailables->save(
            $this->StockAvailables->newEntity(
                [
                    'id_product' => $newProductId,
                    'quantity' => $defaultQuantity
                ]
            )
        );

        $newProduct = $this->find('all', [
            'conditions' => [
                'Products.id_product' => $newProductId
            ]
        ])->first();

        return $productEntity;
    }
}
