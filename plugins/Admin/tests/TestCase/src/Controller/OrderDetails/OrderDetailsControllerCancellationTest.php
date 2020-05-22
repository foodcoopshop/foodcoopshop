<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\TestCase\OrderDetailsControllerTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class OrderDetailsControllerCancellationTest extends OrderDetailsControllerTestCase
{

    public $cancellationReason = 'Product was not fresh any more.';

    public function testCancellationAsManufacturer()
    {
        $this->loginAsVegetableManufacturer();

        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationAsSuperadminWithEnabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationAsSuperadminWithEnabledNotificationAfterOrderListsWereSent()
    {
        $this->loginAsSuperadmin();
        $this->simulateSendOrderListsCronjob($this->orderDetailIdA);

        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [
            Configure::read('test.loginEmailVegetableManufacturer')
        ];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationAsSuperadminWithDisabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->simulateSendOrderListsCronjob($this->orderDetailIdA);

        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_deleted_notification', 0);

        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationProductAttributeStockAvailableAsSuperadmin()
    {
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdC]);
        $this->assertChangedStockAvailable($this->productIdC, 20);
    }

    public function testCancellationStockAvailableAlwaysAvailableAsSuperadminAttribute()
    {
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $this->Product->changeQuantity([[$this->productIdC => [
            'always_available' => 1,
            'quantity' => 10,
        ]]]);
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdC]);
        $this->assertChangedStockAvailable($this->productIdC, 10);
    }

    public function testCancellationStockAvailableAlwaysAvailableAsSuperadminProduct()
    {
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $this->Product->changeQuantity([[$this->productIdA => [
            'always_available' => 1,
            'quantity' => 10,
        ]]]);
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);
        $this->assertChangedStockAvailable($this->productIdA, 10);
    }

    public function testCancellationWithTimebasedCurrency()
    {
        $cart = $this->prepareTimebasedCurrencyCart();
        $orderDetailId = $cart->cart_products[1]->order_detail->id_order_detail;
        $this->deleteAndAssertRemoveFromDatabase([$orderDetailId]);

        // assert if record TimebasedCurrencyOrderDetail was removed
        $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
        $timebasedCurrencyOrderDetail = $this->TimebasedCurrencyOrderDetail->find('all', [
            'conditions' => [
                'TimebasedCurrencyOrderDetails.id_order_detail' => $orderDetailId
            ]
        ]);
        $this->assertEquals(0, $timebasedCurrencyOrderDetail->count());
    }

    private function deleteAndAssertRemoveFromDatabase($orderDetailIds)
    {
        $this->deleteOrderDetail($orderDetailIds, $this->cancellationReason);
        $orderDetails = $this->getOrderDetailsFromDatabase($orderDetailIds);
        $this->assertEmpty($orderDetails, 'order detail was not deleted properly');
    }

    private function assertOrderDetailDeletedEmails($emailLogIndex, $expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[$emailLogIndex], 'Produkt storniert: Artischocke : Stück', [$this->cancellationReason, '1,82', 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
    }

    private function deleteOrderDetail($orderDetailIds, $cancellationReason)
    {
        $this->httpClient->post(
            '/admin/order-details/delete/',
            [
                'orderDetailIds' => $orderDetailIds,
                'cancellationReason' => $cancellationReason
            ]
        );
    }


}