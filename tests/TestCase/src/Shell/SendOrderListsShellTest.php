<?php

use App\Application;
use Cake\Console\CommandRunner;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Test\TestCase\AppCakeTestCase;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class SendOrderListsShellTest extends AppCakeTestCase
{
    public $EmailLog;
    public $Order;
    public $commandRunner;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->prepareSendingOrderLists();
        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }

    public function testSendOrderListsIfNoOrdersAvailable()
    {
        $this->OrderDetail->deleteAll([]);
        $this->commandRunner->run(['cake', 'send_order_lists']);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(0, count($emailLogs), 'amount of sent emails wrong');
    }

    public function testSendOrderListsIfOneOrderAvailable()
    {
        $this->loginAsSuperadmin();
        $productId = '346'; // artischocke

        $this->addProductToCart($productId, 1);
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->httpClient->getUrl());
        $cart = $this->getCartById($cartId);
        
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        
        $cronjobRunDay = '2019-02-27';
        
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get($orderDetailId),
                [
                    'pickup_day' => Configure::read('app.timeHelper')->getNextDeliveryDay(strtotime($cronjobRunDay)),
                ]
            )
        );
        
        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        
        $this->assertOrderDetailState($orderDetailId, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        
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
    
    public function testSendOrderListsIfMoreOrdersAvailable()
    {
        $cronjobRunDay = '2018-01-31';
        
        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        
        $this->assertOrderDetailState(1, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState(2, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState(3, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(3, count($emailLogs), 'amount of sent emails wrong');
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

    public function testSendOrderListsWithIndividualSendOrderListWeekday()
    {
        $cronjobRunDay = '2018-01-30';
        $productId = 346;
        $orderDetailId = 1;
        
        // 1) run cronjob and assert no changings
        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        $this->assertOrderDetailState($orderDetailId, ORDER_STATE_ORDER_PLACED);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(0, count($emailLogs), 'amount of sent emails wrong');
        
        // 2) change product send_order_list_weekday and run cronjob again
        $this->Product->save(
            $this->Product->patchEntity(
                $this->Product->get($productId),
                [
                        'delivery_rhythm_send_order_list_weekday' => 2
                ]
            )
        );
        
        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        $this->assertOrderDetailState($orderDetailId, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState(2, ORDER_STATE_ORDER_PLACED);
        $this->assertOrderDetailState(3, ORDER_STATE_ORDER_PLACED);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(1, count($emailLogs), 'amount of sent emails wrong');
        
    }
    
    public function testSendOrderListsWithDifferentIndividualSendOrderListDayAndWeeklySendDay()
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $orderDetailIdIndividualDate = 1;
        $deliveryDay = '2019-10-11';
        $cronjobRunDay = '2019-10-02';
        
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get($orderDetailIdIndividualDate),
                [
                    'pickup_day' => $deliveryDay,
                ]
            )
        );
        $this->changeProductDeliveryRhythm($productId, '0-individual', $deliveryDay, '2019-10-01', '', $cronjobRunDay);
        
        $this->addProductToCart(344, 1); //knoblauch
        $this->addProductToCart(163, 1); //mangold
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->httpClient->getUrl());
        $cart = $this->getCartById($cartId);
        
        $orderDetailIdWeeklyA = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get($orderDetailIdWeeklyA),
                [
                    'pickup_day' => '2019-10-04',
                ]
            )
        );
        
        $orderDetailIdWeeklyB = $cart->cart_products[1]->order_detail->id_order_detail;
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get($orderDetailIdWeeklyB),
                [
                    'pickup_day' => '2019-10-04',
                ]
            )
        );
    
        // 1) run cronjob and assert changings
        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        $this->assertOrderDetailState($orderDetailIdIndividualDate, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState($orderDetailIdWeeklyA, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState($orderDetailIdWeeklyB, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(3, count($emailLogs), 'amount of sent emails wrong');
        
        $this->assertEmailLogs(
            $emailLogs[1],
            'Bestellungen für den 11.10.2019',
            [
                'im Anhang findest du zwei Bestelllisten',
                '2019-10-11_Demo-Gemuese-Hersteller_5_Bestellliste_Produkt_FoodCoop-Test.pdf',
                'Content-Type: application/pdf'
            ],
            [
                Configure::read('test.loginEmailVegetableManufacturer')
            ]
        );
        
        $this->assertEmailLogs(
            $emailLogs[2],
            'Bestellungen für den 04.10.2019',
            [
                'im Anhang findest du zwei Bestelllisten',
                '2019-10-04_Demo-Gemuese-Hersteller_5_Bestellliste_Produkt_FoodCoop-Test.pdf',
                'Content-Type: application/pdf'
            ],
            [
                Configure::read('test.loginEmailVegetableManufacturer')
            ]
        );
    }
    
    public function testSendOrderListsWithEmptyIndividualSendOrderListDay()
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $this->changeProductDeliveryRhythm($productId, '0-individual', '2018-01-12', '2018-01-01', '', '');
        
        $cronjobRunDay = '2018-01-30';
        $orderDetailId = 1;
        
        // 1) run cronjob and assert no changings
        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        $this->assertOrderDetailState($orderDetailId, ORDER_STATE_ORDER_PLACED);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(0, count($emailLogs), 'amount of sent emails wrong');
        
    }
    
    private function assertOrderDetailState($orderDetailId, $expectedOrderState)
    {
        $newOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ]
        ])->first();
        $this->assertEquals($expectedOrderState, $newOrderDetail->order_state);
    }
    
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->SendOrderLists);
    }
}
