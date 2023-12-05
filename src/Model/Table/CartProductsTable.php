<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use App\Lib\Error\Exception\InvalidParameterException;
use App\Lib\DeliveryRhythm\DeliveryRhythm;
use App\Model\Traits\CartValidatorTrait;

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
class CartProductsTable extends AppTable
{

    use CartValidatorTrait;

    protected $Cart;

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

    /**
     * @param int $productId
     * @param int $attributeId
     * @param int $amount
     * @return array || boolean
     */
    public function add($appAuth, $productId, $attributeId, $amount, $orderedQuantityInUnits = -1)
    {

        $productsTable = FactoryLocator::get('Table')->get('Products');
        $initialProductId = $productsTable->getCompositeProductIdAndAttributeId($productId, $attributeId);

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
        $product = $productsTable->find('all', [
            'conditions' => [
                'Products.id_product' => (int) $productId
            ],
            'contain' => [
                'DepositProducts',
                'Manufacturers',
                'StockAvailables',
                'ProductAttributes',
                'UnitProducts',
                'ProductAttributes.DepositProductAttributes',
                'ProductAttributes.StockAvailables',
                'ProductAttributes.ProductAttributeCombinations.Attributes',
                'ProductAttributes.UnitProductAttributes',
                'Taxes',
            ]
        ])
        ->first();

        $existingCartProduct = $appAuth->CartService->getProduct($initialProductId);
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

        $product->next_delivery_day = DeliveryRhythm::getNextDeliveryDayForProduct($product, $appAuth);

        // stock available check for product
        $availableQuantity = $product->stock_available->quantity;
        if ($product->is_stock_product && $product->manufacturer->stock_management_enabled) {
            $availableQuantity = $product->stock_available->quantity - $product->stock_available->quantity_limit;
        }

        $message = $this->isAmountAvailableProduct(
            $product->is_stock_product,
            $product->manufacturer->stock_management_enabled,
            $product->stock_available->always_available,
            $attributeId,
            $availableQuantity,
            $combinedAmount,
            $product->name,
        );
        if ($message !== true && $amount > 0) {
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId,
            ];
        }

        $unitObject = $product->unit_product;
        $depositObject = $product->deposit_product;
        $price = $product->price;

        if ($attributeId > 0) {
            foreach ($product->product_attributes as $attribute) {
                if ($attribute->id_product_attribute == $attributeId) {
                    $unitObject = null;
                    $depositObject = $attribute->deposit_product_attribute;
                    $price = $attribute->price;
                    if (isset($attribute->unit_product_attribute) && $attribute->unit_product_attribute->price_per_unit_enabled) {
                        $unitObject =  $attribute->unit_product_attribute;
                    }
                    continue;
                }
            }
        }

        $cartTable = FactoryLocator::get('Table')->get('Carts');
        $prices = $cartTable->getPricesRespectingPricePerUnit(
            $product->id_product,
            $price,
            $unitObject,
            $amount,
            $orderedQuantityInUnits == -1 ? null : $orderedQuantityInUnits,
            $depositObject,
            $product->tax->rate ?? 0,
        );

        $result = $this->validateMinimalCreditBalance($appAuth, $prices['gross_with_deposit']);
        if ($result !== true) {
            return [
                'status' => 0,
                'msg' => $result,
                'productId' => $initialProductId,
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
                    $message = $this->isAmountAvailableAttribute(
                        $product->is_stock_product,
                        $product->manufacturer->stock_management_enabled,
                        $attribute->stock_available->always_available,
                        $availableQuantity,
                        $combinedAmount,
                        $attribute->product_attribute_combination->attribute->name,
                        $product->name,
                    );
                    if ($message !== true && $amount > 0) {
                        return [
                            'status' => 0,
                            'msg' => $message,
                            'productId' => $initialProductId
                        ];
                    }

                    $result = $this->validateQuantityInUnitsForSelfServiceMode($appAuth, $attribute, 'unit_product_attribute', $orderedQuantityInUnits);
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

        $message = $this->isProductActive($product->active, $product->name);
        if ($message !== true) {
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId,
            ];
        }

        $message = $this->isManufacturerActiveOrManufacturerHasDeliveryBreak(
            $appAuth,
            $productsTable,
            $product->manufacturer->active,
            $product->manufacturer->no_delivery_days,
            $product->next_delivery_day,
            $product->manufacturer->stock_management_enabled,
            $product->is_stock_product,
            $product->name,
        );
        if ($message !== true) {
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId,
            ];
        }

        $message = $this->isProductBulkOrderStillPossible(
            $appAuth,
            $product->manufacturer->stock_management_enabled,
            $product->is_stock_product,
            $product->delivery_rhythm_type,
            $product->delivery_rhythm_order_possible_until,
            $product->name,
        );
        if ($message !== true) {
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId,
            ];
        }

        $message = $this->isGlobalDeliveryBreakEnabled($appAuth, $productsTable, $product->next_delivery_day, $product->name);
        if ($message !== true) {
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId,
            ];
        }

        $result = $this->validateQuantityInUnitsForSelfServiceMode($appAuth, $product, 'unit_product', $orderedQuantityInUnits);
        if ($result !== true) {
            return [
                'status' => 0,
                'msg' => $result,
                'productId' => $initialProductId
            ];
        }

        $message = $this->hasProductDeliveryRhythmTriggeredDeliveryBreak($appAuth, $product->next_delivery_day, $product->name);
        if ($message !== true) {
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId,
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

    public function setPickupDays($cartProducts, $customerId, $cartType, $appAuth)
    {
        $pickupDayTable = FactoryLocator::get('Table')->get('PickupDays');
        foreach($cartProducts as &$cartProduct) {
            $cartProduct->pickup_day = DeliveryRhythm::getNextDeliveryDayForProduct($cartProduct->product, $appAuth);
        }

        $pickupDays = [];
        $uniquePickupDays = $pickupDayTable->getUniquePickupDays($cartProducts);
        if (!empty($uniquePickupDays)) {
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
        $this->Cart = FactoryLocator::get('Table')->get('Carts');
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
        $cartProductUnitsTable = FactoryLocator::get('Table')->get('CartProductUnits');
        $result |= $cartProductUnitsTable->deleteAll(['id_cart_product' => $cartProducts->id_cart_product]);

        return $result;
    }
}
