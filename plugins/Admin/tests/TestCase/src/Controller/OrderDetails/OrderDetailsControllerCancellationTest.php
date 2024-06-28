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
use Cake\Datasource\FactoryLocator;

class OrderDetailsControllerCancellationTest extends OrderDetailsControllerTestCase
{

    protected $OrderDetail;
    protected $Product;
    public $cancellationReason = 'Product was not fresh any more.';

    public function testCancellationWithPurchasePrice()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToCart(346, 3);
        $this->finishCart();
        $orderDetailId = 4;
        $this->deleteAndAssertRemoveFromDatabase([$orderDetailId]);

        $changedOrderDetailPurchasePrices = $this->OrderDetail->OrderDetailPurchasePrices->find('all',
            conditions: [
                'OrderDetailPurchasePrices.id_order_detail IN' => [$orderDetailId],
            ],
        )->toArray();
        $this->assertEmpty($changedOrderDetailPurchasePrices);
    }

    public function testCancellationAsManufacturer()
    {
        $this->loginAsVegetableManufacturer();

        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails);

        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationAsSuperadminWithEnabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails);

        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationAsSuperadminWithEnabledNotificationAfterOrderListsWereSent()
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

    public function testCancellationAsSuperadminWithDisabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->simulateSendOrderListsCronjob($this->orderDetailIdA);

        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_deleted_notification', 0);

        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails);

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

    public function testCancellationStockProductWithPricePerWeightUseAmountAsWeight()
    {
        $productId = 351;
        $unitsTable = $this->getTableLocator()->get('Units');
        $unitEntityA = $unitsTable->get(8);
        $unitEntityA->use_weight_as_amount = 1;
        $unitsTable->save($unitEntityA);

        $this->loginAsSuperadmin();
        $this->addProductToCart($productId, 1);
        $this->finishCart(1, 1);

        $this->assertChangedStockAvailable($productId, 998);
        $this->deleteAndAssertRemoveFromDatabase([4]);

        $this->assertChangedStockAvailable($productId, 999);

    }

    private function deleteAndAssertRemoveFromDatabase($orderDetailIds)
    {
        $this->deleteOrderDetail($orderDetailIds, $this->cancellationReason);
        $orderDetails = $this->getOrderDetailsFromDatabase($orderDetailIds);
        $this->assertEmpty($orderDetails);
    }

    private function assertOrderDetailDeletedEmails($emailIndex, $expectedToEmails)
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