<?php

namespace App\Model\Table;

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
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CartProductsTable extends AppTable
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_cart_product');
        $this->belongsTo('Products', [
            'foreignKey' => 'id_product'
        ]);
        $this->belongsTo('ProductLangs', [
            'foreignKey' => ['id_product']
        ]);
        $this->belongsTo('ProductAttributes', [
            'foreignKey' => 'id_product_attribute'
        ]);
        $this->addBehavior('Timestamp');
    }

    /**
     * @param int $productId
     * @param int $attributeId
     * @param int $amount
     * @return array || boolean
     */
    public function add($appAuth, $productId, $attributeId, $amount)
    {

        $initialProductId = $this->Products->getCompositeProductIdAndAttributeId($productId, $attributeId);

        // allow -1 and 1 - 99
        if ($amount == 0 || $amount < - 1 || $amount > 99) {
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
                'Products.id_product' => $productId
            ],
            'fields' => [
                'is_holiday_active' => '!'.$this->Products->getManufacturerHolidayConditions()
            ],
            'contain' => [
                'Manufacturers',
                'ProductLangs',
                'StockAvailables',
                'ProductAttributes',
                'ProductAttributes.StockAvailables',
                'ProductAttributes.ProductAttributeCombinations.Attributes'
            ]
        ])
        ->select($this->Products)
        ->select($this->Products->ProductLangs)
        ->select($this->Products->StockAvailables)
        ->select($this->Products->Manufacturers)
        ->select($this->Products->ProductAttributes->StockAvailables)
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

        // stock available check for product
        if ($attributeId == 0 && $product->stock_available->quantity < $combinedAmount && $amount > 0) {
            $message = __('The_desired_amount_{0}_of_the_product_{1}_is_not_available_any_more_available_amount_{2}.', ['<b>' . $combinedAmount . '</b>', '<b>' . $product->product_lang->name . '</b>', $product->stock_available->quantity]);
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
                    if ($attribute->stock_available->quantity < $combinedAmount && $amount > 0) {
                        $message = __('The_desired_amount_{0}_of_the_attribute_{1}_of_the_product_{2}_is_not_available_any_more_available_amount_{3}.', ['<b>' . $combinedAmount . '</b>', '<b>' . $attribute->product_attribute_combination->attribute->name . '</b>', '<b>' . $product->product_lang->name . '</b>', $attribute->stock_available->quantity]);

                        return [
                            'status' => 0,
                            'msg' => $message,
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
            $message = __('The_product_{0}_is_not_activated_any_more.', ['<b>' . $product->product_lang->name . '</b>']);
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ];
        }

        if (! $product->manufacturer->active || $product->is_holiday_active) {
            $message = __('The_manufacturer_of_the_product_{0}_is_on_holiday_or_product_is_not_activated.', ['<b>' . $product->product_lang->name . '</b>']);
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
        if ($existingCartProduct) {
            $oldEntity = $this->get($existingCartProduct['cartProductId']);
            $entity = $this->patchEntity($oldEntity, $cartProduct2save);
        } else {
            $entity = $this->newEntity($cartProduct2save);
        }
        $this->save($entity);

        return true;

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
        return $this->deleteAll($cartProduct2remove);
    }
}
