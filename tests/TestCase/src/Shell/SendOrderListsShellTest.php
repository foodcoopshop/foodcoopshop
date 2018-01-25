<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class SendOrderListsShellTest extends AppCakeTestCase
{
    public $EmailLog;
    public $Order;
    public $SendOrderLists;

    public function setUp()
    {
        parent::setUp();
        $this->EmailLog = TableRegistry::get('EmailLogs');
        $this->Order = TableRegistry::get('Orders');
        $this->SendOrderLists = $this->createMockShell('SendOrderListsShell');
    }

    public function testSendOrderListsIfNoOrdersAvailable()
    {
        $this->SendOrderLists->main();
        $emailLogs = $this->EmailLog->find('all');
        $this->assertEquals(0, count($emailLogs), 'amount of sent emails wrong');
    }

    public function testSendOrderListsIfOneOrderAvailable()
    {
        $this->loginAsSuperadmin();
        $productId = '346'; // artischocke

        //TODO calling the method addProductToCart only once leads to order error - needs debugging
        $this->addProductToCart($productId, 1);
        $this->addProductToCart($productId, 1);
        $this->finishCart();
        $orderId = Configure::read('AppConfig.htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());

        // reset date if needed
        $currentWeekday = Configure::read('AppConfig.timeHelper')->getCurrentWeekday();
        if (in_array($currentWeekday, Configure::read('AppConfig.timeHelper')->getWeekdaysBetweenOrderSendAndDelivery())) {
            $order2update = [
                'date_add' => Configure::read('AppConfig.timeHelper')->getDateForShopOrder(Configure::read('AppConfig.timeHelper')->getCurrentDay()),
            ];
            $this->Order->id = $orderId;
            $this->Order->save($order2update);
        }

        $this->SendOrderLists->main();
        $emailLogs = $this->EmailLog->find('all');
        $this->assertEquals(2, count($emailLogs), 'amount of sent emails wrong');
        $this->assertEmailLogs($emailLogs[1], 'Bestellungen für den', ['im Anhang findest du zwei Bestelllisten'], [Configure::read('test.loginEmailVegetableManufacturer')]);
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->SendOrderLists);
    }
}
