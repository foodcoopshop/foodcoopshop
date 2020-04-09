<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Lib\Error\Exception\InvalidParameterException;

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
class CartProductsTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_cart_product');
        $this->hasOne('OrderDetails', [
            'foreignKey' => 'id_cart_product'
        ]);
        $this->hasOne('CartProductUnits', [
            'foreignKey' => 'id_cart_product'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'id_product'
        ]);
        $this->belongsTo('ProductAttributes', [
            'foreignKey' => 'id_product_attribute'
        ]);
        $this->belongsTo('Carts', [
            'foreignKey' => 'id_cart'
        ]);
        $this->addBehavior('Timestamp');
    }
    
    public function validateQuantityInUnitsForSelfServiceMode($appAuth, $object, $unitObject, $orderedQuantityInUnits, $initialProductId)
    {
        $result = true;
        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && ($appAuth->isSelfServiceModeByReferer() || $appAuth->isSelfServiceModeByUrl())) {
            if ($object->{$unitObject} && $object->{$unitObject}->price_per_unit_enabled && $orderedQuantityInUnits < 0 /* !sic < 0 see getStringAsFloat */) {
                $result = __('Please_provide_a_valid_ordered_quantity_in_units.');
            }
        }
        return $result;
    }

    /**
     * @param int $productId
     * @param int $attributeId
     * @param int $amount
     * @return array || boolean
     */
    public function add($appAuth, $productId, $attributeId, $amount, $orderedQuantityInUnits = -1)
    {

        $initialProductId = $this->Products->getCompositeProductIdAndAttributeId($productId, $attributeId);

        // allow -1 and 1 to MAX_PRODUCT_AMOUNT_FOR_CART
        if ($amount == 0 || $amount < - 1 || $amount > MAX_CART_PRODUCT_AMOUNT) {
            $message = __('The_desired_amount_{0}_is_not_valid.', ['<b>' . $amount . '</b>']);
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ];
        }

        // get product data from database
        $product = $this->Products->find('all', [
            'conditions' => [
                'Products.id_product' => (int) $productId
            ],
            'contain' => [
                'Manufacturers',
                'StockAvailables',
                'ProductAttributes',
                'UnitProducts',
                'ProductAttributes.StockAvailables',
                'ProductAttributes.ProductAttributeCombinations.Attributes',
                'ProductAttributes.UnitProductAttributes'
            ]
        ])
        ->first();

        $existingCartProduct = $appAuth->Cart->getProduct($initialProductId);
        $combinedAmount = $amount;
        if ($existingCartProduct) {
            $combinedAmount = $existingCartProduct['amount'] + $amount;
        }
        // check if passed product exists
        if (empty($product)) {
            $message = __('Product_with_id_{0}_does_not_exist.', [$productId]);
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ];
        }
        
        $result = $this->validateQuantityInUnitsForSelfServiceMode($appAuth, $product, 'unit_product', $orderedQuantityInUnits, $initialProductId);
        if ($result !== true) {
            return [
                'status' => 0,
                'msg' => $result,
                'productId' => $initialProductId
            ];
        }

        // stock available check for product
        $availableQuantity = $product->stock_available->quantity;
        if ($product->is_stock_product && $product->manufacturer->stock_management_enabled) {
            $availableQuantity = $product->stock_available->quantity - $product->stock_available->quantity_limit;
        }
        if ((($product->is_stock_product && $product->manufacturer->stock_management_enabled) || !$product->stock_available->always_available) && $attributeId == 0 && $availableQuantity < $combinedAmount && $amount > 0) {
            $message = __('The_desired_amount_{0}_of_the_product_{1}_is_not_available_any_more_available_amount_{2}.', ['<b>' . $combinedAmount . '</b>', '<b>' . $product->name . '</b>', $availableQuantity]);
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ];
        }

        // check if passed optional product/attribute relation exists
        if ($attributeId > 0) {
            $attributeIdFound = false;
            foreach ($product->product_attributes as $attribute) {
                if ($attribute->id_product_attribute == $attributeId) {
                    
                    $attributeIdFound = true;
                    
                    // stock available check for attribute
                    $availableQuantity = $attribute->stock_available->quantity;
                    if ($product->is_stock_product && $product->manufacturer->stock_management_enabled) {
                        $availableQuantity = $attribute->stock_available->quantity - $attribute->stock_available->quantity_limit;
                    }
                    if ((($product->is_stock_product && $product->manufacturer->stock_management_enabled) || !$attribute->stock_available->always_available) && $availableQuantity < $combinedAmount && $amount > 0) {
                        $message = __('The_desired_amount_{0}_of_the_attribute_{1}_of_the_product_{2}_is_not_available_any_more_available_amount_{3}.', ['<b>' . $combinedAmount . '</b>', '<b>' . $attribute->product_attribute_combination->attribute->name . '</b>', '<b>' . $product->name . '</b>', $availableQuantity]);
                        return [
                            'status' => 0,
                            'msg' => $message,
                            'productId' => $initialProductId
                        ];
                    }

                    $result = $this->validateQuantityInUnitsForSelfServiceMode($appAuth, $attribute, 'unit_product_attribute', $orderedQuantityInUnits, $initialProductId);
                    if ($result !== true) {
                        return [
                            'status' => 0,
                            'msg' => $result,
                            'productId' => $initialProductId
                        ];
                    }
                    
                    break;
                }
            }
            if (! $attributeIdFound) {
                $message = __('The_attribute_does_not_exist:_{0}', [$initialProductId]);
                return [
                    'status' => 0,
                    'msg' => $message,
                    'productId' => $initialProductId
                ];
            }
        }

        if (! $product->active) {
            $message = __('The_product_{0}_is_not_activated_any_more.', ['<b>' . $product->name . '</b>']);
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ];
        }

        if (! $product->manufacturer->active || (!$appAuth->isInstantOrderMode() && !$appAuth->isSelfServiceModeByReferer() && $this->Products->deliveryBreakEnabled($product->manufacturer->no_delivery_days, $product->next_delivery_day))) {
            $message = __('The_manufacturer_of_the_product_{0}_has_a_delivery_break_or_product_is_not_activated.', ['<b>' . $product->name . '</b>']);
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ];
        }
        
        if (!$appAuth->isInstantOrderMode()) {
            if (!($product->manufacturer->stock_management_enabled && $product->is_stock_product) && $product->delivery_rhythm_type == 'individual') {
                if ($product->delivery_rhythm_order_possible_until->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')) < Configure::read('app.timeHelper')->getCurrentDateForDatabase()) {
                    $message = __('It_is_not_possible_to_order_the_product_{0}_any_more.', ['<b>' . $product->name . '</b>']);
                    return [
                        'status' => 0,
                        'msg' => $message,
                        'productId' => $initialProductId
                    ];
                }
            }
        }
        
        if (!$appAuth->isInstantOrderMode() && !$appAuth->isSelfServiceModeByReferer() && $this->Products->deliveryBreakEnabled(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'), $product->next_delivery_day)) {
            $message = __('{0}_has_activated_the_delivery_break_and_product_{1}_cannot_be_ordered.',
                [
                    Configure::read('appDb.FCS_APP_NAME'),
                    '<b>' . $product->name . '</b>'
                ]
            );
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ];
        }

        // update amount if cart product already exists
        $cart = $appAuth->getCart();
        $appAuth->setCart($cart);

        $cartProduct2save = [
            'id_product' => $productId,
            'amount' => $combinedAmount,
            'id_product_attribute' => $attributeId,
            'id_cart' => $cart['Cart']['id_cart']
        ];
        
        $options = [];
        if ($orderedQuantityInUnits > 0) {
            if ($existingCartProduct && !is_null($existingCartProduct['orderedQuantityInUnits'])) {
                $orderedQuantityInUnits += $existingCartProduct['orderedQuantityInUnits'];
            }
            $cartProduct2save['cart_product_unit'] = [
                'ordered_quantity_in_units' => $orderedQuantityInUnits
            ];
            $options = [
                'associated' => [
                    'CartProductUnits'
                ]
            ];
        }
        
        if ($existingCartProduct) {
            $oldEntity = $this->get($existingCartProduct['cartProductId']);
            $entity = $this->patchEntity($oldEntity, $cartProduct2save, $options);
        } else {
            $entity = $this->newEntity($cartProduct2save, $options);
        }
        $this->save($entity);

        return true;

    }
    
    public function setPickupDays($cartProducts, $customerId, $cartType)
    {
        $pickupDayTable = TableRegistry::getTableLocator()->get('PickupDays');
        $cartTable = TableRegistry::getTableLocator()->get('Carts');
        
        foreach($cartProducts as &$cartProduct) {
            $cartProduct->pickup_day = Configure::read('app.timeHelper')->getCurrentDateForDatabase();
            if ($cartType == $cartTable::CART_TYPE_WEEKLY_RHYTHM) {
                $cartProduct->pickup_day = $cartProduct->product->next_delivery_day;
            }
        }
        
        $uniquePickupDays = $pickupDayTable->getUniquePickupDays($cartProducts);
        $pickupDays = $pickupDayTable->find('all', [
            'conditions' => [
                'PickupDays.customer_id' => $customerId,
                'PickupDays.pickup_day IN' => $uniquePickupDays
            ],
            'order' => [
                'PickupDays.pickup_day' => 'ASC'
            ]
        ]);
        
        $existingPickupDays = [];
        foreach($pickupDays->all()->extract('pickup_day')->toArray() as $p) {
            $existingPickupDays[] = $p->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
        }
        $missingPickupDays = array_diff($uniquePickupDays, $existingPickupDays);
        $pickupDays = $pickupDays->toArray();
        
        if (!empty($missingPickupDays)) {
            foreach($missingPickupDays as $missingPickupDay) {
                $pickupDays[] = $pickupDayTable->newEntity([
                    'customer_id' => $customerId,
                    'pickup_day' => $missingPickupDay
                ]);
            }
        }
        return $pickupDays;
    }

    public function removeAll($cartId, $customerId)
    {
        $cartId = (int) $cartId;
        if (!$cartId > 0) {
            throw new InvalidParameterException('wrong cartId: ' . $cartId);
        }
        // deleteAll cannot check associations
        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => $cartId,
                'Carts.id_customer' => $customerId
            ]
        ])->first();
        if (empty($cart)) {
            throw new InvalidParameterException('no cart found for cartId: ' . $cartId . ' and customerId: ' . $customerId);
        }
        $cartProduct2remove = [
            'CartProducts.id_cart' => $cartId
        ];
        return $this->deleteAll($cartProduct2remove);
    }

    public function remove($productId, $attributeId, $cartId)
    {
        $cartId = (int) $cartId;
        if (!$cartId > 0) {
            throw new InvalidParameterException('wrong cartId: ' . $cartId);
        }
        $cartProduct2remove = [
            'CartProducts.id_product' => $productId,
            'CartProducts.id_product_attribute' => $attributeId,
            'CartProducts.id_cart' => $cartId
        ];
        
        // if product attribute was deleted after adding product to cart,
        // remove product without check for product_attribute_id so that the cart can be emptied!
        $cartProducts = $this->find('all', [
            'conditions' => $cartProduct2remove
        ])->first();
        
        if (empty($cartProducts)) {
            unset($cartProduct2remove['CartProducts.id_product_attribute']);
        }
        
        $result = $this->deleteAll($cartProduct2remove);
        $result |= $this->CartProductUnits->deleteAll(['id_cart_product' => $cartProducts->id_cart_product]);
        
        return $result;
    }
}
