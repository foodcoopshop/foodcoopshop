<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\TestCase\OrderDetailsControllerTestCase;

class OrderDetailsControllerEditNameTest extends OrderDetailsControllerTestCase
{

    public function testEditOrderDetailNameNotValid(): void
    {
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart(1, 2);
        $this->editOrderDetailName($this->mockCart->cart_products[1]->order_detail->id_order_detail, '');
        $this->assertEquals($this->getJsonDecodedContent()->msg, 'Bitte gib einen Namen ein.');
    }

    public function testEditOrderDetailNameOk(): void
    {
        $this->loginAsSuperadmin();
        $newName = 'new name';
        $this->mockCart = $this->generateAndGetCart(1, 2);
        $this->editOrderDetailName($this->mockCart->cart_products[1]->order_detail->id_order_detail, $newName);
        $changedOrder = $this->getChangedMockCartFromDatabase();
        $this->assertEquals($newName, $changedOrder->cart_products[1]->order_detail->product_name);
    }

    private function editOrderDetailName($orderDetailId, $productName): void
    {
        $this->ajaxPost(
            '/admin/order-details/editProductName/',
            [
                'orderDetailId' => $orderDetailId,
                'productName' => $productName,
            ]
        );
    }
}