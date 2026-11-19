<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Model\Entity\OrderDetail;
use App\Services\DeliveryRhythmService;
use App\Services\PickupDayExceptionService;
use App\Test\Fixture\ProductsFixture;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\LoginTrait;
use App\View\Helper\MyTimeHelper;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Configure;
use Cake\I18n\Date;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * test config: pickup day friday, order lists are sent on wednesday (christmas 2026 and new year 2027 are fridays)
 */
class PickupDayExceptionServiceTest extends AppCakeTestCase
{

    use ConsoleIntegrationTestTrait;
    use EmailTrait;
    use IntegrationTestTrait;
    use LoginTrait;

    private function prepareChristmasExceptions(): void
    {
        $this->changeConfiguration(PickupDayExceptionService::CONFIGURATION_NAME, (string) json_encode([
            ['original_pickup_day' => '2026-12-25', 'pickup_day' => '2026-12-24', 'order_possible_until' => '2026-12-21'],
            ['original_pickup_day' => '2027-01-01', 'pickup_day' => '2026-12-31', 'order_possible_until' => '2026-12-28'],
        ]));
    }

    /**
     * @param array<string, mixed> $productData
     * @param array<string, string> $expected currentDay => pickupDay
     */
    private function assertNextPickupDays(array $productData, array $expected): void
    {
        $product = $this->getTableLocator()->get('Products')->newEntity(array_merge([
            'delivery_rhythm_type' => 'week',
            'delivery_rhythm_count' => '1',
            'delivery_rhythm_send_order_list_weekday' => MyTimeHelper::WEDNESDAY,
            'is_stock_product' => '0',
            'manufacturer' => ['stock_management_enabled' => '0'],
        ], $productData));
        foreach($expected as $currentDay => $pickupDay) {
            $this->assertEquals($pickupDay, (new DeliveryRhythmService())->getNextPickupDayForProduct($product, $currentDay), $currentDay);
        }
    }

    public function testNextPickupDay(): void
    {
        $this->prepareChristmasExceptions();
        $this->assertNextPickupDays([], [
            '2026-12-15' => '2026-12-18',
            '2026-12-16' => '2026-12-24',
            '2026-12-21 23:59:59' => '2026-12-24',
            '2026-12-22' => '2026-12-31',
            '2026-12-29' => '2027-01-08',
        ]);
        // earlier order deadline (sunday) is kept
        $this->assertNextPickupDays(['delivery_rhythm_send_order_list_weekday' => MyTimeHelper::MONDAY], [
            '2026-12-20' => '2026-12-24',
            '2026-12-21' => '2026-12-31',
        ]);
        $this->assertNextPickupDays(['delivery_rhythm_type' => 'month', 'delivery_rhythm_count' => '0'], [
            '2026-12-21' => '2026-12-24',
            '2026-12-22' => '2027-01-29',
        ]);
        // "Sammelbestellungen" are not moved
        $this->assertNextPickupDays(['delivery_rhythm_type' => 'individual', 'delivery_rhythm_count' => '0', 'delivery_rhythm_first_delivery_day' => new Date('2027-01-01')], [
            '2026-12-21' => '2027-01-01',
        ]);
    }

    public function testDeliveryDayAndLastOrderDay(): void
    {
        $this->prepareChristmasExceptions();
        $deliveryRhythmService = new DeliveryRhythmService();
        $this->assertEquals('2026-12-24', date('Y-m-d', $deliveryRhythmService->getDeliveryDayWithPickupDayExceptions((int) strtotime('2026-12-21'))));
        $this->assertEquals('2026-12-31', date('Y-m-d', $deliveryRhythmService->getDeliveryDayWithPickupDayExceptions((int) strtotime('2026-12-22'))));
        $this->assertEquals('2026-12-21', $deliveryRhythmService->getLastOrderDay('2026-12-24', 'week', MyTimeHelper::WEDNESDAY, null));
        $this->assertEquals('2026-12-20', $deliveryRhythmService->getLastOrderDay('2026-12-24', 'week', MyTimeHelper::MONDAY, null));
    }

    public function testGetPickupDaysForSendingOrderLists(): void
    {
        $this->prepareChristmasExceptions();
        $expected = [
            '2026-12-16' => ['2026-12-18' => [MyTimeHelper::WEDNESDAY]],
            '2026-12-21' => ['2026-12-24' => [MyTimeHelper::MONDAY]],
            '2026-12-22' => ['2026-12-24' => [MyTimeHelper::TUESDAY, MyTimeHelper::WEDNESDAY, MyTimeHelper::THURSDAY]],
            '2026-12-23' => [],
            '2027-01-06' => ['2027-01-08' => [MyTimeHelper::WEDNESDAY]],
        ];
        foreach($expected as $cronjobRunDay => $result) {
            $this->assertEquals($result, (new DeliveryRhythmService())->getPickupDaysForSendingOrderLists($cronjobRunDay), $cronjobRunDay);
        }
    }

    public function testSendOrderLists(): void
    {
        $this->prepareChristmasExceptions();
        $this->loginAsSuperadmin();
        $this->addProductToCart(ProductsFixture::ID_ARTICHOKE, 1);
        $this->finishCart();
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->updateAll(['pickup_day' => '2026-12-24'], []);
        $orderDetailId = $orderDetailsTable->find()->orderByDesc('id_order_detail')->firstOrFail()->id_order_detail;

        $this->exec('send_order_lists 2026-12-21');
        $this->runAndAssertQueue();
        $this->assertEquals(OrderDetail::STATE_OPEN, $orderDetailsTable->get($orderDetailId)->order_state);

        $this->exec('send_order_lists 2026-12-22');
        $this->runAndAssertQueue();
        $this->assertEquals(OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER, $orderDetailsTable->get($orderDetailId)->order_state);
        $this->assertMailSubjectContainsAt(1, 'Bestellungen für den 24.12.2026');
    }

    public function testDeliveryBreakAndBulkOrderDeliveryDay(): void
    {
        $this->prepareChristmasExceptions();
        $productsTable = $this->getTableLocator()->get('Products');
        $this->assertTrue($productsTable->deliveryBreakGlobalEnabled('2026-12-25', '2026-12-24'));
        $this->assertFalse($productsTable->deliveryBreakGlobalEnabled('2026-12-18', '2026-12-24'));

        $product = $productsTable->newEntity([
            'delivery_rhythm_type' => 'individual',
            'delivery_rhythm_count' => 0,
            'delivery_rhythm_first_delivery_day' => '2026-12-24',
            'delivery_rhythm_order_possible_until' => '2026-12-18',
            'delivery_rhythm_send_order_list_day' => '',
        ], ['validate' => 'deliveryRhythm']);
        $this->assertEmpty($product->getError('delivery_rhythm_first_delivery_day'));
    }

    public function testValidateAndSave(): void
    {
        $service = new PickupDayExceptionService();
        $originalPickupDay = $service->addDays((new DeliveryRhythmService())->getDeliveryDateByCurrentDayForDb(), 28);
        $valid = ['original_pickup_day' => $originalPickupDay, 'pickup_day' => $service->addDays($originalPickupDay, -1), 'order_possible_until' => $service->addDays($originalPickupDay, -4)];
        $this->assertEquals([], $service->validate((string) json_encode([$valid])));
        $this->assertNotEmpty($service->validate((string) json_encode([array_merge($valid, ['pickup_day' => $service->addDays($originalPickupDay, -3)])])));
        $this->assertCount(1, $service->validate((string) json_encode([array_merge($valid, ['order_possible_until' => $service->addDays($originalPickupDay, -2)])])));
        $errors = $service->validate((string) json_encode([array_merge($valid, ['pickup_day' => '2000-01-01']), $valid]));
        $this->assertStringContainsString('Der reguläre Abholtag wurde bereits ausgewählt.', $errors[0]);
        $this->assertCount(1, $service->validate((string) json_encode([array_merge($valid, ['pickup_day' => ''])])));

        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', $originalPickupDay);
        $this->assertStringContainsString('Lieferpause für alle Hersteller', implode('', $service->validate((string) json_encode([$valid]))));
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', '');

        $this->loginAsSuperadmin();
        $emptyRow = ['original_pickup_day' => '', 'pickup_day' => '', 'order_possible_until' => ''];
        $this->post('/admin/configurations/edit/' . PickupDayExceptionService::CONFIGURATION_NAME, [
            'Configurations' => ['value' => [$valid, $emptyRow]],
            'referer' => '/',
        ]);
        $this->assertRedirect();
        $this->assertEquals(json_encode([$valid]), $this->getTableLocator()->get('Configurations')->get(PickupDayExceptionService::CONFIGURATION_NAME)->value);

        $this->get('/admin/configurations/edit/' . PickupDayExceptionService::CONFIGURATION_NAME);
        $this->assertResponseOk();
        $this->assertResponseContains('Ausnahme hinzufügen');
    }

    public function testProductsWithChangedOrderDeadline(): void
    {
        $this->prepareChristmasExceptions();
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->updateAll(['delivery_rhythm_send_order_list_weekday' => MyTimeHelper::THURSDAY], ['id_product' => ProductsFixture::ID_ARTICHOKE]);
        $exception = (new PickupDayExceptionService())->getExceptions()[0];
        $result = (new PickupDayExceptionService())->getProductsWithChangedOrderDeadline($exception);
        $this->assertEquals([[
            'manufacturer_name' => 'Demo Gemüse-Hersteller',
            'product_count' => 1,
            'regular_last_order_day' => '2026-12-23',
            'new_last_order_day' => '2026-12-21',
        ]], $result);

        // earlier order deadline is not changed
        $productsTable->updateAll(['delivery_rhythm_send_order_list_weekday' => MyTimeHelper::MONDAY], ['id_product' => ProductsFixture::ID_ARTICHOKE]);
        $this->assertEquals([], (new PickupDayExceptionService())->getProductsWithChangedOrderDeadline($exception));
    }

    public function testBulkOrdersInfo(): void
    {
        $this->getTableLocator()->get('Products')->updateAll([
            'delivery_rhythm_type' => 'individual',
            'delivery_rhythm_count' => 0,
            'delivery_rhythm_first_delivery_day' => '2026-12-25',
        ], ['id_product' => ProductsFixture::ID_ARTICHOKE]);
        $this->getTableLocator()->get('OrderDetails')->updateAll(['pickup_day' => '2026-12-25'], ['product_id' => ProductsFixture::ID_ARTICHOKE]);
        $bulkOrders = (new PickupDayExceptionService())->getBulkOrders('2026-12-25');
        $this->assertCount(1, $bulkOrders);
        $this->assertEquals('Demo Gemüse-Hersteller', $bulkOrders[0]['manufacturer_name']);
        $this->assertEquals(1, $bulkOrders[0]['product_count']);
        $this->assertEquals(1, $bulkOrders[0]['ordered_amount']);
        $this->assertEquals(1.82, $bulkOrders[0]['order_value']);
        $this->assertEquals([], (new PickupDayExceptionService())->getBulkOrders('2026-12-18'));
    }

    public function testNotAvailableIfCustomerCanSelectPickupDay(): void
    {
        $this->prepareChristmasExceptions();
        $this->changeConfiguration('FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY', 1);
        $this->assertEquals([], (new PickupDayExceptionService())->getExceptions());
        $this->assertNextPickupDays([], ['2026-12-21' => '2026-12-25']);
        $this->loginAsSuperadmin();
        $this->get('/admin/configurations');
        $this->assertResponseOk();
        $this->assertResponseNotContains('Abholtag-Ausnahmen');
        $this->get('/admin/configurations/edit/' . PickupDayExceptionService::CONFIGURATION_NAME);
        $this->assertResponseCode(404);
    }

    public function testMovedPickupDayIsShownInCatalogAndCart(): void
    {
        $service = new PickupDayExceptionService();
        $originalPickupDay = (new DeliveryRhythmService())->getDeliveryDateByCurrentDayForDb();
        $this->changeConfiguration(PickupDayExceptionService::CONFIGURATION_NAME, (string) json_encode([[
            'original_pickup_day' => $originalPickupDay,
            'pickup_day' => $service->addDays($originalPickupDay, -1),
            'order_possible_until' => $service->addDays($originalPickupDay, -3),
        ]]));
        $this->loginAsCustomer();
        $this->get($this->Slug->getProductDetail(ProductsFixture::ID_ARTICHOKE, 'Artischocke'));
        $this->assertResponseContains('Abholtag verschoben (regulär: ' . Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($originalPickupDay)) . ')');
        $this->assertResponseContains('Cart.movedPickupDayInfos');
    }

}
