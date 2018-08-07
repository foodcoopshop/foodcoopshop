<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Shell\SendOrderListsShell;

class SendOrderListsShellTest extends AppCakeTestCase
{
    public $EmailLog;
    public $Order;
    public $SendOrderLists;

    public function setUp()
    {
        parent::setUp();
        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $this->SendOrderLists = new SendOrderListsShell(new ConsoleIo());
    }

    public function testSendOrderListsIfNoOrdersAvailable()
    {
        $this->OrderDetail->deleteAll([]);
        $this->SendOrderLists->main();
        $emailLogs = $this->EmailLog->find('all')->toArray();
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
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->browser->getUrl());
        $cart = $this->getCartById($cartId);
        
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get($orderDetailId),
                [
                    'pickup_day' => Configure::read('app.timeHelper')->getDeliveryDateForSendOrderListsShell(),
                ]
            )
        );
        
        $this->SendOrderLists->main();
        
        $newOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ]
        ])->first();
        $this->assertEquals(ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER, $newOrderDetail->order_state);
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(2, count($emailLogs), 'amount of sent emails wrong');
        $this->assertEmailLogs(
            $emailLogs[1],
            'Bestellungen für den',
            [
                'im Anhang findest du zwei Bestelllisten',
                'Demo-Gemuese-Hersteller_5_Bestellliste_Produkt_FoodCoop-Test.pdf',
                'Content-Type: application/pdf'
            ],
            [
                Configure::read('test.loginEmailVegetableManufacturer')
            ]
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->SendOrderLists);
    }
}
