<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Test\TestCase;

use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;

abstract class OrderDetailsControllerTestCase extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public $Manufacturer;

    public $productIdA = 346;
    public $productIdB = 340;
    public $productIdC = '60-10';

    public $orderDetailIdA = 1;
    public $orderDetailIdB = 2;
    public $orderDetailIdC = 3;

    public function setUp(): void
    {
        parent::setUp();
        $this->Cart = $this->getTableLocator()->get('Carts');
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
    }

    protected function prepareTimebasedCurrencyCart()
    {
        $reducedMaxPercentage = 15;
        $this->prepareTimebasedCurrencyConfiguration($reducedMaxPercentage);
        $this->loginAsSuperadmin();
        $this->addProductToCart(344, 1);
        $this->addProductToCart(346, 2);
        $this->finishCart(1, 1, '', '352');
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cart = $this->getCartById($cartId);
        return $cart;
    }

    protected function simulateSendOrderListsCronjob($orderDetailId)
    {
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get($orderDetailId),
                [
                    'order_state' => ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER
                ]
            )
        );
    }

    protected function assertTimebasedCurrencyOrderDetail($changedOrderDetail, $moneyExcl, $moneyIncl, $seconds)
    {
        $this->assertEquals($changedOrderDetail->timebased_currency_order_detail->money_excl, $moneyExcl);
        $this->assertEquals($changedOrderDetail->timebased_currency_order_detail->money_incl, $moneyIncl);
        $this->assertEquals($changedOrderDetail->timebased_currency_order_detail->seconds, $seconds);
    }

    protected function getChangedMockCartFromDatabase()
    {
        if (!$this->mockCart) {
            return false;
        }
        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => $this->mockCart->id_cart
            ],
            'contain' => [
                'CartProducts.OrderDetails.OrderDetailUnits',
                'CartProducts.OrderDetails.OrderDetailTaxes',
            ]
        ])->first();
        return $cart;
    }

    protected function assertChangedStockAvailable($productIds, $expectedAmount)
    {
        $this->Product = $this->getTableLocator()->get('Products');
        $ids = $this->Product->getProductIdAndAttributeId($productIds);
        $this->StockAvailable = $this->getTableLocator()->get('StockAvailables');
        $changedStockAvailable = $this->StockAvailable->find('all', [
            'conditions' => [
                'StockAvailables.id_product' => $ids['productId'],
                'StockAvailables.id_product_attribute' => $ids['attributeId'],
            ]
        ])->first();
        $quantity = $changedStockAvailable->quantity;
        $this->assertEquals($expectedAmount, $quantity, 'amount was not corrected properly');
    }

    protected function getOrderDetailsFromDatabase($orderDetailIds) {
        $orderDetails = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail IN' => $orderDetailIds
            ],
            'contain' => [
                'OrderDetailTaxes',
                'OrderDetailUnits',
                'TimebasedCurrencyOrderDetails'
            ]
        ])->toArray();
        return $orderDetails;
    }

    /**
     * @return array $order
     */
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
