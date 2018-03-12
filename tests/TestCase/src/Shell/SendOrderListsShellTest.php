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
        $this->EmailLog = TableRegistry::get('EmailLogs');
        $this->Order = TableRegistry::get('Orders');
        $this->SendOrderLists = new SendOrderListsShell(new ConsoleIo());
    }

    public function testSendOrderListsIfNoOrdersAvailable()
    {
        $this->Order->deleteAll([]);
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
        $orderId = Configure::read('app.htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());

        // reset date if needed
        $currentWeekday = Configure::read('app.timeHelper')->getCurrentWeekday();
        if (in_array($currentWeekday, Configure::read('app.timeHelper')->getWeekdaysBetweenOrderSendAndDelivery())) {
            $this->Order->save(
                $this->Order->patchEntity(
                    $this->Order->get($orderId),
                    [
                        'date_add' => Configure::read('app.timeHelper')->getDateForShopOrder(Configure::read('app.timeHelper')->getCurrentDay()),
                    ]
                )
            );
        }

        $this->SendOrderLists->main();
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
