<?php
/**
 * Cart
 *
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
class Cart extends AppModel
{

    public $primaryKey = 'id_cart';

    public $actsAs = array(
        'Content'
    );

    public $belongsTo = array(
        'Customer' => array(
            'foreignKey' => 'id_customer'
        )
    );

    public $hasMany = array(
        'CartProducts' => array(
            'foreignKey' => 'id_cart'
        )
    );

    public function getProductNameWithUnity($productName, $unity)
    {
        return $productName . ($unity != '' ? ' : ' . $unity : '');
    }

    public function getCart($customerId)
    {
        $this->recursive = - 1;
        $cart = $this->find('first', array(
            'conditions' => array(
                'Cart.status' => APP_ON,
                'Cart.id_customer' => $customerId
            )
        ));
        if (empty($cart)) {
            $this->id = null;
            $cart2save = array(
                'id_customer' => $customerId
            );
            $cart = $this->save($cart2save);
        }

        $ccp = ClassRegistry::init('CartProduct');
        $ccp->recursive = 3;
        $cartProducts = $ccp->find('all', array(
            'conditions' => array(
                'CartProduct.id_cart' => $cart['Cart']['id_cart']
            ),
            'order' => array(
                'ProductLang.name' => 'ASC'
            )
        ));

        $preparedCart = array(
            'Cart' => $cart['Cart'],
            'CartProducts' => array()
        );
        foreach ($cartProducts as &$cartProduct) {
            $manufacturerLink = Configure::read('htmlHelper')->link($cartProduct['Product']['Manufacturer']['name'], Configure::read('slugHelper')->getManufacturerDetail($cartProduct['Product']['Manufacturer']['id_manufacturer'], $cartProduct['Product']['Manufacturer']['name']));

            $imageId = 0;
            if (!empty($cartProduct['Product']['Image'])) {
                $imageId = $cartProduct['Product']['Image']['id_image'];
            }

            $productImage = Configure::read('htmlHelper')->image(Configure::read('htmlHelper')->getProductImageSrc($imageId, 'home'));
            $productLink = Configure::read('htmlHelper')->link(
                $cartProduct['ProductLang']['name'],
                Configure::read('slugHelper')->getProductDetail(
                    $cartProduct['CartProduct']['id_product'],
                    $cartProduct['ProductLang']['name']
                ),
                array('class' => 'product-name')
            );

            if (isset($cartProduct['ProductAttribute']['ProductAttributeCombination'])) {
                // attribute
                $preparedCart['CartProducts'][] = array(
                    'cartProductId' => $cartProduct['CartProduct']['id_cart_product'],
                    'productId' => $cartProduct['CartProduct']['id_product'] . '-' . $cartProduct['CartProduct']['id_product_attribute'],
                    'productName' => $cartProduct['ProductLang']['name'],
                    'productLink' => $productLink,
                    'unity' => $cartProduct['ProductAttribute']['ProductAttributeCombination']['Attribute']['name'],
                    'amount' => $cartProduct['CartProduct']['amount'],
                    'manufacturerId' => $cartProduct['Product']['id_manufacturer'],
                    'manufacturerLink' => $manufacturerLink,
                    'manufacturerName' => $cartProduct['Product']['Manufacturer']['name'],
                    'image' => $productImage,
                    'deposit' => isset($cartProduct['ProductAttribute']['DepositProductAttribute']['deposit']) ? $cartProduct['ProductAttribute']['DepositProductAttribute']['deposit'] * $cartProduct['CartProduct']['amount'] : 0, // * 1 to convert to float
                    'price' => $ccp->Product->getGrossPrice($cartProduct['Product']['id_product'], $cartProduct['ProductAttribute']['ProductAttributeShop']['price']) * $cartProduct['CartProduct']['amount'],
                    'priceExcl' => $cartProduct['ProductAttribute']['ProductAttributeShop']['price'] * $cartProduct['CartProduct']['amount'],
                    'tax' => $ccp->Product->getUnitTax(
                        $ccp->Product->getGrossPrice(
                            $cartProduct['Product']['id_product'],
                            $cartProduct['ProductAttribute']['ProductAttributeShop']['price']
                        ) * $cartProduct['CartProduct']['amount'],
                        $cartProduct['ProductAttribute']['ProductAttributeShop']['price'],
                        $cartProduct['CartProduct']['amount']
                    ) * $cartProduct['CartProduct']['amount']
                );
            } else {
                // no attribute
                $preparedCart['CartProducts'][] = array(
                    'cartProductId' => $cartProduct['CartProduct']['id_cart_product'],
                    'productId' => $cartProduct['CartProduct']['id_product'],
                    'productName' => $cartProduct['ProductLang']['name'],
                    'productLink' => $productLink,
                    'unity' => $cartProduct['Product']['ProductLang']['unity'],
                    'amount' => $cartProduct['CartProduct']['amount'],
                    'manufacturerId' => $cartProduct['Product']['id_manufacturer'],
                    'manufacturerLink' => $manufacturerLink,
                    'manufacturerName' => $cartProduct['Product']['Manufacturer']['name'],
                    'image' => $productImage,
                    'deposit' => isset($cartProduct['Product']['DepositProduct']['deposit']) ? $cartProduct['Product']['DepositProduct']['deposit'] * $cartProduct['CartProduct']['amount'] : 0,
                    'price' => $ccp->Product->getGrossPrice($cartProduct['Product']['id_product'], $cartProduct['Product']['ProductShop']['price']) * $cartProduct['CartProduct']['amount'],
                    'priceExcl' => $cartProduct['Product']['ProductShop']['price'] * $cartProduct['CartProduct']['amount'],
                    'tax' => $ccp->Product->getUnitTax(
                        $ccp->Product->getGrossPrice(
                            $cartProduct['Product']['id_product'],
                            $cartProduct['Product']['ProductShop']['price']
                        ) * $cartProduct['CartProduct']['amount'],
                        $cartProduct['Product']['ProductShop']['price'],
                        $cartProduct['CartProduct']['amount']
                    ) * $cartProduct['CartProduct']['amount']
                );
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
