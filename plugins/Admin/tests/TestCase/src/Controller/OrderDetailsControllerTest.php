<?php
/**
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

    public $newAmount = 1;
    public $editAmountReason = 'One product was not delivered.';

    public $mockOrder;
    public $mockOrderId = 1;

    public function setUp()
    {
        parent::setUp();
        $this->Order = TableRegistry::getTableLocator()->get('Orders');
        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
    }

    public function testCancellationAsManufacturer()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getMockOrder();
        $this->logout();
        $this->loginAsVegetableManufacturer();

        $this->deleteAndAssertRemoveFromDatabase([$this->mockOrder->order_details[0]->id_order_detail], $this->mockOrder->id_order, 1);
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
        $this->deleteAndAssertRemoveFromDatabase([$this->mockOrder->order_details[0]->id_order_detail], $this->mockOrder->id_order, 1);

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

        $this->deleteAndAssertRemoveFromDatabase([$this->mockOrder->order_details[0]->id_order_detail], $this->mockOrder->id_order, 1);

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

        $this->deleteAndAssertRemoveFromDatabase([$this->mockOrder->order_details[0]->id_order_detail], $this->mockOrder->id_order, 1);

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
        $this->deleteAndAssertRemoveFromDatabase([$this->mockOrder->order_details[1]->id_order_detail], $this->mockOrder->id_order, 1);
        $this->assertChangedStockAvailable($this->productId1, 20);
    }
    
    public function testCancellationWithTimebasedCurrency()
    {
        
        $order = $this->prepareTimebasedCurrencyOrder();
        $orderDetailId = $order->order_details[1]->id_order_detail;
        $this->deleteAndAssertRemoveFromDatabase([$orderDetailId], $order->id_order, 1);
        $this->assertChangedOrderPrice($order->id_order, 2.8);
        
        $changedOrder = $this->getOrderWithTimebasedCurrencyAssociations($order->id_order);
       
        // assert if record TimebasedCurrencyOrderDetail was removed
        $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
        $timebasedCurrencyOrderDetail = $this->TimebasedCurrencyOrderDetail->find('all', [
            'conditions' => [
                'TimebasedCurrencyOrderDetails.id_order_detail' => $orderDetailId
            ]
        ]);
        $this->assertEquals(0, $timebasedCurrencyOrderDetail->count());
        $this->assertTimebasedCurrencyOrderSums($changedOrder, 0.76, 0.84, 300);
    }

    public function testEditOrderDetailPriceAsManufacturer()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getMockOrder();
        $this->logout();
        $this->loginAsVegetableManufacturer();
        $this->editOrderDetailPrice($this->mockOrder->order_details[0]->id_order_detail, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrder->order_details[0]->total_price_tax_incl), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedOrderPrice($this->mockOrder->id_order, 8.075455);
    }
    
    public function testEditOrderDetailQuantityAsSuperadmin()
    {
        $this->loginAsSuperadmin();

        $order = $this->preparePricePerUnitOrder();
        
        $newQuantity = 800.584;
        $this->editOrderDetailQuantity($order->order_details[0]->id_order_detail, $newQuantity, false);
        
        $changedOrder = $this->getOrderWithUnitAssociations($order->id_order);
        
        $this->assertEquals(12.00876, $changedOrder->total_paid);
        $this->assertEquals(12.00876, $changedOrder->total_paid_tax_incl);
        $this->assertEquals(10.90876, $changedOrder->total_paid_tax_excl);
        $this->assertEquals(12.00876, $changedOrder->order_details[0]->total_price_tax_incl);
        $this->assertEquals(10.90876, $changedOrder->order_details[0]->total_price_tax_excl);
        $this->assertEquals($newQuantity, $changedOrder->order_details[0]->order_detail_unit->product_quantity_in_units);
        
        $this->assertEquals(0.55, $changedOrder->order_details[0]->order_detail_tax->unit_amount);
        $this->assertEquals(1.10, $changedOrder->order_details[0]->order_detail_tax->total_amount);
        
        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [Configure::read('test.loginEmailMeatManufacturer')];
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[1], 'Gewicht angepasst: Forelle : Stück', [$newQuantity, 'Demo Superadmin', 'Der Basis-Preis beträgt 1,50&nbsp;€ / 100 g'], $expectedToEmails, $expectedCcEmails);
    }
    
    public function testEditOrderDetailQuantityAsSuperadminDoNotChangePrice() 
    {
        $this->loginAsSuperadmin();
        
        $order = $this->preparePricePerUnitOrder();
        
        $newQuantity = 800.854;
        $this->editOrderDetailQuantity($order->order_details[0]->id_order_detail, $newQuantity, true);
        
        $changedOrder = $this->getOrderWithUnitAssociations($order->id_order);
        
        $this->assertEquals($order->total_paid, $changedOrder->total_paid);
        $this->assertEquals($order->total_paid_tax_incl, $changedOrder->total_paid_tax_incl);
        $this->assertEquals($order->total_paid_tax_excl, $changedOrder->total_paid_tax_excl);
        $this->assertEquals($order->order_details[0]->total_price_tax_incl, $changedOrder->order_details[0]->total_price_tax_incl);
        $this->assertEquals($order->order_details[0]->total_price_tax_excl, $changedOrder->order_details[0]->total_price_tax_excl);
        $this->assertEquals($newQuantity, $changedOrder->order_details[0]->order_detail_unit->product_quantity_in_units);
        
        $this->assertEquals($order->order_details[0]->order_detail_tax->unit_amount, $changedOrder->order_details[0]->order_detail_tax->unit_amount);
        $this->assertEquals($order->order_details[0]->order_detail_tax->total_amount, $changedOrder->order_details[0]->order_detail_tax->total_amount);
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(1, count($emailLogs));
        
    }
    
    public function testEditOrderDetailPriceWithTimebasedCurrency()
    {
        
        $order = $this->prepareTimebasedCurrencyOrder();
        $orderDetailId = $order->order_details[0]->id_order_detail;
        $this->editOrderDetailPrice($orderDetailId, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getOrderWithTimebasedCurrencyAssociations($order->id_order);
        
        $this->assertChangedOrderPrice($changedOrder->id_order, 4.026364);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrder->order_details[0]->total_price_tax_incl), 'order detail price was not changed properly');
        
        $this->assertTimebasedCurrencyOrderDetail($changedOrder->order_details[0], 1.38, 1.52, 544);
        $this->assertTimebasedCurrencyOrderSums($changedOrder, 1.52, 1.66, 596);
    }

    public function testEditOrderDetailPriceAsSuperadminWithEnabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getMockOrder();

        $this->editOrderDetailPrice($this->mockOrder->order_details[0]->id_order_detail, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrder->order_details[0]->total_price_tax_incl), 'order detail price was not changed properly');

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
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrder->order_details[0]->total_price_tax_incl), 'order detail price was not changed properly');

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
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrder->order_details[0]->total_price_tax_incl), 'order detail price was not changed properly');

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
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrder->order_details[0]->total_price_tax_incl), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails, $expectedCcEmails);
        $this->assertChangedOrderPrice($this->mockOrder->id_order, 8.075455);
    }

    public function testEditOrderDetailAmountAsManufacturer()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->generateAndGetOrder(5, 2);
        $this->logout();
        $this->loginAsVegetableManufacturer();

        $this->editOrderDetailAmount($this->mockOrder->order_details[0]->id_order_detail, $this->newAmount, $this->editAmountReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->order_details[0]->product_amount, 'order detail amount was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedOrderPrice($this->mockOrder->id_order, 10.91091);
        $this->assertChangedStockAvailable($this->productId1, 96);
    }
    
    public function testEditOrderDetailAmountWithTimebasedCurrency()
    {
        
        $order = $this->prepareTimebasedCurrencyOrder();
        $orderDetailId = $order->order_details[0]->id_order_detail;
        
        $this->editOrderDetailAmount($orderDetailId, $this->newAmount, $this->editAmountReason);
        
        $changedOrder = $this->getOrderWithTimebasedCurrencyAssociations($order->id_order);
        
        $this->assertEquals($this->newAmount, $changedOrder->order_details[0]->product_amount, 'order detail amount was not changed properly');
        $this->assertChangedOrderPrice($changedOrder->id_order, 1.896364);
        $this->assertEquals('1,40', Configure::read('app.numberHelper')->formatAsDecimal($changedOrder->order_details[0]->total_price_tax_incl), 'order detail price was not changed properly');
        
        $this->assertTimebasedCurrencyOrderDetail($changedOrder->order_details[0], 0.55, 0.6, 216);
        $this->assertTimebasedCurrencyOrderSums($changedOrder, 0.69, 0.74, 268);
    }

    public function testEditOrderDetailAmountAsSuperadminWithEnabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->generateAndGetOrder(1, 2);

        $this->editOrderDetailAmount($this->mockOrder->order_details[0]->id_order_detail, $this->newAmount, $this->editAmountReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->order_details[0]->product_amount, 'order detail amount was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $weekday = date('N');
        if (in_array($weekday, [3,4,5])) {
            $expectedCcEmails[] = Configure::read('test.loginEmailVegetableManufacturer');
        }
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedOrderPrice($this->mockOrder->id_order, 10.91091);
        $this->assertChangedStockAvailable($this->productId1, 96);
    }

    public function testEditOrderDetailAmountAsSuperadminWithDisabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->generateAndGetOrder(1, 2);
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_amount_changed_notification', 0);

        $this->editOrderDetailAmount($this->mockOrder->order_details[0]->id_order_detail, $this->newAmount, $this->editAmountReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->order_details[0]->product_amount, 'order detail amount was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedOrderPrice($this->mockOrder->id_order, 10.91091);
        $this->assertChangedStockAvailable($this->productId1, 96);
    }

    public function testEditOrderDetailAmountAsSuperadminWithEnabledBulkOrders()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->generateAndGetOrder(1, 2);
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'bulk_orders_allowed', 1);

        $this->editOrderDetailAmount($this->mockOrder->order_details[0]->id_order_detail, $this->newAmount, $this->editAmountReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->order_details[0]->product_amount, 'order detail amount was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedOrderPrice($this->mockOrder->id_order, 10.91091);
        $this->assertChangedStockAvailable($this->productId1, 96);
    }
    
    private function preparePricePerUnitOrder()
    {
        $productIdA = 347; // forelle
        
        $this->addProductToCart($productIdA, 1);
        $this->addProductToCart($productIdA, 1); // addProductToCart needs to be called twice!
        
        $this->finishCart(1, 1);
        $orderId = Configure::read('app.htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());
        $order = $this->getOrderWithUnitAssociations($orderId);
        return $order;
    }

    private function prepareTimebasedCurrencyOrder()
    {
        $reducedMaxPercentage = 15;
        $this->prepareTimebasedCurrencyConfiguration($reducedMaxPercentage);
        $this->loginAsSuperadmin();
        $this->addProductToCart(344, 1); // addProductToCart needs to be called twice!
        $this->addProductToCart(346, 2);
        $this->finishCart(1, 1, '', '352');
        $orderId = Configure::read('app.htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());
        $order = $this->getOrderWithTimebasedCurrencyAssociations($orderId);
        return $order;
    }
    
    private function assertTimebasedCurrencyOrderSums($changedOrder, $moneyExclSum, $moneyInclSum, $secondsSum)
    {
        $this->assertEquals($changedOrder->timebased_currency_order->money_excl_sum, $moneyExclSum);
        $this->assertEquals($changedOrder->timebased_currency_order->money_incl_sum, $moneyInclSum);
        $this->assertEquals($changedOrder->timebased_currency_order->seconds_sum, $secondsSum);
    }
    
    private function assertTimebasedCurrencyOrderDetail($changedOrderDetail, $moneyExcl, $moneyIncl, $seconds)
    {
        $this->assertEquals($changedOrderDetail->timebased_currency_order_detail->money_excl, $moneyExcl);
        $this->assertEquals($changedOrderDetail->timebased_currency_order_detail->money_incl, $moneyIncl);
        $this->assertEquals($changedOrderDetail->timebased_currency_order_detail->seconds, $seconds);
    }
    
    private function getOrderWithUnitAssociations($orderId)
    {
        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ],
            'contain' => [
                'OrderDetails.OrderDetailTaxes',
                'OrderDetails.OrderDetailUnits',
            ]
        ])->first();
        return $order;
    }
    
    private function getOrderWithTimebasedCurrencyAssociations($orderId)
    {
        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ],
            'contain' => [
                'TimebasedCurrencyOrders',
                'OrderDetails.TimebasedCurrencyOrderDetails',
                'OrderDetails.OrderDetailTaxes'
            ]
        ])->first();
        return $order;
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

    private function deleteAndAssertRemoveFromDatabase($orderDetailIds, $orderId, $expectedOrderDetailCount)
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
    private function generateAndGetOrder($product1Amount = 1, $product2Amount = 1)
    {

        //TODO calling the method addProductToCart only once leads to order error - needs debugging
        $this->addProductToCart($this->productId1, $product1Amount);
        $this->addProductToCart($this->productId2, $product2Amount);
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
        $this->assertEmailLogs($emailLogs[$emailLogIndex], 'Preis angepasst: Artischocke', [$this->editPriceReason, $this->newPrice, 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
    }

    private function assertOrderDetailProductAmountChangedEmails($emailLogIndex, $expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[$emailLogIndex], 'Bestellte Anzahl angepasst: Artischocke : Stück', ['Die Anzahl des Produktes <b>Artischocke : Stück</b> wurde angepasst', $this->editAmountReason, 'Neue Anzahl: <b>' . $this->newAmount . '</b>', 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
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

    private function assertChangedStockAvailable($productIds, $expectedAmount)
    {
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $ids = $this->Product->getProductIdAndAttributeId($productIds);
        $this->StockAvailable = TableRegistry::getTableLocator()->get('StockAvailables');
        $changedStockAvailable = $this->StockAvailable->find('all', [
            'conditions' => [
                'StockAvailables.id_product' => $ids['productId'],
                'StockAvailables.id_product_attribute' => $ids['attributeId'],
            ]
        ])->first();
        $quantity = $changedStockAvailable->quantity;
        $this->assertEquals($expectedAmount, $quantity, 'amount was not corrected properly');
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

    private function editOrderDetailQuantity($orderDetailId, $productQuantity, $doNotChangePrice)
    {
        $this->browser->post(
            '/admin/orderDetails/editProductQuantity/',
            [
                'orderDetailId' => $orderDetailId,
                'productQuantity' => $productQuantity,
                'doNotChangePrice' => $doNotChangePrice
            ]
        );
    }
    
    private function editOrderDetailAmount($orderDetailId, $productAmount, $editAmountReason)
    {
        $this->browser->post(
            '/admin/orderDetails/editProductAmount/',
            [
                'orderDetailId' => $orderDetailId,
                'productAmount' => $productAmount,
                'editAmountReason' => $editAmountReason
            ]
        );
    }
}
