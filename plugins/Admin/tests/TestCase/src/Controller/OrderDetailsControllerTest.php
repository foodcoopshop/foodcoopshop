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

    public $productId = 346;

    public $cancellationReason = 'Product was not fresh any more.';

    public $newPrice = '3,53';
    public $editPriceReason = 'Product was smaller than expected.';

    public $newQuantity = 1;
    public $editQuantityReason = 'One product was not delivered.';

    public $mockOrder;

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
        $this->mockOrder = $this->getOrder();
        $this->logout();
        $this->loginAsVegetableManufacturer();

        $this->assertRemoveFromDatabase([$this->mockOrder->order_details[0]->id_order_detail], $this->mockOrder->id_order);
        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testCancellationAsSuperadminWithEnabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getOrder();
        $this->assertRemoveFromDatabase([$this->mockOrder->order_details[0]->id_order_detail], $this->mockOrder->id_order);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $weekday = date('N');
        if (in_array($weekday, [3,4,5])) {
            $expectedCcEmails[] = Configure::read('test.loginEmailVegetableManufacturer');
        }

        $this->assertOrderDetailDeletedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testCancellationAsSuperadminWithDisabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getOrder();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_deleted_notification', 0);

        $this->assertRemoveFromDatabase([$this->mockOrder->order_details[0]->id_order_detail], $this->mockOrder->id_order);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testCancellationAsSuperadminWithEnabledBulkOrders()
    {
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getOrder();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'bulk_orders_allowed', 1);

        $this->assertRemoveFromDatabase([$this->mockOrder->order_details[0]->id_order_detail], $this->mockOrder->id_order);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailPriceAsManufacturer()
    {
        $this->markTestSkipped();
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getOrder();
        $this->logout();
        $this->loginAsVegetableManufacturer();

        $this->editOrderDetailPrice($this->mockOrder->order_details[0]->id_order_detail, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newPrice, Configure::read('app.htmlHelper')->formatAsDecimal($changedOrder['OrderDetails'][0]['total_price_tax_incl']), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailPriceAsSuperadminWithEnabledNotification()
    {
        $this->markTestSkipped();
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getOrder();

        $this->editOrderDetailPrice($this->mockOrder->order_details[0]->id_order_detail, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newPrice, Configure::read('app.htmlHelper')->formatAsDecimal($changedOrder['OrderDetails'][0]['total_price_tax_incl']), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [Configure::read('test.loginEmailVegetableManufacturer')];

        $this->assertOrderDetailProductPriceChangedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailPriceIfPriceWasZero()
    {
        $this->markTestSkipped();
        $this->loginAsSuperadmin();
        $this->changeProductPrice($this->productId, 0);
        $this->mockOrder = $this->getOrder();

        $this->editOrderDetailPrice($this->mockOrder->order_details[0]->id_order_detail, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newPrice, Configure::read('app.htmlHelper')->formatAsDecimal($changedOrder['OrderDetails'][0]['total_price_tax_incl']), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailPriceAsSuperadminWithDisabledNotification()
    {
        $this->markTestSkipped();
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getOrder();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_price_changed_notification', 0);

        $this->editOrderDetailPrice($this->mockOrder->order_details[0]->id_order_detail, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newPrice, Configure::read('app.htmlHelper')->formatAsDecimal($changedOrder['OrderDetails'][0]['total_price_tax_incl']), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailPriceAsSuperadminWithEnabledBulkOrders()
    {
        $this->markTestSkipped();
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getOrder();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'bulk_orders_allowed', 1);

        $this->editOrderDetailPrice($this->mockOrder->order_details[0]->id_order_detail, $this->newPrice, $this->editPriceReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newPrice, Configure::read('app.htmlHelper')->formatAsDecimal($changedOrder['OrderDetails'][0]['total_price_tax_incl']), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailQuantityAsManufacturer()
    {
        $this->markTestSkipped();
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getOrder();
        $this->logout();
        $this->loginAsVegetableManufacturer();

        $this->editOrderDetailQuantity($this->mockOrder->order_details[0]->id_order_detail, $this->newQuantity, $this->editQuantityReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newQuantity, $changedOrder['OrderDetails'][0]['product_quantity'], 'order detail quantity was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductQuantityChangedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailQuantityAsSuperadminWithEnabledNotification()
    {
        $this->markTestSkipped();
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getOrder();

        $this->editOrderDetailQuantity($this->mockOrder->order_details[0]->id_order_detail, $this->newQuantity, $this->editQuantityReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newQuantity, $changedOrder['OrderDetails'][0]['product_quantity'], 'order detail quantity was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $weekday = date('N');
        if (in_array($weekday, [3,4,5])) {
            $expectedCcEmails[] = Configure::read('test.loginEmailVegetableManufacturer');
        }
        $this->assertOrderDetailProductQuantityChangedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailQuantityAsSuperadminWithDisabledNotification()
    {
        $this->markTestSkipped();
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getOrder();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_quantity_changed_notification', 0);

        $this->editOrderDetailQuantity($this->mockOrder->order_details[0]->id_order_detail, $this->newQuantity, $this->editQuantityReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newQuantity, $changedOrder['OrderDetails'][0]['product_quantity'], 'order detail quantity was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductQuantityChangedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailQuantityAsSuperadminWithEnabledBulkOrders()
    {
        $this->markTestSkipped();
        $this->loginAsSuperadmin();
        $this->mockOrder = $this->getOrder();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'bulk_orders_allowed', 1);

        $this->editOrderDetailQuantity($this->mockOrder->order_details[0]->id_order_detail, $this->newQuantity, $this->editQuantityReason);

        $changedOrder = $this->getChangedMockOrderFromDatabase();
        $this->assertEquals($this->newQuantity, $changedOrder['OrderDetails'][0]['product_quantity'], 'order detail quantity was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductQuantityChangedEmails($expectedToEmails, $expectedCcEmails);
    }

    private function getChangedMockOrderFromDatabase()
    {
        if (!$this->mockOrder) {
            return false;
        }

        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $this->mockOrder->id_order
            ]
        ])->first();
        return $order;
    }

    private function assertRemoveFromDatabase($orderDetailIds, $orderId)
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

        $this->assertEquals(0, count($order->order_details), 'order detail was not deleted properly');
    }

    /**
     * @return array $order
     */
    private function getOrder()
    {

        //TODO calling the method addProductToCart only once leads to order error - needs debugging
        $this->addProductToCart($this->productId, 1);
        $this->addProductToCart($this->productId, 1);
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

    private function assertOrderDetailDeletedEmails($expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[1], 'Produkt kann nicht geliefert werden: Artischocke : Stück', [$this->cancellationReason, '3,64', 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
    }

    private function assertOrderDetailProductPriceChangedEmails($expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[1], 'Preis korrigiert: Artischocke', [$this->editPriceReason, $this->newPrice, 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
    }

    private function assertOrderDetailProductQuantityChangedEmails($expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[1], 'Bestellte Anzahl korrigiert: Artischocke : Stück', ['Die Anzahl des Produktes <b>Artischocke : Stück</b> wurde korrigiert', $this->editQuantityReason, 'Neue Anzahl: <b>' . $this->newQuantity . '</b>', 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
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
                'editPriceReason' => $editPriceReason,
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
                'editQuantityReason' => $editQuantityReason,
            ]
        );
    }
}
