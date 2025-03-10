<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use App\Services\DeliveryRhythmService;
use App\Model\Traits\CartValidatorTrait;
use App\Services\OrderCustomerService;
use Cake\Routing\Router;
use App\Services\ProductQuantityService;
use Cake\ORM\TableRegistry;

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

    public function add($productId, $attributeId, $amount, $orderedQuantityInUnits = -1): array|true
    {
        $identity = Router::getRequest()->getAttribute('identity');
        $orderCustomerService = new OrderCustomerService();

        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $initialProductId = $productsTable->getCompositeProductIdAndAttributeId($productId, $attributeId);

        if ($amount == 0 || $amount < - 1 || $amount > Configure::read('app.maxProductAmountForCart')) {
            $message = __('The_desired_amount_{0}_is_not_valid.', ['<b>' . $amount . '</b>']);
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ];
        }

        // get product data from database
        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => (int) $productId
            ],
            contain: [
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
        )
        ->first();

        $existingCartProduct = $identity->getProduct($initialProductId);

        if (empty($product)) {
            $message = __('Product_with_id_{0}_does_not_exist.', [$productId]);
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId,
            ];
        }

        $combinedAmount = $amount;
        if ($existingCartProduct) {
            $combinedAmount = $existingCartProduct['amount'] + $amount;
        }

        $productQuantityService = new ProductQuantityService();
        $isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($product, $product->unit_product);
        if ($isAmountBasedOnQuantityInUnits) {
            if ($orderedQuantityInUnits == -1 && !$orderCustomerService->isSelfServiceMode()) {
                $orderedQuantityInUnits = $product->unit_product->quantity_in_units * $amount;
            }
            $combinedAmount = $productQuantityService->getCombinedAmount($existingCartProduct, $orderedQuantityInUnits);
        }

        $product->next_delivery_day = (new DeliveryRhythmService())->getNextDeliveryDayForProduct($product, $orderCustomerService);

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
            $isAmountBasedOnQuantityInUnits ? $product->unit_product->name : '',
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

        $cartsTable = TableRegistry::getTableLocator()->get('Carts');
        $prices = $cartsTable->getPricesRespectingPricePerUnit(
            $price,
            $unitObject,
            $amount,
            $orderedQuantityInUnits == -1 ? null : $orderedQuantityInUnits,
            $depositObject,
            $product->tax->rate ?? 0,
        );

        $result = $this->validateMinimalCreditBalance($prices['gross_with_deposit'], $orderCustomerService);
        if ($result !== true) {
            return [
                'status' => 0,
                'msg' => $result,
                'productId' => $initialProductId,
            ];
        }

        // check if passed optional product/attribute relation exists
        $attributeIdFound = false;
        if ($attributeId > 0) {
            foreach ($product->product_attributes as $attribute) {
                if ($attribute->id_product_attribute == $attributeId) {

                    $attributeIdFound = true;

                    $isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($product, $attribute->unit_product_attribute);
                    if ($isAmountBasedOnQuantityInUnits) {
                        if ($orderedQuantityInUnits == -1 && !$orderCustomerService->isSelfServiceMode()) {
                            $orderedQuantityInUnits = $attribute->unit_product_attribute->quantity_in_units * $amount;
                        }
                        $combinedAmount = $productQuantityService->getCombinedAmount($existingCartProduct, $orderedQuantityInUnits);
                    }

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
                        $isAmountBasedOnQuantityInUnits ? $attribute->unit_product_attribute->name : '',
                    );
                    if ($message !== true && $amount > 0) {
                        return [
                            'status' => 0,
                            'msg' => $message,
                            'productId' => $initialProductId
                        ];
                    }

                    $result = $this->validateQuantityInUnitsForSelfServiceMode($orderCustomerService, $attribute, 'unit_product_attribute', $orderedQuantityInUnits);
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
            $orderCustomerService,
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
            $orderCustomerService,
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

        $message = $this->isGlobalDeliveryBreakEnabled($orderCustomerService, $product->next_delivery_day, $product->name);
        if ($message !== true) {
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId,
            ];
        }

        if (!$attributeIdFound) {
            $result = $this->validateQuantityInUnitsForSelfServiceMode($orderCustomerService, $product, 'unit_product', $orderedQuantityInUnits);
            if ($result !== true) {
                return [
                    'status' => 0,
                    'msg' => $result,
                    'productId' => $initialProductId
                ];
            }
        }

        $message = $this->hasProductDeliveryRhythmTriggeredDeliveryBreak($orderCustomerService, $product->next_delivery_day, $product->name);
        if ($message !== true) {
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId,
            ];
        }

        // update amount if cart product already exists
        $cart = $identity->getCart();
        $identity->setCart($cart);

        $amountForDatabase = $combinedAmount;
        if ($isAmountBasedOnQuantityInUnits) {
            $amountForDatabase = $amount;
            if ($existingCartProduct) {
                $amountForDatabase = $existingCartProduct['amount'] + $amount;
            }
            if ($orderCustomerService->isSelfServiceMode()) {
                $amountForDatabase = 1;
            }
        }

        $cartProduct2save = [
            'id_product' => $productId,
            'amount' => $amountForDatabase,
            'id_product_attribute' => $attributeId,
            'id_cart' => $cart['Cart']['id_cart'],
        ];

        $options = [];
        if ($orderedQuantityInUnits > 0) {
            if ($existingCartProduct && !is_null($existingCartProduct['orderedQuantityInUnits'])) {
                $orderedQuantityInUnits += $existingCartProduct['orderedQuantityInUnits'];
            }
            $cartProduct2save['cart_product_unit'] = [
                'ordered_quantity_in_units' => $orderedQuantityInUnits,
            ];
            $options = [
                'associated' => [
                    'CartProductUnits',
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

    public function setPickupDays($cartProducts, $customerId, $orderCustomerService): array
    {
        $pickupDaysTable = TableRegistry::getTableLocator()->get('PickupDays');
        foreach($cartProducts as &$cartProduct) {
            $cartProduct->pickup_day = (new DeliveryRhythmService())->getNextDeliveryDayForProduct($cartProduct->product, $orderCustomerService);
        }

        $pickupDays = [];
        $uniquePickupDays = $pickupDaysTable->getUniquePickupDays($cartProducts);
        if (!empty($uniquePickupDays)) {
            $pickupDays = $pickupDaysTable->find('all',
                conditions: [
                    $pickupDaysTable->aliasField('customer_id') => $customerId,
                    $pickupDaysTable->aliasField('pickup_day IN') => $uniquePickupDays,
                ],
                order: [
                    $pickupDaysTable->aliasField('pickup_day') => 'ASC',
                ],
            );

            $existingPickupDays = [];
            foreach($pickupDays->all()->extract('pickup_day')->toArray() as $p) {
                $existingPickupDays[] = $p->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
            }
            $missingPickupDays = array_diff($uniquePickupDays, $existingPickupDays);
            $pickupDays = $pickupDays->toArray();

            if (!empty($missingPickupDays)) {
                foreach($missingPickupDays as $missingPickupDay) {
                    $pickupDays[] = $pickupDaysTable->newEntity([
                        'customer_id' => $customerId,
                        'pickup_day' => $missingPickupDay
                    ]);
                }
            }
        }
        return $pickupDays;
    }

    public function removeAll($cartId, $customerId): int
    {
        $cartId = (int) $cartId;
        if (!$cartId > 0) {
            throw new \Exception('wrong cartId: ' . $cartId);
        }
        // deleteAll cannot check associations
        $cartsTable = TableRegistry::getTableLocator()->get('Carts');
        $cart = $cartsTable->find('all',
            conditions: [
                'Carts.id_cart' => $cartId,
                'Carts.id_customer' => $customerId,
            ]
        )->first();
        if (empty($cart)) {
            throw new \Exception('no cart found for cartId: ' . $cartId . ' and customerId: ' . $customerId);
        }
        $cartProduct2remove = [
            $this->aliasField('id_cart') => $cartId,
        ];
        return $this->deleteAll($cartProduct2remove);
    }

    public function remove($productId, $attributeId, $cartId): int
    {
        $cartId = (int) $cartId;
        if (!$cartId > 0) {
            throw new \Exception('wrong cartId: ' . $cartId);
        }
        $cartProduct2remove = [
            $this->aliasField('id_product') => $productId,
            $this->aliasField('id_product_attribute') => $attributeId,
            $this->aliasField('id_cart') => $cartId,
        ];

        // if product attribute was deleted after adding product to cart,
        // remove product without check for product_attribute_id so that the cart can be emptied!
        $cartProducts = $this->find('all', conditions: $cartProduct2remove)->first();

        if (empty($cartProducts)) {
            unset($cartProduct2remove['CartProducts.id_product_attribute']);
        }

        $result = $this->deleteAll($cartProduct2remove);
        $cartProductUnitsTable = TableRegistry::getTableLocator()->get('CartProductUnits');
        $result += $cartProductUnitsTable->deleteAll(['id_cart_product' => $cartProducts->id_cart_product]);

        return $result;
    }
}
