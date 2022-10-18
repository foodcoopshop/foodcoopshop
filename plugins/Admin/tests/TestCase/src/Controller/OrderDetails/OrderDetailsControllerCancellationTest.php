<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\TestCase\OrderDetailsControllerTestCase;
use Cake\Core\Configure;

class OrderDetailsControllerCancellationTest extends OrderDetailsControllerTestCase
{

    public $cancellationReason = 'Product was not fresh any more.';

    public function testCancellationWithPurchasePrice()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToCart(346, 3);
        $this->finishCart();
        $orderDetailId = 4;
        $this->deleteAndAssertRemoveFromDatabase([$orderDetailId]);

        $changedOrderDetailPurchasePrices = $this->OrderDetail->OrderDetailPurchasePrices->find('all', [
            'conditions' => [
                'OrderDetailPurchasePrices.id_order_detail IN' => [$orderDetailId],
            ],
        ])->toArray();
        $this->assertEmpty($changedOrderDetailPurchasePrices);
    }

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
        $this->Product = $this->getTableLocator()->get('Products');
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
        $this->Product = $this->getTableLocator()->get('Products');
        $this->Product->changeQuantity([[$this->productIdA => [
            'always_available' => 1,
            'quantity' => 10,
        ]]]);
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);
        $this->assertChangedStockAvailable($this->productIdA, 10);
    }

    public function testCancellationStockAvailableDefaultQuantityAfterSendingOrderListsAsSuperadminProduct()
    {
        $this->Product = $this->getTableLocator()->get('Products');
        $this->Product->changeQuantity([[$this->productIdA => [
            'always_available' => 0,
            'quantity' => 10,
            'default_quantity_after_sending_order_lists' => 10,
        ]]]);
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);
        $this->assertChangedStockAvailable($this->productIdA, 10);
    }

    public function testCancellationStockAvailableDefaultQuantityAfterSendingOrderListsAsSuperadminAttribute()
    {
        $this->Product = $this->getTableLocator()->get('Products');
        $this->Product->changeQuantity([[$this->productIdC => [
            'always_available' => 0,
            'quantity' => 10,
            'default_quantity_after_sending_order_lists' => 10,
        ]]]);
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdC]);
        $this->assertChangedStockAvailable($this->productIdC, 10);
    }

    private function deleteAndAssertRemoveFromDatabase($orderDetailIds)
    {
        $this->deleteOrderDetail($orderDetailIds, $this->cancellationReason);
        $orderDetails = $this->getOrderDetailsFromDatabase($orderDetailIds);
        $this->assertEmpty($orderDetails);
    }

    private function assertOrderDetailDeletedEmails($emailIndex, $expectedToEmails, $expectedCcEmails)
    {

        $this->runAndAssertQueue();

        $this->assertMailSubjectContainsAt($emailIndex, 'Produkt storniert: Artischocke : Stück');

        foreach($expectedToEmails as $expectedToEmail) {
            $this->assertMailSentToAt($emailIndex, $expectedToEmail);
        }
        foreach($expectedCcEmails as $expectedCcEmail) {
            $this->assertMailSentWithAt($emailIndex, $expectedCcEmail, 'cc');
        }

        $this->assertMailContainsHtmlAt($emailIndex, $this->cancellationReason);
        $this->assertMailContainsHtmlAt($emailIndex, '1,82');
        $this->assertMailContainsHtmlAt($emailIndex, 'Demo Gemüse-Hersteller');

    }

    private function deleteOrderDetail($orderDetailIds, $cancellationReason)
    {
        $this->ajaxPost(
            '/admin/order-details/delete/',
            [
                'orderDetailIds' => $orderDetailIds,
                'cancellationReason' => $cancellationReason
            ]
        );
    }


}