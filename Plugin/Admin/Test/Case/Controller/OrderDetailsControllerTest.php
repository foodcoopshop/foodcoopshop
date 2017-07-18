<?php

App::uses('AppCakeTestCase', 'Test');
App::uses('Order', 'Model');
App::uses('EmailLog', 'Model');
App::uses('Manufacturer', 'Model');

/**
 * CartsControllerTest
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
class OrderDetailsControllerTest extends AppCakeTestCase
{

    public $Order;

    public $Manufacturer;

    public $EmailLog;

    public $cancellationReason = 'Product was not fresh any more.';

    public $newPrice = '3,53';
    public $editPriceReason = 'Product was smaller than expected.';

    public $mockOrder;

    public function setUp()
    {
        parent::setUp();
        $this->Order = new Order();
        $this->EmailLog = new EmailLog();
        $this->Manufacturer = new Manufacturer();
        $this->mockOrder = $this->getOrder();
    }

    public function testCancellationAsManufacturer()
    {
        $this->logout();
        $this->loginAsVegetableManufacturer();

        $orderDetailIds = array($this->mockOrder['OrderDetails'][0]['id_order_detail']);
        $this->assertRemoveFromDatabase($orderDetailIds, $this->mockOrder['Order']['id_order']);
        $expectedToEmails = array(Configure::read('test.loginEmailSuperadmin'));
        $expectedCcEmails = array();
        $this->assertOrderDetailDeletedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testCancellationAsSuperadminWithEnabledNotification()
    {
        $orderDetailIds = array($this->mockOrder['OrderDetails'][0]['id_order_detail']);
        $this->assertRemoveFromDatabase($orderDetailIds, $this->mockOrder['Order']['id_order']);

        $expectedToEmails = array(Configure::read('test.loginEmailSuperadmin'));
        $expectedCcEmails = array();
        $weekday = date('N');
        if (in_array($weekday, array(3,4,5))) {
            $expectedCcEmails[] = Configure::read('test.loginEmailVegetableManufacturer');
        }

        $this->assertOrderDetailDeletedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testCancellationAsSuperadminWithDisabledNotification()
    {

        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->Order->query('UPDATE ' . $this->Manufacturer->tablePrefix . $this->Manufacturer->useTable.' SET send_ordered_product_deleted_notification = 0 WHERE id_manufacturer = ' . $manufacturerId);

        $orderDetailIds = array($this->mockOrder['OrderDetails'][0]['id_order_detail']);
        $this->assertRemoveFromDatabase($orderDetailIds, $this->mockOrder['Order']['id_order']);

        $expectedToEmails = array(Configure::read('test.loginEmailSuperadmin'));
        $expectedCcEmails = array();
        $this->assertOrderDetailDeletedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testCancellationAsSuperadminWithEnabledBulkOperations()
    {

        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->Order->query('UPDATE ' .  $this->Manufacturer->tablePrefix . $this->Manufacturer->useTable.' SET bulk_orders_allowed = 1 WHERE id_manufacturer = ' . $manufacturerId);

        $orderDetailIds = array($this->mockOrder['OrderDetails'][0]['id_order_detail']);
        $this->assertRemoveFromDatabase($orderDetailIds, $this->mockOrder['Order']['id_order']);

        $expectedToEmails = array(Configure::read('test.loginEmailSuperadmin'));
        $expectedCcEmails = array();
        $this->assertOrderDetailDeletedEmails($expectedToEmails, $expectedCcEmails);
    }

    public function testEditProductPriceAsManufacturer()
    {
        $this->logout();
        $this->loginAsVegetableManufacturer();

        $orderDetailId = $this->mockOrder['OrderDetails'][0]['id_order_detail'];

        $this->editProductPrice($orderDetailId, $this->newPrice, $this->editPriceReason);

        $this->Order->recursive = 2;
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'Order.id_order' => $this->mockOrder['Order']['id_order']
            )
        ));

        $this->assertEquals($this->newPrice, Configure::read('htmlHelper')->formatAsDecimal($order['OrderDetails'][0]['total_price_tax_incl']), 'order detail price was not changed properly');

        $expectedToEmails = array(Configure::read('test.loginEmailSuperadmin'));
        $expectedCcEmails = array();
        $this->assertOrderDetailProductPriceChangedEmails($expectedToEmails, $expectedCcEmails);
    }


    private function assertRemoveFromDatabase($orderDetailIds, $orderId)
    {
        $this->deleteOrderDetail($orderDetailIds, $this->cancellationReason);

        $this->Order->recursive = 2;
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'Order.id_order' => $orderId
            )
        ));

        $this->assertEquals(0, count($order['OrderDetails']), 'order detail was not deleted properly');
    }

    /**
     * @return array $order
     */
    private function getOrder()
    {

        $this->loginAsSuperadmin();
        $productId = 346;

        //TODO calling the method addProductToCart only once leads to order error - needs debugging
        $this->addProductToCart($productId, 1);
        $this->addProductToCart($productId, 1);
        $this->finishCart();
        $orderId = Configure::read('htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());

        $this->Order->recursive = 2;
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'Order.id_order' => $orderId
            )
        ));

        return $order;
    }

    private function assertOrderDetailDeletedEmails($expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all');
        $this->assertEmailLogs($emailLogs[1], 'Artikel kann nicht geliefert werden: Artischocke : Stück', array($this->cancellationReason, '3,64', 'Demo Gemüse-Hersteller'), $expectedToEmails, $expectedCcEmails);
    }

    private function assertOrderDetailProductPriceChangedEmails($expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all');
        $this->assertEmailLogs($emailLogs[1], 'Preis korrigiert: Artischocke', array($this->editPriceReason, $this->newPrice, 'Demo Gemüse-Hersteller'), $expectedToEmails, $expectedCcEmails);
    }

    private function deleteOrderDetail($orderDetailIds, $cancellationReason)
    {
        $this->browser->post(
            '/admin/orderDetails/delete/',
            array(
                'data' => array(
                    'orderDetailIds' => $orderDetailIds,
                    'cancellationReason' => $cancellationReason
                )
            )
        );
    }

    private function editProductPrice($orderDetailId, $productPrice, $editPriceReason)
    {
        $this->browser->post(
            '/admin/orderDetails/editProductPrice/',
            array(
                'data' => array(
                    'orderDetailId' => $orderDetailId,
                    'productPrice' => $productPrice,
                    'editPriceReason' => $editPriceReason,
                )
            )
        );
    }
}
