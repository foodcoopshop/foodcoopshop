<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use App\Model\Entity\Cart;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CartProductsTableTest extends AppCakeTestCase
{

    public function testRemoveAllWithWrongCartId(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('wrong cartId: 0');
        $cartProductsTable = $this->getTableLocator()->get('CartProducts');
        $cartProductsTable->removeAll('bla', Configure::read('test.superadminId'));
    }

    public function testRemoveAllWithCorrectCartIdAndWrongCustomerId(): void
    {
        $cartId = 1;
        $customerId = Configure::read('test.adminId');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('no cart found for cartId: 1 and customerId: 88');
        $cartProductsTable = $this->getTableLocator()->get('CartProducts');
        $cartProductsTable->removeAll($cartId, $customerId);
    }

    public function testRemoveAllWithCorrectCartIdAndCorrectCustomerId(): void
    {
        $cartId = 1;
        $customerId = Configure::read('test.superadminId');
        $cartProductsTable = $this->getTableLocator()->get('CartProducts');
        $cartProductsTable->removeAll($cartId, $customerId);
        $cart = $this->getCartWithCartProducts($cartId, $customerId);
        $this->assertEmpty($cart->cart_products, 'cart products not empty');
    }

    private function getCartWithCartProducts($cartId, $customerId): Cart
    {
        $cartsTable = $this->getTableLocator()->get('Carts');
        $cart = $cartsTable->find('all',
            conditions: [
                'Carts.id_cart' => $cartId,
                'Carts.id_customer' => $customerId
            ],
            contain: [
                'CartProducts'
            ]
        )->first();
        return $cart;
    }

}
