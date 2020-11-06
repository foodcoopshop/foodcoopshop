<?php

use App\Application;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Console\CommandRunner;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestEmailTransport;

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

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public $Order;
    public $commandRunner;

    public function setUp(): void
    {
        parent::setUp();
        $this->prepareSendingOrderLists();
        $this->Cart = $this->getTableLocator()->get('Carts');
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->Product = $this->getTableLocator()->get('Products');
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }

    public function testSendOrderListsIfNoOrdersAvailable()
    {
        $this->OrderDetail->deleteAll([]);
        $this->commandRunner->run(['cake', 'send_order_lists']);
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);
        $this->assertMailCount(0);
    }

    public function testSendOrderListsIfOneOrderAvailable()
    {
        $this->loginAsSuperadmin();
        $productId = '346'; // artischocke

        $this->addProductToCart($productId, 1);
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cart = $this->getCartById($cartId);

        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;

        $cronjobRunDay = '2019-02-27';
        $pickupDay = Configure::read('app.timeHelper')->getNextDeliveryDay(strtotime($cronjobRunDay));

        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get($orderDetailId),
                [
                    'pickup_day' => $pickupDay,
                ]
            )
        );

        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

        $this->assertOrderDetailState($orderDetailId, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);

        $this->assertMailCount(2);

        $pickupDayFormated = new FrozenDate($pickupDay);
        $pickupDayFormated = $pickupDayFormated->i18nFormat(
            Configure::read('app.timeHelper')->getI18Format('DateLong2')
        );

        $this->assertMailSentWithAt(1, 'Bestellungen für den ' . $pickupDayFormated, 'originalSubject');
        $this->assertMailContainsAt(1, 'im Anhang findest du zwei Bestelllisten');

        $pickupDayFormated = new FrozenDate($pickupDay);
        $pickupDayFormated = $pickupDayFormated->i18nFormat(
            Configure::read('app.timeHelper')->getI18Format('DateLong2')
        );
        $this->assertEquals(2, count(TestEmailTransport::getMessages()[1]->getAttachments()));
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailVegetableManufacturer'));
    }

    public function testSendOrderListsIfMoreOrdersAvailable()
    {
        $cronjobRunDay = '2018-01-31';
        $pickupDay = Configure::read('app.timeHelper')->getNextDeliveryDay(strtotime($cronjobRunDay));

        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

        $this->assertOrderDetailState(1, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState(2, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState(3, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);

        $this->assertMailCount(3);

        $pickupDayFormated = new FrozenDate($pickupDay);
        $pickupDayFormated = $pickupDayFormated->i18nFormat(
            Configure::read('app.timeHelper')->getI18Format('DateLong2')
        );

        $this->assertMailSentWithAt(1, 'Bestellungen für den ' . $pickupDayFormated, 'originalSubject');
        $this->assertMailContainsAt(1, 'im Anhang findest du zwei Bestelllisten');

        $this->assertEquals(2, count(TestEmailTransport::getMessages()[1]->getAttachments()));
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailVegetableManufacturer'));
    }

    public function testSendOrderListsWithSendOrderListFalse()
    {
        $cronjobRunDay = '2018-01-31';
        $pickupDay = Configure::read('app.timeHelper')->getNextDeliveryDay(strtotime($cronjobRunDay));

        $this->changeManufacturer(4, 'send_order_list', 0);
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

        $this->assertOrderDetailState(1, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState(2, ORDER_STATE_ORDER_PLACED);
        $this->assertOrderDetailState(3, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);

        $this->assertMailCount(2);

        $pickupDayFormated = new FrozenDate($pickupDay);
        $pickupDayFormated = $pickupDayFormated->i18nFormat(
            Configure::read('app.timeHelper')->getI18Format('DateLong2')
            );

        $this->assertMailSentWithAt(1, 'Bestellungen für den ' . $pickupDayFormated, 'originalSubject');
        $this->assertMailContainsAt(1, 'im Anhang findest du zwei Bestelllisten');

        $this->assertEquals(2, count(TestEmailTransport::getMessages()[1]->getAttachments()));
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailVegetableManufacturer'));

    }

    public function testSendOrderListsWithIndividualSendOrderListWeekday()
    {
        $cronjobRunDay = '2018-01-30';
        $productId = 346;
        $orderDetailId = 1;

        // 1) run cronjob and assert no changings
        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

        $this->assertOrderDetailState($orderDetailId, ORDER_STATE_ORDER_PLACED);
        $this->assertMailCount(0);

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
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

        $this->assertOrderDetailState($orderDetailId, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState(2, ORDER_STATE_ORDER_PLACED);
        $this->assertOrderDetailState(3, ORDER_STATE_ORDER_PLACED);

        $this->assertMailCount(1);

        // 3) assert action log
        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all', [
            'conditions' => [
                'type' => 'cronjob_send_order_lists'
            ]
        ])->toArray();
        $this->assertRegExpWithUnquotedString('Demo Gemüse-Hersteller: 1 Produkt / 1,82 €<br />Verschickte Bestelllisten: 1', $actionLogs[1]->text);

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
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
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
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

        $this->assertOrderDetailState($orderDetailIdIndividualDate, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState($orderDetailIdWeeklyA, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState($orderDetailIdWeeklyB, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);

        $this->assertMailCount(3);

        $this->assertMailSentWithAt(1, 'Bestellungen für den 04.10.2019', 'originalSubject');
        $this->assertMailContainsAt(1, 'im Anhang findest du zwei Bestelllisten');
        $this->assertEquals(2, count(TestEmailTransport::getMessages()[1]->getAttachments()));
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailVegetableManufacturer'));

        $this->assertMailSentWithAt(2, 'Bestellungen für den 11.10.2019', 'originalSubject');
        $this->assertMailContainsAt(2, 'im Anhang findest du zwei Bestelllisten');
        $this->assertEquals(2, count(TestEmailTransport::getMessages()[2]->getAttachments()));
        $this->assertMailSentToAt(2, Configure::read('test.loginEmailVegetableManufacturer'));

        // 2) assert action log
        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $actionLog = $this->ActionLog->find('all', [
            'conditions' => [
                'type' => 'cronjob_send_order_lists'
            ]
        ])->first();
        $this->assertRegExpWithUnquotedString('Demo Gemüse-Hersteller: 2 Produkte / 2,00 €', $actionLog->text);
        $this->assertRegExpWithUnquotedString('Demo Gemüse-Hersteller: 1 Produkt / 1,82 € / Liefertag: 11.10.2019<br />Verschickte Bestelllisten: 2', $actionLog->text);

        // 3) run cronjob again - no additional emails must be sent
        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

        $this->assertMailCount(3);

    }

    public function testSendOrderListsWithEmptyIndividualSendOrderListDay()
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $this->changeProductDeliveryRhythm($productId, '0-individual', '2018-01-12', '2018-01-01', '', '');

        $cronjobRunDay = '2018-01-30';
        $orderDetailId = 1;

        // run cronjob and assert no changings
        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

        $this->assertOrderDetailState($orderDetailId, ORDER_STATE_ORDER_PLACED);
        $this->assertMailCount(0);
    }

    public function testSendOrderListAndResetQuantity()
    {
        $productId1 = 346;
        $productId2 = '60-10';
        $defaultQuantity = 20;

        $newProductData = [
            'default_quantity_after_sending_order_lists' => $defaultQuantity,
            'quantity' => 10,
        ];
        $this->Product = $this->getTableLocator()->get('Products');
        $this->Product->changeQuantity([
            [$productId1 => $newProductData],
            [$productId2 => $newProductData]
        ]);
        $cronjobRunDay = '2018-01-31';
        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

        $product1 = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId1
            ],
            'contain' => [
                'StockAvailables'
            ]
        ])->first();
        $this->assertEquals($defaultQuantity, $product1->stock_available->default_quantity_after_sending_order_lists);
        $this->assertEquals($defaultQuantity, $product1->stock_available->quantity);

        $product2 = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $this->Product->getProductIdAndAttributeId($productId2)['productId']
            ],
            'contain' => [
                'ProductAttributes.StockAvailables'
            ]
        ])->first();
        $this->assertEquals($defaultQuantity, $product2->product_attributes[0]->stock_available->default_quantity_after_sending_order_lists);
        $this->assertEquals($defaultQuantity, $product2->product_attributes[0]->stock_available->quantity);
    }

    public function testContentOfOrderListWithoutPricePerUnit()
    {
        $this->loginAsSuperadmin();

        $this->get('/admin/manufacturers/getOrderListByProduct.pdf?manufacturerId=4&pickupDay=02.02.2018&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'orderListByProductWithoutPricePerUnit.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);

        $this->get('/admin/manufacturers/getOrderListByCustomer.pdf?manufacturerId=4&pickupDay=02.02.2018&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'orderListByCustomerWithoutPricePerUnit.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);

    }

    public function testSendOrderListWithCustomerCanSelectPickupDay()
    {

        $this->changeConfiguration('FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY', 1);

        $this->loginAsSuperadmin();

        $orderDetailId = 1;

        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get($orderDetailId),
                [
                    'pickup_day' => '2020-08-05',
                ]
            )
        );

        $cronjobRunDay = '2020-08-05';
        $this->commandRunner->run(['cake', 'send_order_lists', $cronjobRunDay]);
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

        $this->assertOrderDetailState($orderDetailId, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER);

        $this->assertMailCount(1);
        $this->assertMailSentWithAt(0, 'Bestellungen für den 05.08.2020', 'originalSubject');
        $this->assertMailContainsAt(0, 'im Anhang findest du zwei Bestelllisten');
        $this->assertEquals(2, count(TestEmailTransport::getMessages()[0]->getAttachments()));
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailVegetableManufacturer'));

    }

    public function testContentOfOrderListWithPricePerUnit()
    {

        $productIdA = '351';
        $productIdB = '350-15';
        $productIdC = '346';

        $this->loginAsCustomer();
        $this->addProductToCart($productIdA, 1);
        $this->addProductToCart($productIdB, 1);
        $this->addProductToCart($productIdC, 2);
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cartA = $this->getCartById($cartId);

        $this->loginAsSuperadmin();
        $this->addProductToCart($productIdA, 2);
        $this->addProductToCart($productIdB, 5);
        $this->addProductToCart($productIdC, 1);
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cartB = $this->getCartById($cartId);

        $pickupDay = '2019-02-22';

        foreach([$cartA, $cartB] as $cart) {
            foreach($cart->cart_products as $cartProduct) {
                $orderDetailId = $cartProduct->order_detail->id_order_detail;
                $this->OrderDetail->save(
                    $this->OrderDetail->patchEntity(
                        $this->OrderDetail->get($orderDetailId),
                        [
                            'pickup_day' => new FrozenDate($pickupDay),
                            'created' => new FrozenDate('2020-11-05'),
                        ]
                    )
                );
            }
        }

        $this->get('/admin/manufacturers/getOrderListByProduct.pdf?manufacturerId=5&pickupDay=22.02.2019&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'orderListByProductWithPricePerUnit.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);

        $this->get('/admin/manufacturers/getOrderListByCustomer.pdf?manufacturerId=5&pickupDay=22.02.2019&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'orderListByCustomerWithPricePerUnit.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);

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
