<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\Validation\Validator;
use App\Services\DeliveryRhythmService;
use App\Services\OrderCustomerService;
use Cake\Routing\Router;

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
class CartsTable extends AppTable
{

    protected $Product;

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
        if (Configure::read('app.rightOfWithdrawalEnabled')) {
            $validator->requirePresence('cancellation_terms_accepted', true, __('Please_accept_the_information_about_right_of_withdrawal'));
            $validator->equals('cancellation_terms_accepted', 1, __('Please_accept_the_information_about_right_of_withdrawal.'));
        }
        if (Configure::read('app.generalTermsAndConditionsEnabled')) {
            $validator->requirePresence('general_terms_and_conditions_accepted', true, __('Please_accept_the_general_terms_and_conditions.'));
            $validator->equals('general_terms_and_conditions_accepted', 1, __('Please_accept_the_general_terms_and_conditions.'));
        }
        $validator->notEmptyArray('self_service_payment_type', __('Please_select_your_payment_type.'));
        return $validator;
    }

    /**
     * no checkboxes are shown here - do not validate them neither use requirePresence
     */
    public function validationSelfServiceForDifferentCustomer(Validator $validator): Validator
    {
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
            if (!in_array($value, array_keys((new DeliveryRhythmService())->getNextDailyDeliveryDays(21)))
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

    public function getCart($identity, $cartType): array
    {

        $this->Product = FactoryLocator::get('Table')->get('Products');

        $identity = Router::getRequest()->getAttribute('identity');
        $customerId = $identity->getId();

        $cart = $this->find('all', conditions: [
            'Carts.status' => APP_ON,
            'Carts.id_customer' => $customerId,
            'Carts.cart_type' => $cartType,
        ])->first();

        if (empty($cart)) {
            $cart2save = [
                'id_customer' => $customerId,
                'cart_type' => $cartType,
            ];
            $newCartEntity = $this->newEntity($cart2save, ['validate' => false]);
            $cart = $this->save($newCartEntity);
        }

        $cartProductsTable = FactoryLocator::get('Table')->get('CartProducts');
        $cartProducts = $cartProductsTable->find('all',
            conditions: [
                'CartProducts.id_cart' => $cart->id_cart,
                'CartProducts.amount > 0',
            ],
            contain: [
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
        )->toArray();

        $orderCustomerService = new OrderCustomerService();
        if (!empty($cartProducts)) {
            $cart->pickup_day_entities = $cartProductsTable->setPickupDays($cartProducts, $customerId, $orderCustomerService);
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
                $productData = $this->prepareProductAttribute($cartProduct);
            } else {
                $productData = $this->prepareMainProduct($cartProduct);
            }

            $productImageData = Configure::read('app.htmlHelper')->getProductImageSrcWithManufacturerImageFallback(
                $imageId,
                $cartProduct->product->id_manufacturer,
            );
            $productImage = Configure::read('app.htmlHelper')->image($productImageData['productImageSrc']);

            $manufacturerLink = Configure::read('app.htmlHelper')->link($cartProduct->product->manufacturer->name, Configure::read('app.slugHelper')->getManufacturerDetail($cartProduct->product->id_manufacturer, $cartProduct->product->manufacturer->name));
            $productData['image'] = $productImage;
            $productData['productName'] = $cartProduct->product->name;
            $productData['manufacturerLink'] = $manufacturerLink;

            $nextDeliveryDay = (new DeliveryRhythmService())->getNextDeliveryDayForProduct($cartProduct->product, $orderCustomerService);
            if ($nextDeliveryDay == 'delivery-rhythm-triggered-delivery-break') {
                $dateFormattedWithWeekday = __('Delivery_break');
            } else {
                $nextDeliveryDay = strtotime($nextDeliveryDay);
                $dateFormattedWithWeekday = Configure::read('app.timeHelper')->getDateFormattedWithWeekday($nextDeliveryDay);
            }
            $productData['nextDeliveryDay'] = $dateFormattedWithWeekday;

            $preparedCart['CartProducts'][] = $productData;

        }

        $deliveryDaySortArray = [];
        $idSortArray = [];
        foreach($preparedCart['CartProducts'] as $cartProduct) {
            $deliveryDaySortArray[] = $cartProduct['nextDeliveryDay'];
            $idSortArray[] = $cartProduct['cartProductId'];
        }

        array_multisort(
            $deliveryDaySortArray, SORT_DESC, SORT_NUMERIC, // !SIC - array is reversed later
            $idSortArray, SORT_DESC, // !SIC - array is reversed later
            $preparedCart['CartProducts'],
        );

        // sum up deposits and products
        $preparedCart['ProductsWithUnitCount'] = $this->getProductsWithUnitCount($preparedCart['CartProducts']);
        $preparedCart['CartDepositSum'] = 0;
        $preparedCart['CartProductSum'] = 0;
        $preparedCart['CartProductSumExcl'] = 0;
        $preparedCart['CartTaxSum'] = 0;
        foreach ($preparedCart['CartProducts'] as $p) {
            $preparedCart['CartDepositSum'] += $p['deposit'];
            $preparedCart['CartProductSum'] += $p['price'];
            $preparedCart['CartTaxSum'] += $p['tax'];
            $preparedCart['CartProductSumExcl'] += $p['priceExcl'];
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

    private function addPurchasePricePerUnitProductData($productData, $unitProduct)
    {
        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            if (!empty($unitProduct)) {
                $productData['purchasePriceInclPerUnit'] = $unitProduct->purchase_price_incl_per_unit;
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

    public function getPricesRespectingPricePerUnit($netPricePerPiece, $unitProduct, $amount, $orderedQuantityInUnits, $deposit, $taxRate)
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

    private function prepareMainProduct($cartProduct): array
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
        $modifiedProductPricesByShoppingPrice = $cm->getModifiedProductPricesByShoppingPrice($cartProduct->id_product, $cartProduct->product->price, $priceInclPerUnit, $deposit, $taxRate);
        $cartProduct->product->price = $modifiedProductPricesByShoppingPrice['price'];
        if (!empty($unitProduct)) {
            $unitProduct->price_incl_per_unit = $modifiedProductPricesByShoppingPrice['price_incl_per_unit'];
        }
        if (!empty($cartProduct->product->deposit_product->deposit)) {
            $cartProduct->product->deposit_product->deposit = $modifiedProductPricesByShoppingPrice['deposit'];
        }
        // END override shopping with purchase prices / zero prices

        $prices = $this->getPricesRespectingPricePerUnit(
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

            $productData['quantityInUnits'] = $unitProduct->quantity_in_units ?? 0;
            $productQuantityInUnits = $unitProduct->quantity_in_units * $cartProduct->amount;
            $markAsSaved = APP_OFF;
            $orderCustomerService = new OrderCustomerService();
            if (!is_null($orderedQuantityInUnits) && $orderCustomerService->isSelfServiceMode())  {
                $productQuantityInUnits = $orderedQuantityInUnits;
                $markAsSaved = APP_ON;
            }
            $productData['productQuantityInUnits'] = $productQuantityInUnits;
            $productData['markAsSaved'] = $markAsSaved;
            $productData = $this->addPurchasePricePerUnitProductData($productData, $unitProduct);

        }
        $productData['unity_with_unit'] = $unity;
        $productData['unitName'] = $unitName;
        $productData['unitAmount'] = $unitAmount;
        $productData['priceInclPerUnit'] = $priceInclPerUnit;

        return $productData;

    }

    private function prepareProductAttribute($cartProduct): array
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
        $modifiedProductPricesByShoppingPrice = $cm->getModifiedAttributePricesByShoppingPrice($cartProduct->id_product, $cartProduct->id_product_attribute, $cartProduct->product_attribute->price, $priceInclPerUnit, $deposit, $taxRate);
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
            $orderCustomerService = new OrderCustomerService();
            if (!is_null($orderedQuantityInUnits) &&  $orderCustomerService->isSelfServiceMode()) {
                $productQuantityInUnits = $orderedQuantityInUnits;
                $markAsSaved = APP_ON;
            }
            $productData['productQuantityInUnits'] = $productQuantityInUnits;
            $productData['markAsSaved'] = $markAsSaved;
            $productData = $this->addPurchasePricePerUnitProductData($productData, $unitProductAttribute);

        } else {
            $unity = $cartProduct->product_attribute->product_attribute_combination->attribute->name;
            $unityName = $unity;
        }
        $productData['unity'] = $unityName;
        $productData['unity_with_unit'] = $unity;
        $productData['unitName'] = $unitName;
        $productData['unitAmount'] = $unitAmount;
        $productData['priceInclPerUnit'] = $priceInclPerUnit;

        return $productData;

    }

}
