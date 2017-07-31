<?php
/**
 * CakeCart
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
class CakeCart extends AppModel
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
        'CakeCartProducts' => array(
            'foreignKey' => 'id_cart'
        )
    );

    public function getProductNameWithUnity($productName, $unity)
    {
        return $productName . ($unity != '' ? ' : ' . $unity : '');
    }

    public function getCakeCart($customerId)
    {
        $this->recursive = - 1;
        $cakeCart = $this->find('first', array(
            'conditions' => array(
                'CakeCart.status' => APP_ON,
                'CakeCart.id_customer' => $customerId
            )
        ));
        if (empty($cakeCart)) {
            $this->id = null;
            $cart2save = array(
                'id_customer' => $customerId
            );
            $cakeCart = $this->save($cart2save);
        }

        $ccp = ClassRegistry::init('CakeCartProduct');
        $ccp->recursive = 3;
        $cakeCartProducts = $ccp->find('all', array(
            'conditions' => array(
                'CakeCartProduct.id_cart' => $cakeCart['CakeCart']['id_cart']
            ),
            'order' => array(
                'ProductLang.name' => 'ASC'
            )
        ));

        $preparedCart = array(
            'CakeCart' => $cakeCart['CakeCart'],
            'CakeCartProducts' => array()
        );
        foreach ($cakeCartProducts as &$cartProduct) {
            $manufacturerLink = Configure::read('htmlHelper')->link($cartProduct['Product']['Manufacturer']['name'], Configure::read('slugHelper')->getManufacturerDetail($cartProduct['Product']['Manufacturer']['id_manufacturer'], $cartProduct['Product']['Manufacturer']['name']));

            $imageId = 0;
            $imageLegend = '';
            if (isset($cartProduct['Product']['ImageShop']['ImageLang'])) {
                $imageId = $cartProduct['Product']['ImageShop']['ImageLang']['id_image'];
                $imageLegend = $cartProduct['Product']['ImageShop']['ImageLang']['legend'];
            }

            $productImage = Configure::read('htmlHelper')->image(Configure::read('htmlHelper')->getProductImageSrc($imageId, $imageLegend, 'home'));
            $productLink = Configure::read('htmlHelper')->link(
                $cartProduct['ProductLang']['name'],
                Configure::read('slugHelper')->getProductDetail(
                    $cartProduct['CakeCartProduct']['id_product'],
                    $cartProduct['ProductLang']['name']
                ),
                array('class' => 'product-name')
            );

            if (isset($cartProduct['ProductAttribute']['ProductAttributeCombination'])) {
                // attribute
                $preparedCart['CakeCartProducts'][] = array(
                    'cakeCartProductId' => $cartProduct['CakeCartProduct']['id_cart_product'],
                    'productId' => $cartProduct['CakeCartProduct']['id_product'] . '-' . $cartProduct['CakeCartProduct']['id_product_attribute'],
                    'productName' => $cartProduct['ProductLang']['name'],
                    'productLink' => $productLink,
                    'unity' => $cartProduct['ProductAttribute']['ProductAttributeCombination']['AttributeLang']['name'],
                    'amount' => $cartProduct['CakeCartProduct']['amount'],
                    'manufacturerId' => $cartProduct['Product']['id_manufacturer'],
                    'manufacturerLink' => $manufacturerLink,
                    'manufacturerName' => $cartProduct['Product']['Manufacturer']['name'],
                    'image' => $productImage,
                    'deposit' => isset($cartProduct['ProductAttribute']['CakeDepositProductAttribute']['deposit']) ? $cartProduct['ProductAttribute']['CakeDepositProductAttribute']['deposit'] * $cartProduct['CakeCartProduct']['amount'] : 0, // * 1 to convert to float
                    'price' => $ccp->Product->getGrossPrice($cartProduct['Product']['id_product'], $cartProduct['ProductAttribute']['ProductAttributeShop']['price']) * $cartProduct['CakeCartProduct']['amount'],
                    'priceExcl' => $cartProduct['ProductAttribute']['ProductAttributeShop']['price'] * $cartProduct['CakeCartProduct']['amount'],
                    'tax' => $ccp->Product->getUnitTax(
                        $ccp->Product->getGrossPrice(
                            $cartProduct['Product']['id_product'],
                            $cartProduct['ProductAttribute']['ProductAttributeShop']['price']
                        ) * $cartProduct['CakeCartProduct']['amount'],
                        $cartProduct['ProductAttribute']['ProductAttributeShop']['price'],
                        $cartProduct['CakeCartProduct']['amount']
                    ) * $cartProduct['CakeCartProduct']['amount']
                );
            } else {
                // no attribute
                $preparedCart['CakeCartProducts'][] = array(
                    'cakeCartProductId' => $cartProduct['CakeCartProduct']['id_cart_product'],
                    'productId' => $cartProduct['CakeCartProduct']['id_product'],
                    'productName' => $cartProduct['ProductLang']['name'],
                    'productLink' => $productLink,
                    'unity' => $cartProduct['Product']['ProductLang']['unity'],
                    'amount' => $cartProduct['CakeCartProduct']['amount'],
                    'manufacturerId' => $cartProduct['Product']['id_manufacturer'],
                    'manufacturerLink' => $manufacturerLink,
                    'manufacturerName' => $cartProduct['Product']['Manufacturer']['name'],
                    'image' => $productImage,
                    'deposit' => isset($cartProduct['Product']['CakeDepositProduct']['deposit']) ? $cartProduct['Product']['CakeDepositProduct']['deposit'] * $cartProduct['CakeCartProduct']['amount'] : 0,
                    'price' => $ccp->Product->getGrossPrice($cartProduct['Product']['id_product'], $cartProduct['Product']['ProductShop']['price']) * $cartProduct['CakeCartProduct']['amount'],
                    'priceExcl' => $cartProduct['Product']['ProductShop']['price'] * $cartProduct['CakeCartProduct']['amount'],
                    'tax' => $ccp->Product->getUnitTax(
                        $ccp->Product->getGrossPrice(
                            $cartProduct['Product']['id_product'],
                            $cartProduct['Product']['ProductShop']['price']
                        ) * $cartProduct['CakeCartProduct']['amount'],
                        $cartProduct['Product']['ProductShop']['price'],
                        $cartProduct['CakeCartProduct']['amount']
                    ) * $cartProduct['CakeCartProduct']['amount']
                );
            }
        }

        // sum up deposits and products
        $preparedCart['CakeCartDepositSum'] = 0;
        $preparedCart['CakeCartProductSum'] = 0;
        $preparedCart['CakeCartProductSumExcl'] = 0;
        $preparedCart['CakeCartTaxSum'] = 0;
        foreach ($preparedCart['CakeCartProducts'] as $p) {
            $preparedCart['CakeCartDepositSum'] += $p['deposit'];
            $preparedCart['CakeCartProductSum'] += $p['price'];
            $preparedCart['CakeCartTaxSum'] += $p['tax'];
            $preparedCart['CakeCartProductSumExcl'] += $p['priceExcl'];
        }
        return $preparedCart;
    }
}
