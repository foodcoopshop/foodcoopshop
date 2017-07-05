<?php
App::uses('SendOrderListsShell', 'Console/Command');
App::uses('AppCakeTestCase', 'Test');
App::uses('EmailLog', 'Model');
App::uses('Order', 'Model');

class SendOrderListsShellTest extends AppCakeTestCase
{
    public $EmailLog;
    public $Order;
    public $SendOrderLists;

    public function setUp()
    {
        parent::setUp();
        $this->EmailLog = new EmailLog();
        $this->Order = new Order();
        $this->SendOrderLists = $this->createMockShell('SendOrderListsShell');
    }

    public function testSendOrderListsIfNoOrdersAvailable()
    {
        $this->SendOrderLists->main();
        $emailLogs = $this->EmailLog->find('all');
        $this->assertEquals(0, count($emailLogs), 'amount of sent emails wrong');
    }

    /*
    public function testSendOrderListsIfOneOrderAvailable()
    {
        $this->loginAsSuperadmin();
        $productId = '346'; // artischocke
        $this->addProductToCart($productId, 1);
        $this->addProductToCart($productId, 1);
        $this->finishCart();
        $orderId = Configure::read('htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());

        $newDate = Configure::read('timeHelper')->getDateForShopOrder();
        $order2update = array(
            'date_add' => $newDate,
        );
        $this->Order->id = $orderId;
        $this->Order->save($order2update);

        $this->SendOrderLists->main();
        $emailLogs = $this->EmailLog->find('all');
        $this->debug($emailLogs);
        $this->assertEquals(1, count($emailLogs), 'amount of sent emails wrong');
        $this->logout();
    }
    */

    public function tearDown()
    {
        parent::tearDown();
        unset($this->SendOrderLists);
    }
}
