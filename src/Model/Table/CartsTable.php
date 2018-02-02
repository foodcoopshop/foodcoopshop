<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

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
class CartsTable extends AppTable
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_cart');
        $this->belongsTo('Customer', [
            'foreignKey' => 'id_customer'
        ]);
        $this->hasMany('CartProducts', [
            'foreignKey' => 'id_cart'
        ]);
    }

    public function getProductNameWithUnity($productName, $unity)
    {
        return $productName . ($unity != '' ? ' : ' . $unity : '');
    }

    public function getCart($customerId)
    {
        $cart = $this->find('all', [
            'conditions' => [
                'Carts.status' => APP_ON,
                'Carts.id_customer' => $customerId
            ]
        ])->first();
        
        if (empty($cart)) {
            $cart2save = [
                'id_customer' => $customerId
            ];
            $cart = $this->save($this->newEntity($cart2save));
        }

        $ccp = TableRegistry::get('CartProducts');
        $cartProducts = $ccp->find('all', [
            'conditions' => [
                'CartProducts.id_cart' => $cart['id_cart']
            ],
            'order' => [
                'ProductLangs.name' => 'ASC'
            ],
            'contain' => [
                'ProductLangs',
                'Products.ProductShops',
                'Products.Manufacturers',
                'Products.DepositProducts',
                'ProductAttributes.ProductAttributeCombinations.Attributes',
                'ProductAttributes.ProductAttributeShops',
                'ProductAttributes.DepositProductAttributes'
            ]
        ])->toArray();

        $preparedCart = [
            'Cart' => $cart,
            'CartProducts' => []
        ];
        foreach ($cartProducts as &$cartProduct) {
            $manufacturerLink = Configure::read('app.htmlHelper')->link($cartProduct->product->manufacturer->name, Configure::read('app.slugHelper')->getManufacturerDetail($cartProduct->product->id_manufacturer, $cartProduct->product->manufacturer->name));
            
            $imageId = 0;
            if (!empty($cartProduct['Products']['Images'])) {
                $imageId = $cartProduct['Products']['Images']['id_image'];
            }

            $productImage = Configure::read('app.htmlHelper')->image(Configure::read('app.htmlHelper')->getProductImageSrc($imageId, 'home'));
            $productLink = Configure::read('app.htmlHelper')->link(
                $cartProduct->product_lang->name,
                Configure::read('app.slugHelper')->getProductDetail(
                    $cartProduct->id_product,
                    $cartProduct->product_lang->name
                ),
                ['class' => 'product-name']
            );

            if (!empty($cartProduct->product_attribute->product_attribute_combination)) {
                // attribute
                $preparedCart['CartProducts'][] = [
                    'cartProductId' => $cartProduct->id_cart_product,
                    'productId' => $cartProduct->id_product . '-' . $cartProduct->id_product_attribute,
                    'productName' => $cartProduct->product_lang->name,
                    'productLink' => $productLink,
                    'unity' => $cartProduct->product_attribute->product_attribute_combination->attribute->name,
                    'amount' => $cartProduct->amount,
                    'manufacturerId' => $cartProduct->product->id_manufacturer,
                    'manufacturerLink' => $manufacturerLink,
                    'manufacturerName' => $cartProduct->product->manufacturer->name,
                    'image' => $productImage,
                    'deposit' => !empty($cartProduct->product_attribute->deposit_product_attribute->deposit) ? $cartProduct->product_attribute->deposit_product_attribute->deposit * $cartProduct->amount : 0, // * 1 to convert to float
                    'price' => $ccp->Products->getGrossPrice($cartProduct->id_product, $cartProduct->product_attribute->product_attribute_shop->price) * $cartProduct->amount,
                    'priceExcl' => $cartProduct->product_attribute->product_attribute_shop->price * $cartProduct->amount,
                    'tax' => $ccp->Products->getUnitTax(
                        $ccp->Products->getGrossPrice(
                            $cartProduct->id_product,
                            $cartProduct->product_attribute->product_attribute_shop->price
                        ) * $cartProduct->amount,
                        $cartProduct->product_attribute->product_attribute_shop->price,
                        $cartProduct->amount
                    ) * $cartProduct->amount
                ];
            } else {
                // no attribute
                $preparedCart['CartProducts'][] = [
                    'cartProductId' => $cartProduct->id_cart_product,
                    'productId' => $cartProduct->id_product,
                    'productName' => $cartProduct->product_lang->name,
                    'productLink' => $productLink,
                    'unity' => $cartProduct->product_lang->unity,
                    'amount' => $cartProduct->amount,
                    'manufacturerId' => $cartProduct->product->id_manufacturer,
                    'manufacturerLink' => $manufacturerLink,
                    'manufacturerName' => $cartProduct->product->manufacturer->name,
                    'image' => $productImage,
                    'deposit' => !empty($cartProduct->product->deposit_product->deposit) ? $cartProduct->product->deposit_product->deposit * $cartProduct->amount : 0,
                    'price' => $ccp->Products->getGrossPrice($cartProduct->id_product, $cartProduct->product->product_shop->price) * $cartProduct->amount,
                    'priceExcl' => $cartProduct->product->product_shop->price * $cartProduct->amount,
                    'tax' => $ccp->Products->getUnitTax(
                        $ccp->Products->getGrossPrice(
                            $cartProduct->id_product,
                            $cartProduct->product->product_shop->price
                        ) * $cartProduct->amount,
                        $cartProduct->product->product_shop->price,
                        $cartProduct->amount
                    ) * $cartProduct->amount
                ];
            }
        }

        // sum up deposits and products
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
}
