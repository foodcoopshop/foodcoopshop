<?php

namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\Validation\Validator;

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
class CartsTable extends AppTable
{

    public const CART_TYPE_WEEKLY_RHYTHM = 1;
    public const CART_TYPE_INSTANT_ORDER = 2;
    public const CART_TYPE_SELF_SERVICE  = 3;

    public const CART_SELF_SERVICE_PAYMENT_TYPE_CASH   = 1;
    public const CART_SELF_SERVICE_PAYMENT_TYPE_CREDIT = 2;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_cart');
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
        $this->hasMany('CartProducts', [
            'foreignKey' => 'id_cart',
            'conditions' => [
                'CartProducts.amount > 0',
            ],
        ]);
        $this->hasMany('PickupDayEntities', [
            'className' => 'PickupDays', // field has same name and would clash
            'foreignKey' => [
                'id_customer'
            ]
        ]);
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->equals('cancellation_terms_accepted', 1, __('Please_accept_the_information_about_right_of_withdrawal.'));
        $validator->equals('general_terms_and_conditions_accepted', 1, __('Please_accept_the_general_terms_and_conditions.'));
        $validator->equals('promise_to_pickup_products', 1, __('Please_promise_to_pick_up_the_ordered_products.'));
        $validator->notEmptyArray('self_service_payment_type', __('Please_select_your_payment_type.'));
        return $validator;
    }

    public function validationCustomerCanSelectPickupDay(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);
        $validator->requirePresence('pickup_day', true, __('Please_select_a_pickup_day.'));
        $validator->notEmptyDate('pickup_day', __('Please_select_a_pickup_day.'));
        $validator = $this->getAllowOnlyDefinedPickupDaysValidator($validator, 'pickup_day');
        return $validator;
    }

    public function getAllowOnlyDefinedPickupDaysValidator(Validator $validator, $field)
    {
        $validator->add($field, 'allow-only-defined-pickup-days', [
            'rule' => function ($value, $context) {
            if (!in_array($value, array_keys(Configure::read('app.timeHelper')->getNextDailyDeliveryDays(14)))
                || in_array($value, Configure::read('app.htmlHelper')->getGlobalNoDeliveryDaysAsArray())) {
                    return false;
                }
                return true;
            },
            'message' => __('The_pickup_day_is_not_valid.'),
        ]);
        return $validator;
    }

    public function getProductNameWithUnity($productName, $unity)
    {
        return $productName . ($unity != '' ? ' : ' . $unity : '');
    }

    public function adaptCartWithTimebasedCurrency($cart, $selectedTimeAdaptionFactor)
    {

        $cartProductSum = 0;
        $cartProductSumExcl = 0;
        $cartProductSecondsSum = 0;
        foreach($cart['CartProducts'] as &$cartProduct) {
            if (isset($cartProduct['timebasedCurrencySeconds'])) {
                $calculatedSeconds = round($cartProduct['timebasedCurrencySeconds'] * $selectedTimeAdaptionFactor, 0);
                $cartProduct['timebasedCurrencySeconds'] = $calculatedSeconds;
                $cartProductSecondsSum += $cartProduct['timebasedCurrencySeconds'];
                $cartProduct['isTimebasedCurrencyUsed'] = true;
            }
            if (isset($cartProduct['timebasedCurrencyMoneyIncl'])) {
                $cartProduct['timebasedCurrencyMoneyIncl'] = round($cartProduct['timebasedCurrencyMoneyIncl'] * $selectedTimeAdaptionFactor, 2);
                $cartProduct['price'] -= $cartProduct['timebasedCurrencyMoneyIncl'];
                $cartProductSum += $cartProduct['price'];
            }
            if (isset($cartProduct['timebasedCurrencyMoneyExcl'])) {
                $cartProduct['timebasedCurrencyMoneyExcl'] = round($cartProduct['timebasedCurrencyMoneyExcl'] *  $selectedTimeAdaptionFactor, 2);
                $cartProduct['priceExcl'] -= $cartProduct['timebasedCurrencyMoneyExcl'];
                $cartProductSumExcl += $cartProduct['priceExcl'];
            }
        }

        $cart['CartTimebasedCurrencyUsed'] = true;
        $cart['CartTimebasedCurrencySecondsSum'] = $cartProductSecondsSum;
        $cart['CartProductSum'] = $cartProductSum;
        $cart['CartProductSumExcl'] = $cartProductSumExcl;

        return $cart;
    }

    public function getCart($appAuth, $cartType): array
    {

        $this->Product = FactoryLocator::get('Table')->get('Products');
        $customerId = $appAuth->getUserId();

        $cart = $this->find('all', [
            'conditions' => [
                'Carts.status' => APP_ON,
                'Carts.id_customer' => $customerId,
                'Carts.cart_type' => $cartType,
            ]
        ])->first();

        if (empty($cart)) {
            $cart2save = [
                'id_customer' => $customerId,
                'cart_type' => $cartType,
            ];
            $cart = $this->save($this->newEntity($cart2save));
        }

        $cartProductsTable = FactoryLocator::get('Table')->get('CartProducts');
        $cartProducts = $cartProductsTable->find('all', [
            'conditions' => [
                'CartProducts.id_cart' => $cart->id_cart,
                'CartProducts.amount > 0',
            ],
            'order' => [
                'Products.name',
            ],
            'contain' => [
                'OrderDetails',
                'CartProductUnits',
                'Products.Manufacturers',
                'Products.DepositProducts',
                'Products.UnitProducts',
                'ProductAttributes.ProductAttributeCombinations.Attributes',
                'ProductAttributes.DepositProductAttributes',
                'ProductAttributes.UnitProductAttributes',
                'Products.Images',
                'Products.Taxes',
            ]
        ])->toArray();

        if (!empty($cartProducts)) {
            $cart->pickup_day_entities = $this->CartProducts->setPickupDays($cartProducts, $customerId, $cartType, $appAuth);
        }

        $preparedCart = [
            'Cart' => $cart,
            'CartProducts' => []
        ];

        foreach ($cartProducts as &$cartProduct) {

            $imageId = 0;
            if (!empty($cartProduct->product->image)) {
                $imageId = $cartProduct->product->image->id_image;
            }

            if (!empty($cartProduct->product_attribute->product_attribute_combination)) {
                $productData = $this->prepareProductAttribute($appAuth, $cartProduct);
            } else {
                $productData = $this->prepareMainProduct($appAuth, $cartProduct);
            }

            $productImageData = Configure::read('app.htmlHelper')->getProductImageSrcWithManufacturerImageFallback(
                $imageId,
                $cartProduct->product->id_manufacturer,
            );
            $productImage = Configure::read('app.htmlHelper')->image($productImageData['productImageLargeSrc']);

            $manufacturerLink = Configure::read('app.htmlHelper')->link($cartProduct->product->manufacturer->name, Configure::read('app.slugHelper')->getManufacturerDetail($cartProduct->product->id_manufacturer, $cartProduct->product->manufacturer->name));
            $productData['image'] = $productImage;
            $productData['productName'] = $cartProduct->product->name;
            $productData['manufacturerLink'] = $manufacturerLink;

            $nextDeliveryDay = $this->Product->getNextDeliveryDay($cartProduct->product, $appAuth);
            $nextDeliveryDay = strtotime($nextDeliveryDay);
            $productData['nextDeliveryDay'] = Configure::read('app.timeHelper')->getDateFormattedWithWeekday($nextDeliveryDay);

            $preparedCart['CartProducts'][] = $productData;

        }

        $productName = [];
        $deliveryDay = [];
        foreach($preparedCart['CartProducts'] as $cartProduct) {
            $deliveryDay[] = $cartProduct['nextDeliveryDay'];
            $productName[] = mb_strtolower(StringComponent::slugify($cartProduct['productName']));
        }

        array_multisort(
            $deliveryDay, SORT_DESC, // !SIC - array is reversed later
            $productName, SORT_DESC, // !SIC - array is reversed later
            $preparedCart['CartProducts']
        );

        // sum up deposits and products
        $preparedCart['ProductsWithUnitCount'] = $this->getProductsWithUnitCount($preparedCart['CartProducts']);
        $preparedCart['CartDepositSum'] = 0;
        $preparedCart['CartProductSum'] = 0;
        $preparedCart['CartProductSumExcl'] = 0;
        $preparedCart['CartTaxSum'] = 0;
        $preparedCart['CartTimebasedCurrencyMoneyExclSum'] = 0;
        $preparedCart['CartTimebasedCurrencyMoneyInclSum'] = 0;
        $preparedCart['CartTimebasedCurrencySecondsSum'] = 0;
        foreach ($preparedCart['CartProducts'] as $p) {
            $preparedCart['CartDepositSum'] += $p['deposit'];
            $preparedCart['CartProductSum'] += $p['price'];
            $preparedCart['CartTaxSum'] += $p['tax'];
            $preparedCart['CartProductSumExcl'] += $p['priceExcl'];
            if (!empty($p['timebasedCurrencyMoneyExcl'])) {
                $preparedCart['CartTimebasedCurrencyMoneyExclSum'] += $p['timebasedCurrencyMoneyExcl'];
            }
            if (!empty($p['timebasedCurrencyMoneyIncl'])) {
                $preparedCart['CartTimebasedCurrencyMoneyInclSum'] += $p['timebasedCurrencyMoneyIncl'];
            }
            if (!empty($p['timebasedCurrencySeconds'])) {
                $preparedCart['CartTimebasedCurrencySecondsSum'] += $p['timebasedCurrencySeconds'];
            }
        }
        return $preparedCart;
    }

    public function getCartGroupedByPickupDay($cart, $customerSelectedPickupDay=null)
    {
        $manufacturerName = [];
        $productName = [];
        foreach($cart['CartProducts'] as $cartProduct) {
            $manufacturerName[] = StringComponent::slugify($cartProduct['manufacturerName']);
            $productName[] = StringComponent::slugify($cartProduct['productName']);
        }

        array_multisort(
            $manufacturerName, SORT_ASC,
            $productName, SORT_ASC,
            $cart['CartProducts']
        );

        $preparedCartProducts = [];
        foreach($cart['CartProducts'] as $cartProduct) {
            $pickupDay = $cartProduct['pickupDay'];
            if (!is_null($customerSelectedPickupDay)) {
                $pickupDay = $customerSelectedPickupDay;
            }
            if (!isset($preparedCartProducts[$pickupDay])) {
                $preparedCartProducts[$pickupDay] = [
                    'CartDepositSum' => 0,
                    'CartProductSum' => 0,
                    'Products' => [],
                ];
            }
            $preparedCartProducts[$pickupDay]['CartDepositSum'] += $cartProduct['deposit'] ?? 0;
            $preparedCartProducts[$pickupDay]['CartProductSum'] += $cartProduct['price'] ?? 0;
            $preparedCartProducts[$pickupDay]['Products'][] = $cartProduct ?? 0;
        }
        $cart['CartProducts'] = $preparedCartProducts;
        return $cart;
    }

    private function addPurchasePricePerUnitProductData($appAuth, $productData, $unitProduct)
    {
        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            if (!empty($unitProduct)) {
                $productData['purchasePriceInclPerUnit'] = $unitProduct->purchase_price_incl_per_unit;
            }
        }
        return $productData;
    }

    private function addTimebasedCurrencyProductData($appAuth, $productData, $cartProduct, $grossPricePerPiece, $netPricePerPiece)
    {
        $manufacturersTable = FactoryLocator::get('Table')->get('Manufacturers');
        if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && !empty($appAuth->user()) && $appAuth->user('timebased_currency_enabled')) {
            if ($manufacturersTable->getOptionTimebasedCurrencyEnabled($cartProduct->product->manufacturer->timebased_currency_enabled)) {
                $manufacturerLimitReached = $manufacturersTable->hasManufacturerReachedTimebasedCurrencyLimit($cartProduct->product->id_manufacturer);
                if (!$manufacturerLimitReached) {
                    $productData['timebasedCurrencyMoneyIncl'] = round($manufacturersTable->getTimebasedCurrencyMoney($grossPricePerPiece, $cartProduct->product->manufacturer->timebased_currency_max_percentage), 2) * $cartProduct->amount;
                    $productData['timebasedCurrencyMoneyExcl'] = round($manufacturersTable->getTimebasedCurrencyMoney($netPricePerPiece, $cartProduct->product->manufacturer->timebased_currency_max_percentage), 2) * $cartProduct->amount;
                    $productData['timebasedCurrencySeconds'] = $manufacturersTable->getCartTimebasedCurrencySeconds($grossPricePerPiece, $cartProduct->product->manufacturer->timebased_currency_max_percentage) * $cartProduct->amount;
                }
            }
        }
        return $productData;

    }

    /**
     * @param array $productData
     * @return number
     */
    private function getProductsWithUnitCount($productData)
    {
        $count = 0;
        foreach($productData as $product) {
            if (isset($product['usesQuantityInUnits']) && $product['usesQuantityInUnits']) {
                $count++;
            }
        }
        return $count;
    }

    public function getPricesRespectingPricePerUnit($productId, $netPricePerPiece, $unitProduct, $amount, $orderedQuantityInUnits, $deposit, $taxRate)
    {

        $productsTable = FactoryLocator::get('Table')->get('Products');

        if ((!empty($unitProduct) && !$unitProduct->price_per_unit_enabled ) || is_null($unitProduct)) {

            $grossPricePerPiece = $productsTable->getGrossPrice($netPricePerPiece, $taxRate);
            $grossPrice = $grossPricePerPiece * $amount;
            $tax = $productsTable->getUnitTax($grossPrice, $netPricePerPiece, $amount) * $amount;

            $prices = [
                'net_per_piece' => $netPricePerPiece,
                'gross_per_piece' => $grossPricePerPiece,
                'gross' => $grossPrice,
                'net' => $grossPrice - $tax,
                'tax' => $tax,
                'tax_per_piece' => $tax / $amount,
                'gross_with_deposit' => $grossPrice + ($deposit->deposit ?? 0),
            ];

        } else {

            $priceInclPerUnit = $unitProduct->price_incl_per_unit;
            $unitAmount = $unitProduct->amount;

            $quantityInUnitsForPrice = $unitProduct->quantity_in_units;
            if (!is_null($orderedQuantityInUnits)) {
                $quantityInUnitsForPrice = $orderedQuantityInUnits;
            }

            $grossPricePerPiece = round($priceInclPerUnit * $quantityInUnitsForPrice / $unitAmount, 2);
            $netPricePerPiece = round($productsTable->getNetPrice($grossPricePerPiece, $taxRate), 2);
            $grossPrice = $grossPricePerPiece * $amount;
            if (!is_null($orderedQuantityInUnits)) {
                $grossPrice = $grossPricePerPiece;
            }

            $tax = ($grossPricePerPiece - $netPricePerPiece) * $amount;

            $prices = [
                'net_per_piece' => $netPricePerPiece,
                'gross_per_piece' => $grossPricePerPiece,
                'gross' => $grossPrice,
                'net' => $grossPrice - $tax,
                'tax' => $tax,
                'tax_per_piece' => $tax / $amount,
                'gross_with_deposit' => $grossPrice + ($deposit->deposit ?? 0),
            ];

        }

        return $prices;
    }

    private function prepareMainProduct($appAuth, $cartProduct): array
    {

        $orderedQuantityInUnits = isset($cartProduct->cart_product_unit) ? $cartProduct->cart_product_unit->ordered_quantity_in_units : null;
        $taxRate = $cartProduct->product->tax->rate ?? 0;
        $unitProduct = $cartProduct->product->unit_product;
        $deposit = !empty($cartProduct->product->deposit_product) ? $cartProduct->product->deposit_product->deposit : 0;

        // START: override shopping with purchase prices / zero prices
        $cm = FactoryLocator::get('Table')->get('Customers');
        $priceInclPerUnit = null;
        if (!empty($unitProduct)) {
            $priceInclPerUnit = $unitProduct->price_incl_per_unit;
        }
        $modifiedProductPricesByShoppingPrice = $cm->getModifiedProductPricesByShoppingPrice($appAuth, $cartProduct->id_product, $cartProduct->product->price, $priceInclPerUnit, $deposit, $taxRate);
        $cartProduct->product->price = $modifiedProductPricesByShoppingPrice['price'];
        if (!empty($unitProduct)) {
            $unitProduct->price_incl_per_unit = $modifiedProductPricesByShoppingPrice['price_incl_per_unit'];
        }
        if (!empty($cartProduct->product->deposit_product->deposit)) {
            $cartProduct->product->deposit_product->deposit = $modifiedProductPricesByShoppingPrice['deposit'];
        }
        // END override shopping with purchase prices / zero prices

        $prices = $this->getPricesRespectingPricePerUnit(
            $cartProduct->id_product,
            $cartProduct->product->price,
            $unitProduct,
            $cartProduct->amount,
            $orderedQuantityInUnits,
            $cartProduct->product->deposit_product,
            $taxRate,
        );

        $productData = [
            'cartProductId' => $cartProduct->id_cart_product,
            'productId' => $cartProduct->id_product,
            'productName' => $cartProduct->product->name,
            'amount' => $cartProduct->amount,
            'manufacturerId' => $cartProduct->product->id_manufacturer,
            'manufacturerName' => $cartProduct->product->manufacturer->name,
            'price' => $prices['gross'],
            'priceExcl' => $prices['net'],
            'tax' => $prices['tax'],
            'taxPerPiece' => $prices['tax_per_piece'],
            'pickupDay' => $cartProduct->pickup_day,
            'isStockProduct' => $cartProduct->product->is_stock_product
        ];

        $deposit = 0;
        if (Configure::read('app.isDepositEnabled') && !empty($cartProduct->product->deposit_product->deposit)) {
            $deposit = $cartProduct->product->deposit_product->deposit * $cartProduct->amount;
        }
        $productData['deposit'] = $deposit;

        $unitName = '';
        $unitAmount = 0;
        $priceInclPerUnit = 0;
        $unity = $cartProduct->product->unity;
        $productData['unity'] = $unity;

        if (!empty($unitProduct) && $unitProduct->price_per_unit_enabled) {

            $unitName = $unitProduct->name;
            $unitAmount = $unitProduct->amount;
            $priceInclPerUnit = $unitProduct->price_incl_per_unit;

            if (!is_null($orderedQuantityInUnits)) {
                $productData['orderedQuantityInUnits'] = $orderedQuantityInUnits;
            }

            if ($unity != '') {
                $unity .= ', ';
            }
            $unity .=  Configure::read('app.pricePerUnitHelper')->getQuantityInUnits(
                $unitProduct->price_per_unit_enabled,
                $unitProduct->quantity_in_units,
                $unitName,
                $cartProduct->amount
            );
            $productData['usesQuantityInUnits'] = true;

            $productData['quantityInUnits'] = isset($unitProduct) ? $unitProduct->quantity_in_units : 0;
            $productQuantityInUnits = $unitProduct->quantity_in_units * $cartProduct->amount;
            $markAsSaved = APP_OFF;
            if (!is_null($orderedQuantityInUnits)) {
                $productQuantityInUnits = $orderedQuantityInUnits;
                $markAsSaved = APP_ON;
            }
            $productData['productQuantityInUnits'] = $productQuantityInUnits;
            $productData['markAsSaved'] = $markAsSaved;
            $productData = $this->addPurchasePricePerUnitProductData($appAuth, $productData, $unitProduct);

        }
        $productData['unity_with_unit'] = $unity;
        $productData['unitName'] = $unitName;
        $productData['unitAmount'] = $unitAmount;
        $productData['priceInclPerUnit'] = $priceInclPerUnit;

        $productData = $this->addTimebasedCurrencyProductData($appAuth, $productData, $cartProduct, $prices['gross_per_piece'], $prices['net_per_piece']);

        return $productData;

    }

    private function prepareProductAttribute($appAuth, $cartProduct): array
    {

        $unitProductAttribute = $cartProduct->product_attribute->unit_product_attribute;
        $taxRate = $cartProduct->product->tax->rate ?? 0;
        $deposit = !empty($cartProduct->product_attribute->deposit_product_attribute) ? $cartProduct->product_attribute->deposit_product_attribute->deposit : 0;

        // START: override shopping with purchase prices / zero prices
        $cm = FactoryLocator::get('Table')->get('Customers');
        $priceInclPerUnit = null;
        if (!empty($unitProductAttribute)) {
            $priceInclPerUnit = $unitProductAttribute->price_incl_per_unit;
        }
        $modifiedProductPricesByShoppingPrice = $cm->getModifiedAttributePricesByShoppingPrice($appAuth, $cartProduct->id_product, $cartProduct->id_product_attribute, $cartProduct->product_attribute->price, $priceInclPerUnit, $deposit, $taxRate);
        $cartProduct->product_attribute->price = $modifiedProductPricesByShoppingPrice['price'];
        if (!empty($unitProductAttribute)) {
            $unitProductAttribute->price_incl_per_unit = $modifiedProductPricesByShoppingPrice['price_incl_per_unit'];
        }
        if (!empty(!empty($cartProduct->product_attribute->deposit_product_attribute))) {
            $cartProduct->product_attribute->deposit_product_attribute->deposit = $modifiedProductPricesByShoppingPrice['deposit'];
        }
        // END: override shopping with purchase prices / zero prices

        $orderedQuantityInUnits = isset($cartProduct->cart_product_unit) ? $cartProduct->cart_product_unit->ordered_quantity_in_units : null;
        $prices = $this->getPricesRespectingPricePerUnit(
            $cartProduct->id_product,
            $cartProduct->product_attribute->price,
            $unitProductAttribute,
            $cartProduct->amount,
            $orderedQuantityInUnits,
            $cartProduct->product_attribute->deposit_product_attribute,
            $taxRate,
        );

        $productData = [
            'cartProductId' => $cartProduct->id_cart_product,
            'productId' => $cartProduct->id_product . '-' . $cartProduct->id_product_attribute,
            'productName' => $cartProduct->product->name,
            'amount' => $cartProduct->amount,
            'manufacturerId' => $cartProduct->product->id_manufacturer,
            'manufacturerName' => $cartProduct->product->manufacturer->name,
            'price' => $prices['gross'],
            'priceExcl' => $prices['net'],
            'tax' => $prices['tax'],
            'taxPerPiece' => $prices['tax_per_piece'],
            'pickupDay' => $cartProduct->pickup_day,
            'isStockProduct' => $cartProduct->product->is_stock_product
        ];

        $deposit = 0;
        if (Configure::read('app.isDepositEnabled') && !empty($cartProduct->product_attribute->deposit_product_attribute->deposit)) {
            $deposit = $cartProduct->product_attribute->deposit_product_attribute->deposit * $cartProduct->amount;
        }
        $productData['deposit'] = $deposit;

        $unitName = '';
        $unityName = '';
        $unitAmount = 0;
        $priceInclPerUnit = 0;

        if (!empty($unitProductAttribute) && $unitProductAttribute->price_per_unit_enabled) {

            $unitName = $unitProductAttribute->name;
            if (!$cartProduct->product_attribute->product_attribute_combination->attribute->can_be_used_as_unit) {
                $unityName = $cartProduct->product_attribute->product_attribute_combination->attribute->name;
            }
            $unitAmount = $unitProductAttribute->amount;
            $priceInclPerUnit = $unitProductAttribute->price_incl_per_unit;

            if (!is_null($orderedQuantityInUnits)) {
                $productData['orderedQuantityInUnits'] = $orderedQuantityInUnits;
            }

            $unity = Configure::read('app.pricePerUnitHelper')->getQuantityInUnitsStringForAttributes(
                $cartProduct->product_attribute->product_attribute_combination->attribute->name,
                $cartProduct->product_attribute->product_attribute_combination->attribute->can_be_used_as_unit,
                $unitProductAttribute->price_per_unit_enabled,
                $unitProductAttribute->quantity_in_units,
                $unitName,
                $cartProduct->amount
            );
            $productData['usesQuantityInUnits'] = true;

            $productData['quantityInUnits'] = isset($unitProductAttribute->quantity_in_units) ? $unitProductAttribute->quantity_in_units : 0;
            $productQuantityInUnits = $unitProductAttribute->quantity_in_units * $cartProduct->amount;
            $markAsSaved = APP_OFF;
            if (!is_null($orderedQuantityInUnits)) {
                $productQuantityInUnits = $orderedQuantityInUnits;
                $markAsSaved = APP_ON;
            }
            $productData['productQuantityInUnits'] = $productQuantityInUnits;
            $productData['markAsSaved'] = $markAsSaved;
            $productData = $this->addPurchasePricePerUnitProductData($appAuth, $productData, $unitProductAttribute);

        } else {
            $unity = $cartProduct->product_attribute->product_attribute_combination->attribute->name;
            $unityName = $unity;
        }
        $productData['unity'] = $unityName;
        $productData['unity_with_unit'] = $unity;
        $productData['unitName'] = $unitName;
        $productData['unitAmount'] = $unitAmount;
        $productData['priceInclPerUnit'] = $priceInclPerUnit;

        $productData = $this->addTimebasedCurrencyProductData($appAuth, $productData, $cartProduct, $prices['gross_per_piece'], $prices['net_per_piece']);

        return $productData;

    }

}
