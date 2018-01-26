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
        $this->hasMany('CakeProducts', [
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
            $this->id = null;
            $cart2save = [
                'id_customer' => $customerId
            ];
            $cart = $this->save($cart2save);
        }

        $ccp = TableRegistry::get('CartProducts');
        $cartProducts = $ccp->find('all', [
            'conditions' => [
                'CartProducts.id_cart' => $cart['Cart']['id_cart']
            ],
            'order' => [
                'ProductLangs.name' => 'ASC'
            ],
            'contain' => [
                'ProductLangs'
            ]
        ])->toArray();

        $preparedCart = [
            'Cart' => $cart['Cart'],
            'CartProducts' => []
        ];
        foreach ($cartProducts as &$cartProduct) {
            $manufacturerLink = Configure::read('app.htmlHelper')->link($cartProduct['Products']['Manufacturers']['name'], Configure::read('app.slugHelper')->getManufacturerDetail($cartProduct['Products']['Manufacturers']['id_manufacturer'], $cartProduct['Products']['Manufacturers']['name']));

            $imageId = 0;
            if (!empty($cartProduct['Products']['Images'])) {
                $imageId = $cartProduct['Products']['Images']['id_image'];
            }

            $productImage = Configure::read('app.htmlHelper')->image(Configure::read('app.htmlHelper')->getProductImageSrc($imageId, 'home'));
            $productLink = Configure::read('app.htmlHelper')->link(
                $cartProduct['ProductLangs']['name'],
                Configure::read('app.slugHelper')->getProductDetail(
                    $cartProduct['CartProducts']['id_product'],
                    $cartProduct['ProductLangs']['name']
                ),
                ['class' => 'product-name']
            );

            if (isset($cartProduct['ProductAttributes']['ProductAttributeCombinations'])) {
                // attribute
                $preparedCart['CartProducts'][] = [
                    'cartProductId' => $cartProduct['CartProducts']['id_cart_product'],
                    'productId' => $cartProduct['CartProducts']['id_product'] . '-' . $cartProduct['CartProducts']['id_product_attribute'],
                    'productName' => $cartProduct['ProductLangs']['name'],
                    'productLink' => $productLink,
                    'unity' => $cartProduct['ProductAttributes']['ProductAttributeCombinations']['Attributes']['name'],
                    'amount' => $cartProduct['CartProducts']['amount'],
                    'manufacturerId' => $cartProduct['Products']['id_manufacturer'],
                    'manufacturerLink' => $manufacturerLink,
                    'manufacturerName' => $cartProduct['Products']['Manufacturers']['name'],
                    'image' => $productImage,
                    'deposit' => isset($cartProduct['ProductAttributes']['DepositProductAttribute']['deposit']) ? $cartProduct['ProductAttributes']['DepositProductAttribute']['deposit'] * $cartProduct['CartProducts']['amount'] : 0, // * 1 to convert to float
                    'price' => $ccp->Product->getGrossPrice($cartProduct['Products']['id_product'], $cartProduct['ProductAttributes']['ProductAttributeShops']['price']) * $cartProduct['CartProducts']['amount'],
                    'priceExcl' => $cartProduct['ProductAttributes']['ProductAttributeShops']['price'] * $cartProduct['CartProducts']['amount'],
                    'tax' => $ccp->Product->getUnitTax(
                        $ccp->Product->getGrossPrice(
                            $cartProduct['Products']['id_product'],
                            $cartProduct['ProductAttributes']['ProductAttributeShops']['price']
                        ) * $cartProduct['CartProducts']['amount'],
                        $cartProduct['ProductAttributes']['ProductAttributeShops']['price'],
                        $cartProduct['CartProducts']['amount']
                    ) * $cartProduct['CartProducts']['amount']
                ];
            } else {
                // no attribute
                $preparedCart['CartProducts'][] = [
                    'cartProductId' => $cartProduct['CartProducts']['id_cart_product'],
                    'productId' => $cartProduct['CartProducts']['id_product'],
                    'productName' => $cartProduct['ProductLangs']['name'],
                    'productLink' => $productLink,
                    'unity' => $cartProduct['Products']['ProductLangs']['unity'],
                    'amount' => $cartProduct['CartProducts']['amount'],
                    'manufacturerId' => $cartProduct['Products']['id_manufacturer'],
                    'manufacturerLink' => $manufacturerLink,
                    'manufacturerName' => $cartProduct['Products']['Manufacturers']['name'],
                    'image' => $productImage,
                    'deposit' => isset($cartProduct['Products']['DepositProduct']['deposit']) ? $cartProduct['Products']['DepositProduct']['deposit'] * $cartProduct['CartProducts']['amount'] : 0,
                    'price' => $ccp->Product->getGrossPrice($cartProduct['Products']['id_product'], $cartProduct['Products']['ProductShop']['price']) * $cartProduct['CartProducts']['amount'],
                    'priceExcl' => $cartProduct['Products']['ProductShop']['price'] * $cartProduct['CartProducts']['amount'],
                    'tax' => $ccp->Product->getUnitTax(
                        $ccp->Product->getGrossPrice(
                            $cartProduct['Products']['id_product'],
                            $cartProduct['Products']['ProductShop']['price']
                        ) * $cartProduct['CartProducts']['amount'],
                        $cartProduct['Products']['ProductShop']['price'],
                        $cartProduct['CartProducts']['amount']
                    ) * $cartProduct['CartProducts']['amount']
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
