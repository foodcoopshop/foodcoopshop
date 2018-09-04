<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CartProductsTableTest extends AppCakeTestCase
{

    public $CartProduct;
    public $Cart;

    public function setUp()
    {
        parent::setUp();
        $this->CartProduct = TableRegistry::getTableLocator()->get('CartProducts');
        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
    }

    /**
     * @expectedException App\Lib\Error\Exception\InvalidParameterException
     * @expectedExceptionMessage wrong cartId: 0
     */
    public function testRemoveAllWithWrongCartId()
    {
        $this->CartProduct->removeAll('bla', Configure::read('test.superadminId'));
    }

    /**
     * @expectedException App\Lib\Error\Exception\InvalidParameterException
     * @expectedExceptionMessage no cart found for cartId: 1 and customerId: 88
     */
    public function testRemoveAllWithCorrectCartIdAndWrongCustomerId()
    {
        $cartId = 1;
        $customerId = Configure::read('test.adminId');
        $this->CartProduct->removeAll($cartId, $customerId);
    }

    public function testRemoveAllWithCorrectCartIdAndCorrectCustomerId()
    {
        $cartId = 1;
        $customerId = Configure::read('test.superadminId');
        $this->CartProduct->removeAll($cartId, $customerId);
        $cart = $this->getCartWithCartProducts($cartId, $customerId);
        $this->assertEmpty($cart->cart_products, 'cart products not empty');
    }

    private function getCartWithCartProducts($cartId, $customerId)
    {
        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => $cartId,
                'Carts.id_customer' => $customerId
            ],
            'contain' => [
                'CartProducts'
            ]
        ])->first();
        return $cart;
    }

}
