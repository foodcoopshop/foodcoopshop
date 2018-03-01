<?php

/**
 * OrderDetailsControllerTest
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class OrderDetailsControllerTest extends AppCakeTestCase
{

    public $Order;

    public $Manufacturer;

    public $EmailLog;

    public $productId1 = 346;
    public $productId2 = 340;
    
    public $cancellationReason = 'Product was not fresh any more.';

    public $newPrice = '3,53';
    public $editPriceReason = 'Product was smaller than expected.';

    public $newQuantity = 1;
    public $editQuantityReason = 'One product was not delivered.';

    public $mockOrder;
    public $mockOrderId = 1;

    public function setUp()
    {
        parent::setUp();
        $this->Order = TableRegistry::get('Orders');
        $this->EmailLog = TableRegistry::get('EmailLogs');
        $this->Manufacturer = TableRegistry::get('Manufacturers');
    }

    public function testCancellationAsManufacturer()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getMockOrder();
        $this->logout();
        $this->loginAsVegetableManufacturer();

        $this->assertRemoveFromDatabase([$this->mockOrder->order_details[0]->id_order_detail], $this->mockOrder->id_order, 1);
        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 4.545455);
        $this->assertChangedStockAvailable($this->productId1, 98);
    }

    public function testCancellationAsSuperadminWithEnabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getMockOrder();
        $this->assertRemoveFromDatabase([$this->mockOrder->order_details[0]->id_order_detail], $this->mockOrder->id_order, 1);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $weekday = date('N');
        if (in_array($weekday, [3,4,5])) {
            $expectedCcEmails[] = Configure::read('test.loginEmailVegetableManufacturer');
        }

        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 4.545455);
        $this->assertChangedStockAvailable($this->productId1, 98);
    }

    public function testCancellationAsSuperadminWithDisabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getMockOrder();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_deleted_notification', 0);

        $this->assertRemoveFromDatabase([$this->mockOrder->order_details[0]->id_order_detail], $this->mockOrder->id_order, 1);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 4.545455);
        $this->assertChangedStockAvailable($this->productId1, 98);
    }

    public function testCancellationAsSuperadminWithEnabledBulkOrders()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getMockOrder();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'bulk_orders_allowed', 1);

        $this->assertRemoveFromDatabase([$this->mockOrder->order_details[0]->id_order_detail], $this->mockOrder->id_order, 1);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 4.545455);
        $this->assertChangedStockAvailable($this->productId1, 98);
    }
    
    public function testCancellationProductAttributeStockAvailableAsSuperadmin()
    {
        $this->loginAsSuperadmin();
        $this->productId1 = '60-10';
        $this->mockOrder = $this->getMockOrder();
        $this->assertRemoveFromDatabase([$this->mockOrder->order_details[1]->id_order_detail], $this->mockOrder->id_order, 1);
        $this->assertChangedStockAvailable($this->productId1, 20);
    }
    
    public function testEditOrderDetailPriceAsManufacturer()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getMockOrder();
        $this->logout();
        $this->loginAsVegetableManufacturer();
        $this->editOrderDetailPrice($this->mockOrder->order_details[0]->id_order_detail, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newPrice, Configure::read('app.htmlHelper')->formatAsDecimal($changedOrder->order_details[0]->total_price_tax_incl), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails, $expectedCcEmails);
        
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 8.075455);
    }

    public function testEditOrderDetailPriceAsSuperadminWithEnabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getMockOrder();

        $this->editOrderDetailPrice($this->mockOrder->order_details[0]->id_order_detail, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newPrice, Configure::read('app.htmlHelper')->formatAsDecimal($changedOrder->order_details[0]->total_price_tax_incl), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [Configure::read('test.loginEmailVegetableManufacturer')];

        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails, $expectedCcEmails);
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 8.075455);
    }

    public function testEditOrderDetailPriceIfPriceWasZero()
    {
        $this->loginAsSuperadmin();
        $this->changeProductPrice($this->productId1, 0);
        $this->mockOrder = $this->generateAndGetOrder();
        $this->editOrderDetailPrice($this->mockOrder->order_details[0]->id_order_detail, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newPrice, Configure::read('app.htmlHelper')->formatAsDecimal($changedOrder->order_details[0]->total_price_tax_incl), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails(1, $expectedToEmails, $expectedCcEmails);
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 8.075455);
    }

    public function testEditOrderDetailPriceAsSuperadminWithDisabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getMockOrder();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_price_changed_notification', 0);

        $this->editOrderDetailPrice($this->mockOrder->order_details[0]->id_order_detail, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newPrice, Configure::read('app.htmlHelper')->formatAsDecimal($changedOrder->order_details[0]->total_price_tax_incl), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails, $expectedCcEmails);
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 8.075455);
    }

    public function testEditOrderDetailPriceAsSuperadminWithEnabledBulkOrders()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getMockOrder();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'bulk_orders_allowed', 1);

        $this->editOrderDetailPrice($this->mockOrder->order_details[0]->id_order_detail, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newPrice, Configure::read('app.htmlHelper')->formatAsDecimal($changedOrder->order_details[0]->total_price_tax_incl), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails, $expectedCcEmails);
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 8.075455);
    }

    public function testEditOrderDetailQuantityAsManufacturer()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->generateAndGetOrder(1, 2);
        $this->logout();
        $this->loginAsVegetableManufacturer();

        $this->editOrderDetailQuantity($this->mockOrder->order_details[0]->id_order_detail, $this->newQuantity, $this->editQuantityReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newQuantity, $changedOrder->order_details[0]->product_quantity, 'order detail quantity was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductQuantityChangedEmails(1, $expectedToEmails, $expectedCcEmails);
        
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 10.91091);
        $this->assertChangedStockAvailable($this->productId1, 96);
    }

    public function testEditOrderDetailQuantityAsSuperadminWithEnabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->generateAndGetOrder(1, 2);

        $this->editOrderDetailQuantity($this->mockOrder->order_details[0]->id_order_detail, $this->newQuantity, $this->editQuantityReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newQuantity, $changedOrder->order_details[0]->product_quantity, 'order detail quantity was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $weekday = date('N');
        if (in_array($weekday, [3,4,5])) {
            $expectedCcEmails[] = Configure::read('test.loginEmailVegetableManufacturer');
        }
        $this->assertOrderDetailProductQuantityChangedEmails(1, $expectedToEmails, $expectedCcEmails);
        
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 10.91091);
        $this->assertChangedStockAvailable($this->productId1, 96);
    }

    public function testEditOrderDetailQuantityAsSuperadminWithDisabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->generateAndGetOrder(1, 2);
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_quantity_changed_notification', 0);

        $this->editOrderDetailQuantity($this->mockOrder->order_details[0]->id_order_detail, $this->newQuantity, $this->editQuantityReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newQuantity, $changedOrder->order_details[0]->product_quantity, 'order detail quantity was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductQuantityChangedEmails(1, $expectedToEmails, $expectedCcEmails);
        
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 10.91091);
        $this->assertChangedStockAvailable($this->productId1, 96);
    }

    public function testEditOrderDetailQuantityAsSuperadminWithEnabledBulkOrders()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->generateAndGetOrder(1, 2);
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'bulk_orders_allowed', 1);

        $this->editOrderDetailQuantity($this->mockOrder->order_details[0]->id_order_detail, $this->newQuantity, $this->editQuantityReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newQuantity, $changedOrder->order_details[0]->product_quantity, 'order detail quantity was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductQuantityChangedEmails(1, $expectedToEmails, $expectedCcEmails);
        
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 10.91091);
        $this->assertChangedStockAvailable($this->productId1, 96);
    }

    private function getChangedMockOrderFromDatabase()
    {
        if (!$this->mockOrder) {
            return false;
        }

        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $this->mockOrder->id_order
            ],
            'contain' => [
                'OrderDetails'
            ]
        ])->first();
        return $order;
    }

    private function assertRemoveFromDatabase($orderDetailIds, $orderId, $expectedOrderDetailCount)
    {
        $this->deleteOrderDetail($orderDetailIds, $this->cancellationReason);
        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ],
            'contain' => [
                'OrderDetails'
            ]
        ])->first();
        $this->assertEquals($expectedOrderDetailCount, count($order->order_details), 'order detail was not deleted properly');
    }
    
    private function getMockOrder()
    {
        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $this->mockOrderId
            ],
            'contain' => [
                'OrderDetails'
            ]
        ])->first();
        return $order;
    }
    
    /**
     * @return array $order
     */
    private function generateAndGetOrder($product1Quantity=1, $product2Quantity=1)
    {

        //TODO calling the method addProductToCart only once leads to order error - needs debugging
        $this->addProductToCart($this->productId1, $product1Quantity);
        $this->addProductToCart($this->productId2, $product2Quantity);
        $this->finishCart();
        $orderId = Configure::read('app.htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());

        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ],
            'contain' => [
                'OrderDetails'
            ]
        ])->first();

        return $order;
    }

    private function assertOrderDetailDeletedEmails($emailLogIndex, $expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[$emailLogIndex], 'Produkt wurde storniert: Artischocke : Stück', [$this->cancellationReason, '1,82', 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
    }

    private function assertOrderDetailProductPriceChangedEmails($emailLogIndex, $expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[$emailLogIndex], 'Preis korrigiert: Artischocke', [$this->editPriceReason, $this->newPrice, 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
    }

    private function assertOrderDetailProductQuantityChangedEmails($emailLogIndex, $expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[$emailLogIndex], 'Bestellte Anzahl korrigiert: Artischocke : Stück', ['Die Anzahl des Produktes <b>Artischocke : Stück</b> wurde korrigiert', $this->editQuantityReason, 'Neue Anzahl: <b>' . $this->newQuantity . '</b>', 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
    }

    private function assertChangedOrderPrice($orderId, $expectedTotalPaidTaxIncl)
    {
        $changedOrder = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ],
        ])->first();
        $this->assertEquals($expectedTotalPaidTaxIncl, $changedOrder->total_paid_tax_incl, 'recalculated sum in order failed');
    }
    
    private function assertChangedStockAvailable($productIds, $expectedQuantity)
    {
        $this->Product = TableRegistry::get('Products');
        $ids = $this->Product->getProductIdAndAttributeId($productIds);
        $this->StockAvailable = TableRegistry::get('StockAvailables');
        $changedStockAvailable = $this->StockAvailable->find('all', [
            'conditions' => [
                'StockAvailables.id_product' => $ids['productId'],
                'StockAvailables.id_product_attribute' => $ids['attributeId'],
            ]
        ])->first();
        $quantity = $changedStockAvailable->quantity;
        $this->assertEquals($expectedQuantity, $quantity, 'amount was not corrected properly');
    }
    
    private function deleteOrderDetail($orderDetailIds, $cancellationReason)
    {
        $this->browser->post(
            '/admin/orderDetails/delete/',
            [
                'orderDetailIds' => $orderDetailIds,
                'cancellationReason' => $cancellationReason
            ]
        );
    }

    private function editOrderDetailPrice($orderDetailId, $productPrice, $editPriceReason)
    {
        $this->browser->post(
            '/admin/orderDetails/editProductPrice/',
            [
                'orderDetailId' => $orderDetailId,
                'productPrice' => $productPrice,
                'editPriceReason' => $editPriceReason
            ]
        );
    }

    private function editOrderDetailQuantity($orderDetailId, $productQuantity, $editQuantityReason)
    {
        $this->browser->post(
            '/admin/orderDetails/editProductQuantity/',
            [
                'orderDetailId' => $orderDetailId,
                'productQuantity' => $productQuantity,
                'editQuantityReason' => $editQuantityReason
            ]
        );
    }
}
