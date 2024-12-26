<?php
declare(strict_types=1);

use App\Services\DeliveryRhythmService;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestEmailTransport;
use Cake\I18n\Date;
use App\Model\Entity\OrderDetail;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class SendOrderListsCommandTest extends AppCakeTestCase
{

    protected $SendOrderLists;

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public $Order;

    public function setUp(): void
    {
        parent::setUp();
        $this->prepareSendingOrderLists();
    }

    public function testSendOrderListsIfNoOrdersAvailable()
    {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->deleteAll([]);
        $this->exec('send_order_lists');
        $this->runAndAssertQueue();
        $this->assertMailCount(0);
    }

    public function testSendOrderListsIfOneOrderAvailable()
    {

        $this->changeManufacturer(5, 'anonymize_customers', 1);

        $this->loginAsSuperadmin();
        $productId = '346'; // artischocke

        $this->addProductToCart($productId, 1);
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cart = $this->getCartById($cartId);

        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;

        $cronjobRunDay = '2019-02-27';
        $pickupDay = (new DeliveryRhythmService())->getNextDeliveryDay(strtotime($cronjobRunDay));

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get($orderDetailId),
                [
                    'pickup_day' => $pickupDay,
                ]
            )
        );

        $this->exec('send_order_lists ' . $cronjobRunDay);
        $this->runAndAssertQueue();

        $this->assertOrderDetailState($orderDetailId, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);

        $this->assertMailCount(2);

        $pickupDayFormatted = new Date($pickupDay);
        $pickupDayFormatted = $pickupDayFormatted->i18nFormat(
            Configure::read('app.timeHelper')->getI18Format('DateLong2')
        );

        $this->assertMailSubjectContainsAt(1, 'Bestellungen für den ' . $pickupDayFormatted);
        $this->assertMailContainsAt(1, 'im Anhang findest du zwei Bestelllisten');

        $pickupDayFormatted = new Date($pickupDay);
        $pickupDayFormatted = $pickupDayFormatted->i18nFormat(
            Configure::read('app.timeHelper')->getI18Format('DateLong2')
        );
        $this->assertEquals(2, count(TestEmailTransport::getMessages()[1]->getAttachments()));
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailVegetableManufacturer'));

        $this->assertGenerationOfOrderLists('2019'.DS.'03', [0,1], [2,3]);

    }

    public function testSendOrderListsIfMoreOrdersAvailable()
    {
        $cronjobRunDay = '2018-01-31';
        $pickupDay = (new DeliveryRhythmService())->getNextDeliveryDay(strtotime($cronjobRunDay));
        $this->changeManufacturer(5, 'anonymize_customers', 1);

        $this->exec('send_order_lists ' . $cronjobRunDay);
        $this->runAndAssertQueue();

        $this->assertOrderDetailState(1, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState(2, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState(3, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);

        $this->assertMailCount(3);

        $pickupDayFormatted = new Date($pickupDay);
        $pickupDayFormatted = $pickupDayFormatted->i18nFormat(
            Configure::read('app.timeHelper')->getI18Format('DateLong2')
        );

        $this->assertMailSubjectContainsAt(1, 'Bestellungen für den ' . $pickupDayFormatted);
        $this->assertMailContainsAt(1, 'im Anhang findest du zwei Bestelllisten');

        $this->assertEquals(2, count(TestEmailTransport::getMessages()[1]->getAttachments()));
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailVegetableManufacturer'));

        $this->assertGenerationOfOrderLists('2018'.DS.'02', [0,1,2,3,4,5], [6,7]);
    }

    public function testSendOrderListsWithSendOrderListFalse()
    {
        $cronjobRunDay = '2018-01-31';
        $pickupDay = (new DeliveryRhythmService())->getNextDeliveryDay(strtotime($cronjobRunDay));

        $this->changeManufacturer(5, 'anonymize_customers', 1);
        $this->changeManufacturer(4, 'send_order_list', 0);
        $this->runAndAssertQueue();

        $this->exec('send_order_lists ' . $cronjobRunDay);
        $this->runAndAssertQueue();

        $this->assertOrderDetailState(1, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState(2, OrderDetail::STATE_OPEN);
        $this->assertOrderDetailState(3, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);

        $this->assertMailCount(2);

        $pickupDayFormatted = new Date($pickupDay);
        $pickupDayFormatted = $pickupDayFormatted->i18nFormat(
            Configure::read('app.timeHelper')->getI18Format('DateLong2')
        );

        $this->assertMailSubjectContainsAt(1, 'Bestellungen für den ' . $pickupDayFormatted);
        $this->assertMailContainsAt(1, 'im Anhang findest du zwei Bestelllisten');

        $this->assertEquals(2, count(TestEmailTransport::getMessages()[1]->getAttachments()));
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailVegetableManufacturer'));

        $this->assertGenerationOfOrderLists('2018'.DS.'02', [0,1,2,3,4,5], [6,7]);

    }

    public function testSendOrderListsWithIndividualSendOrderListWeekday()
    {
        $cronjobRunDay = '2018-01-30';
        $productId = 346;
        $orderDetailId = 1;
        $this->changeManufacturer(5, 'anonymize_customers', 1);

        // 1) run cronjob and assert no changings
        $this->exec('send_order_lists ' . $cronjobRunDay);
        $this->runAndAssertQueue();

        $this->assertOrderDetailState($orderDetailId, OrderDetail::STATE_OPEN);
        $this->assertMailCount(0);

        // 2) change product send_order_list_weekday and run cronjob again
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->save(
            $productsTable->patchEntity(
                $productsTable->get($productId),
                [
                        'delivery_rhythm_send_order_list_weekday' => 2
                ]
            )
        );

        $this->exec('send_order_lists ' . $cronjobRunDay);
        $this->runAndAssertQueue();

        $this->assertOrderDetailState($orderDetailId, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState(2, OrderDetail::STATE_OPEN);
        $this->assertOrderDetailState(3, OrderDetail::STATE_OPEN);

        $this->assertMailCount(1);

        // 3) assert action log
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogs = $actionLogsTable->find('all', conditions: [
            'type' => 'cronjob_send_order_lists'
        ])->toArray();
        $this->assertRegExpWithUnquotedString('Demo Gemüse-Hersteller: 1 Produkt / 1,82 €<br />Verschickte Bestelllisten: 1', $actionLogs[1]->text);

        $this->assertGenerationOfOrderLists('2018'.DS.'02', [0,1], [2,3]);

    }

    public function testSendOrderListsWithDifferentIndividualSendOrderListDayAndWeeklySendDay()
    {

        $this->changeManufacturer(5, 'anonymize_customers', 1);
        $this->loginAsSuperadmin();
        $productId = 346;
        $orderDetailIdIndividualDate = 1;
        $deliveryDay = '2019-10-11';
        $cronjobRunDay = '2019-10-02';

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get($orderDetailIdIndividualDate),
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
        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get($orderDetailIdWeeklyA),
                [
                    'pickup_day' => '2019-10-04',
                ]
            )
        );

        $orderDetailIdWeeklyB = $cart->cart_products[1]->order_detail->id_order_detail;
        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get($orderDetailIdWeeklyB),
                [
                    'pickup_day' => '2019-10-04',
                ]
            )
        );

        // 1) run cronjob and assert changings
        $this->exec('send_order_lists ' . $cronjobRunDay);
        $this->runAndAssertQueue();

        $this->assertOrderDetailState($orderDetailIdIndividualDate, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState($orderDetailIdWeeklyA, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState($orderDetailIdWeeklyB, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);

        $this->assertMailCount(3);

        $this->assertMailSubjectContainsAt(1, 'Bestellungen für den 04.10.2019');
        $this->assertMailContainsAt(1, 'im Anhang findest du zwei Bestelllisten');
        $this->assertEquals(2, count(TestEmailTransport::getMessages()[1]->getAttachments()));
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailVegetableManufacturer'));

        $this->assertMailSubjectContainsAt(2, 'Bestellungen für den 11.10.2019');
        $this->assertMailContainsAt(2, 'im Anhang findest du zwei Bestelllisten');
        $this->assertEquals(2, count(TestEmailTransport::getMessages()[2]->getAttachments()));
        $this->assertMailSentToAt(2, Configure::read('test.loginEmailVegetableManufacturer'));

        // 2) assert action log
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLog = $actionLogsTable->find('all', conditions: [
            'type' => 'cronjob_send_order_lists'
        ])->first();
        $this->assertRegExpWithUnquotedString('Demo Gemüse-Hersteller: 2 Produkte / 2,00 €', $actionLog->text);
        $this->assertRegExpWithUnquotedString('Demo Gemüse-Hersteller: 1 Produkt / 1,82 € / Liefertag: 11.10.2019<br />Verschickte Bestelllisten: 2', $actionLog->text);

        // 3) run cronjob again - no additional emails must be sent
        $this->exec('send_order_lists ' . $cronjobRunDay);
        $this->runAndAssertQueue();

        $this->assertMailCount(3);

        $this->assertGenerationOfOrderLists('2019'.DS.'10', [0,1,2,3], [4,5,6,7]);

    }

    public function testSendOrderListsWithEmptyIndividualSendOrderListDay()
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $this->changeProductDeliveryRhythm($productId, '0-individual', '2018-01-12', '2018-01-01', '', '');

        $cronjobRunDay = '2018-01-30';
        $orderDetailId = 1;

        // run cronjob and assert no changings
        $this->exec('send_order_lists ' . $cronjobRunDay);
        $this->runAndAssertQueue();

        $this->assertOrderDetailState($orderDetailId, OrderDetail::STATE_OPEN);
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
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeQuantity([
            [$productId1 => $newProductData],
            [$productId2 => $newProductData]
        ]);
        $cronjobRunDay = '2018-01-31';
        $this->exec('send_order_lists ' . $cronjobRunDay);
        $this->runAndAssertQueue();

        $product1 = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId1,
            ],
            contain: [
                'StockAvailables',
            ]
        )->first();
        $this->assertEquals($defaultQuantity, $product1->stock_available->default_quantity_after_sending_order_lists);
        $this->assertEquals($defaultQuantity, $product1->stock_available->quantity);

        $product2 = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productsTable->getProductIdAndAttributeId($productId2)['productId'],
            ],
            contain: [
                'ProductAttributes.StockAvailables',
            ]
        )->first();
        $this->assertEquals($defaultQuantity, $product2->product_attributes[0]->stock_available->default_quantity_after_sending_order_lists);
        $this->assertEquals($defaultQuantity, $product2->product_attributes[0]->stock_available->quantity);
    }

    public function testSendOrderListWithoutStockProducts()
    {

        $stockProductId = 346;
        $productsTable = $this->getTableLocator()->get('Products');
        $this->changeManufacturer(5, 'stock_management_enabled', 1);
        $this->changeManufacturer(5, 'include_stock_products_in_order_lists', 0);
        $productsTable->changeIsStockProduct([[$stockProductId => true]]);

        $cronjobRunDay = '2018-01-31';
        $this->exec('send_order_lists ' . $cronjobRunDay);
        $this->runAndAssertQueue();

        $this->assertOrderDetailState(1, OrderDetail::STATE_OPEN);
        $this->assertOrderDetailState(2, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);
        $this->assertOrderDetailState(3, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);

    }

    public function testContentOfOrderListWithoutPricePerUnitAnonymized()
    {
        $this->changeManufacturer(4, 'anonymize_customers', 1);
        $this->loginAsSuperadmin();
        $this->get('/admin/manufacturers/getOrderListByProduct.pdf?manufacturerId=4&pickupDay=02.02.2018&isAnonymized=1&outputType=html');
        $this->assertResponseContains('D.S. - ID 92');
        $this->assertResponseNotContains('Demo Superadmin');
        $this->get('/admin/manufacturers/getOrderListByCustomer.pdf?manufacturerId=4&pickupDay=02.02.2018&isAnonymized=1&outputType=html');
        $this->assertResponseContains('D.S. - ID 92');
        $this->assertResponseNotContains('Demo Superadmin');
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

    public function testContentOfOrderListWithoutPricePerUnitAndPurchasePriceEnabled()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();

        $this->get('/admin/manufacturers/getOrderListByProduct.pdf?manufacturerId=4&pickupDay=02.02.2018&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'orderListByProductWithoutPricePerUnitAndPurchasePriceEnabled.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);

        $this->get('/admin/manufacturers/getOrderListByCustomer.pdf?manufacturerId=4&pickupDay=02.02.2018&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'orderListByCustomerWithoutPricePerUnitAndPurchasePriceEnabled.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);

    }

    public function testSendOrderListWithCustomerCanSelectPickupDay()
    {

        $this->changeConfiguration('FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY', 1);

        $this->loginAsSuperadmin();

        $orderDetailId = 1;

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get($orderDetailId),
                [
                    'pickup_day' => '2020-08-05',
                ]
            )
        );

        $cronjobRunDay = '2020-08-05';
        $this->exec('send_order_lists ' . $cronjobRunDay);
        $this->runAndAssertQueue();

        $this->assertOrderDetailState($orderDetailId, OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER);

        $this->assertMailCount(1);
        $this->assertMailSubjectContainsAt(0, 'Bestellungen für den 05.08.2020');
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
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');

        foreach([$cartA, $cartB] as $cart) {
            foreach($cart->cart_products as $cartProduct) {
                $orderDetailId = $cartProduct->order_detail->id_order_detail;
                $orderDetailsTable->save(
                    $orderDetailsTable->patchEntity(
                        $orderDetailsTable->get($orderDetailId),
                        [
                            'pickup_day' => new Date($pickupDay),
                            'created' => new Date('2020-11-05'),
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
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $newOrderDetail = $orderDetailsTable->find('all',
            conditions: [
                'OrderDetails.id_order_detail' => $orderDetailId,
            ],
        )->first();
        $this->assertEquals($expectedOrderState, $newOrderDetail->order_state);
    }

    private function assertGenerationOfOrderLists(string $datePath, array $clearText, array $anonymous)
    {
        $path = realpath(Configure::read('app.folder_order_lists') . DS . $datePath);
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);

        $files = [];
        foreach ($objects as $name => $object) {
            if (!preg_match('/\.pdf$/', $name)) {
                continue;
            }
            $files[] = str_replace(Configure::read('app.folder_order_lists'), '', $object->getPathName());
        }
        sort($files);

        $this->assertEquals(count($clearText) + count($anonymous), count($files));
        foreach($clearText as $clearTextIndex) {
            $this->assertDoesNotMatchRegularExpression('/anonymized/', $files[$clearTextIndex]);
        }
        foreach($anonymous as $anonymousIndex) {
            $this->assertMatchesRegularExpression('/anonymized/', $files[$anonymousIndex]);
        }
    }

}
