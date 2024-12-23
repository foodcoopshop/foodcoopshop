<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Test\TestCase;

use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use App\Model\Entity\OrderDetail;

abstract class OrderDetailsControllerTestCase extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public $productIdA = 346;
    public $productIdB = 340;
    public $productIdC = '60-10';

    public $orderDetailIdA = 1;
    public $orderDetailIdB = 2;
    public $orderDetailIdC = 3;

    protected $mockCart;
    protected $Product;
    protected $StockAvailable;

    protected function simulateSendOrderListsCronjob($orderDetailId)
    {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get($orderDetailId),
                [
                    'order_state' => OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER,
                ]
            )
        );
    }

    protected function getChangedMockCartFromDatabase()
    {
        if (!$this->mockCart) {
            return false;
        }
        $cartsTable = $this->getTableLocator()->get('Carts');
        $cart = $cartsTable->find('all',
            conditions: [
                'Carts.id_cart' => $this->mockCart->id_cart,
            ],
            contain: [
                'CartProducts.OrderDetails.OrderDetailUnits',
            ],
        )->first();
        return $cart;
    }

    protected function assertChangedStockAvailable($productIds, $expectedAmount)
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable = $this->getTableLocator()->get('Products');
        $ids = $productsTable->getProductIdAndAttributeId($productIds);
        $stockAvailablesTable = $this->getTableLocator()->get('StockAvailables');
        $changedStockAvailable = $stockAvailablesTable->find('all', conditions: [
            'StockAvailables.id_product' => $ids['productId'],
            'StockAvailables.id_product_attribute' => $ids['attributeId'],
        ])->first();
        $quantity = $changedStockAvailable->quantity;
        $this->assertEquals($expectedAmount, $quantity, 'amount was not corrected properly');
    }

    protected function getOrderDetailsFromDatabase($orderDetailIds) {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $orderDetailsTable->find('all',
            conditions: [
                'OrderDetails.id_order_detail IN' => $orderDetailIds,
            ],
            contain: [
                'OrderDetailUnits',
            ],
        )->toArray();
        return $orderDetails;
    }

    protected function generateAndGetCart($productAAmount = 1, $productBAmount = 1)
    {
        $this->addProductToCart($this->productIdA, $productAAmount);
        $this->addProductToCart($this->productIdB, $productBAmount);
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cart = $this->getCartById($cartId);
        return $cart;
    }

}
