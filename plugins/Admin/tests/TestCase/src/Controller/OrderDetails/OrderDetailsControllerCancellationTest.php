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

    public string $cancellationReason = 'Product was not fresh any more.';

    public function testCancellationWithPurchasePrice(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToCart(346, 3);
        $this->finishCart();
        $orderDetailId = 4;
        $this->deleteAndAssertRemoveFromDatabase([$orderDetailId]);

        $orderDetailsPurchasePricesTable = $this->getTableLocator()->get('OrderDetailPurchasePrices');
        $changedOrderDetailPurchasePrices = $orderDetailsPurchasePricesTable->find('all',
            conditions: [
                'OrderDetailPurchasePrices.id_order_detail IN' => [$orderDetailId],
            ],
        )->toArray();
        $this->assertEmpty($changedOrderDetailPurchasePrices);
    }

    public function testCancellationAsManufacturer(): void
    {
        $this->loginAsVegetableManufacturer();

        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails);

        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationAsSuperadminWithEnabledNotification(): void
    {
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails);

        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationAsSuperadminWithEnabledNotificationAfterOrderListsWereSent(): void
    {
        $this->changeManufacturer(5, 'anonymize_customers', 1);
        $this->loginAsSuperadmin();
        $this->simulateSendOrderListsCronjob($this->orderDetailIdA);

        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails);

        $expectedToEmails = [Configure::read('test.loginEmailVegetableManufacturer')];
        $this->assertOrderDetailDeletedEmails(1, $expectedToEmails);
        $this->assertMailContainsHtmlAt(1, 'Hallo Demo Gemüse-Hersteller');
        $this->assertMailContainsHtmlAt(1, 'D.S. - ID 92');

        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationAsSuperadminWithDisabledNotification(): void
    {
        $this->loginAsSuperadmin();
        $this->simulateSendOrderListsCronjob($this->orderDetailIdA);

        $customersTable = $this->getTableLocator()->get('Customers');
        $manufacturerId = $customersTable->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_deleted_notification', 0);

        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails);

        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationProductAttributeStockAvailableAsSuperadmin(): void
    {
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdC]);
        $this->assertChangedStockAvailable($this->productIdC, 20);
    }

    public function testCancellationStockAvailableAlwaysAvailableAsSuperadminAttribute(): void
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeQuantity([[$this->productIdC => [
            'always_available' => 1,
            'quantity' => 10,
        ]]]);
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdC]);
        $this->assertChangedStockAvailable($this->productIdC, 10);
    }

    public function testCancellationStockAvailableAlwaysAvailableAsSuperadminProduct(): void
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeQuantity([[$this->productIdA => [
            'always_available' => 1,
            'quantity' => 10,
        ]]]);
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);
        $this->assertChangedStockAvailable($this->productIdA, 10);
    }

    public function testCancellationStockAvailableDefaultQuantityAfterSendingOrderListsAsSuperadminProduct(): void
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeQuantity([[$this->productIdA => [
            'always_available' => 0,
            'quantity' => 10,
            'default_quantity_after_sending_order_lists' => 10,
        ]]]);
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);
        $this->assertChangedStockAvailable($this->productIdA, 10);
    }

    public function testCancellationStockAvailableDefaultQuantityAfterSendingOrderListsAsSuperadminAttribute(): void
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeQuantity([[$this->productIdC => [
            'always_available' => 0,
            'quantity' => 10,
            'default_quantity_after_sending_order_lists' => 10,
        ]]]);
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdC]);
        $this->assertChangedStockAvailable($this->productIdC, 10);
    }

    public function testCancellationStockProductWithPricePerWeightUseWeightAsAmount(): void
    {
        $productId = 351;
        $unitsTable = $this->getTableLocator()->get('Units');
        $unitEntityA = $unitsTable->get(8);
        $data = [
            'use_weight_as_amount' => 1,
            'quantity_in_units' => 5.3,
        ];
        $patchedEntity = $unitsTable->patchEntity($unitEntityA, $data);
        $unitsTable->save($patchedEntity);

        $this->loginAsSuperadmin();
        $this->addProductToCart($productId, 2);
        $this->finishCart(1, 1);

        $this->assertChangedStockAvailable($productId, 988.4);
        $this->deleteAndAssertRemoveFromDatabase([4]);
        $this->assertChangedStockAvailable($productId, 999);

        $this->runAndAssertQueue();
        $this->assertMailContainsAt(1, 'Menge: <b>10,6 kg</b>');

    }

    private function deleteAndAssertRemoveFromDatabase($orderDetailIds): void
    {
        $this->deleteOrderDetail($orderDetailIds, $this->cancellationReason);
        $orderDetails = $this->getOrderDetailsFromDatabase($orderDetailIds);
        $this->assertEmpty($orderDetails);
    }

    private function assertOrderDetailDeletedEmails($emailIndex, $expectedToEmails): void
    {

        $this->runAndAssertQueue();

        $this->assertMailSubjectContainsAt($emailIndex, 'Produkt storniert: Artischocke : Stück');

        foreach($expectedToEmails as $expectedToEmail) {
            $this->assertMailSentToAt($emailIndex, $expectedToEmail);
        }

        $this->assertMailContainsHtmlAt($emailIndex, $this->cancellationReason);
        $this->assertMailContainsHtmlAt($emailIndex, '1,82');
        $this->assertMailContainsHtmlAt($emailIndex, 'Demo Gemüse-Hersteller');

    }

    private function deleteOrderDetail($orderDetailIds, $cancellationReason): void
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